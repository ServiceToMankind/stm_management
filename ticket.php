<?php
include 'includes/header.php';

$rid = isset($_GET['rid']) && is_numeric($_GET['rid']) ? (int)$_GET['rid'] : 0;
if ($rid < 1) {
    set_flash('danger', 'No ticket was specified.');
    redirect('tickets');
}

// ── Post an update or move the ticket along ──────────────────────────────────
if (isset($_POST['_update'])) {
    $post = with_actor([
        'rid'        => $rid,
        'note'       => $_POST['note']       ?? '',
        'new_status' => $_POST['new_status'] ?? '',
    ]);

    if (trim($post['note']) === '' && $post['new_status'] === '' && empty($_FILES['proof']['name'][0])) {
        set_flash('danger', 'Write an update, change the status, or attach something.');
    } else {
        $resp = post_api_data_files("$api_url/tickets/manage_ticket", $post, 'proof', $_FILES['proof'] ?? null);
        $resp = json_decode($resp, true);

        $msg = $resp['message'] ?? 'Could not post the update.';
        if (!empty($resp['warnings'])) $msg .= ' (' . implode(' ', $resp['warnings']) . ')';
        set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger', $msg);
    }

    redirect('ticket?rid=' . $rid);
}

// ── Delete ───────────────────────────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $resp = json_decode(get_api_data("$api_url/tickets/manage_ticket?action=delete&rid=$rid&" . actor_qs()), true);
    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not delete the ticket.');
    redirect('' . (($resp['status'] ?? '') === 'success' ? 'tickets' : 'ticket?rid=' . $rid));
}

$resp = json_decode(get_api_data("$api_url/tickets/tickets?rid=$rid"), true);
$t    = $resp['data'][0] ?? null;

if (!$t) {
    set_flash('danger', 'That ticket no longer exists.');
    redirect('tickets');
}

$updates = $resp['updates'] ?? [];
$funds   = $resp['funds']   ?? [];
$files     = $resp['files']     ?? [];
$assignees = $resp['assignees'] ?? [];

$assignee_uids = array_column($assignees, 'uid');
$is_mine    = $t['raised_by'] === portal_uid() || in_array(portal_uid(), $assignee_uids, true);
$can_edit   = is_admin() || ($is_mine && !in_array($t['status'], ['closed', 'rejected'], true));
$is_settled = in_array($t['status'], ['closed', 'rejected'], true);

