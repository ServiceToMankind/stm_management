<?php
include 'includes/header.php';

$STAGES = ['planned' => 'Planned', 'in_progress' => 'In Progress',
           'completed' => 'Completed', 'cancelled' => 'Cancelled'];

// ── Form state ───────────────────────────────────────────────────────────────
$rid           = '';
$title         = '';
$description   = '';
$category_id   = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : 0;
$stage         = 'planned';
$start_date    = date('Y-m-d');
$start_time    = '';
$end_date      = '';
$end_time      = '';
$venue         = '';
$area          = '';
$city          = '';
$beneficiaries = '';
$est_cost      = '';
$sponsored_by  = '';
$coordinator   = portal_uid();
$outcome       = '';
$ticket_id     = isset($_GET['ticket_id']) && is_numeric($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
$status        = 1;
$existing      = [];
$is_edit       = false;
$alert_msg     = '';

$cats = json_decode(get_api_data("$api_url/ops/categories"), true);
$cats = is_array($cats['data'] ?? null) ? $cats['data'] : [];

$coords = json_decode(get_api_data("$api_url/access/coordinators"), true);
$coords = is_array($coords['data'] ?? null) ? $coords['data'] : [];

$tk = json_decode(get_api_data("$api_url/tickets/tickets?status=live&limit=200&" . actor_qs()), true);
$open_tickets = is_array($tk['data'] ?? null) ? $tk['data'] : [];

// ── Save ─────────────────────────────────────────────────────────────────────
if (isset($_POST['_submit'])) {
    $post = with_actor([
        'rid'             => $_POST['rid']             ?? '',
        'title'           => $_POST['title']           ?? '',
        'description'     => $_POST['description']     ?? '',
        'category_id'     => $_POST['category_id']     ?? '',
        'stage'           => $_POST['stage']           ?? 'planned',
        'start_date'      => $_POST['start_date']      ?? '',
        'start_time'      => $_POST['start_time']      ?? '',
        'end_date'        => $_POST['end_date']        ?? '',
        'end_time'        => $_POST['end_time']        ?? '',
        'venue'           => $_POST['venue']           ?? '',
        'area'            => $_POST['area']            ?? '',
        'city'            => $_POST['city']            ?? '',
        'beneficiaries'   => $_POST['beneficiaries']   ?? '0',
        'est_cost'        => $_POST['est_cost']        ?? '0',
        'sponsored_by'    => $_POST['sponsored_by']    ?? '',
        'coordinator_uid' => $_POST['coordinator_uid'] ?? '',
        'outcome'         => $_POST['outcome']         ?? '',
        'ticket_id'       => $_POST['ticket_id']       ?? '',
        'status'          => isset($_POST['status']) ? '1' : '0',
    ]);

    $resp = post_api_data_files("$api_url/ops/manage_activity", $post, 'proof', $_FILES['proof'] ?? null);
    $resp = json_decode($resp, true);

    if (($resp['status'] ?? '') === 'success') {
        $msg = $resp['message'] ?? 'Activity saved.';
        if (!empty($resp['warnings'])) $msg .= ' (' . implode(' ', $resp['warnings']) . ')';
        set_flash('success', $msg);
        redirect('activity?rid=' . (int)$resp['data']);
    }

    $alert_msg     = $resp['message'] ?? 'Could not save the activity. Please try again.';
    $rid           = $_POST['rid']             ?? '';
    $title         = $_POST['title']           ?? '';
    $description   = $_POST['description']     ?? '';
    $category_id   = (int)($_POST['category_id'] ?? 0);
    $stage         = $_POST['stage']           ?? 'planned';
    $start_date    = $_POST['start_date']      ?? '';
    $start_time    = $_POST['start_time']      ?? '';
    $end_date      = $_POST['end_date']        ?? '';
    $end_time      = $_POST['end_time']        ?? '';
    $venue         = $_POST['venue']           ?? '';
    $area          = $_POST['area']            ?? '';
    $city          = $_POST['city']            ?? '';
    $beneficiaries = $_POST['beneficiaries']   ?? '';
    $est_cost      = $_POST['est_cost']        ?? '';
    $sponsored_by  = $_POST['sponsored_by']    ?? '';
    $coordinator   = $_POST['coordinator_uid'] ?? '';
    $outcome       = $_POST['outcome']         ?? '';
    $ticket_id     = (int)($_POST['ticket_id'] ?? 0);
    $status        = isset($_POST['status']) ? 1 : 0;
    $is_edit       = $rid !== '';
}

// ── Load for editing ─────────────────────────────────────────────────────────
if (isset($_GET['rid']) && is_numeric($_GET['rid']) && $alert_msg === '') {
    $resp = json_decode(get_api_data("$api_url/ops/activities?rid=" . (int)$_GET['rid']), true);
    $row  = $resp['data'][0] ?? null;

    if (!$row) {
        set_flash('danger', 'That activity no longer exists.');
        redirect('activities');
    }

    $rid           = $row['id'];
    $title         = $row['title'];
    $description   = $row['description'];
    $category_id   = (int)$row['category_id'];
    $stage         = $row['stage'];
    $start_date    = $row['start_date'];
    $start_time    = $row['start_time'] ? substr($row['start_time'], 0, 5) : '';
    $end_date      = $row['end_date'];
    $end_time      = $row['end_time'] ? substr($row['end_time'], 0, 5) : '';
    $venue         = $row['venue'];
    $area          = $row['area'];
    $city          = $row['city'];
    $beneficiaries = (int)$row['beneficiaries'] > 0 ? $row['beneficiaries'] : '';
    $est_cost      = (float)$row['est_cost'] > 0 ? $row['est_cost'] : '';
    $sponsored_by  = $row['sponsored_by'];
    $coordinator   = $row['coordinator_uid'];
    $outcome       = $row['outcome'];
    $ticket_id     = (int)$row['ticket_id'];
    $status        = (int)$row['status'];
    $existing      = $resp['files'] ?? [];
    $is_edit       = true;

    if ($ticket_id && !in_array($ticket_id, array_map('intval', array_column($open_tickets, 'id')), true)) {
        $open_tickets[] = ['id' => $ticket_id, 'ticket_no' => '#' . $ticket_id, 'title' => 'Linked ticket'];
    }
}

$page_title = $is_edit ? 'Edit Activity' : 'Log an Activity';
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon bg-gradient-primary text-white me-2"><i class="mdi mdi-calendar-check"></i></span>
      <?= $page_title ?>
    </h3>
    <nav aria-label="breadcrumb">
      <a href="<?= $is_edit ? 'activity?rid=' . (int)$rid : 'activities' ?>" class="btn btn-sm btn-light border">
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
            <p class="text-muted mb-4" style="font-size:0.86rem;">
              Every drive, camp, visit and distribution belongs here. Log it when it is planned,
              add the volunteers, then come back afterwards to record who turned up, how many
              people were reached and what happened. That record is what the year's report is
              built from.
            </p>
          <?php endif; ?>

          <form method="POST" action="manage_activity" class="forms-sample" enctype="multipart/form-data">
            <input type="hidden" name="_submit" value="1">
            <input type="hidden" name="rid" value="<?= h($rid) ?>">

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="category_id" class="form-label fw-bold">Programme <span class="text-danger">*</span></label>
                <select name="category_id" id="category_id" class="form-select" required>
                  <option value="">Choose one…</option>
                  <?php foreach ($cats as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $category_id == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3 mb-3">
                <label for="stage" class="form-label fw-bold">Stage</label>
                <select name="stage" id="stage" class="form-select">
                  <?php foreach ($STAGES as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $stage === $k ? 'selected' : '' ?>><?= $v ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3 mb-3">
                <label class="form-label fw-bold d-block">Show in the App</label>
                <div class="form-check form-switch mt-2">
                  <input class="form-check-input" type="checkbox" role="switch" name="status" value="1"
                         id="status" <?= $status ? 'checked' : '' ?>>
                  <label class="form-check-label" for="status">Listed for volunteers</label>
                </div>
              </div>
            </div>

            <div class="form-group mb-3">
              <label for="title" class="form-label fw-bold">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" id="title" class="form-control" maxlength="200" required
                     placeholder="e.g. Food drive at Secunderabad station" value="<?= h($title) ?>">
            </div>

            <div class="form-group mb-4">
              <label for="description" class="form-label fw-bold">What Is Planned</label>
              <textarea name="description" id="description" class="form-control" rows="4"
                        placeholder="What the team will do, what is being distributed, who it is for…"><?= h($description) ?></textarea>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:0.74rem;letter-spacing:0.8px;">When &amp; Where</h6>
            <div class="row">
              <div class="col-md-3 mb-3">
                <label for="start_date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="start_date" class="form-control" required value="<?= h($start_date) ?>">
              </div>
              <div class="col-md-3 mb-3">
                <label for="start_time" class="form-label fw-bold">Start Time</label>
                <input type="time" name="start_time" id="start_time" class="form-control" value="<?= h($start_time) ?>">
              </div>
              <div class="col-md-3 mb-3">
                <label for="end_date" class="form-label fw-bold">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="<?= h($end_date) ?>">
                <small class="text-muted" style="font-size:0.72rem;">Only if it runs over more than a day.</small>
              </div>
              <div class="col-md-3 mb-3">
                <label for="end_time" class="form-label fw-bold">End Time</label>
                <input type="time" name="end_time" id="end_time" class="form-control" value="<?= h($end_time) ?>">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="venue" class="form-label fw-bold">Venue</label>
                <input type="text" name="venue" id="venue" class="form-control" maxlength="200"
                       value="<?= h($venue) ?>" placeholder="Where the team gathers">
              </div>
              <div class="col-md-3 mb-3">
                <label for="area" class="form-label fw-bold">Area</label>
                <input type="text" name="area" id="area" class="form-control" value="<?= h($area) ?>">
              </div>
              <div class="col-md-3 mb-3">
                <label for="city" class="form-label fw-bold">City</label>
                <input type="text" name="city" id="city" class="form-control" value="<?= h($city) ?>">
              </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:0.74rem;letter-spacing:0.8px;">Scale &amp; Ownership</h6>
            <div class="row">
              <div class="col-md-3 mb-3">
                <label for="beneficiaries" class="form-label fw-bold">People Reached</label>
                <input type="number" min="0" name="beneficiaries" id="beneficiaries" class="form-control"
                       value="<?= h($beneficiaries) ?>" placeholder="0">
                <small class="text-muted" style="font-size:0.72rem;">Fill in afterwards.</small>
              </div>
              <div class="col-md-3 mb-3">
                <label for="est_cost" class="form-label fw-bold">Cost</label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" step="0.01" min="0" name="est_cost" id="est_cost" class="form-control"
                         value="<?= h($est_cost) ?>" placeholder="0.00">
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="coordinator_uid" class="form-label fw-bold">Coordinator In Charge</label>
                <select name="coordinator_uid" id="coordinator_uid" class="form-select">
                  <option value="">Nobody yet</option>
                  <?php foreach ($coords as $c): ?>
                    <option value="<?= h($c['uid']) ?>" <?= $coordinator === $c['uid'] ? 'selected' : '' ?>>
                      <?= h($c['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="sponsored_by" class="form-label fw-bold">Sponsored By</label>
                <input type="text" name="sponsored_by" id="sponsored_by" class="form-control" maxlength="160"
                       value="<?= h($sponsored_by) ?>" placeholder="Donor, partner or hospital, if any">
              </div>
              <div class="col-md-6 mb-3">
                <label for="ticket_id" class="form-label fw-bold">Came From Ticket</label>
                <select name="ticket_id" id="ticket_id" class="form-select">
                  <option value="">Not tied to a ticket</option>
                  <?php foreach ($open_tickets as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= $ticket_id == $t['id'] ? 'selected' : '' ?>>
                      <?= h(($t['ticket_no'] ?: '#' . $t['id']) . ' — ' . $t['title']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <small class="text-muted" style="font-size:0.72rem;">Links the spending to the activity.</small>
              </div>
            </div>

            <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size:0.74rem;letter-spacing:0.8px;">Afterwards</h6>
            <div class="form-group mb-3">
              <label for="outcome" class="form-label fw-bold">What Happened</label>
              <textarea name="outcome" id="outcome" class="form-control" rows="4"
                        placeholder="How it went, how many were helped, anything to follow up."><?= h($outcome) ?></textarea>
              <small class="text-muted" style="font-size:0.74rem;">
                Needed before an activity can be marked completed. <span id="outcomeHint"></span>
              </small>
            </div>

            <div class="form-group mb-4">
              <label class="form-label fw-bold">Photographs</label>
              <input type="file" name="proof[]" class="form-control" multiple
                     accept="image/jpeg,image/png,image/webp,image/heic,application/pdf">
              <small class="text-muted" style="font-size:0.74rem;">
                Pictures from the day, and any report. JPG, PNG, WebP, HEIC or PDF, up to 3 MB each.
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

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
              <a href="<?= $is_edit ? 'activity?rid=' . (int)$rid : 'activities' ?>" class="btn btn-light me-md-2">Cancel</a>
              <button type="submit" class="btn btn-gradient-primary btn-lg px-5 fw-bold">
                <i class="mdi mdi-content-save me-1"></i><?= $is_edit ? 'Update Activity' : 'Log Activity' ?>
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
    // Say up front that completing needs a write-up, rather than letting the
    // save bounce back.
    var stage   = document.getElementById('stage');
    var outcome = document.getElementById('outcome');
    var hint    = document.getElementById('outcomeHint');
    if (!stage || !outcome || !hint) return;

    function tick() {
        var needs = stage.value === 'completed';
        var empty = outcome.value.trim() === '';
        hint.textContent = (needs && empty) ? 'Required — this is marked completed.' : '';
        hint.style.color = (needs && empty) ? '#b52a1a' : '';
        outcome.required = needs;
    }
    stage.addEventListener('change', tick);
    outcome.addEventListener('input', tick);
    tick();
})();
</script>

<?php include 'includes/footer.php'; ?>
</div>
