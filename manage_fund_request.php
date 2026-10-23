<?php
include 'includes/header.php';

// ── Form state ───────────────────────────────────────────────────────────────
$rid        = '';
$ticket_id  = isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
$title      = '';
$reason     = '';
$amount     = '';
$needed_by  = '';
$payee      = '';
$payee_det  = '';
$proof_link = '';
$existing   = [];
$is_edit    = false;
$alert_msg  = '';

// Open tickets to pin the request to. The board is already filtered to what
// still needs attention, which is the only thing worth funding.
$tk    = json_decode(get_api_data("$api_url/tickets/tickets?status=live&limit=200&" . actor_qs()), true);
$open_tickets = is_array($tk['data'] ?? null) ? $tk['data'] : [];

// ── Save ─────────────────────────────────────────────────────────────────────
if (isset($_POST['_submit'])) {
    $post = with_actor([
        'rid'           => $_POST['rid']           ?? '',
        'ticket_id'     => $_POST['ticket_id']     ?? '',
        'title'         => $_POST['title']         ?? '',
        'reason'        => $_POST['reason']        ?? '',
        'amount'        => $_POST['amount']        ?? '',
        'needed_by'     => $_POST['needed_by']     ?? '',
        'payee_name'    => $_POST['payee_name']    ?? '',
        'payee_details' => $_POST['payee_details'] ?? '',
        'proof_link'    => $_POST['proof_link']    ?? '',
    ]);

    $resp = post_api_data_files("$api_url/funds/manage_fund_request", $post, 'proof', $_FILES['proof'] ?? null);
    $resp = json_decode($resp, true);

    if (($resp['status'] ?? '') === 'success') {
        $msg = $resp['message'] ?? 'Request saved.';
        if (!empty($resp['warnings'])) $msg .= ' (' . implode(' ', $resp['warnings']) . ')';
        set_flash('success', $msg);
        redirect('fund_request?rid=' . (int)$resp['data']);
    }

    $alert_msg  = $resp['message'] ?? 'Could not save the request. Please try again.';
    $rid        = $_POST['rid']           ?? '';
    $ticket_id  = (int)($_POST['ticket_id'] ?? 0);
    $title      = $_POST['title']         ?? '';
    $reason     = $_POST['reason']        ?? '';
    $amount     = $_POST['amount']        ?? '';
    $needed_by  = $_POST['needed_by']     ?? '';
    $payee      = $_POST['payee_name']    ?? '';
    $payee_det  = $_POST['payee_details'] ?? '';
    $proof_link = $_POST['proof_link']    ?? '';
    $is_edit    = $rid !== '';
}

// ── Load for editing ─────────────────────────────────────────────────────────
if (isset($_GET['rid']) && is_numeric($_GET['rid']) && $alert_msg === '') {
    $resp = json_decode(get_api_data("$api_url/funds/fund_requests?rid=" . (int)$_GET['rid']), true);
    $row  = $resp['data'][0] ?? null;

    if (!$row) {
        set_flash('danger', 'That fund request no longer exists.');
        redirect('fund_requests');
    }

    if ($row['raised_by'] !== portal_uid() && !is_admin()) {
        set_flash('danger', 'You can only edit your own fund requests.');
        redirect('fund_request?rid=' . (int)$row['id']);
    }
    if ($row['status'] !== 'pending' && !is_admin()) {
        set_flash('danger', 'This request has already been reviewed. Raise a fresh one instead.');
        redirect('fund_request?rid=' . (int)$row['id']);
    }

    $rid        = $row['id'];
    $ticket_id  = (int)$row['ticket_id'];
    $title      = $row['title'];
    $reason     = $row['reason'];
    $amount     = $row['amount'];
    $needed_by  = $row['needed_by'];
    $payee      = $row['payee_name'];
    $payee_det  = $row['payee_details'];
    $proof_link = $row['proof_link'];
    $existing   = $resp['files'] ?? [];
    $is_edit    = true;

    // Keep the tied ticket selectable even once it has moved off the live board.
    if ($ticket_id && !in_array($ticket_id, array_map('intval', array_column($open_tickets, 'id')), true)) {
        $open_tickets[] = [
            'id'            => $ticket_id,
            'ticket_no'     => $row['ticket_no'],
            'title'         => $row['ticket_title'],
            'category_name' => $row['category_name'],
        ];
    }
}

// Pre-fill the title from the ticket when coming straight off one.
if (!$is_edit && $ticket_id && $title === '') {
    foreach ($open_tickets as $t) {
        if ((int)$t['id'] === $ticket_id) {
            $title = $t['title'];
            break;
        }
    }
}

