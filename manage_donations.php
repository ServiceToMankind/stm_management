<?php
include 'includes/header.php';

// ── Initialize form values ────────────────────────────────────────────────────
$rid            = '';
$uid            = '';
$donor_name     = '';
$donor_mail     = '';
$donor_mobile   = '';
$amount         = '';
$type           = '1';
$message        = '';
$txid           = '';
$payment_status = '1';
$added_on       = date('Y-m-d\TH:i');
$is_edit        = false;
$alert_msg      = '';
$alert_type     = '';

// ── Handle DELETE ─────────────────────────────────────────────────────────────
if (isset($_GET['rid']) && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $del_rid  = $_GET['rid'];
    $resp     = get_api_data("$api_url/logs/manage_donations?rid=" . urlencode($del_rid) . "&action=delete");
    $resp     = json_decode($resp, true);
    if (isset($resp['status']) && $resp['status'] === 'success') {
        echo "<script>window.location.href='donations'</script>";
        exit;
    }
}

// ── Handle POST (insert / update) ────────────────────────────────────────────
if (isset($_POST['_submit'])) {
    $post_data = [
        'rid'            => $_POST['rid']            ?? '',
        'uid'            => $_POST['uid']            ?? '',
        'name'           => $_POST['donor_name']     ?? '',
        'mail'           => $_POST['donor_mail']     ?? '',
        'mobile'         => $_POST['donor_mobile']   ?? '',
        'amount'         => $_POST['amount']         ?? '',
        'type'           => $_POST['type']           ?? '1',
        'message'        => $_POST['message']        ?? '',
        'txid'           => $_POST['txid']           ?? '',
        'payment_status' => $_POST['payment_status'] ?? '1',
        'custid'         => $_POST['uid']            ?? '',   // map uid → custid for API compat
        'added_on'       => $_POST['added_on']       ?? date('Y-m-d H:i:s'),
    ];

    // Convert datetime-local (Y-m-dTH:i) → MySQL format (Y-m-d H:i:s)
    if (!empty($post_data['added_on'])) {
        $post_data['added_on'] = str_replace('T', ' ', $post_data['added_on']) . ':00';
    }

    $resp = get_api_data_post("$api_url/logs/manage_donations", $post_data);
    $resp = json_decode($resp, true);

    if (isset($resp['status']) && $resp['status'] === 'success') {
        $target_rid = $post_data['rid'] !== '' ? $post_data['rid'] : ($resp['data'] ?? '');
        echo "<script>window.location.href='donations'</script>";
        exit;
    } else {
        $error_detail = isset($resp['message']) ? ': ' . $resp['message'] : '';
        if (!$resp) {
            $error_detail = ': No response from API or invalid JSON';
        }
        $alert_msg  = 'Error saving donation' . $error_detail;
        $alert_type = 'danger';
    }
}

// ── Handle EDIT (load existing record) ───────────────────────────────────────
if (isset($_GET['rid']) && !isset($_GET['action'])) {
    $rid  = $_GET['rid'];
    $url  = "$api_url/logs/donations?rid=" . urlencode($rid);
    $data = get_api_data($url);
    $data = json_decode($data, true);

    if (!empty($data['data'][0])) {
        $row            = $data['data'][0];
        $rid            = $row['id'];
        $uid            = $row['custid'] ?? '';
        $donor_name     = $row['name']   ?? '';
        $donor_mail     = $row['mail']   ?? '';
        $donor_mobile   = $row['mobile'] ?? '';
        $amount         = $row['amount'] ?? '';
        $type           = $row['type']   ?? '1';
        $message        = $row['message'] ?? '';
        $txid           = $row['txid']   ?? '';
        $payment_status = $row['payment_status'] ?? '1';
        // Convert MySQL datetime → datetime-local
        $added_on       = !empty($row['added_on'])
                          ? date('Y-m-d\TH:i', strtotime($row['added_on']))
                          : date('Y-m-d\TH:i');
        $is_edit        = true;
    }
}

$page_title = $is_edit ? 'Edit Donation' : 'Log New Donation';
$is_linked  = ($uid !== '');   // donor is a known user
?>

