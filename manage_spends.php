<?php
include 'includes/header.php';

// ── Initialize form values ────────────────────────────────────────────────────
$rid          = '';
$title        = '';
$amount       = '';
$date         = date('Y-m-d');
$des          = '';
$fund_req_id  = isset($_GET['fund_request_id']) && is_numeric($_GET['fund_request_id'])
                  ? (int)$_GET['fund_request_id'] : 0;
$direct       = false;
$is_edit      = false;
$alert_msg    = '';
$alert_type   = '';

// Which approvals this person can still draw against. Nothing can be logged
// without one — that is the whole point of the approval flow.
$sp         = json_decode(get_api_data("$api_url/funds/fund_requests?spendable=1&" . actor_qs()), true);
$approvals  = is_array($sp['data'] ?? null) ? $sp['data'] : [];

// ── Handle DELETE ─────────────────────────────────────────────────────────────
if (isset($_GET['rid']) && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $del_rid = $_GET['rid'];
    $resp    = get_api_data("$api_url/logs/manage_spends?rid=" . urlencode($del_rid)
                            . "&action=delete&" . actor_qs());
    $resp    = json_decode($resp, true);

    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not delete the record.');
    redirect('spends');
}

// ── Handle POST (insert / update) ────────────────────────────────────────────
if (isset($_POST['_submit'])) {
    $post_data = with_actor([
        'rid'             => $_POST['rid']             ?? '',
        'title'           => $_POST['title']           ?? '',
        'amt'             => $_POST['amt']             ?? '',
        'date'            => $_POST['date']            ?? '',
        'des'             => $_POST['des']             ?? '',
        'fund_request_id' => $_POST['fund_request_id'] ?? '',
        'direct'          => (is_admin() && !empty($_POST['direct'])) ? '1' : '',
    ]);

    $resp = get_api_data_post("$api_url/logs/manage_spends", $post_data);
    $resp = json_decode($resp, true);

    if (($resp['status'] ?? '') === 'success') {
        set_flash('success', $resp['message'] ?? 'Expenditure recorded.');
        redirect('spends');
    }

    $alert_msg   = $resp['message'] ?? 'Unknown error';
    $alert_type  = 'danger';

    $rid         = $_POST['rid']   ?? '';
    $title       = $_POST['title'] ?? '';
    $amount      = $_POST['amt']   ?? '';
    $date        = $_POST['date']  ?? date('Y-m-d');
    $des         = $_POST['des']   ?? '';
    $fund_req_id = (int)($_POST['fund_request_id'] ?? 0);
    $direct      = !empty($_POST['direct']);
    $is_edit     = $rid !== '';
}

// ── Handle EDIT (load existing record) ───────────────────────────────────────
if (isset($_GET['rid']) && !isset($_GET['action']) && $alert_msg === '') {
    $rid  = $_GET['rid'];
    $data = get_api_data("$api_url/logs/spends?rid=" . urlencode($rid));
    $data = json_decode($data, true);

    if (!empty($data['data'][0])) {
        $row         = $data['data'][0];
        $rid         = $row['id'];
        $title       = $row['title']  ?? '';
        $amount      = $row['amount'] ?? '';
        $date        = $row['date']   ?? date('Y-m-d');
        $des         = $row['des']    ?? '';
        $fund_req_id = (int)($row['fund_request_id'] ?? 0);
        $direct      = ($row['entry_source'] ?? '') !== 'approval';
        $is_edit     = true;

        // Rows logged before the approval flow, and admin direct entries, have
        // no approval to attach. Keep them on the direct route when editing.
        if ($fund_req_id === 0) $direct = true;
    }
}

// A coordinator with nothing approved has nowhere to put a spend.
$blocked = empty($approvals) && !is_admin() && !$is_edit;

