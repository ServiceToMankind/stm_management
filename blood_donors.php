<?php
include 'includes/header.php';

$page     = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$group    = $_GET['group'] ?? 'all';
$area     = $_GET['area']  ?? 'all';
$q        = $_GET['q']     ?? '';
$eligible = isset($_GET['eligible']) && $_GET['eligible'] === '1';
$sort     = $_GET['sort']  ?? 'ready';

$url = "$api_url/blood/donors?page=$page&group=" . urlencode($group)
     . "&area=" . urlencode($area) . "&sort=" . urlencode($sort);
if ($q !== '')   $url .= "&q=" . urlencode($q);
if ($eligible)   $url .= "&eligible=1";

$resp        = json_decode(get_api_data($url), true);
$donors      = is_array($resp['data'] ?? null) ? $resp['data'] : [];
$total_pages = $resp['total_pages']   ?? 0;
$total       = $resp['total_records'] ?? 0;
$gap_days    = $resp['gap_days']      ?? 90;

$areas = json_decode(get_api_data("$api_url/blood/donors?areas=1"), true);
$areas = is_array($areas['data'] ?? null) ? $areas['data'] : [];

$GROUPS = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];

function donors_link(array $override = [])
{
    $base = [
        'group'    => $_GET['group']    ?? '',
        'area'     => $_GET['area']     ?? '',
        'q'        => $_GET['q']        ?? '',
        'eligible' => $_GET['eligible'] ?? '',
        'sort'     => $_GET['sort']     ?? '',
    ];
    $params = array_filter(array_merge($base, $override), function ($v) {
        return $v !== '' && $v !== null && $v !== 'all';
    });
    return 'blood_donors' . ($params ? '?' . http_build_query($params) : '');
}
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-account-group-outline"></i></span>
      Donor Registry
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
        <?php if ($eligible): ?><input type="hidden" name="eligible" value="1"><?php endif; ?>
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Name, number or area…"
               value="<?= h($q) ?>" style="width:190px;">
        <select name="group" class="form-select form-select-sm" style="width:115px;">
          <option value="all">Any group</option>
          <?php foreach ($GROUPS as $g): ?>
            <option value="<?= h($g) ?>" <?= $group === $g ? 'selected' : '' ?>><?= h($g) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="area" class="form-select form-select-sm" style="width:155px;">
          <option value="all">Any area</option>
          <?php foreach ($areas as $a): ?>
            <option value="<?= h($a['area']) ?>" <?= $area === $a['area'] ? 'selected' : '' ?>>
              <?= h($a['area']) ?> (<?= (int)$a['n'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <select name="sort" class="form-select form-select-sm" style="width:145px;">
          <option value="ready"  <?= $sort === 'ready'  ? 'selected' : '' ?>>Ready first</option>
          <option value="name"   <?= $sort === 'name'   ? 'selected' : '' ?>>By name</option>
          <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Donated recently</option>
        </select>
        <button class="btn btn-sm btn-gradient-primary" type="submit"><i class="mdi mdi-magnify me-1"></i>Filter</button>
      </form>
      <a href="<?= h(donors_link(['eligible' => $eligible ? '' : '1', 'page' => null])) ?>"
         class="btn btn-sm <?= $eligible ? 'btn-gradient-success' : 'btn-outline-secondary' ?>">
        <i class="mdi mdi-check-decagram me-1"></i><?= $eligible ? 'Eligible Only' : 'All Donors' ?>
      </a>
      <a href="manage_blood_donor" class="btn btn-sm btn-gradient-success"><i class="mdi mdi-plus me-1"></i>Add Donor</a>
    </div>
  </div>

  <!-- Group chips -->
  <div class="filter-chips mb-3">
    <a href="<?= h(donors_link(['group' => '', 'page' => null])) ?>"
       class="filter-chip <?= $group === 'all' ? 'is-active' : '' ?>">Every group</a>
    <?php foreach ($GROUPS as $g): ?>
      <a href="<?= h(donors_link(['group' => $g, 'page' => null])) ?>"
         class="filter-chip <?= $group === $g ? 'is-active' : '' ?>"><?= h($g) ?></a>
    <?php endforeach; ?>
  </div>

  <div class="card border-0">
    <div class="card-body px-0 pb-0">

      <div class="px-4 pb-3">
        <span class="text-muted" style="font-size:0.8rem;">
          <?= number_format((int)$total) ?> donor<?= $total == 1 ? '' : 's' ?><?= $eligible ? ' eligible to give today' : '' ?>
          <?php if ($group !== 'all'): ?> · blood group <?= h($group) ?><?php endif; ?>
          <?php if ($area !== 'all'): ?> · <?= h($area) ?><?php endif; ?>
        </span>
      </div>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:20px;">Donor</th>
              <th>Group</th>
              <th>Contact</th>
              <th>Area</th>
              <th>Last Donated</th>
              <th>Status</th>
              <th style="padding-right:20px;text-align:center;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($donors)): ?>
              <tr>
                <td colspan="7">
                  <div class="stm-empty">
                    <i class="mdi mdi-account-search-outline"></i>
                    <p>No donors match this view.</p>
                    <small>Widen the filters, or add a donor to the registry.</small>
                  </div>
                </td>
              </tr>
            <?php else: foreach ($donors as $d): ?>
              <?php $is_eligible = (int)$d['eligible'] === 1; ?>
              <tr>
                <td style="padding-left:20px;">
                  <div style="font-weight:600;color:#2c3250;"><?= h($d['name']) ?></div>
                  <div style="font-size:0.72rem;color:var(--stm-text-muted);">
                    <?php if ($d['uid']): ?>
                      <i class="mdi mdi-account-check-outline"></i> <?= h($d['uid']) ?> · member
                    <?php else: ?>
                      Outside donor
                    <?php endif; ?>
                    <?php if ((int)$d['total_donations'] > 0): ?>
                      · <?= (int)$d['total_donations'] ?> donation<?= (int)$d['total_donations'] == 1 ? '' : 's' ?>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <span style="font-weight:800;font-size:1.05rem;color:var(--stm-blood);"><?= h($d['blood_group']) ?></span>
                </td>
                <td style="font-size:0.84rem;white-space:nowrap;">
                  <?php if ($d['mobile']): ?>
                    <a href="tel:<?= h($d['mobile']) ?>"><?= h($d['mobile']) ?></a>
                    <?php if ($d['alt_mobile']): ?>
                      <div style="font-size:0.74rem;color:var(--stm-text-muted);"><?= h($d['alt_mobile']) ?></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:0.82rem;">
                  <?= $d['area'] ? h($d['area']) : '<span class="text-muted">—</span>' ?>
                  <?php if ($d['city']): ?>
                    <div style="font-size:0.74rem;color:var(--stm-text-muted);"><?= h($d['city']) ?></div>
                  <?php endif; ?>
                </td>
                <td style="font-size:0.82rem;white-space:nowrap;">
                  <?php if ($d['last_donated_on']): ?>
                    <?= nice_date($d['last_donated_on']) ?>
                    <div style="font-size:0.72rem;color:var(--stm-text-muted);">
                      <?= (int)$d['days_since'] ?> days ago
                    </div>
                  <?php else: ?>
                    <span class="text-muted">Not yet</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ((int)$d['is_available'] === 0): ?>
                    <span class="badge-stm-muted">Unavailable</span>
                  <?php elseif ($is_eligible): ?>
                    <span class="badge-gradient-success">Can donate</span>
                  <?php else: ?>
                    <span class="badge-gradient-warning">
                      Wait <?= max(0, $gap_days - (int)$d['days_since']) ?>d
                    </span>
                  <?php endif; ?>
                </td>
                <td style="padding-right:20px;text-align:center;white-space:nowrap;">
                  <?php if ($d['mobile']): ?>
                    <a href="tel:<?= h($d['mobile']) ?>" class="btn btn-gradient-success btn-xs me-1" title="Call">
                      <i class="mdi mdi-phone"></i>
                    </a>
                  <?php endif; ?>
                  <a href="manage_blood_donor?rid=<?= (int)$d['id'] ?>" class="btn btn-gradient-info btn-xs me-1" title="Edit">
                    <i class="mdi mdi-pencil"></i>
                  </a>
                  <a href="blood_log?donor_id=<?= (int)$d['id'] ?>" class="btn btn-light border btn-xs" title="Donation history">
                    <i class="mdi mdi-history"></i>
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
            <li class="page-item"><a class="page-link" href="<?= h(donors_link(['page' => $page - 1])) ?>">‹ Prev</a></li>
          <?php endif; ?>
          <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= h(donors_link(['page' => $i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="<?= h(donors_link(['page' => $page + 1])) ?>">Next ›</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<?php include 'includes/footer.php'; ?>
</div>
