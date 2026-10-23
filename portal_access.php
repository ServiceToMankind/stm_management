<?php
include 'includes/header.php';

// Handing out admin and treasurer rights is an admin's job alone.
require_portal_role(['admin']);

$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$q      = $_GET['q']           ?? '';
$filter = $_GET['portal_role'] ?? '';

// ── Set or reset an access level ─────────────────────────────────────────────
if (isset($_POST['_set'])) {
    $resp = json_decode(get_api_data_post("$api_url/access/portal_access", with_actor([
        'uid'         => $_POST['uid']         ?? '',
        'portal_role' => $_POST['portal_role'] ?? '',
        'status'      => isset($_POST['status']) ? '1' : '0',
        'note'        => $_POST['note']        ?? '',
    ])), true);

    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not change the access level.');
    redirect('portal_access' . ($q !== '' ? '?q=' . urlencode($q) : ''));
}

if (isset($_GET['action']) && $_GET['action'] === 'reset' && isset($_GET['uid'])) {
    $resp = json_decode(get_api_data("$api_url/access/portal_access?action=reset&uid="
                                     . urlencode($_GET['uid']) . "&" . actor_qs()), true);
    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not reset the access level.');
    redirect('portal_access');
}

$url = "$api_url/access/portal_access?page=$page";
if ($q !== '')      $url .= "&q=" . urlencode($q);
if ($filter !== '') $url .= "&portal_role=" . urlencode($filter);

$resp        = json_decode(get_api_data($url), true);
$rows        = is_array($resp['data'] ?? null) ? $resp['data'] : [];
$total_pages = $resp['total_pages']   ?? 0;
$total       = $resp['total_records'] ?? 0;
$summary     = $resp['summary']       ?? [];

function access_link(array $override = [])
{
    $base = ['q' => $_GET['q'] ?? '', 'portal_role' => $_GET['portal_role'] ?? ''];
    $params = array_filter(array_merge($base, $override), function ($v) {
        return $v !== '' && $v !== null;
    });
    return 'portal_access' . ($params ? '?' . http_build_query($params) : '');
}

