<?php
include 'includes/header.php';

$GROUPS = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];

// ── Form state ───────────────────────────────────────────────────────────────
$rid         = '';
$uid         = '';
$name        = '';
$mobile      = '';
$alt_mobile  = '';
$mail        = '';
$blood_group = $_GET['group'] ?? '';
$gender      = '';
$dob         = '';
$area        = '';
$city        = '';
$last_on     = '';
$notes       = '';
$available   = 1;
$history     = [];
$is_edit     = false;
$alert_msg   = '';

// ── Retire / toggle availability ─────────────────────────────────────────────
if (isset($_GET['rid']) && isset($_GET['action']) && in_array($_GET['action'], ['delete', 'toggle'], true)) {
    $resp = json_decode(get_api_data("$api_url/blood/manage_donor?action=" . $_GET['action']
                                     . "&rid=" . (int)$_GET['rid'] . "&" . actor_qs()), true);
    $ok = ($resp['status'] ?? '') === 'success';

    set_flash($ok ? 'success' : 'danger', $resp['message'] ?? 'Could not complete that.');
    redirect('' . ($ok && $_GET['action'] === 'delete'
                            ? 'blood_donors'
                            : 'manage_blood_donor?rid=' . (int)$_GET['rid']));
}

// ── Save ─────────────────────────────────────────────────────────────────────
if (isset($_POST['_submit'])) {
    $post = with_actor([
        'rid'             => $_POST['rid']             ?? '',
        'uid'             => $_POST['uid']             ?? '',
        'name'            => $_POST['name']            ?? '',
        'mobile'          => $_POST['mobile']          ?? '',
        'alt_mobile'      => $_POST['alt_mobile']      ?? '',
        'mail'            => $_POST['mail']            ?? '',
        'blood_group'     => $_POST['blood_group']     ?? '',
        'gender'          => $_POST['gender']          ?? '',
        'dob'             => $_POST['dob']             ?? '',
        'area'            => $_POST['area']            ?? '',
        'city'            => $_POST['city']            ?? '',
        'last_donated_on' => $_POST['last_donated_on'] ?? '',
        'notes'           => $_POST['notes']           ?? '',
        'is_available'    => isset($_POST['is_available']) ? '1' : '0',
    ]);

    $resp = json_decode(get_api_data_post("$api_url/blood/manage_donor", $post), true);

    if (($resp['status'] ?? '') === 'success') {
        set_flash('success', $resp['message'] ?? 'Donor saved.');
        redirect('blood_donors');
    }

    $alert_msg   = $resp['message'] ?? 'Could not save the donor. Please try again.';
    $rid         = $_POST['rid']             ?? '';
    $uid         = $_POST['uid']             ?? '';
    $name        = $_POST['name']            ?? '';
    $mobile      = $_POST['mobile']          ?? '';
    $alt_mobile  = $_POST['alt_mobile']      ?? '';
    $mail        = $_POST['mail']            ?? '';
    $blood_group = $_POST['blood_group']     ?? '';
    $gender      = $_POST['gender']          ?? '';
    $dob         = $_POST['dob']             ?? '';
    $area        = $_POST['area']            ?? '';
    $city        = $_POST['city']            ?? '';
    $last_on     = $_POST['last_donated_on'] ?? '';
    $notes       = $_POST['notes']           ?? '';
    $available   = isset($_POST['is_available']) ? 1 : 0;
    $is_edit     = $rid !== '';
}

// ── Load for editing ─────────────────────────────────────────────────────────
if (isset($_GET['rid']) && is_numeric($_GET['rid']) && $alert_msg === '') {
    $resp = json_decode(get_api_data("$api_url/blood/donors?rid=" . (int)$_GET['rid']), true);
    $row  = $resp['data'][0] ?? null;

    if (!$row) {
        set_flash('danger', 'That donor is no longer on the registry.');
        redirect('blood_donors');
    }

    $rid         = $row['id'];
    $uid         = $row['uid'];
    $name        = $row['name'];
    $mobile      = $row['mobile'];
    $alt_mobile  = $row['alt_mobile'];
    $mail        = $row['mail'];
    $blood_group = $row['blood_group'];
    $gender      = $row['gender'];
    $dob         = $row['dob'];
    $area        = $row['area'];
    $city        = $row['city'];
    $last_on     = $row['last_donated_on'];
    $notes       = $row['notes'];
    $available   = (int)$row['is_available'];
    $history     = $resp['history'] ?? [];
    $is_edit     = true;
}