$page_title = $is_edit ? 'Edit Fund Request' : 'Raise a Fund Request';
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="mdi mdi-cash-usd"></i></span>
      <?= $page_title ?>
    </h3>
    <nav aria-label="breadcrumb">
      <a href="<?= $is_edit ? 'fund_request?rid=' . (int)$rid : 'fund_requests' ?>" class="btn btn-sm btn-light border">
        <i class="mdi mdi-arrow-left me-1"></i> Back
      </a>
    </nav>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-8 grid-margin stretch-card">
      <div class="card shadow-sm">
        <div class="card-body">

          <?php if ($alert_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?= h($alert_msg) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <div class="gate-note mb-4">
            <i class="mdi mdi-information-outline"></i>
            <div>
              No money leaves STM without the treasurer signing it off. Set out clearly what the
              funds are for and attach a bill, quote or estimate — that is what the treasurer
              approves against. Once sanctioned, you can log the spend up to the approved amount.
            </div>
          </div>

          <form method="POST" action="manage_fund_request" class="forms-sample" enctype="multipart/form-data">
            <input type="hidden" name="_submit" value="1">
            <input type="hidden" name="rid" value="<?= h($rid) ?>">

            <div class="form-group mb-3">
              <label for="ticket_id" class="form-label fw-bold">Against Which Ticket</label>
              <select name="ticket_id" id="ticket_id" class="form-select">
                <option value="">Not tied to a ticket</option>
                <?php foreach ($open_tickets as $t): ?>
                  <option value="<?= (int)$t['id'] ?>" <?= $ticket_id == $t['id'] ? 'selected' : '' ?>>
                    <?= h(($t['ticket_no'] ?: '#' . $t['id']) . ' — ' . $t['title']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted" style="font-size:0.74rem;">
                Tying the request to a ticket keeps the money and the activity together in the record.
              </small>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="title" class="form-label fw-bold">What For <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control" maxlength="200" required
                       placeholder="e.g. Two weeks of dialysis charges" value="<?= h($title) ?>">
              </div>
              <div class="col-md-3 mb-3">
                <label for="amount" class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" step="0.01" min="1" name="amount" id="amount" class="form-control"
                         placeholder="0.00" required value="<?= h($amount) ?>">
                </div>
              </div>
              <div class="col-md-3 mb-3">
                <label for="needed_by" class="form-label fw-bold">Needed By</label>
                <input type="date" name="needed_by" id="needed_by" class="form-control" value="<?= h($needed_by) ?>">
              </div>
            </div>

            <div class="form-group mb-4">
              <label for="reason" class="form-label fw-bold">Reason <span class="text-danger">*</span></label>
              <textarea name="reason" id="reason" class="form-control" rows="5" required minlength="20"
                        placeholder="What the money is for, who it helps, how the figure was arrived at, and what has been verified."><?= h($reason) ?></textarea>
              <small class="text-muted" style="font-size:0.74rem;">
                A couple of sentences at least. <span id="reasonCount"></span>
              </small>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:0.74rem;letter-spacing:0.8px;">Payment</h6>
            <div class="row">
              <div class="col-md-5 mb-3">
                <label for="payee_name" class="form-label fw-bold">Pay To</label>
                <input type="text" name="payee_name" id="payee_name" class="form-control"
                       placeholder="Hospital, shop or person" value="<?= h($payee) ?>">
              </div>
              <div class="col-md-7 mb-3">
                <label for="payee_details" class="form-label fw-bold">UPI / Account Details</label>
                <input type="text" name="payee_details" id="payee_details" class="form-control" maxlength="255"
                       placeholder="UPI id, or account number with IFSC" value="<?= h($payee_det) ?>">
              </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-2 mt-2" style="font-size:0.74rem;letter-spacing:0.8px;">Proof</h6>
            <div class="form-group mb-3">
              <input type="file" name="proof[]" class="form-control" multiple
                     accept="image/jpeg,image/png,image/webp,image/heic,application/pdf">
              <small class="text-muted" style="font-size:0.74rem;">
                Bills, quotations, prescriptions, estimates. JPG, PNG, WebP, HEIC or PDF, up to 3 MB each.
              </small>

              <?php if (!empty($existing)): ?>
                <div class="proof-grid mt-3">
                  <?php foreach ($existing as $f): ?>
                    <a class="proof-item" target="_blank" href="<?= h(proof_url($api_url, $f['id'])) ?>">
                      <?php if ((int)$f['is_image'] === 1): ?>
                        <img src="<?= h(proof_url($api_url, $f['id'])) ?>" alt="<?= h($f['file_name']) ?>">
                      <?php else: ?>
                        <span class="proof-doc"><i class="mdi mdi-file-pdf-box"></i></span>
                      <?php endif; ?>
                      <span class="proof-name"><?= h($f['file_name']) ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="form-group mb-4">
              <label for="proof_link" class="form-label fw-bold">Or a Link</label>
              <input type="url" name="proof_link" id="proof_link" class="form-control" maxlength="500"
                     placeholder="https://… a Drive folder or shared document" value="<?= h($proof_link) ?>">
              <small class="text-muted" style="font-size:0.74rem;">Useful when the paperwork is too large to upload.</small>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
              <a href="<?= $is_edit ? 'fund_request?rid=' . (int)$rid : 'fund_requests' ?>" class="btn btn-light me-md-2">Cancel</a>
              <button type="submit" class="btn btn-gradient-primary btn-lg px-5 fw-bold">
                <i class="mdi mdi-send me-1"></i>
                <?= $is_edit ? 'Update Request' : 'Send to Treasurer' ?>
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

</div>

<script>
(function () {
    "use strict";
    // Show how close the reason is to the minimum, rather than letting the
    // form bounce back from the API.
    var box   = document.getElementById('reason');
    var count = document.getElementById('reasonCount');
    if (!box || !count) return;

    var tick = function () {
        var n = box.value.trim().length;
        count.textContent = n >= 20 ? '' : (20 - n) + ' more characters needed.';
        count.style.color = n >= 20 ? '' : '#b52a1a';
    };
    box.addEventListener('input', tick);
    tick();
})();
</script>

<?php include 'includes/footer.php'; ?>
</div>