$page_title = $is_edit ? 'Edit Expenditure' : 'Log New Expenditure';
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-cash-multiple"></i>
                </span>
                <?= $page_title ?>
            </h3>
            <nav aria-label="breadcrumb">
                <a href="spends" class="btn btn-sm btn-light border">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to List
                </a>
            </nav>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8 grid-margin stretch-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <?php if ($alert_msg): ?>
                            <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
                                <?= h($alert_msg) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($blocked): ?>
                            <!-- Nothing approved, so there is nothing to spend against. -->
                            <div class="stm-empty">
                                <i class="mdi mdi-shield-lock-outline"></i>
                                <p>You have no approved funds to spend against.</p>
                                <small>
                                    Every expenditure has to trace back to a fund request the treasurer
                                    has signed off. Raise one with the reason and the proof, and log the
                                    spend here once it is approved.
                                </small>
                                <div class="mt-4">
                                    <a href="manage_fund_request" class="btn btn-gradient-primary">
                                        <i class="mdi mdi-cash-usd me-1"></i>Raise a Fund Request
                                    </a>
                                    <a href="fund_requests" class="btn btn-light border ms-2">See My Requests</a>
                                </div>
                            </div>

                        <?php else: ?>

                            <form method="POST" action="manage_spends" class="forms-sample" id="spendForm">
                                <input type="hidden" name="_submit" value="1">
                                <input type="hidden" name="rid" value="<?= h($rid) ?>">

                                <?php if (is_admin()): ?>
                                    <!-- Admins keep a route for back-dated and historical records. -->
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                               name="direct" value="1" id="directToggle" <?= $direct ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold" for="directToggle">
                                            Direct entry — no approval behind it
                                        </label>
                                        <div class="text-muted" style="font-size:0.74rem;">
                                            For back-dated and historical records. The ledger marks these as direct entries.
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="form-group mb-3" id="approvalBlock">
                                    <label for="fund_request_id" class="form-label fw-bold">
                                        Approved Fund Request <span class="text-danger">*</span>
                                    </label>
                                    <select name="fund_request_id" id="fund_request_id" class="form-select">
                                        <option value="">Choose the approval this comes under…</option>
                                        <?php foreach ($approvals as $a): ?>
                                            <option value="<?= (int)$a['id'] ?>"
                                                    data-remaining="<?= h($a['remaining']) ?>"
                                                    <?= $fund_req_id == $a['id'] ? 'selected' : '' ?>>
                                                <?= h($a['request_no'] . ' — ' . $a['title']) ?>
                                                (<?= money($a['remaining']) ?> left<?= $a['raised_by_name'] && is_admin() ? ', ' . h($a['raised_by_name']) : '' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                        <?php if ($is_edit && $fund_req_id > 0
                                                  && !in_array($fund_req_id, array_map('intval', array_column($approvals, 'id')), true)): ?>
                                            <!-- The approval this row already settles, now fully drawn. -->
                                            <option value="<?= $fund_req_id ?>" selected>Keep the current approval (#<?= $fund_req_id ?>)</option>
                                        <?php endif; ?>
                                    </select>
                                    <small class="text-muted" style="font-size:0.74rem;" id="remainingNote">
                                        Only approvals with money still on them are listed.
                                    </small>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="title" class="form-label fw-bold">Title / Purpose <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" id="title" placeholder="What was this for?" value="<?= h($title) ?>" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="amt" class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" min="0.01" name="amt" class="form-control" id="amt" placeholder="0.00" value="<?= h($amount) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control" id="date" value="<?= h($date) ?>" required>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="des" class="form-label fw-bold">Detailed Description</label>
                                    <textarea name="des" class="form-control" id="des" rows="6" placeholder="Provide more context about this expenditure..."><?= h($des) ?></textarea>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="spends" class="btn btn-light me-md-2">Cancel</a>
                                    <button type="submit" class="btn btn-gradient-primary btn-lg px-5 fw-bold">
                                        <i class="mdi mdi-content-save me-1"></i>
                                        <?= $is_edit ? 'Update Changes' : 'Save Expenditure' ?>
                                    </button>
                                </div>
                            </form>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php if (!$blocked): ?>
<script>
(function () {
    "use strict";
    var select = document.getElementById('fund_request_id');
    var amt    = document.getElementById('amt');
    var note   = document.getElementById('remainingNote');
    var block  = document.getElementById('approvalBlock');
    var toggle = document.getElementById('directToggle');

    // Cap the amount at what is left on the chosen approval, so the figure is
    // corrected here rather than rejected after the round trip.
    function syncCap() {
        if (!select || !amt) return;
        var opt  = select.options[select.selectedIndex];
        var left = opt ? parseFloat(opt.getAttribute('data-remaining') || '') : NaN;

        if (!isNaN(left)) {
            amt.max = left;
            note.textContent = '₹' + left.toLocaleString('en-IN') + ' is still available on this approval.';
        } else {
            amt.removeAttribute('max');
            note.textContent = 'Only approvals with money still on them are listed.';
        }
    }

    // Direct entry has no approval, so hide the picker instead of leaving a
    // required field that cannot be satisfied.
    function syncMode() {
        if (!toggle || !block) return;
        var isDirect = toggle.checked;
        block.style.display = isDirect ? 'none' : '';
        if (select) select.required = !isDirect;
        if (isDirect && select) select.value = '';
        syncCap();
    }

    if (select) {
        select.required = !(toggle && toggle.checked);
        select.addEventListener('change', syncCap);
    }
    if (toggle) toggle.addEventListener('change', syncMode);

    syncMode();
    syncCap();
})();
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