<!-- ── Page content ──────────────────────────────────────────────────────── -->
<div class="main-panel">
    <div class="content-wrapper">

        <!-- Page header -->
        <div class="page-header mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="page-title-icon bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:46px;height:46px;flex-shrink:0;">
                    <i class="mdi mdi-currency-inr fs-5"></i>
                </div>
                <div>
                    <h3 class="page-title mb-0"><?= htmlspecialchars($page_title) ?></h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size:.82rem;">
                            <li class="breadcrumb-item"><a href="donations">Donations</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($page_title) ?></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <?php if ($alert_msg): ?>
        <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($alert_msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                <div class="card shadow-sm border-0" style="border-radius:14px; overflow:hidden;">
                    <div class="card-header bg-gradient-danger text-white py-3 px-4" style="border-bottom:none;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="mdi mdi-file-document-edit-outline fs-5"></i>
                            <span class="fw-semibold"><?= htmlspecialchars($page_title) ?></span>
                            <?php if ($is_edit): ?>
                            <span class="badge bg-white text-danger ms-auto" style="font-size:.75rem;">Record #<?= htmlspecialchars($rid) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body p-4">

                        <form method="POST" action="manage_donations" id="donationForm" autocomplete="off">
                            <input type="hidden" name="_submit" value="1">
                            <input type="hidden" name="rid" value="<?= htmlspecialchars($rid) ?>">
                            <input type="hidden" name="uid" id="hiddenUid" value="<?= htmlspecialchars($uid) ?>">
                            <input type="hidden" name="donor_name"   id="hiddenName"   value="<?= htmlspecialchars($donor_name) ?>">
                            <input type="hidden" name="donor_mail"   id="hiddenMail"   value="<?= htmlspecialchars($donor_mail) ?>">
                            <input type="hidden" name="donor_mobile" id="hiddenMobile" value="<?= htmlspecialchars($donor_mobile) ?>">

                            <!-- ── SECTION 1: Donor ───────────────────────────────────────── -->
                            <p class="text-muted fw-semibold mb-3" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.07em;">
                                <i class="mdi mdi-account-circle-outline me-1"></i> Donor
                            </p>

                            <!-- Name search + autocomplete -->
                            <div class="mb-3 position-relative" id="nameSearchBlock">
                                <label class="form-label fw-semibold" for="donorNameInput">
                                    Donor Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="mdi mdi-magnify text-muted"></i>
                                    </span>
                                    <input type="text"
                                           id="donorNameInput"
                                           class="form-control border-start-0 ps-0"
                                           placeholder="Type donor name to search or add new…"
                                           value="<?= htmlspecialchars($donor_name) ?>"
                                           required>
                                </div>
                                <!-- Dropdown suggestions -->
                                <div id="donorSuggestions"
                                     class="position-absolute w-100 bg-white border rounded shadow-sm mt-1"
                                     style="z-index:1050;display:none;max-height:240px;overflow-y:auto;top:100%;">
                                </div>
                            </div>

                            <!-- Linked donor info card (shown after user selected from DB) -->
                            <div id="linkedDonorCard"
                                 class="mb-3 p-3 rounded-3 border"
                                 style="background:#f8f9ff;display:<?= $is_linked ? 'block' : 'none' ?>;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-bold text-dark" id="lcName"><?= htmlspecialchars($donor_name) ?></div>
                                        <div class="text-muted small mt-1">
                                            <i class="mdi mdi-email-outline me-1"></i><span id="lcMail"><?= htmlspecialchars($donor_mail) ?></span>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="mdi mdi-phone-outline me-1"></i><span id="lcMobile"><?= htmlspecialchars($donor_mobile) ?></span>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="mdi mdi-identifier me-1"></i><span id="lcUid"><?= htmlspecialchars($uid) ?></span>
                                        </div>
                                    </div>
                                    <button type="button" id="clearDonorBtn" class="btn btn-sm btn-outline-secondary" title="Change donor">
                                        <i class="mdi mdi-close"></i> Change
                                    </button>
                                </div>
                            </div>

                            <!-- New donor notice (shown when name is new & not from DB) -->
                            <div id="newDonorNotice" class="mb-3" style="display:none;">
                                <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-0" style="font-size:.87rem;">
                                    <i class="mdi mdi-alert-circle-outline fs-5"></i>
                                    <span>This name is not in the system. &nbsp;
                                        <button type="button" class="btn btn-sm btn-warning fw-semibold" id="openCreateDonorBtn">
                                            <i class="mdi mdi-account-plus me-1"></i>Create Donor Profile
                                        </button>
                                    </span>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- ── SECTION 2: Donation details ────────────────────────────── -->
                            <p class="text-muted fw-semibold mb-3" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.07em;">
                                <i class="mdi mdi-cash me-1"></i> Donation Details
                            </p>

                            <div class="row g-3">
                                <!-- Amount -->
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold" for="inputAmount">
                                        Amount (₹) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">₹</span>
                                        <input type="number" name="amount" id="inputAmount"
                                               class="form-control"
                                               value="<?= htmlspecialchars($amount) ?>"
                                               placeholder="0.00" min="1" step="0.01" required>
                                    </div>
                                </div>

                                <!-- Payment type -->
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold" for="inputType">Payment Mode</label>
                                    <select name="type" id="inputType" class="form-select">
                                        <option value="1" <?= $type == '1' ? 'selected' : '' ?>>💳 Online</option>
                                        <option value="2" <?= $type == '2' ? 'selected' : '' ?>>💵 Cash</option>
                                    </select>
                                </div>

                                <!-- Status -->
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold" for="inputStatus">Payment Status</label>
                                    <select name="payment_status" id="inputStatus" class="form-select">
                                        <option value="1" <?= $payment_status == '1' ? 'selected' : '' ?>>✅ Success</option>
                                        <option value="2" <?= $payment_status == '2' ? 'selected' : '' ?>>❌ Failed</option>
                                    </select>
                                </div>

                                <!-- Date -->
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold" for="inputDate">Date &amp; Time</label>
                                    <input type="datetime-local" name="added_on" id="inputDate"
                                           class="form-control"
                                           value="<?= htmlspecialchars($added_on) ?>">
                                </div>

                                <!-- Transaction ID (only shown for online) -->
                                <div class="col-12" id="txidBlock" style="display:<?= $type == '2' ? 'none' : 'block' ?>;">
                                    <label class="form-label fw-semibold" for="inputTxid">Transaction ID</label>
                                    <input type="text" name="txid" id="inputTxid"
                                           class="form-control font-monospace"
                                           value="<?= htmlspecialchars($txid) ?>"
                                           placeholder="e.g. TXN3291847ABCD">
                                </div>

                                <!-- Message -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold" for="inputMessage">Note / Message</label>
                                    <textarea name="message" id="inputMessage"
                                              class="form-control" rows="2"
                                              placeholder="Any remarks about this donation…"><?= htmlspecialchars($message) ?></textarea>
                                </div>
                            </div>

                            <!-- ── Actions ─────────────────────────────────────────────────── -->
                            <div class="d-flex align-items-center gap-2 mt-4">
                                <button type="submit" class="btn btn-gradient-danger px-4 fw-semibold" id="submitBtn">
                                    <i class="mdi mdi-content-save me-1"></i>
                                    <?= $is_edit ? 'Update Donation' : 'Log Donation' ?>
                                </button>
                                <a href="donations" class="btn btn-outline-secondary px-3">
                                    <i class="mdi mdi-arrow-left me-1"></i> Cancel
                                </a>
                                <?php if ($is_edit): ?>
                                <a href="manage_donations?rid=<?= urlencode($rid) ?>&action=delete"
                                   class="btn btn-outline-danger px-3 ms-auto"
                                   onclick="return confirm('Permanently delete this donation record?')">
                                    <i class="mdi mdi-trash-can-outline me-1"></i> Delete
                                </a>
                                <?php endif; ?>
                            </div>

                        </form><!-- /donationForm -->

                    </div><!-- /card-body -->
                </div><!-- /card -->
            </div><!-- /col -->
        </div><!-- /row -->

    </div><!-- /content-wrapper -->

    <!-- ═══════════════════════════════════════════════════════════════════
         Create Donor Modal
    ════════════════════════════════════════════════════════════════════ -->
    <div class="modal fade" id="createDonorModal" tabindex="-1" aria-labelledby="createDonorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius:14px;overflow:hidden;">
                <div class="modal-header bg-gradient-warning text-dark border-0">
                    <h5 class="modal-title" id="createDonorModalLabel">
                        <i class="mdi mdi-account-plus me-2"></i>Create New Donor Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        This will create a basic profile for the donor <strong>without any login access</strong>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="cdName">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="cdName" class="form-control" placeholder="Full name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="cdMobile">Mobile</label>
                        <input type="tel" id="cdMobile" class="form-control" placeholder="10-digit mobile">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="cdMail">Email</label>
                        <input type="email" id="cdMail" class="form-control" placeholder="email@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="cdGender">Gender</label>
                        <select id="cdGender" class="form-select">
                            <option value="">— Select —</option>
                            <option value="1">Male</option>
                            <option value="2">Female</option>
                            <option value="3">Other</option>
                        </select>
                    </div>
                    <div id="cdError" class="alert alert-danger py-2 small" style="display:none;"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning fw-semibold" id="saveDonorBtn">
                        <span id="saveDonorSpinner" class="spinner-border spinner-border-sm me-1" style="display:none;"></span>
                        <i class="mdi mdi-check me-1" id="saveDonorIcon"></i> Create &amp; Select
                    </button>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>

<!-- ═══════════════════════════════════════════════════════════════════════════
     Inline script: autocomplete + donor modal logic
════════════════════════════════════════════════════════════════════════════ -->
<style>
#donorSuggestions {
    position: absolute;
    width: 100%;
    background: #white;
    border: 1px solid #f0f0f0;
    border-radius: 4px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    z-index: 2000;
    display: none;
    max-height: 240px;
    overflow-y: auto;
}
#donorSuggestions .suggestion-item:last-child { border-bottom: none; }
#donorSuggestions .suggestion-item:hover,
#donorSuggestions .suggestion-item.active { background: #fff3f3; }
#donorSuggestions .suggestion-item .sug-name  { font-weight: 600; font-size: .9rem; color:#222; }
#donorSuggestions .suggestion-item .sug-sub   { font-size: .78rem; color:#888; }
#donorSuggestions .suggestion-new {
    padding: 10px 14px;
    cursor: pointer;
    background:#fffbea;
    color:#856404;
    font-size:.85rem;
    font-weight:600;
    border-top:1px solid #f0e68c;
    display:flex;
    align-items:center;
    gap:6px;
}
#donorSuggestions .suggestion-new:hover { background:#fff3cd; }
</style>