// Admins alone may close or reject; everyone else works the ticket forward.
$open_statuses = ['open' => 'Open', 'in_review' => 'In Review', 'approved' => 'Approved',
                  'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'resolved' => 'Resolved'];
if (is_admin()) {
    $open_statuses['closed']   = 'Closed';
    $open_statuses['rejected'] = 'Rejected';
}

$approved_total = 0;
foreach ($funds as $f) {
    if (in_array($f['status'], ['approved', 'settled'], true)) {
        $approved_total += $f['approved_amount'] !== null ? (float)$f['approved_amount'] : (float)$f['amount'];
    }
}
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi <?= h($t['category_icon'] ?: 'mdi-ticket-outline') ?>"></i></span>
      <?= h($t['ticket_no'] ?: 'Ticket #' . $t['id']) ?>
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="tickets" class="btn btn-sm btn-light border"><i class="mdi mdi-arrow-left me-1"></i>Board</a>
      <?php if ($can_edit): ?>
        <a href="manage_ticket?rid=<?= $rid ?>" class="btn btn-sm btn-gradient-info"><i class="mdi mdi-pencil me-1"></i>Edit</a>
      <?php endif; ?>
      <?php if (!$is_settled): ?>
        <a href="manage_fund_request?ticket_id=<?= $rid ?>" class="btn btn-sm btn-gradient-success">
          <i class="mdi mdi-cash-usd me-1"></i>Request Funds
        </a>
      <?php endif; ?>
      <?php if (is_admin()): ?>
        <a href="ticket?rid=<?= $rid ?>&action=delete" class="btn btn-sm btn-gradient-danger"
           onclick="return confirm('Delete this ticket and its updates? This cannot be undone.')">
          <i class="mdi mdi-delete"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">

    <!-- ── Left: the ticket itself ──────────────────────────────────────── -->
    <div class="col-lg-7 mb-4">

      <div class="card border-0 mb-4">
        <div class="card-body px-4 py-4">

          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
              <h5 class="mb-1" style="font-weight:700;color:#2c3250;"><?= h($t['title']) ?></h5>
              <div style="font-size:0.78rem;color:var(--stm-text-muted);">
                <i class="mdi <?= h($t['category_icon'] ?: 'mdi-ticket-outline') ?> me-1"></i><?= h($t['category_name']) ?>
                · raised by <?= h($t['raised_by_name'] ?: '—') ?> on <?= nice_date($t['created_at']) ?>
              </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
              <?= priority_badge($t['priority']) ?>
              <?= ticket_status_badge($t['status']) ?>
            </div>
          </div>

          <?php if ($t['description']): ?>
            <p style="font-size:0.9rem;color:#4a5168;white-space:pre-line;margin-bottom:1.25rem;"><?= h($t['description']) ?></p>
          <?php endif; ?>

          <div class="row">
            <div class="col-sm-6">
              <dl class="detail-list">
                <dt>Beneficiary</dt>
                <dd><?= $t['beneficiary_name'] ? h($t['beneficiary_name']) : '—' ?></dd>

                <dt>Contact</dt>
                <dd>
                  <?php if ($t['beneficiary_mobile']): ?>
                    <a href="tel:<?= h($t['beneficiary_mobile']) ?>"><?= h($t['beneficiary_mobile']) ?></a>
                  <?php else: ?>—<?php endif; ?>
                </dd>

                <dt>Location</dt>
                <dd><?= h(trim(($t['area'] ?: '') . ($t['area'] && $t['city'] ? ', ' : '') . ($t['city'] ?: ''))) ?: '—' ?></dd>
              </dl>
            </div>
            <div class="col-sm-6">
              <dl class="detail-list">
                <dt>Assigned To</dt>
                <dd>
                  <?php if (empty($assignees)): ?>
                    <span class="badge-stm-muted">Nobody yet</span>
                  <?php else: foreach ($assignees as $as): ?>
                    <div style="margin-bottom:3px;">
                      <?= h($as['name'] ?: $as['uid']) ?>
                      <?php if ($as['mobile']): ?>
                        <span style="font-size:0.78rem;color:var(--stm-text-muted);">· <?= h($as['mobile']) ?></span>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; endif; ?>
                </dd>

                <dt>Needed By</dt>
                <dd><?= nice_date($t['due_date']) ?></dd>

                <dt>Estimated Cost</dt>
                <dd><?= (float)$t['est_amount'] > 0 ? money($t['est_amount']) : '—' ?></dd>
              </dl>
            </div>
          </div>

          <?php if (!empty($files)): ?>
            <h6 class="fw-bold text-uppercase text-muted mt-4 mb-2" style="font-size:0.72rem;letter-spacing:0.7px;">
              Attachments (<?= count($files) ?>)
            </h6>
            <div class="proof-grid">
              <?php foreach ($files as $f): ?>
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
      </div>

      <!-- Fund requests against this ticket -->
      <div class="card border-0">
        <div class="card-body px-4 py-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0" style="font-weight:700;color:#2c3250;">
              <i class="mdi mdi-cash-multiple me-2" style="color:var(--stm-navy);"></i>Fund Requests
            </h6>
            <?php if ($approved_total > 0): ?>
              <span class="text-muted" style="font-size:0.78rem;"><?= money($approved_total) ?> approved</span>
            <?php endif; ?>
          </div>

          <?php if (empty($funds)): ?>
            <p class="text-muted mb-0" style="font-size:0.85rem;">
              Nothing requested yet.
              <?php if (!$is_settled): ?>
                <a href="manage_fund_request?ticket_id=<?= $rid ?>">Raise a request</a> when you know what this will cost —
                the treasurer has to approve it before any money is spent.
              <?php endif; ?>
            </p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr><th>Request</th><th>Asked</th><th>Approved</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                  <?php foreach ($funds as $f): ?>
                    <tr>
                      <td>
                        <div style="font-weight:600;font-size:0.85rem;"><?= h($f['title']) ?></div>
                        <div style="font-size:0.72rem;color:var(--stm-text-muted);">
                          <?= h($f['request_no']) ?> · <?= h($f['raised_by_name'] ?: '—') ?>
                        </div>
                      </td>
                      <td style="font-weight:600;"><?= money($f['amount']) ?></td>
                      <td style="font-weight:700;color:var(--stm-green-dark);">
                        <?= $f['approved_amount'] !== null ? money($f['approved_amount']) : '—' ?>
                      </td>
                      <td><?= fund_status_badge($f['status']) ?></td>
                      <td style="text-align:right;">
                        <a href="fund_request?rid=<?= (int)$f['id'] ?>" class="btn btn-gradient-info btn-xs">
                          <i class="mdi mdi-arrow-right"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- ── Right: the timeline ──────────────────────────────────────────── -->
    <div class="col-lg-5 mb-4">
      <div class="card border-0">
        <div class="card-body px-4 py-4">

          <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
            <i class="mdi mdi-timeline-text-outline me-2" style="color:var(--stm-navy);"></i>Activity
          </h6>

          <?php if (!$is_settled || is_admin()): ?>
            <form method="POST" action="ticket?rid=<?= $rid ?>" enctype="multipart/form-data" class="mb-4">
              <input type="hidden" name="_update" value="1">

              <textarea name="note" class="form-control mb-2" rows="3"
                        placeholder="What happened? Who did you speak to? What is next?"></textarea>

              <div class="row g-2 mb-2">
                <div class="col-7">
                  <select name="new_status" class="form-select form-select-sm">
                    <option value="">Leave status as <?= h(str_replace('_', ' ', $t['status'])) ?></option>
                    <?php foreach ($open_statuses as $k => $v): ?>
                      <?php if ($k === $t['status']) continue; ?>
                      <option value="<?= $k ?>">Move to <?= $v ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-5">
                  <button type="submit" class="btn btn-sm btn-gradient-primary w-100">
                    <i class="mdi mdi-send me-1"></i>Post
                  </button>
                </div>
              </div>

              <input type="file" name="proof[]" class="form-control form-control-sm" multiple
                     accept="image/jpeg,image/png,image/webp,image/heic,application/pdf">
              <small class="text-muted" style="font-size:0.72rem;">Attach a photo or receipt with this update (optional).</small>
            </form>
          <?php else: ?>
            <div class="gate-note mb-4">
              <i class="mdi mdi-lock-outline"></i>
              <div>This ticket is <?= h($t['status']) ?>, so it is read-only. Ask an admin to reopen it if there is more to do.</div>
            </div>
          <?php endif; ?>

          <?php if (empty($updates)): ?>
            <p class="text-muted mb-0" style="font-size:0.85rem;">Nothing logged yet.</p>
          <?php else: ?>
            <div class="stm-timeline">
              <?php foreach (array_reverse($updates) as $u): ?>
                <?php
                  $classes = 'stm-timeline-item';
                  if ((int)$u['is_system'] === 1) $classes .= ' is-system';
                  if ($u['status_to'])            $classes .= ' is-status';
                ?>
                <div class="<?= $classes ?>">
                  <div class="d-flex justify-content-between align-items-baseline gap-2">
                    <span class="stm-timeline-who"><?= h($u['uid_name'] ?: 'System') ?></span>
                    <span class="stm-timeline-when" title="<?= h(nice_datetime($u['created_at'])) ?>">
                      <?= time_ago($u['created_at']) ?>
                    </span>
                  </div>

                  <?php if ($u['status_to']): ?>
                    <div style="font-size:0.78rem;margin-top:3px;">
                      <?php if ($u['status_from']): ?>
                        <span class="badge-stm-muted"><?= h(ucwords(str_replace('_', ' ', $u['status_from']))) ?></span>
                        <i class="mdi mdi-arrow-right mx-1" style="color:var(--stm-muted);"></i>
                      <?php endif; ?>
                      <?= ticket_status_badge($u['status_to']) ?>
                    </div>
                  <?php endif; ?>

                  <?php if (trim((string)$u['note']) !== ''): ?>
                    <p class="stm-timeline-note"><?= h($u['note']) ?></p>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </div>

</div>
<?php include 'includes/footer.php'; ?>
</div>
