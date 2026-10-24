<?php
include 'includes/header.php';

$rid = isset($_GET['rid']) && is_numeric($_GET['rid']) ? (int)$_GET['rid'] : 0;
if ($rid < 1) {
    set_flash('danger', 'No activity was specified.');
    redirect('activities');
}

// ── Roster and attendance ────────────────────────────────────────────────────
if (isset($_POST['_roster'])) {
    $resp = json_decode(get_api_data_post("$api_url/ops/attendance", with_actor([
        'aid'        => $rid,
        'volunteers' => $_POST['volunteers'] ?? [],
    ])), true);
    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not update the roster.');
    redirect('activity?rid=' . $rid);
}

if (isset($_POST['_mark'])) {
    $resp = json_decode(get_api_data_post("$api_url/ops/attendance", with_actor([
        'aid'       => $rid,
        'action'    => 'mark',
        'uid'       => $_POST['uid']       ?? '',
        'attended'  => !empty($_POST['attended']) ? '1' : '0',
        'hours'     => $_POST['hours']     ?? '0',
        'role_note' => $_POST['role_note'] ?? '',
    ])), true);
    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not record attendance.');
    redirect('activity?rid=' . $rid);
}

if (isset($_POST['_mark_all'])) {
    $resp = json_decode(get_api_data_post("$api_url/ops/attendance", with_actor([
        'aid' => $rid, 'action' => 'mark_all', 'hours' => $_POST['hours'] ?? '0',
    ])), true);
    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not record attendance.');
    redirect('activity?rid=' . $rid);
}

if (isset($_GET['action']) && $_GET['action'] === 'drop' && isset($_GET['uid'])) {
    $resp = json_decode(get_api_data_post("$api_url/ops/attendance", with_actor([
        'aid' => $rid, 'action' => 'remove', 'uid' => $_GET['uid'],
    ])), true);
    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not update the roster.');
    redirect('activity?rid=' . $rid);
}

// ── Stage and delete ─────────────────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'stage' && isset($_GET['to'])) {
    $resp = json_decode(get_api_data("$api_url/ops/manage_activity?action=stage&rid=$rid&to="
                                     . urlencode($_GET['to']) . "&" . actor_qs()), true);
    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not change the stage.');
    redirect('activity?rid=' . $rid);
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $resp = json_decode(get_api_data("$api_url/ops/manage_activity?action=delete&rid=$rid&" . actor_qs()), true);
    $ok = ($resp['status'] ?? '') === 'success';
    set_flash($ok ? 'success' : 'danger', $resp['message'] ?? 'Could not delete the activity.');
    redirect($ok ? 'activities' : 'activity?rid=' . $rid);
}

$resp = json_decode(get_api_data("$api_url/ops/activities?rid=$rid"), true);
$a    = $resp['data'][0] ?? null;

if (!$a) {
    set_flash('danger', 'That activity no longer exists.');
    redirect('activities');
}

$vols   = $resp['volunteers'] ?? [];
$spends = $resp['spends']     ?? [];
$files  = $resp['files']      ?? [];

$roster_uids = array_filter(array_column($vols, 'uid'));

// Everyone who could be added. Volunteers and coordinators both take part.
$people = json_decode(get_api_data("$api_url/global/users?page=1&role=1"), true);
$people = is_array($people['data'] ?? null) ? $people['data'] : [];
$coords = json_decode(get_api_data("$api_url/access/coordinators"), true);
$coords = is_array($coords['data'] ?? null) ? $coords['data'] : [];

$can_edit = is_admin() || $a['added_by'] === portal_uid() || $a['coordinator_uid'] === portal_uid();
$is_shut  = in_array($a['stage'], ['completed', 'cancelled'], true);

// Attendance mistakes usually surface after the write-up, so an admin can
// still correct the roster once an activity is closed.
$can_roster = $can_edit && (!$is_shut || is_admin());

