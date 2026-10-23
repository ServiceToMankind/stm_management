<?php
include 'includes/header.php';

$page     = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$donor_id = isset($_GET['donor_id']) && is_numeric($_GET['donor_id']) ? (int)$_GET['donor_id'] : 0;
$group    = $_GET['group'] ?? 'all';
$year     = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : 0;

// ── Undo a wrongly logged donation ───────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['rid'])) {
    $resp = json_decode(get_api_data("$api_url/blood/donations?action=delete&rid="
                                     . (int)$_GET['rid'] . "&" . actor_qs()), true);
    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not remove the donation.');
    redirect('blood_log' . ($donor_id ? '?donor_id=' . $donor_id : ''));
}

// ── Save a correction ────────────────────────────────────────────────────────
if (isset($_POST['_edit'])) {
    $resp = json_decode(get_api_data_post("$api_url/blood/donations", with_actor([
        'rid'              => $_POST['rid']              ?? '',
        'donor_id'         => $_POST['donor_id']         ?? '',
        'blood_request_id' => $_POST['blood_request_id'] ?? '',
        'donated_on'       => $_POST['donated_on']       ?? '',
        'units'            => $_POST['units']            ?? '1',
        'hospital'         => $_POST['hospital']         ?? '',
        'recipient_name'   => $_POST['recipient_name']   ?? '',
        'remarks'          => $_POST['remarks']          ?? '',
    ])), true);

    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not save the donation.');
    redirect('blood_log' . ($donor_id ? '?donor_id=' . $donor_id : ''));
}

$url = "$api_url/blood/donations?page=$page&group=" . urlencode($group);
if ($donor_id > 0) $url .= "&donor_id=$donor_id";
if ($year > 0)     $url .= "&year=$year";

$resp        = json_decode(get_api_data($url), true);
$rows        = is_array($resp['data'] ?? null) ? $resp['data'] : [];
$total_pages = $resp['total_pages']   ?? 0;
$total       = $resp['total_records'] ?? 0;
$total_units = $resp['total_units']   ?? 0;

$GROUPS = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];

$donor_name = '';
if ($donor_id > 0 && !empty($rows)) $donor_name = $rows[0]['name'];

