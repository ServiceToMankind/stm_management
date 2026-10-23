<?php
include 'includes/header.php';

$GROUPS = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];

// ── Form state ───────────────────────────────────────────────────────────────
$rid          = '';
$blood_group  = $_GET['group'] ?? '';
$units        = '1';
$patient      = '';
$patient_age  = '';
$hospital     = '';
$area         = '';
$city         = '';
$needed_on    = '';
$urgency      = 'urgent';
$contact      = $_SESSION['NAME'] ?? '';
$contact_mob  = '';
$alt_mobile   = '';
$reason       = '';
$notes        = '';
$ticket_id    = isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
$is_edit      = false;
$alert_msg    = '';

// ── Save ─────────────────────────────────────────────────────────────────────
if (isset($_POST['_submit'])) {
    $post = with_actor([
        'rid'            => $_POST['rid']            ?? '',
        'ticket_id'      => $_POST['ticket_id']      ?? '',
        'blood_group'    => $_POST['blood_group']    ?? '',
        'units_needed'   => $_POST['units_needed']   ?? '',
        'patient_name'   => $_POST['patient_name']   ?? '',
        'patient_age'    => $_POST['patient_age']    ?? '',
        'hospital'       => $_POST['hospital']       ?? '',
        'area'           => $_POST['area']           ?? '',
        'city'           => $_POST['city']           ?? '',
        'needed_on'      => $_POST['needed_on']      ?? '',
        'urgency'        => $_POST['urgency']        ?? 'urgent',
        'contact_name'   => $_POST['contact_name']   ?? '',
        'contact_mobile' => $_POST['contact_mobile'] ?? '',
        'alt_mobile'     => $_POST['alt_mobile']     ?? '',
        'reason'         => $_POST['reason']         ?? '',
        'notes'          => $_POST['notes']          ?? '',
    ]);

    $resp = json_decode(get_api_data_post("$api_url/blood/manage_request", $post), true);

    if (($resp['status'] ?? '') === 'success') {
        set_flash('success', $resp['message'] ?? 'Requirement posted.');
        // Straight to the poster — sharing it is the point of posting one.
        redirect('blood_poster?rid=' . (int)$resp['data']);
    }

    $alert_msg   = $resp['message'] ?? 'Could not post the requirement. Please try again.';
    $rid         = $_POST['rid']            ?? '';
    $ticket_id   = (int)($_POST['ticket_id'] ?? 0);
    $blood_group = $_POST['blood_group']    ?? '';
    $units       = $_POST['units_needed']   ?? '1';
    $patient     = $_POST['patient_name']   ?? '';
    $patient_age = $_POST['patient_age']    ?? '';
    $hospital    = $_POST['hospital']       ?? '';
    $area        = $_POST['area']           ?? '';
    $city        = $_POST['city']           ?? '';
    $needed_on   = $_POST['needed_on']      ?? '';
    $urgency     = $_POST['urgency']        ?? 'urgent';
    $contact     = $_POST['contact_name']   ?? '';
    $contact_mob = $_POST['contact_mobile'] ?? '';
    $alt_mobile  = $_POST['alt_mobile']     ?? '';
    $reason      = $_POST['reason']         ?? '';
    $notes       = $_POST['notes']          ?? '';
    $is_edit     = $rid !== '';
}

// ── Load for editing ─────────────────────────────────────────────────────────
if (isset($_GET['rid']) && is_numeric($_GET['rid']) && $alert_msg === '') {
    $resp = json_decode(get_api_data("$api_url/blood/requests?rid=" . (int)$_GET['rid']), true);
    $row  = $resp['data'][0] ?? null;

    if (!$row) {
        set_flash('danger', 'That requirement no longer exists.');
        redirect('blood_requests');
    }
    if ($row['raised_by'] !== portal_uid() && !is_admin()) {
        set_flash('danger', 'You can only edit requirements you posted.');
        redirect('blood_request?rid=' . (int)$row['id']);
    }

    $rid         = $row['id'];
    $ticket_id   = (int)$row['ticket_id'];
    $blood_group = $row['blood_group'];
    $units       = $row['units_needed'];
    $patient     = $row['patient_name'];
    $patient_age = $row['patient_age'];
    $hospital    = $row['hospital'];
    $area        = $row['area'];
    $city        = $row['city'];
    $needed_on   = $row['needed_on'];
    $urgency     = $row['urgency'];
    $contact     = $row['contact_name'];
    $contact_mob = $row['contact_mobile'];
    $alt_mobile  = $row['alt_mobile'];
    $reason      = $row['reason'] ?? '';
    $notes       = $row['notes'];
    $is_edit     = true;
}