$page_title = $is_edit ? 'Edit Donor' : 'Add a Blood Donor';
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="mdi mdi-water-outline"></i></span>
      <?= $page_title ?>
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="blood_donors" class="btn btn-sm btn-light border"><i class="mdi mdi-arrow-left me-1"></i>Registry</a>
      <?php if ($is_edit): ?>
        <a href="manage_blood_donor?rid=<?= (int)$rid ?>&action=toggle"
           class="btn btn-sm <?= $available ? 'btn-outline-secondary' : 'btn-gradient-success' ?>">
          <i class="mdi mdi-power me-1"></i><?= $available ? 'Mark Unavailable' : 'Mark Available' ?>
        </a>
        <a href="manage_blood_donor?rid=<?= (int)$rid ?>&action=delete" class="btn btn-sm btn-gradient-danger"
           onclick="return confirm('Remove this donor from the registry? Anyone with donation history is archived instead.')">
          <i class="mdi mdi-delete"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-<?= $is_edit ? '8' : '9' ?> grid-margin stretch-card">
      <div class="card shadow-sm">
        <div class="card-body">

          <?php if ($alert_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?= h($alert_msg) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <?php if (!$is_edit): ?>
            <p class="text-muted mb-4" style="font-size:0.86rem;">
              Every donor is on the STM member list, so pick them below — or register them
              as a volunteer in one step if they are new. The number and the area matter
              most: those are what we go on when a patient needs blood in a hurry.
            </p>
          <?php endif; ?>

          <form method="POST" action="manage_blood_donor" class="forms-sample">
            <input type="hidden" name="_submit" value="1">
            <input type="hidden" name="rid" value="<?= h($rid) ?>">

            <!-- ── Who the donor is ─────────────────────────────────────────
                 A donor is an STM member, so they are picked from the member
                 list rather than typed in free hand. Anyone not on it yet is
                 added as a volunteer from here, in one step. -->
            <div class="mb-3 position-relative" id="memberSearchBlock">
              <label class="form-label fw-bold" for="memberInput">
                Donor <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="mdi mdi-magnify text-muted"></i>
                </span>
                <input type="text" id="memberInput" class="form-control border-start-0 ps-0"
                       placeholder="Search members by name, mobile or email…"
                       value="<?= h($name) ?>" autocomplete="off">
              </div>
              <div class="form-text mt-1" style="font-size:0.78rem;">
                Pick a member, or add them as a new volunteer if they are not on the list yet.
              </div>
              <div id="memberSuggestions" class="position-absolute w-100 bg-white border rounded shadow-sm mt-1"
                   style="z-index:1050;display:none;max-height:240px;overflow-y:auto;top:100%;"></div>
            </div>

            <!-- The member once chosen -->
            <div id="linkedMemberCard" class="mb-3 p-3 rounded-3 border"
                 style="background:#f8f9ff;display:<?= $uid ? 'block' : 'none' ?>;">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                  <div class="fw-bold text-dark" id="lmName"><?= h($name) ?></div>
                  <div class="text-muted small mt-1">
                    <i class="mdi mdi-pound me-1"></i><span id="lmUid"><?= h($uid) ?></span>
                  </div>
                  <div class="text-muted small">
                    <i class="mdi mdi-phone-outline me-1"></i><span id="lmMobile"><?= h($mobile) ?: '—' ?></span>
                  </div>
                </div>
                <button type="button" id="clearMemberBtn" class="btn btn-sm btn-outline-secondary" title="Choose a different member">
                  <i class="mdi mdi-close"></i> Change
                </button>
              </div>
            </div>

            <!-- Shown when the typed name matches nobody -->
            <div id="newMemberNotice" class="mb-3" style="display:none;">
              <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-0" style="font-size:0.87rem;">
                <i class="mdi mdi-alert-circle-outline fs-5"></i>
                <span>Not on the member list yet. &nbsp;
                  <button type="button" class="btn btn-sm btn-warning fw-semibold" id="openCreateMemberBtn">
                    <i class="mdi mdi-account-plus me-1"></i>Register as Volunteer
                  </button>
                </span>
              </div>
            </div>

            <input type="hidden" name="name" id="hiddenName" value="<?= h($name) ?>">
            <input type="hidden" name="uid"  id="hiddenUid"  value="<?= h($uid) ?>">

            <div class="row">
              <div class="col-md-3 mb-3">
                <label for="blood_group" class="form-label fw-bold">Blood Group <span class="text-danger">*</span></label>
                <select name="blood_group" id="blood_group" class="form-select" required>
                  <option value="">Choose…</option>
                  <?php foreach ($GROUPS as $g): ?>
                    <option value="<?= h($g) ?>" <?= $blood_group === $g ? 'selected' : '' ?>><?= h($g) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3 mb-3">
                <label for="gender" class="form-label fw-bold">Gender</label>
                <select name="gender" id="gender" class="form-select">
                  <option value="">Not stated</option>
                  <?php foreach (['Male', 'Female', 'Other'] as $gn): ?>
                    <option value="<?= $gn ?>" <?= $gender === $gn ? 'selected' : '' ?>><?= $gn ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="mobile" class="form-label fw-bold">Mobile <span class="text-danger">*</span></label>
                <input type="text" name="mobile" id="mobile" class="form-control" required
                       value="<?= h($mobile) ?>" placeholder="10-digit number">
              </div>
              <div class="col-md-4 mb-3">
                <label for="alt_mobile" class="form-label fw-bold">Alternate Number</label>
                <input type="text" name="alt_mobile" id="alt_mobile" class="form-control"
                       value="<?= h($alt_mobile) ?>" placeholder="Family or friend">
              </div>
              <div class="col-md-4 mb-3">
                <label for="mail" class="form-label fw-bold">Email</label>
                <input type="email" name="mail" id="mail" class="form-control" value="<?= h($mail) ?>">
              </div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="area" class="form-label fw-bold">Area / Locality</label>
                <input type="text" name="area" id="area" class="form-control" value="<?= h($area) ?>"
                       placeholder="Where they can reach from">
              </div>
              <div class="col-md-4 mb-3">
                <label for="city" class="form-label fw-bold">City</label>
                <input type="text" name="city" id="city" class="form-control" value="<?= h($city) ?>">
              </div>
              <div class="col-md-4 mb-3">
                <label for="dob" class="form-label fw-bold">Date of Birth</label>
                <input type="date" name="dob" id="dob" class="form-control" value="<?= h($dob) ?>">
              </div>
            </div>

            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="last_donated_on" class="form-label fw-bold">Last Donated</label>
                <input type="date" name="last_donated_on" id="last_donated_on" class="form-control"
                       max="<?= date('Y-m-d') ?>" value="<?= h($last_on) ?>">
                <small class="text-muted" style="font-size:0.74rem;">
                  If known. Later donations are logged against a requirement and update this on their own.
                </small>
              </div>
              <div class="col-md-8 mb-3">
                <label class="form-label fw-bold d-block">Availability</label>
                <div class="form-check form-switch mt-2">
                  <input class="form-check-input" type="checkbox" role="switch" name="is_available"
                         value="1" id="is_available" <?= $available ? 'checked' : '' ?>>
                  <label class="form-check-label" for="is_available">Willing to be called</label>
                </div>
              </div>
            </div>

            <div class="form-group mb-4">
              <label for="notes" class="form-label fw-bold">Notes</label>
              <input type="text" name="notes" id="notes" class="form-control" maxlength="255"
                     value="<?= h($notes) ?>" placeholder="Best time to call, medical conditions, anything useful">
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
              <a href="blood_donors" class="btn btn-light me-md-2">Cancel</a>
              <button type="submit" class="btn btn-gradient-primary btn-lg px-5 fw-bold">
                <i class="mdi mdi-content-save me-1"></i><?= $is_edit ? 'Update Donor' : 'Add Donor' ?>
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

    <?php if ($is_edit): ?>
      <div class="col-lg-4 grid-margin stretch-card">
        <div class="card shadow-sm">
          <div class="card-body">
            <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
              <i class="mdi mdi-history me-2" style="color:var(--stm-navy);"></i>Donation History
            </h6>

            <?php if (empty($history)): ?>
              <p class="text-muted mb-0" style="font-size:0.85rem;">
                Nothing logged yet. Donations are recorded against a requirement, from the
                requirement's own page.
              </p>
            <?php else: ?>
              <div class="stm-timeline">
                <?php foreach ($history as $hh): ?>
                  <div class="stm-timeline-item is-status">
                    <div class="d-flex justify-content-between align-items-baseline gap-2">
                      <span class="stm-timeline-who"><?= nice_date($hh['donated_on']) ?></span>
                      <span class="stm-timeline-when"><?= units_text($hh['units']) ?> unit(s)</span>
                    </div>
                    <p class="stm-timeline-note">
                      <?php if ($hh['hospital']): ?><?= h($hh['hospital']) ?><?php endif; ?>
                      <?php if ($hh['recipient_name']): ?><br>for <?= h($hh['recipient_name']) ?><?php endif; ?>
                      <?php if ($hh['request_no']): ?>
                        <br><span style="font-size:0.74rem;color:var(--stm-muted);"><?= h($hh['request_no']) ?></span>
                      <?php endif; ?>
                      <?php if ($hh['logged_by_name']): ?>
                        <br><span style="font-size:0.74rem;color:var(--stm-muted);">logged by <?= h($hh['logged_by_name']) ?></span>
                      <?php endif; ?>
                    </p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div>

</div>

<!-- ── Register a new volunteer without leaving the form ─────────────────── -->
<div class="modal fade" id="createMemberModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:14px;">
      <div class="modal-header">
        <h5 class="modal-title" style="font-weight:700;color:var(--stm-navy);">
          <i class="mdi mdi-account-plus me-1"></i>Register as Volunteer
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted" style="font-size:0.84rem;">
          This adds them to STM as a volunteer, and their blood group is saved on
          their member profile as well as the donor registry.
        </p>
        <div class="alert alert-danger py-2" id="cmError" style="display:none;font-size:0.85rem;"></div>

        <div class="mb-3">
          <label class="form-label fw-bold" for="cmName">Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="cmName" maxlength="120">
        </div>
        <div class="row">
          <div class="col-sm-6 mb-3">
            <label class="form-label fw-bold" for="cmMobile">Mobile <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="cmMobile" placeholder="10-digit number">
          </div>
          <div class="col-sm-6 mb-3">
            <label class="form-label fw-bold" for="cmGender">Gender</label>
            <select class="form-select" id="cmGender">
              <option value="">Not stated</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
        <div class="mb-0">
          <label class="form-label fw-bold" for="cmMail">Email</label>
          <input type="email" class="form-control" id="cmMail">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gradient-primary fw-bold" id="saveMemberBtn">
          <span class="spinner-border spinner-border-sm me-1" id="saveMemberSpinner" style="display:none;"></span>
          <i class="mdi mdi-content-save me-1" id="saveMemberIcon"></i>Register
        </button>
      </div>
    </div>
  </div>
</div>

<style>
#memberSuggestions .suggestion-item {
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f2f4f8;
}
#memberSuggestions .suggestion-item:last-child { border-bottom: none; }
#memberSuggestions .suggestion-item:hover { background: #f5f7ff; }
#memberSuggestions .sug-name { font-weight: 600; font-size: 0.9rem; color: #2c3250; }
#memberSuggestions .sug-sub  { font-size: 0.78rem; color: #8a93b0; }
#memberSuggestions .suggestion-new {
    padding: 10px 14px;
    cursor: pointer;
    background: #fffbea;
    color: #856404;
    font-size: 0.85rem;
    font-weight: 600;
    border-top: 1px solid #f0e68c;
    display: flex;
    align-items: center;
    gap: 6px;
}
#memberSuggestions .suggestion-new:hover { background: #fff6d6; }
</style>

