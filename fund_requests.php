<?php
include 'includes/header.php';

$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$q      = $_GET['q'] ?? '';

// A coordinator's own requests are all that concern them; the treasurer and
// admins open on the queue that needs clearing.
$reviewer = can_approve_funds();
$status   = $_GET['status'] ?? ($reviewer ? 'pending' : 'all');
$mine     = isset($_GET['mine']) ? $_GET['mine'] === '1' : !$reviewer;

$url = "$api_url/funds/fund_requests?page=$page&status=" . urlencode($status) . "&" . actor_qs();
if ($mine)     $url .= "&mine=1";
if ($q !== '') $url .= "&q=" . urlencode($q);

$resp        = json_decode(get_api_data($url), true);
$rows        = is_array($resp['data'] ?? null) ? $resp['data'] : [];
$total_pages = $resp['total_pages']     ?? 0;
$total       = $resp['total_records']   ?? 0;
$requested   = $resp['total_requested'] ?? 0;
$approved    = $resp['total_approved']  ?? 0;

// Count the same set the table below is showing, or the chips contradict it.
$summary_url = "$api_url/funds/fund_requests?summary=1";
if ($mine) $summary_url .= "&mine=1&" . actor_qs();

$summary = json_decode(get_api_data($summary_url), true);
$counts  = $summary['counts'] ?? [];

function funds_link(array $override = [])
{
    $base = [
        'status' => $_GET['status'] ?? '',
        'mine'   => $_GET['mine']   ?? '',
        'q'      => $_GET['q']      ?? '',
    ];
    $params = array_filter(array_merge($base, $override), function ($v) {
        return $v !== '' && $v !== null;
    });
    return 'fund_requests' . ($params ? '?' . http_build_query($params) : '');
}

