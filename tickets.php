<?php
include 'includes/header.php';

$page     = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$status   = $_GET['status']   ?? 'live';
$category = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : 0;
$priority = $_GET['priority'] ?? 'all';
$q        = $_GET['q']        ?? '';
$mine     = isset($_GET['mine']) && $_GET['mine'] === '1';

// The board, the category list and the status counts, in one pass.
$url = "$api_url/tickets/tickets?page=$page&status=" . urlencode($status)
     . "&priority=" . urlencode($priority) . "&" . actor_qs();
if ($category > 0) $url .= "&category=$category";
if ($q !== '')     $url .= "&q=" . urlencode($q);
if ($mine)         $url .= "&mine=1";

$resp        = json_decode(get_api_data($url), true);
$tickets     = is_array($resp['data'] ?? null) ? $resp['data'] : [];
$total_pages = $resp['total_pages']   ?? 0;
$total       = $resp['total_records'] ?? 0;
$total_est   = $resp['total_est']     ?? 0;

$cats = json_decode(get_api_data("$api_url/tickets/categories"), true);
$cats = is_array($cats['data'] ?? null) ? $cats['data'] : [];

$board  = json_decode(get_api_data("$api_url/tickets/tickets?board=1"), true);
$counts = $board['counts'] ?? [];

// Keeps the current filters on every link that only changes one of them.
function tickets_link(array $override = [])
{
    $base = [
        'status'   => $_GET['status']   ?? 'live',
        'category' => $_GET['category'] ?? '',
        'priority' => $_GET['priority'] ?? 'all',
        'q'        => $_GET['q']        ?? '',
        'mine'     => isset($_GET['mine']) && $_GET['mine'] === '1' ? '1' : '',
    ];
    $params = array_filter(array_merge($base, $override), function ($v) {
        return $v !== '' && $v !== null;
    });
    return 'tickets' . ($params ? '?' . http_build_query($params) : '');
}

$live_count = ($counts['open'] ?? 0) + ($counts['in_review'] ?? 0)
            + ($counts['in_progress'] ?? 0) + ($counts['on_hold'] ?? 0)
            + ($counts['approved'] ?? 0);

$status_chips = [
    'live'        => ['Needs Action', $live_count],
    'open'        => ['Open',         $counts['open']        ?? 0],
    'in_review'   => ['In Review',    $counts['in_review']   ?? 0],
    'in_progress' => ['In Progress',  $counts['in_progress'] ?? 0],
    'on_hold'     => ['On Hold',      $counts['on_hold']     ?? 0],
    'resolved'    => ['Resolved',     $counts['resolved']    ?? 0],
    'closed'      => ['Closed',       $counts['closed']      ?? 0],
    'rejected'    => ['Rejected',     $counts['rejected']    ?? 0],
    'all'         => ['Everything',   array_sum($counts)],
];
?>