function log_link(array $override = [])
{
    $base = [
        'donor_id' => $_GET['donor_id'] ?? '',
        'group'    => $_GET['group']    ?? '',
        'year'     => $_GET['year']     ?? '',
    ];
    $params = array_filter(array_merge($base, $override), function ($v) {
        return $v !== '' && $v !== null && $v !== 'all';
    });
    return 'blood_log' . ($params ? '?' . http_build_query($params) : '');
}
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-history"></i></span>
      Donation Log<?= $donor_name ? ' · ' . h($donor_name) : '' ?>
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" class="d-flex align-items-center gap-2">
        <?php if ($donor_id): ?><input type="hidden" name="donor_id" value="<?= $donor_id ?>"><?php endif; ?>
        <select name="group" class="form-select form-select-sm" style="width:115px;">
          <option value="all">Any group</option>
          <?php foreach ($GROUPS as $g): ?>
            <option value="<?= h($g) ?>" <?= $group === $g ? 'selected' : '' ?>><?= h($g) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="year" class="form-select form-select-sm" style="width:110px;">
          <option value="">All years</option>
          <?php for ($yy = (int)date('Y'); $yy >= 2019; $yy--): ?>
            <option value="<?= $yy ?>" <?= $year === $yy ? 'selected' : '' ?>><?= $yy ?></option>
          <?php endfor; ?>
        </select>
        <button class="btn btn-sm btn-gradient-primary" type="submit"><i class="mdi mdi-magnify me-1"></i>Filter</button>
      </form>
      <?php if ($donor_id): ?>
        <a href="blood_log" class="btn btn-sm btn-outline-secondary">Everyone</a>
      <?php endif; ?>
      <a href="blood_requests" class="btn btn-sm btn-gradient-danger">
        <i class="mdi mdi-bullhorn-outline me-1"></i>Requirements
      </a>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-4 mb-3">
      <div class="mini-stat">
        <div class="mini-stat-icon" style="background:#fdf3f2;color:var(--stm-blood);"><i class="mdi mdi-water"></i></div>
        <div>
          <p class="mini-stat-label">Donations</p>
          <p class="mini-stat-value"><?= number_format((int)$total) ?></p>
          <p class="mini-stat-note">In this view</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="mini-stat">
        <div class="mini-stat-icon" style="background:#e8f8f1;color:#1a6b35;"><i class="mdi mdi-heart-outline"></i></div>
        <div>
          <p class="mini-stat-label">Units</p>
          <p class="mini-stat-value"><?= units_text($total_units) ?></p>
          <p class="mini-stat-note">Given between them</p>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0">
    <div class="card-body px-0 pb-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:20px;">Date</th>
              <th>Donor</th>
              <th>Group</th>
              <th>Units</th>
              <th>Where / For Whom</th>
              <th>Logged By</th>
              <th style="padding-right:20px;text-align:center;"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="7">
                  <div class="stm-empty">
                    <i class="mdi mdi-water-off"></i>
                    <p>No donations logged in this view.</p>
                    <small>Donations are recorded from a requirement's page, as each donor turns up.</small>
                  </div>
                </td>
              </tr>
            <?php else: foreach ($rows as $d): ?>
              <tr>
                <td style="padding-left:20px;white-space:nowrap;font-size:0.82rem;">
                  <?= nice_date($d['donated_on']) ?>
                </td>
                <td>
                  <a href="manage_blood_donor?rid=<?= (int)$d['donor_id'] ?>"
                     style="font-weight:600;color:#2c3250;text-decoration:none;"><?= h($d['name']) ?></a>
                  <?php if ($d['mobile']): ?>
                    <div style="font-size:0.74rem;color:var(--stm-text-muted);"><?= h($d['mobile']) ?></div>
                  <?php endif; ?>
                </td>
                <td><span style="font-weight:800;color:var(--stm-blood);"><?= h($d['blood_group']) ?></span></td>
                <td style="font-weight:600;"><?= units_text($d['units']) ?></td>
                <td style="font-size:0.82rem;max-width:250px;">
                  <?= $d['hospital'] ? h($d['hospital']) : '<span class="text-muted">—</span>' ?>
                  <?php if ($d['recipient_name']): ?>
                    <div style="font-size:0.74rem;color:var(--stm-text-muted);">for <?= h($d['recipient_name']) ?></div>
                  <?php endif; ?>
                  <?php if ($d['request_no']): ?>
                    <div style="font-size:0.72rem;">
                      <a href="blood_request?rid=<?= (int)$d['blood_request_id'] ?>"><?= h($d['request_no']) ?></a>
                    </div>
                  <?php endif; ?>
                </td>
                <td style="font-size:0.8rem;"><?= h($d['logged_by_name'] ?: '—') ?></td>
                <td style="padding-right:20px;text-align:center;white-space:nowrap;">
                  <button type="button" class="btn btn-gradient-info btn-xs me-1 js-edit-donation"
                          title="Correct this entry"
                          data-rid="<?= (int)$d['id'] ?>"
                          data-donor="<?= (int)$d['donor_id'] ?>"
                          data-donor-name="<?= h($d['name'] . ' — ' . $d['blood_group'] . ($d['area'] ? ', ' . $d['area'] : '')) ?>"
                          data-request="<?= (int)($d['blood_request_id'] ?? 0) ?>"
                          data-date="<?= h($d['donated_on']) ?>"
                          data-units="<?= h($d['units']) ?>"
                          data-hospital="<?= h($d['hospital']) ?>"
                          data-recipient="<?= h($d['recipient_name']) ?>"
                          data-remarks="<?= h($d['remarks']) ?>">
                    <i class="mdi mdi-pencil"></i>
                  </button>
                  <a href="<?= h(log_link(['action' => 'delete', 'rid' => $d['id']])) ?>"
                     class="btn btn-gradient-danger btn-xs"
                     onclick="return confirm('Remove this donation from the log? The donor\'s eligibility date goes back to what it was.')">
                    <i class="mdi mdi-delete"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($total_pages > 1): ?>
      <div class="px-4 py-3 d-flex justify-content-center">
        <ul class="pagination pagination-sm mb-0">
          <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= h(log_link(['page' => $page - 1])) ?>">‹ Prev</a></li>
          <?php endif; ?>
          <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= h(log_link(['page' => $i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="<?= h(log_link(['page' => $page + 1])) ?>">Next ›</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<!-- ── Correct a logged donation ──────────────────────────────────────────── -->
<div class="modal fade" id="editDonationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius:14px;">
      <form method="POST" action="blood_log<?= $donor_id ? '?donor_id=' . $donor_id : '' ?>" class="forms-sample">
        <input type="hidden" name="_edit" value="1">
        <input type="hidden" name="rid" id="edRid">
        <input type="hidden" name="donor_id" id="edDonorId">
        <input type="hidden" name="blood_request_id" id="edRequestId">

        <div class="modal-header">
          <h5 class="modal-title" style="font-weight:700;color:var(--stm-navy);">
            <i class="mdi mdi-pencil me-1"></i>Correct Donation
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <p class="text-muted" style="font-size:0.84rem;">
            Editing re-counts the donor's eligibility and the units on the requirement,
            so both stay true to the log.
          </p>

          <div class="mb-3">
            <label class="form-label fw-bold">Donor</label>
            <input type="text" class="form-control bg-light" id="edDonorName" readonly>
            <small class="text-muted" style="font-size:0.74rem;">
              To move this donation to a different donor, delete it and log it again.
            </small>
          </div>

          <div class="row">
            <div class="col-sm-4 mb-3">
              <label class="form-label fw-bold" for="edDate">Date</label>
              <input type="date" name="donated_on" id="edDate" class="form-control" max="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-sm-3 mb-3">
              <label class="form-label fw-bold" for="edUnits">Units</label>
              <input type="number" step="0.5" min="0.5" max="5" name="units" id="edUnits" class="form-control" required>
            </div>
            <div class="col-sm-5 mb-3">
              <label class="form-label fw-bold" for="edHospital">Where</label>
              <input type="text" name="hospital" id="edHospital" class="form-control" maxlength="180">
            </div>
          </div>

          <div class="row">
            <div class="col-sm-5 mb-3">
              <label class="form-label fw-bold" for="edRecipient">For Whom</label>
              <input type="text" name="recipient_name" id="edRecipient" class="form-control" maxlength="120">
            </div>
            <div class="col-sm-7 mb-0">
              <label class="form-label fw-bold" for="edRemarks">Remarks</label>
              <input type="text" name="remarks" id="edRemarks" class="form-control" maxlength="255">
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-gradient-primary fw-bold">
            <i class="mdi mdi-content-save me-1"></i>Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
    "use strict";
    var el = document.getElementById('editDonationModal');
    if (!el) return;
    var modal = new bootstrap.Modal(el);

    document.querySelectorAll('.js-edit-donation').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var g = function (k) { return btn.getAttribute('data-' + k) || ''; };
            document.getElementById('edRid').value       = g('rid');
            document.getElementById('edDonorId').value    = g('donor');
            document.getElementById('edDonorName').value  = g('donor-name');
            document.getElementById('edRequestId').value  = g('request') === '0' ? '' : g('request');
            document.getElementById('edDate').value       = g('date');
            document.getElementById('edUnits').value      = parseFloat(g('units') || '1');
            document.getElementById('edHospital').value   = g('hospital');
            document.getElementById('edRecipient').value  = g('recipient');
            document.getElementById('edRemarks').value    = g('remarks');
            modal.show();
        });
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
</div>
