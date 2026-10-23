<?php
include 'includes/header.php';

// ── Form state ───────────────────────────────────────────────────────────────
$rid         = '';
$category_id = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : 0;
$title       = '';
$description = '';
$ben_name    = '';
$ben_mobile  = '';
$area        = '';
$city        = '';
$priority    = 'normal';
$est_amount  = '';
$assigned_to = '';
$due_date    = '';
$existing    = [];
$is_edit     = false;
$alert_msg   = '';
$alert_type  = '';

$cats = json_decode(get_api_data("$api_url/tickets/categories"), true);
$cats = is_array($cats['data'] ?? null) ? $cats['data'] : [];

$coords = json_decode(get_api_data("$api_url/access/coordinators"), true);
$coords = is_array($coords['data'] ?? null) ? $coords['data'] : [];

// ── Save ─────────────────────────────────────────────────────────────────────
if (isset($_POST['_submit'])) {
    $post = with_actor([
        'rid'                => $_POST['rid']                ?? '',
        'category_id'        => $_POST['category_id']        ?? '',
        'title'              => $_POST['title']              ?? '',
        'description'        => $_POST['description']        ?? '',
        'beneficiary_name'   => $_POST['beneficiary_name']   ?? '',
        'beneficiary_mobile' => $_POST['beneficiary_mobile'] ?? '',
        'area'               => $_POST['area']               ?? '',
        'city'               => $_POST['city']               ?? '',
        'priority'           => $_POST['priority']           ?? 'normal',
        'est_amount'         => $_POST['est_amount']         ?? '',
        'assigned_to'        => $_POST['assigned_to']        ?? '',
        'due_date'           => $_POST['due_date']           ?? '',
    ]);

    $resp = post_api_data_files("$api_url/tickets/manage_ticket", $post, 'proof', $_FILES['proof'] ?? null);
    $resp = json_decode($resp, true);

    if (($resp['status'] ?? '') === 'success') {
        $msg = $resp['message'] ?? 'Ticket saved.';
        if (!empty($resp['warnings'])) $msg .= ' (' . implode(' ', $resp['warnings']) . ')';
        set_flash('success', $msg);
        redirect('ticket?rid=' . (int)$resp['data']);
    }

    $alert_msg  = $resp['message'] ?? 'Could not save the ticket. Please try again.';
    $alert_type = 'danger';

    // Hand the form back with what they typed, rather than a blank slate.
    $rid         = $_POST['rid']                ?? '';
    $category_id = (int)($_POST['category_id']  ?? 0);
    $title       = $_POST['title']              ?? '';
    $description = $_POST['description']        ?? '';
    $ben_name    = $_POST['beneficiary_name']   ?? '';
    $ben_mobile  = $_POST['beneficiary_mobile'] ?? '';
    $area        = $_POST['area']               ?? '';
    $city        = $_POST['city']               ?? '';
    $priority    = $_POST['priority']           ?? 'normal';
    $est_amount  = $_POST['est_amount']         ?? '';
    $assigned_to = $_POST['assigned_to']        ?? '';
    $due_date    = $_POST['due_date']           ?? '';
    $is_edit     = $rid !== '';
}

// ── Load for editing ─────────────────────────────────────────────────────────
if (isset($_GET['rid']) && is_numeric($_GET['rid']) && $alert_msg === '') {
    $resp = json_decode(get_api_data("$api_url/tickets/tickets?rid=" . (int)$_GET['rid']), true);
    $row  = $resp['data'][0] ?? null;

    if ($row) {
        $rid         = $row['id'];
        $category_id = (int)$row['category_id'];
        $title       = $row['title'];
        $description = $row['description'];
        $ben_name    = $row['beneficiary_name'];
        $ben_mobile  = $row['beneficiary_mobile'];
        $area        = $row['area'];
        $city        = $row['city'];
        $priority    = $row['priority'];
        $est_amount  = (float)$row['est_amount'] > 0 ? $row['est_amount'] : '';
        $assigned_to = $row['assigned_to'];
        $due_date    = $row['due_date'];
        $existing    = $resp['files'] ?? [];
        $is_edit     = true;

        // Don't let someone open an edit form they are not allowed to save.
        if (!is_admin() && $row['raised_by'] !== portal_uid() && $row['assigned_to'] !== portal_uid()) {
            set_flash('danger', 'You can only edit tickets you raised or own.');
            redirect('ticket?rid=' . (int)$row['id']);
        }
    } else {
        set_flash('danger', 'That ticket no longer exists.');
        redirect('tickets');
    }
}