<script>
(function () {
    "use strict";
    const API_BASE = "<?= rtrim($api_url, '/') ?>";

    const input       = document.getElementById('memberInput');
    const box         = document.getElementById('memberSuggestions');
    const card        = document.getElementById('linkedMemberCard');
    const notice      = document.getElementById('newMemberNotice');
    const hiddenName  = document.getElementById('hiddenName');
    const hiddenUid   = document.getElementById('hiddenUid');
    const mobileField = document.getElementById('mobile');
    const mailField   = document.getElementById('mail');
    const genderField = document.getElementById('gender');
    const bloodField  = document.getElementById('blood_group');
    const areaField   = document.getElementById('area');

    let linked = !!hiddenUid.value;
    let timer  = null;

    if (linked) lock();

    function lock() {
        input.readOnly = true;
        input.classList.add('bg-light');
        card.style.display   = 'block';
        notice.style.display = 'none';
        close();
    }

    function close() { box.style.display = 'none'; }

    function esc(t) {
        return String(t == null ? '' : t)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ── Search ──────────────────────────────────────────────────────────────
    input.addEventListener('input', function () {
        if (linked) return;
        clearTimeout(timer);
        close();
        notice.style.display = 'none';

        const q = this.value.trim();
        if (q.length < 2) return;
        timer = setTimeout(function () { search(q); }, 300);
    });

    input.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#memberSearchBlock')) close();
    });

    function search(q) {
        fetch(API_BASE + '/global/users_search?q=' + encodeURIComponent(q) + '&limit=8')
            .then(function (r) { return r.json(); })
            .then(function (j) { render(j.data || [], q); })
            .catch(close);
    }

    function render(users, q) {
        box.innerHTML = '';

        users.forEach(function (u) {
            const d = document.createElement('div');
            d.className = 'suggestion-item';
            d.innerHTML =
                '<div class="sug-name">' + esc(u.name) + '</div>' +
                '<div class="sug-sub">' +
                    (u.mobile ? '<i class="mdi mdi-phone-outline"></i> ' + esc(u.mobile) : '') +
                    (u.mail ? ' &nbsp;<i class="mdi mdi-email-outline"></i> ' + esc(u.mail) : '') +
                '</div>';
            d.addEventListener('mousedown', function () { select(u); });
            box.appendChild(d);
        });

        const n = document.createElement('div');
        n.className = 'suggestion-new';
        n.innerHTML = '<i class="mdi mdi-account-plus"></i> Not listed? Register "<strong>' + esc(q) + '</strong>" as a volunteer';
        n.addEventListener('mousedown', function () { offerNew(q); });
        box.appendChild(n);

        box.style.display = 'block';
    }

    function select(u) {
        linked = true;
        input.value      = u.name;
        hiddenName.value = u.name;
        hiddenUid.value  = u.uid;

        document.getElementById('lmName').textContent   = u.name;
        document.getElementById('lmUid').textContent    = u.uid;
        document.getElementById('lmMobile').textContent = u.mobile || '—';

        // Carry across what the member record already knows, without
        // overwriting anything already filled in on this form.
        if (mobileField && !mobileField.value && u.mobile) mobileField.value = u.mobile;
        if (mailField   && !mailField.value   && u.mail)   mailField.value   = u.mail;

        lock();
    }

    function offerNew(q) {
        close();
        notice.style.display = 'block';
        document.getElementById('cmName').value = q;
    }

    document.getElementById('clearMemberBtn').addEventListener('click', function () {
        linked = false;
        input.value = '';
        input.readOnly = false;
        input.classList.remove('bg-light');
        hiddenName.value = '';
        hiddenUid.value  = '';
        card.style.display   = 'none';
        notice.style.display = 'none';
        input.focus();
    });

    // ── Register a new volunteer ────────────────────────────────────────────
    const modal = new bootstrap.Modal(document.getElementById('createMemberModal'));
    const cmName = document.getElementById('cmName');
    const cmMobile = document.getElementById('cmMobile');
    const cmMail = document.getElementById('cmMail');
    const cmGender = document.getElementById('cmGender');
    const cmError = document.getElementById('cmError');
    const saveBtn = document.getElementById('saveMemberBtn');
    const spinner = document.getElementById('saveMemberSpinner');
    const saveIcon = document.getElementById('saveMemberIcon');

    document.getElementById('openCreateMemberBtn').addEventListener('click', function () {
        cmName.value   = input.value.trim();
        cmMobile.value = mobileField ? mobileField.value.trim() : '';
        cmMail.value   = mailField ? mailField.value.trim() : '';
        cmGender.value = genderField ? genderField.value.toLowerCase() : '';
        cmError.style.display = 'none';
        modal.show();
    });

    function fail(msg) {
        cmError.textContent = msg;
        cmError.style.display = 'block';
    }

    saveBtn.addEventListener('click', function () {
        cmError.style.display = 'none';

        const name = cmName.value.trim();
        const mob  = cmMobile.value.trim();
        if (!name) { fail('Enter their name.'); return; }
        if (!/^[0-9+\- ]{10,15}$/.test(mob)) { fail('Enter a valid mobile number — it is how we reach them.'); return; }

        spinner.style.display = 'inline-block';
        saveIcon.style.display = 'none';
        saveBtn.disabled = true;

        const fd = new FormData();
        fd.append('name', name);
        fd.append('mobile', mob);
        fd.append('mail', cmMail.value.trim());
        fd.append('gender', cmGender.value);
        fd.append('role', '1');                               // volunteer
        if (bloodField && bloodField.value) fd.append('blood', bloodField.value);
        if (areaField && areaField.value)   fd.append('residence', areaField.value.trim());

        fetch(API_BASE + '/global/create_donor', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j.status === 'success') {
                    modal.hide();
                    select(j.data);
                    if (mobileField && !mobileField.value) mobileField.value = mob;
                } else {
                    fail(j.message || 'Could not register them. Please try again.');
                }
            })
            .catch(function () { fail('Network error. Please try again.'); })
            .finally(function () {
                spinner.style.display = 'none';
                saveIcon.style.display = 'inline-block';
                saveBtn.disabled = false;
            });
    });

    // ── Don't submit a donor who isn't a member ─────────────────────────────
    document.querySelector('form.forms-sample').addEventListener('submit', function (e) {
        if (!hiddenUid.value.trim()) {
            e.preventDefault();
            input.classList.add('is-invalid');
            notice.style.display = 'block';
            input.focus();
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    input.addEventListener('input', function () { input.classList.remove('is-invalid'); });
})();
</script>

<?php include 'includes/footer.php'; ?>
</div>