$role_tone = ['admin' => 'danger', 'treasurer' => 'warning', 'coordinator' => 'info'];
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-shield-account-outline"></i></span>
      Portal Access
    </h3>
    <form method="GET" class="d-flex align-items-center gap-2">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Name, email or STM id…"
             value="<?= h($q) ?>" style="width:220px;">
      <button class="btn btn-sm btn-gradient-primary" type="submit"><i class="mdi mdi-magnify me-1"></i>Search</button>
      <?php if ($q !== ''): ?>
        <a href="portal_access" class="btn btn-sm btn-outline-secondary">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="gate-note mb-4">
    <i class="mdi mdi-information-outline"></i>
    <div>
      Only coordinators on an active account can sign in to this portal — volunteers and donors
      cannot, whatever their password. Everyone listed below is already a coordinator; what you
      set here is what they can do once inside.
      <strong>Treasurer</strong> sanctions fund requests. <strong>Administrator</strong> does that
      too, plus closing tickets, direct spend entry, deletions and this page.
    </div>
  </div>

  <!-- Who holds what -->
  <div class="row mb-4">
    <div class="col-md-4 mb-3">
      <div class="mini-stat">
        <div class="mini-stat-icon" style="background:#fde8e8;color:#b52a1a;"><i class="mdi mdi-shield-account-outline"></i></div>
        <div>
          <p class="mini-stat-label">Administrators</p>
          <p class="mini-stat-value"><?= (int)($summary['admin'] ?? 0) ?></p>
          <p class="mini-stat-note">Full control of the portal</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="mini-stat">
        <div class="mini-stat-icon" style="background:#fef3c7;color:#92400e;"><i class="mdi mdi-gavel"></i></div>
        <div>
          <p class="mini-stat-label">Treasurers</p>
          <p class="mini-stat-value"><?= (int)($summary['treasurer'] ?? 0) ?></p>
          <p class="mini-stat-note">Sanction every rupee spent</p>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <div class="mini-stat">
        <div class="mini-stat-icon" style="background:var(--stm-navy-muted);color:var(--stm-navy);"><i class="mdi mdi-account-group-outline"></i></div>
        <div>
          <p class="mini-stat-label">Coordinators</p>
          <p class="mini-stat-value"><?= number_format((int)$total) ?></p>
          <p class="mini-stat-note">Can sign in to the portal</p>
        </div>
      </div>
    </div>
  </div>

  <?php if ((int)($summary['treasurer'] ?? 0) === 0): ?>
    <div class="gate-note mb-4" style="background:#fdf3f2;border-color:#f3c4bd;color:#8e2318;">
      <i class="mdi mdi-alert-outline"></i>
      <div>
        <strong>No treasurer is set.</strong> Until somebody holds that role, fund requests will
        sit in the queue with nobody but an administrator able to approve them. Set your treasurer
        below.
      </div>
    </div>
  <?php endif; ?>

  <div class="filter-chips mb-3">
    <a href="<?= h(access_link(['portal_role' => '', 'page' => null])) ?>"
       class="filter-chip <?= $filter === '' ? 'is-active' : '' ?>">Everyone</a>
    <?php foreach (['admin' => 'Administrators', 'treasurer' => 'Treasurers', 'coordinator' => 'Coordinators'] as $k => $v): ?>
      <a href="<?= h(access_link(['portal_role' => $k, 'page' => null])) ?>"
         class="filter-chip <?= $filter === $k ? 'is-active' : '' ?>"><?= $v ?></a>
    <?php endforeach; ?>
  </div>

  <div class="card border-0">
    <div class="card-body px-0 pb-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:20px;">Coordinator</th>
              <th>Contact</th>
              <th>Access Level</th>
              <th style="padding-right:20px;width:330px;">Change It</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="4">
                  <div class="stm-empty">
                    <i class="mdi mdi-account-search-outline"></i>
                    <p>No coordinators match this view.</p>
                    <small>Add coordinators under Community Hub, then set their access here.</small>
                  </div>
                </td>
              </tr>
            <?php else: foreach ($rows as $u): ?>
              <?php $is_me = $u['uid'] === portal_uid(); ?>
              <tr>
                <td style="padding-left:20px;">
                  <div style="font-weight:600;color:#2c3250;">
                    <?= h($u['name']) ?>
                    <?php if ($is_me): ?><span class="badge-stm-muted ms-1">you</span><?php endif; ?>
                  </div>
                  <div style="font-size:0.72rem;color:var(--stm-text-muted);"><?= h($u['uid']) ?></div>
                </td>
                <td style="font-size:0.82rem;">
                  <?= h($u['mail']) ?>
                  <div style="font-size:0.74rem;color:var(--stm-text-muted);"><?= h($u['mobile']) ?></div>
                </td>
                <td>
                  <span class="badge-gradient-<?= $role_tone[$u['portal_role']] ?? 'info' ?>">
                    <?= h(role_label($u['portal_role'])) ?>
                  </span>
                  <?php if ((int)$u['portal_status'] !== 1): ?>
                    <div class="mt-1"><span class="badge-gradient-danger">Access withdrawn</span></div>
                  <?php endif; ?>
                  <?php if ($u['note']): ?>
                    <div style="font-size:0.7rem;color:var(--stm-text-muted);margin-top:3px;"><?= h($u['note']) ?></div>
                  <?php endif; ?>
                </td>
                <td style="padding-right:20px;">
                  <?php if ($is_me): ?>
                    <span class="text-muted" style="font-size:0.78rem;">
                      Another admin has to change your own access.
                    </span>
                  <?php else: ?>
                    <form method="POST" action="portal_access" class="d-flex align-items-center gap-2 flex-wrap">
                      <input type="hidden" name="_set" value="1">
                      <input type="hidden" name="uid" value="<?= h($u['uid']) ?>">
                      <select name="portal_role" class="form-select form-select-sm" style="width:135px;">
                        <?php foreach (['coordinator' => 'Coordinator', 'treasurer' => 'Treasurer', 'admin' => 'Administrator'] as $k => $v): ?>
                          <option value="<?= $k ?>" <?= $u['portal_role'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                      </select>
                      <div class="form-check form-switch mb-0" title="Turn this off to lock them out of the portal">
                        <input class="form-check-input" type="checkbox" name="status" value="1"
                               id="st_<?= h($u['uid']) ?>" <?= (int)$u['portal_status'] === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label text-muted" for="st_<?= h($u['uid']) ?>" style="font-size:0.74rem;">
                          Allowed in
                        </label>
                      </div>
                      <button type="submit" class="btn btn-gradient-primary btn-xs">
                        <i class="mdi mdi-content-save"></i>
                      </button>
                      <?php if ((int)$u['is_explicit'] === 1): ?>
                        <a href="<?= h(access_link(['action' => 'reset', 'uid' => $u['uid']])) ?>"
                           class="btn btn-light border btn-xs" title="Back to a plain coordinator"
                           onclick="return confirm('Reset <?= h(addslashes($u['name'])) ?> to a plain coordinator?')">
                          <i class="mdi mdi-undo"></i>
                        </a>
                      <?php endif; ?>
                    </form>
                  <?php endif; ?>
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
            <li class="page-item"><a class="page-link" href="<?= h(access_link(['page' => $page - 1])) ?>">‹ Prev</a></li>
          <?php endif; ?>
          <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= h(access_link(['page' => $i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="<?= h(access_link(['page' => $page + 1])) ?>">Next ›</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<?php include 'includes/footer.php'; ?>
</div>