$chips = [
    'pending'   => 'Awaiting Treasurer',
    'approved'  => 'Approved',
    'settled'   => 'Settled',
    'rejected'  => 'Rejected',
    'cancelled' => 'Withdrawn',
    'all'       => 'Everything',
];
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-cash-usd"></i></span>
      <?= $reviewer ? 'Fund Approvals' : 'My Fund Requests' ?>
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" class="d-flex align-items-center gap-2">
        <input type="hidden" name="status" value="<?= h($status) ?>">
        <?php if (isset($_GET['mine'])): ?><input type="hidden" name="mine" value="<?= h($_GET['mine']) ?>"><?php endif; ?>
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search requests…"
               value="<?= h($q) ?>" style="width:200px;">
        <button class="btn btn-sm btn-gradient-primary" type="submit"><i class="mdi mdi-magnify me-1"></i>Search</button>
      </form>
      <?php if ($reviewer): ?>
        <a href="<?= h(funds_link(['mine' => $mine ? '0' : '1', 'page' => null])) ?>" class="btn btn-sm btn-outline-secondary">
          <i class="mdi <?= $mine ? 'mdi-account-group-outline' : 'mdi-account-outline' ?> me-1"></i><?= $mine ? 'Everyone' : 'Only Mine' ?>
        </a>
      <?php endif; ?>
      <a href="manage_fund_request" class="btn btn-sm btn-gradient-success"><i class="mdi mdi-plus me-1"></i>Raise Request</a>
    </div>
  </div>

  <?php if ($reviewer): ?>
    <!-- Treasurer's headline numbers -->
    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:#fef3c7;color:#92400e;"><i class="mdi mdi-clock-outline"></i></div>
          <div>
            <p class="mini-stat-label">Awaiting You</p>
            <p class="mini-stat-value"><?= (int)($counts['pending']['count'] ?? 0) ?></p>
            <p class="mini-stat-note"><?= money($counts['pending']['requested'] ?? 0) ?> requested</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:#e8f8f1;color:#1a6b35;"><i class="mdi mdi-check-decagram"></i></div>
          <div>
            <p class="mini-stat-label">Approved, Unspent</p>
            <p class="mini-stat-value"><?= (int)($counts['approved']['count'] ?? 0) ?></p>
            <p class="mini-stat-note"><?= money($counts['approved']['approved'] ?? 0) ?> sanctioned</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:var(--stm-navy-muted);color:var(--stm-navy);"><i class="mdi mdi-receipt"></i></div>
          <div>
            <p class="mini-stat-label">Settled</p>
            <p class="mini-stat-value"><?= (int)($counts['settled']['count'] ?? 0) ?></p>
            <p class="mini-stat-note"><?= money($counts['settled']['approved'] ?? 0) ?> drawn</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:#fde8e8;color:#b52a1a;"><i class="mdi mdi-close-circle-outline"></i></div>
          <div>
            <p class="mini-stat-label">Rejected</p>
            <p class="mini-stat-value"><?= (int)($counts['rejected']['count'] ?? 0) ?></p>
            <p class="mini-stat-note">Not sanctioned</p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Status chips -->
  <div class="filter-chips mb-3">
    <?php foreach ($chips as $key => $label): ?>
      <a href="<?= h(funds_link(['status' => $key, 'page' => null])) ?>"
         class="filter-chip <?= $status === $key ? 'is-active' : '' ?>">
        <?= h($label) ?>
        <?php if (isset($counts[$key]['count'])): ?><span class="chip-n"><?= (int)$counts[$key]['count'] ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="card border-0">
    <div class="card-body px-0 pb-0">

      <div class="px-4 pb-3">
        <span class="text-muted" style="font-size:0.8rem;">
          <?= (int)$total ?> request<?= $total == 1 ? '' : 's' ?>
          <?php if ($requested > 0): ?>
            · <?= money($requested) ?> asked, <?= money($approved) ?> approved
          <?php endif; ?>
        </span>
      </div>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:20px;">Request</th>
              <th>Against</th>
              <th>Asked</th>
              <th>Sanctioned</th>
              <th>Status</th>
              <th>Raised By</th>
              <th style="padding-right:20px;text-align:center;">Open</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="7">
                  <div class="stm-empty">
                    <i class="mdi mdi-cash"></i>
                    <p>No fund requests in this view.</p>
                    <small><?= $reviewer ? 'Nothing is waiting on you right now.' : 'Raise one when a ticket needs money behind it.' ?></small>
                  </div>
                </td>
              </tr>
            <?php else: foreach ($rows as $r): ?>
              <?php
                $sanctioned = $r['approved_amount'] !== null ? (float)$r['approved_amount'] : 0;
                $drawn      = (float)$r['settled_amount'];
                $pct        = $sanctioned > 0 ? min(100, round($drawn / $sanctioned * 100)) : 0;
              ?>
              <tr>
                <td style="padding-left:20px;max-width:260px;">
                  <a href="fund_request?rid=<?= (int)$r['id'] ?>" style="font-weight:600;color:#2c3250;text-decoration:none;">
                    <?= h($r['title']) ?>
                  </a>
                  <div style="font-size:0.72rem;color:var(--stm-text-muted);">
                    <?= h($r['request_no'] ?: '#' . $r['id']) ?> · <?= time_ago($r['created_at']) ?>
                    <?php if ((int)$r['file_count'] > 0): ?>
                      · <i class="mdi mdi-paperclip"></i><?= (int)$r['file_count'] ?>
                    <?php elseif (!$r['proof_link']): ?>
                      · <span style="color:#b52a1a;">no proof</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td style="font-size:0.8rem;">
                  <?php if ($r['ticket_no']): ?>
                    <div><?= h($r['category_name']) ?></div>
                    <div style="font-size:0.72rem;color:var(--stm-text-muted);"><?= h($r['ticket_no']) ?></div>
                  <?php else: ?>
                    <span class="badge-stm-muted">No ticket</span>
                  <?php endif; ?>
                </td>
                <td style="font-weight:600;white-space:nowrap;"><?= money($r['amount']) ?></td>
                <td style="white-space:nowrap;">
                  <?php if ($r['approved_amount'] !== null): ?>
                    <span style="font-weight:700;color:var(--stm-green-dark);"><?= money($r['approved_amount']) ?></span>
                    <?php if ($r['status'] === 'approved' || $r['status'] === 'settled'): ?>
                      <div class="fund-bar" style="width:80px;"><span style="width:<?= $pct ?>%;"></span></div>
                      <div style="font-size:0.68rem;color:var(--stm-text-muted);"><?= money($drawn) ?> drawn</div>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td><?= fund_status_badge($r['status']) ?></td>
                <td style="font-size:0.8rem;"><?= h($r['raised_by_name'] ?: '—') ?></td>
                <td style="padding-right:20px;text-align:center;">
                  <a href="fund_request?rid=<?= (int)$r['id'] ?>"
                     class="btn btn-xs <?= ($reviewer && $r['status'] === 'pending') ? 'btn-gradient-warning' : 'btn-gradient-info' ?>">
                    <i class="mdi <?= ($reviewer && $r['status'] === 'pending') ? 'mdi-gavel' : 'mdi-arrow-right' ?>"></i>
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
            <li class="page-item"><a class="page-link" href="<?= h(funds_link(['page' => $page - 1])) ?>">‹ Prev</a></li>
          <?php endif; ?>
          <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= h(funds_link(['page' => $i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="<?= h(funds_link(['page' => $page + 1])) ?>">Next ›</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<?php include 'includes/footer.php'; ?>
</div>