<script>
(function () {
    "use strict";

    const API_BASE       = "<?= rtrim($api_url, '/') ?>";
    const nameInput      = document.getElementById('donorNameInput');
    const suggestions    = document.getElementById('donorSuggestions');
    const linkedCard     = document.getElementById('linkedDonorCard');
    const newDonorNotice = document.getElementById('newDonorNotice');
    const clearBtn       = document.getElementById('clearDonorBtn');
    const openModalBtn   = document.getElementById('openCreateDonorBtn');
    const submitBtn      = document.getElementById('submitBtn');
    const txidBlock      = document.getElementById('txidBlock');
    const typeSelect     = document.getElementById('inputType');

    // Hidden fields
    const hiddenUid    = document.getElementById('hiddenUid');
    const hiddenName   = document.getElementById('hiddenName');
    const hiddenMail   = document.getElementById('hiddenMail');
    const hiddenMobile = document.getElementById('hiddenMobile');

    // Linked card display fields
    const lcName   = document.getElementById('lcName');
    const lcMail   = document.getElementById('lcMail');
    const lcMobile = document.getElementById('lcMobile');
    const lcUid    = document.getElementById('lcUid');

    let debounceTimer = null;
    let lastQuery     = '';
    let isLinked      = <?= $is_linked ? 'true' : 'false' ?>;

    // ── Toggle txid based on payment type ──────────────────────────────────
    typeSelect.addEventListener('change', function () {
        txidBlock.style.display = this.value === '2' ? 'none' : 'block';
    });

    // ── Donor name autocomplete ────────────────────────────────────────────
    nameInput.addEventListener('input', function () {
        const q = this.value.trim();
        if (isLinked) return; // ignore typing when a user is locked-in

        clearTimeout(debounceTimer);
        suggestions.style.display = 'none';
        newDonorNotice.style.display = 'none';

        if (q.length < 2) return;

        debounceTimer = setTimeout(() => fetchSuggestions(q), 300);
    });

    nameInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSuggestions();
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#nameSearchBlock')) closeSuggestions();
    });

    function fetchSuggestions(q) {
        if (q === lastQuery) return;
        lastQuery = q;
        console.log('Searching for:', q);

        fetch(`${API_BASE}/global/users_search?q=${encodeURIComponent(q)}&limit=8`)
            .then(r => r.json())
            .then(json => {
                console.log('Search results:', json);
                renderSuggestions(json.data || [], q);
            })
            .catch(err => {
                console.error('Search error:', err);
                closeSuggestions();
            });
    }

    function renderSuggestions(users, query) {
        suggestions.innerHTML = '';

        if (users.length > 0) {
            users.forEach(u => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.innerHTML = `
                    <div class="sug-name">${highlight(u.name, query)}</div>
                    <div class="sug-sub">
                        ${u.mobile ? '<i class="mdi mdi-phone-outline"></i> ' + escHtml(u.mobile) : ''}
                        ${u.mail   ? ' &nbsp;<i class="mdi mdi-email-outline"></i> ' + escHtml(u.mail) : ''}
                    </div>`;
                div.addEventListener('mousedown', () => selectDonor(u));
                suggestions.appendChild(div);
            });
        }

        // Always show "use as new donor" option at the bottom
        const newDiv = document.createElement('div');
        newDiv.className = 'suggestion-new';
        newDiv.innerHTML = `<i class="mdi mdi-account-plus"></i> Not found? Add "<strong>${escHtml(query)}</strong>" as new donor`;
        newDiv.addEventListener('mousedown', () => promptNewDonor(query));
        suggestions.appendChild(newDiv);

        suggestions.style.display = 'block';
    }

    function selectDonor(u) {
        isLinked = true;
        nameInput.value          = u.name;
        hiddenUid.value          = u.uid;
        hiddenName.value         = u.name;
        hiddenMail.value         = u.mail   || '';
        hiddenMobile.value       = u.mobile || '';

        lcName.textContent   = u.name;
        lcMail.textContent   = u.mail   || '—';
        lcMobile.textContent = u.mobile || '—';
        lcUid.textContent    = u.uid    || '';

        linkedCard.style.display     = 'block';
        newDonorNotice.style.display = 'none';
        nameInput.readOnly           = true;
        nameInput.classList.add('bg-light');
        closeSuggestions();
    }

    function promptNewDonor(name) {
        closeSuggestions();
        // Show the notice banner
        newDonorNotice.style.display = 'block';
        // Pre-fill modal name
        document.getElementById('cdName').value = name;
    }

    function clearDonor() {
        isLinked = false;
        nameInput.value        = '';
        nameInput.readOnly     = false;
        nameInput.classList.remove('bg-light');
        hiddenUid.value        = '';
        hiddenName.value       = '';
        hiddenMail.value       = '';
        hiddenMobile.value     = '';
        linkedCard.style.display     = 'none';
        newDonorNotice.style.display = 'none';
        lastQuery = '';
        nameInput.focus();
    }

    function closeSuggestions() {
        suggestions.style.display = 'none';
    }

    clearBtn.addEventListener('click', clearDonor);

    // ── Create donor modal ─────────────────────────────────────────────────
    const createDonorModal = new bootstrap.Modal(document.getElementById('createDonorModal'));
    const cdName    = document.getElementById('cdName');
    const cdMobile  = document.getElementById('cdMobile');
    const cdMail    = document.getElementById('cdMail');
    const cdGender  = document.getElementById('cdGender');
    const cdError   = document.getElementById('cdError');
    const saveDonorBtn     = document.getElementById('saveDonorBtn');
    const saveDonorSpinner = document.getElementById('saveDonorSpinner');
    const saveDonorIcon    = document.getElementById('saveDonorIcon');

    openModalBtn.addEventListener('click', () => {
        // Pre-fill name from the input
        cdName.value   = nameInput.value.trim();
        cdMobile.value = '';
        cdMail.value   = '';
        cdGender.value = '';
        cdError.style.display = 'none';
        createDonorModal.show();
    });

    saveDonorBtn.addEventListener('click', () => {
        cdError.style.display = 'none';
        const name = cdName.value.trim();
        if (!name) { showCdError('Name is required.'); return; }

        saveDonorSpinner.style.display = 'inline-block';
        saveDonorIcon.style.display    = 'none';
        saveDonorBtn.disabled          = true;

        console.log('Creating donor:', name);
        const fd = new FormData();
        fd.append('name',   name);
        fd.append('mobile', cdMobile.value.trim());
        fd.append('mail',   cdMail.value.trim());
        fd.append('gender', cdGender.value);

        fetch(`${API_BASE}/global/create_donor`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(json => {
                console.log('Create donor response:', json);
                if (json.status === 'success') {
                    createDonorModal.hide();
                    selectDonor(json.data);
                } else {
                    showCdError(json.message || 'Failed to create donor.');
                }
            })
            .catch(err => {
                console.error('Create donor error:', err);
                showCdError('Network error. Please try again.');
            })
            .finally(() => {
                saveDonorSpinner.style.display = 'none';
                saveDonorIcon.style.display    = 'inline-block';
                saveDonorBtn.disabled          = false;
            });
    });

    function showCdError(msg) {
        cdError.textContent    = msg;
        cdError.style.display  = 'block';
    }

    // ── Form submit guard ──────────────────────────────────────────────────
    document.getElementById('donationForm').addEventListener('submit', function (e) {
        // Sync hidden name from visible input if not linked to a DB user
        if (!isLinked) {
            hiddenName.value = nameInput.value.trim();
        }
        if (!hiddenName.value.trim()) {
            e.preventDefault();
            nameInput.focus();
            nameInput.classList.add('is-invalid');
            return;
        }
        nameInput.classList.remove('is-invalid');
    });

    // ── Utilities ──────────────────────────────────────────────────────────
    function highlight(text, query) {
        const escaped = escHtml(text);
        const re = new RegExp('(' + escHtml(query).replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return escaped.replace(re, '<mark class="p-0 bg-warning bg-opacity-25 rounded">$1</mark>');
    }

    function escHtml(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

})();
</script>