$STAGE_TONE = ['planned' => 'info', 'in_progress' => 'warning', 'completed' => 'success', 'cancelled' => 'muted'];
$tone  = $STAGE_TONE[$a['stage']] ?? 'muted';
$badge = $tone === 'muted' ? 'badge-stm-muted' : 'badge-gradient-' . $tone;
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi <?= h($a['category_icon'] ?: 'mdi-calendar-check') ?>"></i></span>
      <?= h($a['activity_no'] ?: 'Activity #' . $a['id']) ?>
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="activities" class="btn btn-sm btn-light border"><i class="mdi mdi-arrow-left me-1"></i>Register</a>
      <?php if ($can_edit): ?>
        <a href="manage_activity?rid=<?= $rid ?>" class="btn btn-sm btn-gradient-info"><i class="mdi mdi-pencil me-1"></i>Edit</a>
      <?php endif; ?>
      <?php if ($can_edit && $a['stage'] === 'planned'): ?>
        <a href="activity?rid=<?= $rid ?>&action=stage&to=in_progress" class="btn btn-sm btn-gradient-warning">
          <i class="mdi mdi-play me-1"></i>Start
        </a>
      <?php endif; ?>
      <?php if ($can_edit && !$is_shut): ?>
        <a href="activity?rid=<?= $rid ?>&action=stage&to=completed" class="btn btn-sm btn-gradient-success"
           onclick="return confirm('Mark this activity completed? It needs a write-up first.')">
          <i class="mdi mdi-check me-1"></i>Complete
        </a>
        <a href="activity?rid=<?= $rid ?>&action=stage&to=cancelled" class="btn btn-sm btn-outline-secondary"
           onclick="return confirm('Cancel this activity?')">Cancel</a>
      <?php endif; ?>
      <?php if (is_admin()): ?>
        <a href="activity?rid=<?= $rid ?>&action=delete" class="btn btn-sm btn-gradient-danger"
           onclick="return confirm('Delete this activity? Only possible while nobody is recorded as having attended.')">
          <i class="mdi mdi-delete"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">

    <!-- ── The activity ─────────────────────────────────────────────────── -->
    <div class="col-lg-5 mb-4">
      <div class="card border-0 mb-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
              <h5 class="mb-1" style="font-weight:700;color:#2c3250;"><?= h($a['title']) ?></h5>
              <div style="font-size:0.78rem;color:var(--stm-text-muted);">
                <i class="mdi <?= h($a['category_icon'] ?: 'mdi-calendar-check') ?> me-1"></i><?= h($a['category_name'] ?: 'Uncategorised') ?>
              </div>
            </div>
            <span class="<?= $badge ?>"><?= h(ucwords(str_replace('_', ' ', $a['stage']))) ?></span>
          </div>

          <?php if ($a['description']): ?>
            <p style="font-size:0.9rem;color:#4a5168;white-space:pre-line;"><?= h($a['description']) ?></p>
          <?php endif; ?>

          <dl class="detail-list">
            <dt>When</dt>
            <dd>
              <?= nice_date($a['start_date']) ?><?= $a['start_time'] ? ' · ' . date('h:i A', strtotime($a['start_time'])) : '' ?>
              <?php if ($a['end_date'] && $a['end_date'] !== $a['start_date']): ?>
                <br>to <?= nice_date($a['end_date']) ?>
              <?php endif; ?>
            </dd>

            <dt>Where</dt>
            <dd>
              <?= $a['venue'] ? h($a['venue']) : '—' ?>
              <?php if ($a['area'] || $a['city']): ?>
                <div style="font-size:0.8rem;color:var(--stm-text-muted);">
                  <?= h(trim(($a['area'] ?: '') . ($a['area'] && $a['city'] ? ', ' : '') . ($a['city'] ?: ''))) ?>
                </div>
              <?php endif; ?>
            </dd>

            <dt>People Reached</dt>
            <dd style="font-weight:700;color:var(--stm-green-dark);">
              <?= (int)$a['beneficiaries'] > 0 ? number_format((int)$a['beneficiaries']) : 'Not recorded yet' ?>
            </dd>

            <dt>Coordinator</dt>
            <dd><?= $a['coordinator_uid_name'] ? h($a['coordinator_uid_name']) : '<span class="badge-stm-muted">Nobody yet</span>' ?></dd>

            <?php if ($a['sponsored_by']): ?>
              <dt>Sponsored By</dt>
              <dd><?= h($a['sponsored_by']) ?></dd>
            <?php endif; ?>

            <?php if ((float)$a['est_cost'] > 0): ?>
              <dt>Cost</dt>
              <dd><?= money($a['est_cost']) ?></dd>
            <?php endif; ?>

            <?php if ($a['ticket_id']): ?>
              <dt>From Ticket</dt>
              <dd><a href="ticket?rid=<?= (int)$a['ticket_id'] ?>">#<?= (int)$a['ticket_id'] ?></a></dd>
            <?php endif; ?>
          </dl>

          <?php if ($a['outcome']): ?>
            <h6 class="fw-bold text-uppercase text-muted mt-4 mb-2" style="font-size:0.72rem;letter-spacing:0.7px;">What Happened</h6>
            <p style="font-size:0.9rem;color:#4a5168;white-space:pre-line;"><?= h($a['outcome']) ?></p>
          <?php elseif (!$is_shut): ?>
            <div class="gate-note mt-3">
              <i class="mdi mdi-pencil-outline"></i>
              <div>No write-up yet. <a href="manage_activity?rid=<?= $rid ?>">Add one</a> before marking this completed.</div>
            </div>
          <?php endif; ?>

          <div style="font-size:0.72rem;color:var(--stm-muted);margin-top:16px;">
            Logged by <?= h($a['added_by_name'] ?: '—') ?> · <?= nice_datetime($a['added_on']) ?>
          </div>
        </div>
      </div>

      <?php if (!empty($files)): ?>
        <div class="card border-0 mb-4">
          <div class="card-body">
            <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
              <i class="mdi mdi-camera me-2" style="color:var(--stm-navy);"></i>Photographs (<?= count($files) ?>)
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
          </div>
        </div>
      <?php endif; ?>

      <?php if (!empty($spends)): ?>
        <div class="card border-0">
          <div class="card-body">
            <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
              <i class="mdi mdi-receipt me-2" style="color:var(--stm-navy);"></i>What It Cost
            </h6>
            <table class="table table-hover mb-0">
              <tbody>
                <?php foreach ($spends as $s): ?>
                  <tr>
                    <td style="font-size:0.84rem;"><?= h($s['title']) ?>
                      <div style="font-size:0.72rem;color:var(--stm-text-muted);"><?= h($s['request_no']) ?> · <?= nice_date($s['date']) ?></div>
                    </td>
                    <td style="text-align:right;font-weight:700;color:#c0392b;white-space:nowrap;"><?= money($s['amount']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- ── Who took part ────────────────────────────────────────────────── -->
    <div class="col-lg-7 mb-4">

      <?php if ($can_roster): ?>
        <div class="card border-0 mb-4">
          <div class="card-body">
            <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
              <i class="mdi mdi-account-multiple-plus me-2" style="color:var(--stm-navy);"></i>Add Volunteers
            </h6>
            <form method="POST" action="activity?rid=<?= $rid ?>" class="row g-2 align-items-end">
              <input type="hidden" name="_roster" value="1">
              <div class="col-md-9">
                <label for="volunteers" class="form-label fw-bold" style="font-size:0.8rem;">
                  Pick everyone who is coming — hold Ctrl to choose several
                </label>
                <select name="volunteers[]" id="volunteers" class="form-select" multiple size="6">
                  <optgroup label="Volunteers">
                    <?php foreach ($people as $p): if (in_array($p['id'], $roster_uids, true)) continue; ?>
                      <option value="<?= h($p['id']) ?>"><?= h($p['name']) ?><?= $p['mobile'] ? ' · ' . h($p['mobile']) : '' ?></option>
                    <?php endforeach; ?>
                  </optgroup>
                  <optgroup label="Coordinators">
                    <?php foreach ($coords as $c): if (in_array($c['uid'], $roster_uids, true)) continue; ?>
                      <option value="<?= h($c['uid']) ?>"><?= h($c['name']) ?></option>
                    <?php endforeach; ?>
                  </optgroup>
                </select>
                <small class="text-muted" style="font-size:0.72rem;">
                  The volunteer list shows the first page of members; coordinators are listed in full.
                </small>
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-gradient-primary w-100">
                  <i class="mdi mdi-plus me-1"></i>Add
                </button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <div class="card border-0">
        <div class="card-body px-0 pb-0">
          <div class="d-flex justify-content-between align-items-center px-4 pb-3 flex-wrap gap-2">
            <h6 class="mb-0" style="font-weight:700;color:#2c3250;">
              <i class="mdi mdi-account-check-outline me-2" style="color:var(--stm-navy);"></i>
              Roster &amp; Attendance
            </h6>
            <span class="text-muted" style="font-size:0.78rem;">
              <?= (int)$a['attended_count'] ?> of <?= (int)$a['volunteer_count'] ?> attended ·
              <?= units_text($a['volunteer_hours']) ?> hours
            </span>
          </div>

          <?php if ($can_roster && !empty($vols)): ?>
            <form method="POST" action="activity?rid=<?= $rid ?>" class="px-4 pb-3 d-flex align-items-end gap-2 flex-wrap">
              <input type="hidden" name="_mark_all" value="1">
              <div>
                <label for="allhours" class="form-label fw-bold" style="font-size:0.78rem;">Everyone turned up, for</label>
                <div class="input-group" style="width:160px;">
                  <input type="number" step="0.5" min="0" max="24" name="hours" id="allhours"
                         class="form-control form-control-sm" value="4">
                  <span class="input-group-text">hours</span>
                </div>
              </div>
              <button type="submit" class="btn btn-sm btn-gradient-success"
                      onclick="return confirm('Mark everyone on the roster as present?')">
                <i class="mdi mdi-check-all me-1"></i>Mark All Present
              </button>
            </form>
          <?php endif; ?>

          <?php if (empty($vols)): ?>
            <div class="stm-empty">
              <i class="mdi mdi-account-off-outline"></i>
              <p>Nobody on the roster yet.</p>
              <small>Add the volunteers who are coming, then record who turned up on the day.</small>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th style="padding-left:20px;">Volunteer</th>
                    <th>Attended</th>
                    <th>Hours</th>
                    <th>Role</th>
                    <th style="padding-right:20px;text-align:center;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($vols as $v): ?>
                    <tr>
                      <td style="padding-left:20px;">
                        <div style="font-weight:600;color:#2c3250;"><?= h($v['name'] ?: ('Member #' . $v['volunteer_id'])) ?></div>
                        <div style="font-size:0.72rem;color:var(--stm-text-muted);">
                          <?= h($v['uid'] ?: ('#' . $v['volunteer_id'])) ?><?= $v['mobile'] ? ' · ' . h($v['mobile']) : '' ?>
                        </div>
                      </td>
                      <td>
                        <?= (int)$v['attended'] === 1
                              ? '<span class="badge-gradient-success">Present</span>'
                              : '<span class="badge-stm-muted">Not marked</span>' ?>
                      </td>
                      <td style="font-weight:600;"><?= (float)$v['hours'] > 0 ? units_text($v['hours']) : '—' ?></td>
                      <td style="font-size:0.82rem;color:var(--stm-text-muted);max-width:180px;">
                        <?= $v['role_note'] ? h($v['role_note']) : '—' ?>
                      </td>
                      <td style="padding-right:20px;text-align:center;white-space:nowrap;">
                        <?php if ($can_roster): ?>
                          <button type="button" class="btn btn-gradient-info btn-xs me-1 js-mark"
                                  data-uid="<?= h($v['uid'] ?: $v['volunteer_id']) ?>"
                                  data-name="<?= h($v['name'] ?: ('Member #' . $v['volunteer_id'])) ?>"
                                  data-attended="<?= (int)$v['attended'] ?>"
                                  data-hours="<?= h($v['hours']) ?>"
                                  data-note="<?= h($v['role_note']) ?>" title="Record attendance">
                            <i class="mdi mdi-clipboard-check-outline"></i>
                          </button>
                          <a href="activity?rid=<?= $rid ?>&action=drop&uid=<?= urlencode($v['uid'] ?: $v['volunteer_id']) ?>"
                             class="btn btn-gradient-danger btn-xs" title="Take off the roster"
                             onclick="return confirm('Remove <?= h(addslashes($v['name'] ?: ('Member #' . $v['volunteer_id']))) ?> from this activity?')">
                            <i class="mdi mdi-close"></i>
                          </a>
                        <?php else: ?>
                          <span class="text-muted" style="font-size:0.74rem;">—</span>
                        <?php endif; ?>
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
  </div>

</div>

<!-- ── Record one person's attendance ─────────────────────────────────────── -->
<div class="modal fade" id="markModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:14px;">
      <form method="POST" action="activity?rid=<?= $rid ?>" class="forms-sample">
        <input type="hidden" name="_mark" value="1">
        <input type="hidden" name="uid" id="mkUid">
        <div class="modal-header">
          <h5 class="modal-title" style="font-weight:700;color:var(--stm-navy);">
            <i class="mdi mdi-clipboard-check-outline me-1"></i>Attendance
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3" style="font-size:0.9rem;"><strong id="mkName"></strong></p>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" name="attended" value="1" id="mkAttended">
            <label class="form-check-label" for="mkAttended">They turned up</label>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold" for="mkHours">Hours Given</label>
            <input type="number" step="0.5" min="0" max="24" name="hours" id="mkHours" class="form-control">
            <small class="text-muted" style="font-size:0.74rem;">Left at zero for anyone marked absent.</small>
          </div>
          <div class="mb-0">
            <label class="form-label fw-bold" for="mkNote">What They Did</label>
            <input type="text" name="role_note" id="mkNote" class="form-control" maxlength="160"
                   placeholder="e.g. served food, registration desk, photography">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-gradient-primary fw-bold">
            <i class="mdi mdi-content-save me-1"></i>Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
    "use strict";
    var el = document.getElementById('markModal');
    if (!el) return;
    var modal = new bootstrap.Modal(el);

    document.querySelectorAll('.js-mark').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var g = function (k) { return btn.getAttribute('data-' + k) || ''; };
            document.getElementById('mkUid').value       = g('uid');
            document.getElementById('mkName').textContent = g('name');
            document.getElementById('mkAttended').checked = g('attended') === '1';
            document.getElementById('mkHours').value      = parseFloat(g('hours') || '0') || '';
            document.getElementById('mkNote').value       = g('note');
            modal.show();
        });
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
</div>
