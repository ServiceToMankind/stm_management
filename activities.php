<?php
include 'includes/header.php';

$page     = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$stage    = $_GET['stage'] ?? 'all';
$category = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : 0;
$year     = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : 0;
$q        = $_GET['q'] ?? '';
$mine     = isset($_GET['mine']) && $_GET['mine'] === '1';

$url = "$api_url/ops/activities?page=$page&stage=" . urlencode($stage) . "&" . actor_qs();
if ($category > 0) $url .= "&category=$category";
if ($year > 0)     $url .= "&year=$year";
if ($q !== '')     $url .= "&q=" . urlencode($q);
if ($mine)         $url .= "&mine=1";

$resp        = json_decode(get_api_data($url), true);
$rows        = is_array($resp['data'] ?? null) ? $resp['data'] : [];
$total_pages = $resp['total_pages']         ?? 0;
$total       = $resp['total_records']       ?? 0;
$reached     = $resp['total_beneficiaries'] ?? 0;
$not_ready   = ($resp['reason'] ?? '') === 'schema_missing';

$cats = json_decode(get_api_data("$api_url/ops/categories"), true);
$cats = is_array($cats['data'] ?? null) ? $cats['data'] : [];

$board  = json_decode(get_api_data("$api_url/ops/activities?board=1"), true);
$counts = $board['counts'] ?? [];
$head   = $board['headline'] ?? [];

function acts_link(array $override = [])
{
    $base = [
        'stage'    => $_GET['stage']    ?? '',
        'category' => $_GET['category'] ?? '',
        'year'     => $_GET['year']     ?? '',
        'q'        => $_GET['q']        ?? '',
        'mine'     => isset($_GET['mine']) && $_GET['mine'] === '1' ? '1' : '',
    ];
    $params = array_filter(array_merge($base, $override), function ($v) {
        return $v !== '' && $v !== null;
    });
    return 'activities' . ($params ? '?' . http_build_query($params) : '');
}

$stage_chips = [
    'all'         => ['Everything',  array_sum($counts)],
    'live'        => ['Coming Up',   ($counts['planned'] ?? 0) + ($counts['in_progress'] ?? 0)],
    'planned'     => ['Planned',     $counts['planned']     ?? 0],
    'in_progress' => ['In Progress', $counts['in_progress'] ?? 0],
    'completed'   => ['Completed',   $counts['completed']   ?? 0],
    'cancelled'   => ['Cancelled',   $counts['cancelled']   ?? 0],
];