$page_title = $is_edit ? 'Edit Requirement' : 'Post a Blood Requirement';
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="mdi mdi-bullhorn-outline"></i></span>
      <?= $page_title ?>
    </h3>
    <nav aria-label="breadcrumb">
      <a href="<?= $is_edit ? 'blood_request?rid=' . (int)$rid : 'blood' ?>" class="btn btn-sm btn-light border">
        <i class="mdi mdi-arrow-left me-1"></i> Back
      </a>
    </nav>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-9 grid-margin stretch-card">
      <div class="card shadow-sm">
        <div class="card-body">

          <?php if ($alert_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?= h($alert_msg) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <?php if (!$is_edit): ?>
            <div class="gate-note mb-4">
              <i class="mdi mdi-image-outline"></i>
              <div>
                Everything you fill in here goes on the poster, so write it as you would want it
                read by a stranger on WhatsApp. Posting also opens a ticket on the main board, and
                you land on the poster to download and share straight away.
              </div>
            </div>
          <?php endif; ?>

          <form method="POST" action="manage_blood_request" class="forms-sample">
            <input type="hidden" name="_submit" value="1">
            <input type="hidden" name="rid" value="<?= h($rid) ?>">
            <input type="hidden" name="ticket_id" value="<?= $ticket_id ?: '' ?>">

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
                <label for="units_needed" class="form-label fw-bold">Units Needed <span class="text-danger">*</span></label>
                <input type="number" step="0.5" min="0.5" max="20" name="units_needed" id="units_needed"
                       class="form-control" required value="<?= h($units) ?>">
              </div>
              <div class="col-md-3 mb-3">
                <label for="urgency" class="form-label fw-bold">Urgency</label>
                <select name="urgency" id="urgency" class="form-select">
                  <?php foreach (['critical' => 'Critical — today', 'urgent' => 'Urgent — a day or two', 'routine' => 'Routine — planned'] as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $urgency === $k ? 'selected' : '' ?>><?= $v ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3 mb-3">
                <label for="needed_on" class="form-label fw-bold">Needed By</label>
                <input type="date" name="needed_on" id="needed_on" class="form-control" value="<?= h($needed_on) ?>">
              </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:0.74rem;letter-spacing:0.8px;">Patient &amp; Hospital</h6>
            <div class="row">
              <div class="col-md-5 mb-3">
                <label for="patient_name" class="form-label fw-bold">Patient Name</label>
                <input type="text" name="patient_name" id="patient_name" class="form-control" maxlength="120"
                       value="<?= h($patient) ?>" placeholder="Leave blank to keep it private">
              </div>
              <div class="col-md-2 mb-3">
                <label for="patient_age" class="form-label fw-bold">Age</label>
                <input type="text" name="patient_age" id="patient_age" class="form-control" maxlength="10"
                       value="<?= h($patient_age) ?>" placeholder="e.g. 42">
              </div>
              <div class="col-md-5 mb-3">
                <label for="hospital" class="form-label fw-bold">Hospital <span class="text-danger">*</span></label>
                <input type="text" name="hospital" id="hospital" class="form-control" maxlength="180" required
                       value="<?= h($hospital) ?>" placeholder="Where the donor should go">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="area" class="form-label fw-bold">Area / Locality</label>
                <input type="text" name="area" id="area" class="form-control" value="<?= h($area) ?>"
                       placeholder="Helps us call the donors closest by">
              </div>
              <div class="col-md-6 mb-3">
                <label for="city" class="form-label fw-bold">City</label>
                <input type="text" name="city" id="city" class="form-control" value="<?= h($city) ?>">
              </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:0.74rem;letter-spacing:0.8px;">
              Who Donors Should Ring
            </h6>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="contact_name" class="form-label fw-bold">Contact Name <span class="text-danger">*</span></label>
                <input type="text" name="contact_name" id="contact_name" class="form-control" maxlength="120" required
                       value="<?= h($contact) ?>">
              </div>
              <div class="col-md-4 mb-3">
                <label for="contact_mobile" class="form-label fw-bold">Contact Number <span class="text-danger">*</span></label>
                <input type="text" name="contact_mobile" id="contact_mobile" class="form-control" required
                       value="<?= h($contact_mob) ?>" placeholder="Goes on the poster">
              </div>
              <div class="col-md-4 mb-3">
                <label for="alt_mobile" class="form-label fw-bold">Second Number</label>
                <input type="text" name="alt_mobile" id="alt_mobile" class="form-control"
                       value="<?= h($alt_mobile) ?>" placeholder="Optional">
              </div>
            </div>

            <div class="form-group mb-3">
              <label for="reason" class="form-label fw-bold">Why the Blood Is Needed</label>
              <textarea name="reason" id="reason" class="form-control" rows="3"
                        placeholder="e.g. Open-heart surgery scheduled for Monday; the family cannot arrange donors themselves."><?= h($reason) ?></textarea>
              <small class="text-muted" style="font-size:0.74rem;">
                This is what persuades someone to come in. It can be shown on the poster.
              </small>
            </div>

            <div class="form-group mb-4">
              <label for="notes" class="form-label fw-bold">Internal Notes</label>
              <textarea name="notes" id="notes" class="form-control" rows="3"
                        placeholder="Ward number, attending doctor, when the donor should come in — for the team, not the poster."><?= h($notes) ?></textarea>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
              <a href="<?= $is_edit ? 'blood_request?rid=' . (int)$rid : 'blood' ?>" class="btn btn-light me-md-2">Cancel</a>
              <button type="submit" class="btn btn-gradient-danger btn-lg px-5 fw-bold">
                <i class="mdi <?= $is_edit ? 'mdi-content-save' : 'mdi-bullhorn' ?> me-1"></i>
                <?= $is_edit ? 'Update Requirement' : 'Post &amp; Make Poster' ?>
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

</div>
<?php include 'includes/footer.php'; ?>
</div>