$page_title = $is_edit ? 'Edit Ticket' : 'Raise a Ticket';
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon bg-gradient-primary text-white me-2">
        <i class="mdi mdi-ticket-confirmation"></i>
      </span>
      <?= $page_title ?>
    </h3>
    <nav aria-label="breadcrumb">
      <a href="<?= $is_edit ? 'ticket?rid=' . (int)$rid : 'tickets' ?>" class="btn btn-sm btn-light border">
        <i class="mdi mdi-arrow-left me-1"></i> <?= $is_edit ? 'Back to Ticket' : 'Back to Board' ?>
      </a>
    </nav>
  </div>

  <div class="row justify-content-center">
    <div class="col-lg-9 grid-margin stretch-card">
      <div class="card shadow-sm">
        <div class="card-body">

          <?php if ($alert_msg): ?>
            <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
              <?= h($alert_msg) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <?php if (!$is_edit): ?>
            <p class="text-muted mb-4" style="font-size:0.86rem;">
              A ticket is how an activity gets started — a medical case, an NGO requirement,
              an orphanage visit, a blood need. Fill in what you know; the rest can be added
              as the work moves along. Money is requested separately once the ticket is open.
            </p>
          <?php endif; ?>

          <form method="POST" action="manage_ticket" class="forms-sample" enctype="multipart/form-data">
            <input type="hidden" name="_submit" value="1">
            <input type="hidden" name="rid" value="<?= h($rid) ?>">

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="category_id" class="form-label fw-bold">Activity Type <span class="text-danger">*</span></label>
                <select name="category_id" id="category_id" class="form-select" required>
                  <option value="">Choose one…</option>
                  <?php foreach ($cats as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $category_id == $c['id'] ? 'selected' : '' ?>>
                      <?= h($c['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3 mb-3">
                <label for="priority" class="form-label fw-bold">Priority</label>
                <select name="priority" id="priority" class="form-select">
                  <?php foreach (['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'critical' => 'Critical'] as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $priority === $k ? 'selected' : '' ?>><?= $v ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3 mb-3">
                <label for="due_date" class="form-label fw-bold">Needed By</label>
                <input type="date" name="due_date" id="due_date" class="form-control" value="<?= h($due_date) ?>">
              </div>
            </div>

            <div class="form-group mb-3">
              <label for="title" class="form-label fw-bold">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" id="title" class="form-control" maxlength="200" required
                     placeholder="e.g. Dialysis support for Ramesh at Gandhi Hospital"
                     value="<?= h($title) ?>">
            </div>

            <div class="form-group mb-4">
              <label for="description" class="form-label fw-bold">What is needed, and why</label>
              <textarea name="description" id="description" class="form-control" rows="5"
                        placeholder="Background, what has been verified so far, what help is being asked for…"><?= h($description) ?></textarea>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:0.74rem;letter-spacing:0.8px;">Beneficiary</h6>
            <div class="row">
              <div class="col-md-5 mb-3">
                <label for="beneficiary_name" class="form-label fw-bold">Name</label>
                <input type="text" name="beneficiary_name" id="beneficiary_name" class="form-control"
                       value="<?= h($ben_name) ?>" placeholder="Who this is for">
              </div>
              <div class="col-md-3 mb-3">
                <label for="beneficiary_mobile" class="form-label fw-bold">Contact</label>
                <input type="text" name="beneficiary_mobile" id="beneficiary_mobile" class="form-control"
                       value="<?= h($ben_mobile) ?>" placeholder="10-digit mobile">
              </div>
              <div class="col-md-2 mb-3">
                <label for="area" class="form-label fw-bold">Area</label>
                <input type="text" name="area" id="area" class="form-control" value="<?= h($area) ?>" placeholder="Locality">
              </div>
              <div class="col-md-2 mb-3">
                <label for="city" class="form-label fw-bold">City</label>
                <input type="text" name="city" id="city" class="form-control" value="<?= h($city) ?>" placeholder="City">
              </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3 mt-2" style="font-size:0.74rem;letter-spacing:0.8px;">Ownership &amp; Estimate</h6>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="assigned_to" class="form-label fw-bold">Assign To</label>
                <select name="assigned_to" id="assigned_to" class="form-select">
                  <option value="">Leave unassigned for now</option>
                  <?php foreach ($coords as $c): ?>
                    <option value="<?= h($c['uid']) ?>" <?= $assigned_to === $c['uid'] ? 'selected' : '' ?>>
                      <?= h($c['name']) ?><?= $c['portal_role'] !== 'coordinator' ? ' (' . h(role_label($c['portal_role'])) . ')' : '' ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="est_amount" class="form-label fw-bold">Estimated Cost</label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" step="0.01" min="0" name="est_amount" id="est_amount" class="form-control"
                         placeholder="0.00" value="<?= h($est_amount) ?>">
                </div>
                <small class="text-muted" style="font-size:0.74rem;">A rough figure. The actual ask goes through a fund request.</small>
              </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-2 mt-2" style="font-size:0.74rem;letter-spacing:0.8px;">Supporting Documents</h6>
            <div class="form-group mb-4">
              <input type="file" name="proof[]" class="form-control" multiple
                     accept="image/jpeg,image/png,image/webp,image/heic,application/pdf">
              <small class="text-muted" style="font-size:0.74rem;">
                Prescriptions, estimates, photographs, survey notes. JPG, PNG, WebP, HEIC or PDF, up to 3 MB each.
              </small>

              <?php if (!empty($existing)): ?>
                <div class="proof-grid mt-3">
                  <?php foreach ($existing as $f): ?>
                    <a class="proof-item" target="_blank"
                       href="<?= h(proof_url($api_url, $f['id'])) ?>">
                      <?php if ((int)$f['is_image'] === 1): ?>
                        <img src="<?= h(proof_url($api_url, $f['id'])) ?>" alt="<?= h($f['file_name']) ?>">
                      <?php else: ?>
                        <span class="proof-doc"><i class="mdi mdi-file-pdf-box"></i></span>
                      <?php endif; ?>
                      <span class="proof-name"><?= h($f['file_name']) ?></span>
                    </a>
                  <?php endforeach; ?>
                </div>
                <small class="text-muted d-block mt-2" style="font-size:0.74rem;">
                  Already attached. Anything you add here is kept alongside these.
                </small>
              <?php endif; ?>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
              <a href="<?= $is_edit ? 'ticket?rid=' . (int)$rid : 'tickets' ?>" class="btn btn-light me-md-2">Cancel</a>
              <button type="submit" class="btn btn-gradient-primary btn-lg px-5 fw-bold">
                <i class="mdi mdi-content-save me-1"></i>
                <?= $is_edit ? 'Update Ticket' : 'Raise Ticket' ?>
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