$STAGE_TONE = ['planned' => 'info', 'in_progress' => 'warning', 'completed' => 'success', 'cancelled' => 'muted'];
function stage_badge($stage)
{
    global $STAGE_TONE;
    $tone  = $STAGE_TONE[$stage] ?? 'muted';
    $class = $tone === 'muted' ? 'badge-stm-muted' : 'badge-gradient-' . $tone;
    return '<span class="' . $class . '">' . h(ucwords(str_replace('_', ' ', (string)$stage))) . '</span>';
}
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-calendar-check"></i></span>
      <?= $mine ? 'My Activities' : 'Activity Register' ?>
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
        <input type="hidden" name="stage" value="<?= h($stage) ?>">
        <?php if ($mine): ?><input type="hidden" name="mine" value="1"><?php endif; ?>
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search activities…"
               value="<?= h($q) ?>" style="width:190px;">
        <select name="category" class="form-select form-select-sm" style="width:180px;">
          <option value="">All programmes</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= $category == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="year" class="form-select form-select-sm" style="width:110px;">
          <option value="">All years</option>
          <?php for ($y = (int)date('Y') + 1; $y >= 2019; $y--): ?>
            <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
        <button class="btn btn-sm btn-gradient-primary" type="submit"><i class="mdi mdi-magnify me-1"></i>Filter</button>
      </form>
      <a href="<?= $mine ? 'activities' : 'activities?mine=1' ?>" class="btn btn-sm btn-outline-secondary">
        <i class="mdi <?= $mine ? 'mdi-account-group-outline' : 'mdi-account-outline' ?> me-1"></i><?= $mine ? 'Everyone' : 'Only Mine' ?>
      </a>
      <a href="ops_report" class="btn btn-sm btn-light border"><i class="mdi mdi-chart-bar me-1"></i>Impact</a>
      <a href="manage_activity" class="btn btn-sm btn-gradient-success"><i class="mdi mdi-plus me-1"></i>Log Activity</a>
    </div>
  </div>

  <?php if ($not_ready): ?>
    <div class="gate-note mb-4">
      <i class="mdi mdi-database-alert"></i>
      <div>
        The activity register is not set up yet — run
        <code>db/migrations/2026-10-24_core_operations.sql</code> against the database.
        Until then this section stays empty; nothing else is affected.
      </div>
    </div>
  <?php else: ?>

  <!-- This year at a glance -->
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="mini-stat">
        <div class="mini-stat-icon" style="background:var(--stm-navy-muted);color:var(--stm-navy);"><i class="mdi mdi-calendar-check"></i></div>
        <div>
          <p class="mini-stat-label">Activities <?= h($head['year'] ?? date('Y')) ?></p>
          <p class="mini-stat-value"><?= number_format((int)($head['activities'] ?? 0)) ?></p>
          <p class="mini-stat-note">Completed this year</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="mini-stat">
        <div class="mini-stat-icon" style="background:#e8f8f1;color:#1a6b35;"><i class="mdi mdi-heart-outline"></i></div>
        <div>
          <p class="mini-stat-label">People Reached</p>
          <p class="mini-stat-value"><?= number_format((int)($head['beneficiaries'] ?? 0)) ?></p>
          <p class="mini-stat-note">Across those activities</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="mini-stat">
        <div class="mini-stat-icon" style="background:#e0f2f1;color:#1a6b5e;"><i class="mdi mdi-clock-outline"></i></div>
        <div>
          <p class="mini-stat-label">Volunteer Hours</p>
          <p class="mini-stat-value"><?= units_text($head['hours'] ?? 0) ?></p>
          <p class="mini-stat-note"><?= number_format((int)($head['volunteers'] ?? 0)) ?> volunteers took part</p>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="mini-stat">
        <div class="mini-stat-icon" style="background:#fef3c7;color:#92400e;"><i class="mdi mdi-calendar-clock"></i></div>
        <div>
          <p class="mini-stat-label">Still Ahead</p>
          <p class="mini-stat-value"><?= number_format(($counts['planned'] ?? 0) + ($counts['in_progress'] ?? 0)) ?></p>
          <p class="mini-stat-note">Planned or under way</p>
        </div>
      </div>
    </div>
  </div>

  <div class="filter-chips mb-3">
    <?php foreach ($stage_chips as $key => [$label, $n]): ?>
      <a href="<?= h(acts_link(['stage' => $key, 'page' => null])) ?>"
         class="filter-chip <?= $stage === $key ? 'is-active' : '' ?>">
        <?= h($label) ?><span class="chip-n"><?= (int)$n ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="card border-0">
    <div class="card-body px-0 pb-0">

      <div class="px-4 pb-3">
        <span class="text-muted" style="font-size:0.8rem;">
          <?= number_format((int)$total) ?> activit<?= $total == 1 ? 'y' : 'ies' ?>
          <?php if ($reached > 0): ?> · <?= number_format((int)$reached) ?> people reached<?php endif; ?>
        </span>
      </div>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:20px;">When</th>
              <th>Activity</th>
              <th>Where</th>
              <th>Volunteers</th>
              <th>Reached</th>
              <th>Stage</th>
              <th style="padding-right:20px;text-align:center;">Open</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="7">
                  <div class="stm-empty">
                    <i class="mdi mdi-calendar-blank-outline"></i>
                    <p>No activities in this view.</p>
                    <small>Log what the team has done — it is what the year's report is built from.</small>
                    <div class="mt-4"><a href="manage_activity" class="btn btn-gradient-success">Log an Activity</a></div>
                  </div>
                </td>
              </tr>
            <?php else: foreach ($rows as $a): ?>
              <tr>
                <td style="padding-left:20px;white-space:nowrap;">
                  <div style="font-weight:600;font-size:0.84rem;color:#2c3250;"><?= nice_date($a['start_date']) ?></div>
                  <div style="font-size:0.72rem;color:var(--stm-text-muted);">
                    <?= $a['start_time'] ? date('h:i A', strtotime($a['start_time'])) : h($a['activity_no'] ?: '') ?>
                  </div>
                </td>
                <td style="max-width:300px;">
                  <a href="activity?rid=<?= (int)$a['id'] ?>" style="font-weight:600;color:#2c3250;text-decoration:none;">
                    <?= h($a['title']) ?>
                  </a>
                  <div style="font-size:0.74rem;color:var(--stm-text-muted);">
                    <i class="mdi <?= h($a['category_icon'] ?: 'mdi-calendar-check') ?> me-1"></i><?= h($a['category_name'] ?: 'Uncategorised') ?>
                    <?php if (!empty($a['sponsored_by'])): ?> · <?= h($a['sponsored_by']) ?><?php endif; ?>
                    <?php if ((int)($a['file_count'] ?? 0) > 0): ?> · <i class="mdi mdi-camera"></i><?= (int)$a['file_count'] ?><?php endif; ?>
                  </div>
                </td>
                <td style="font-size:0.82rem;">
                  <?= $a['venue'] ? h($a['venue']) : '<span class="text-muted">—</span>' ?>
                  <?php if ($a['area']): ?>
                    <div style="font-size:0.74rem;color:var(--stm-text-muted);">
                      <i class="mdi mdi-map-marker-outline"></i> <?= h($a['area']) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td style="font-size:0.82rem;white-space:nowrap;">
                  <?php if ((int)$a['volunteer_count'] > 0): ?>
                    <strong><?= (int)$a['attended_count'] ?></strong> of <?= (int)$a['volunteer_count'] ?>
                    <div style="font-size:0.72rem;color:var(--stm-text-muted);"><?= units_text($a['volunteer_hours']) ?> hours</div>
                  <?php else: ?>
                    <span class="badge-stm-muted">None yet</span>
                  <?php endif; ?>
                </td>
                <td style="font-weight:700;color:var(--stm-green-dark);">
                  <?= (int)$a['beneficiaries'] > 0 ? number_format((int)$a['beneficiaries']) : '—' ?>
                </td>
                <td><?= stage_badge($a['stage']) ?></td>
                <td style="padding-right:20px;text-align:center;">
                  <a href="activity?rid=<?= (int)$a['id'] ?>" class="btn btn-gradient-info btn-xs">
                    <i class="mdi mdi-arrow-right"></i>
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
            <li class="page-item"><a class="page-link" href="<?= h(acts_link(['page' => $page - 1])) ?>">‹ Prev</a></li>
          <?php endif; ?>
          <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= h(acts_link(['page' => $i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="<?= h(acts_link(['page' => $page + 1])) ?>">Next ›</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php endif; ?>

</div>
<?php include 'includes/footer.php'; ?>
</div>