<div class="main-panel">
<div class="content-wrapper">

  <!-- Page Header -->
  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-ticket-confirmation"></i></span>
      <?= $mine ? 'My Tickets' : 'Ticket Board' ?>
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
        <input type="hidden" name="status" value="<?= h($status) ?>">
        <?php if ($mine): ?><input type="hidden" name="mine" value="1"><?php endif; ?>
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search tickets…"
               value="<?= h($q) ?>" style="width:190px;">
        <select name="category" class="form-select form-select-sm" style="width:175px;">
          <option value="">All activities</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= $category == $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="priority" class="form-select form-select-sm" style="width:130px;">
          <option value="all">Any priority</option>
          <?php foreach (['critical' => 'Critical', 'high' => 'High', 'normal' => 'Normal', 'low' => 'Low'] as $k => $v): ?>
            <option value="<?= $k ?>" <?= $priority === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-gradient-primary" type="submit"><i class="mdi mdi-magnify me-1"></i>Filter</button>
      </form>
      <a href="<?= $mine ? 'tickets' : 'tickets?mine=1' ?>" class="btn btn-sm btn-outline-secondary">
        <i class="mdi <?= $mine ? 'mdi-account-group-outline' : 'mdi-account-outline' ?> me-1"></i><?= $mine ? 'Everyone' : 'Only Mine' ?>
      </a>
      <a href="manage_ticket" class="btn btn-sm btn-gradient-success"><i class="mdi mdi-plus me-1"></i>Raise Ticket</a>
    </div>
  </div>

  <!-- Status filter chips -->
  <div class="filter-chips mb-3">
    <?php foreach ($status_chips as $key => [$label, $n]): ?>
      <a href="<?= h(tickets_link(['status' => $key, 'page' => null])) ?>"
         class="filter-chip <?= $status === $key ? 'is-active' : '' ?>">
        <?= h($label) ?><span class="chip-n"><?= (int)$n ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Table Card -->
  <div class="card border-0">
    <div class="card-body px-0 pb-0">

      <div class="d-flex justify-content-between align-items-center px-4 pb-3 flex-wrap gap-2">
        <span class="text-muted" style="font-size:0.8rem;">
          <?= (int)$total ?> ticket<?= $total == 1 ? '' : 's' ?>
          <?php if ($total_est > 0): ?>
            · <?= money($total_est) ?> estimated between them
          <?php endif; ?>
        </span>
      </div>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:20px;">Ticket</th>
              <th>Activity</th>
              <th>Beneficiary / Area</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Owner</th>
              <th style="padding-right:20px;text-align:center;">Open</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($tickets)): ?>
              <tr>
                <td colspan="7">
                  <div class="stm-empty">
                    <i class="mdi mdi-ticket-outline"></i>
                    <p>No tickets match this view.</p>
                    <small>Raise one to start an activity, or widen the filters above.</small>
                  </div>
                </td>
              </tr>
            <?php else: foreach ($tickets as $t): ?>
              <tr>
                <td style="padding-left:20px;">
                  <a href="ticket?rid=<?= (int)$t['id'] ?>" style="font-weight:700;color:var(--stm-navy);font-size:0.84rem;text-decoration:none;">
                    <?= h($t['ticket_no'] ?: '#' . $t['id']) ?>
                  </a>
                  <div style="font-size:0.72rem;color:var(--stm-text-muted);"><?= time_ago($t['created_at']) ?></div>
                </td>
                <td style="max-width:290px;">
                  <div style="font-weight:600;color:#2c3250;"><?= h($t['title']) ?></div>
                  <div style="font-size:0.74rem;color:var(--stm-text-muted);">
                    <i class="mdi <?= h($t['category_icon'] ?: 'mdi-ticket-outline') ?> me-1"></i><?= h($t['category_name']) ?>
                    <?php if ((float)$t['est_amount'] > 0): ?>
                      · <?= money($t['est_amount']) ?> est.
                    <?php endif; ?>
                    <?php if ((int)$t['file_count'] > 0): ?>
                      · <i class="mdi mdi-paperclip"></i><?= (int)$t['file_count'] ?>
                    <?php endif; ?>
                    <?php if ((int)$t['note_count'] > 0): ?>
                      · <i class="mdi mdi-comment-outline"></i><?= (int)$t['note_count'] ?>
                    <?php endif; ?>
                  </div>
                </td>
                <td style="font-size:0.82rem;">
                  <?= $t['beneficiary_name'] ? h($t['beneficiary_name']) : '<span class="text-muted">—</span>' ?>
                  <?php if ($t['area']): ?>
                    <div style="font-size:0.74rem;color:var(--stm-text-muted);">
                      <i class="mdi mdi-map-marker-outline"></i> <?= h($t['area']) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td><?= priority_badge($t['priority']) ?></td>
                <td><?= ticket_status_badge($t['status']) ?></td>
                <td style="font-size:0.8rem;">
                  <?php if ($t['assigned_to_name']): ?>
                    <?= h($t['assigned_to_name']) ?>
                  <?php else: ?>
                    <span class="badge-stm-muted">Unassigned</span>
                  <?php endif; ?>
                  <div style="font-size:0.72rem;color:var(--stm-text-muted);">
                    by <?= h($t['raised_by_name'] ?: '—') ?>
                  </div>
                </td>
                <td style="padding-right:20px;text-align:center;">
                  <a href="ticket?rid=<?= (int)$t['id'] ?>" class="btn btn-gradient-info btn-xs">
                    <i class="mdi mdi-arrow-right"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
      <div class="px-4 py-3 d-flex justify-content-center">
        <ul class="pagination pagination-sm mb-0">
          <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= h(tickets_link(['page' => $page - 1])) ?>">‹ Prev</a></li>
          <?php endif; ?>
          <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= h(tickets_link(['page' => $i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="<?= h(tickets_link(['page' => $page + 1])) ?>">Next ›</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<?php include 'includes/footer.php'; ?>
</div>
