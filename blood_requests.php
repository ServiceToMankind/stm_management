<?php
include 'includes/header.php';

$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$status = $_GET['status'] ?? 'live';
$group  = $_GET['group']  ?? 'all';
$q      = $_GET['q']      ?? '';

$url = "$api_url/blood/requests?page=$page&status=" . urlencode($status) . "&group=" . urlencode($group);
if ($q !== '') $url .= "&q=" . urlencode($q);

$resp        = json_decode(get_api_data($url), true);
$rows        = is_array($resp['data'] ?? null) ? $resp['data'] : [];
$total_pages = $resp['total_pages']   ?? 0;
$total       = $resp['total_records'] ?? 0;

$GROUPS = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];

function needs_link(array $override = [])
{
    $base = [
        'status' => $_GET['status'] ?? '',
        'group'  => $_GET['group']  ?? '',
        'q'      => $_GET['q']      ?? '',
    ];
    $params = array_filter(array_merge($base, $override), function ($v) {
        return $v !== '' && $v !== null && $v !== 'all';
    });
    return 'blood_requests' . ($params ? '?' . http_build_query($params) : '');
}

$chips = [
    'live'      => 'Still Needed',
    'fulfilled' => 'Fulfilled',
    'closed'    => 'Closed',
    'cancelled' => 'Cancelled',
    'all'       => 'Everything',
];

$urgency_tone  = ['critical' => 'danger', 'urgent' => 'warning', 'routine' => 'info'];
$urgency_label = ['critical' => 'Critical', 'urgent' => 'Urgent', 'routine' => 'Routine'];
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-bullhorn-outline"></i></span>
      Blood Requirements
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" class="d-flex align-items-center gap-2">
        <input type="hidden" name="status" value="<?= h($status) ?>">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Patient, hospital, area…"
               value="<?= h($q) ?>" style="width:200px;">
        <select name="group" class="form-select form-select-sm" style="width:115px;">
          <option value="all">Any group</option>
          <?php foreach ($GROUPS as $g): ?>
            <option value="<?= h($g) ?>" <?= $group === $g ? 'selected' : '' ?>><?= h($g) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-gradient-primary" type="submit"><i class="mdi mdi-magnify me-1"></i>Filter</button>
      </form>
      <a href="manage_blood_request" class="btn btn-sm btn-gradient-danger">
        <i class="mdi mdi-plus me-1"></i>Post Requirement
      </a>
    </div>
  </div>

  <div class="filter-chips mb-3">
    <?php foreach ($chips as $key => $label): ?>
      <a href="<?= h(needs_link(['status' => $key, 'page' => null])) ?>"
         class="filter-chip <?= $status === $key ? 'is-active' : '' ?>"><?= h($label) ?></a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($rows)): ?>
    <div class="card border-0">
      <div class="card-body">
        <div class="stm-empty">
          <i class="mdi mdi-water-off"></i>
          <p>No requirements in this view.</p>
          <small>Post one when a patient needs blood — you get a shareable poster with it.</small>
          <div class="mt-4">
            <a href="manage_blood_request" class="btn btn-gradient-danger">Post a Requirement</a>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>

    <p class="text-muted mb-3" style="font-size:0.8rem;">
      <?= number_format((int)$total) ?> requirement<?= $total == 1 ? '' : 's' ?>
    </p>

    <div class="row g-3 mb-3">
      <?php foreach ($rows as $r): ?>
        <?php
          $short = (float)$r['units_short'];
          $pct   = (float)$r['units_needed'] > 0
                     ? min(100, round((float)$r['units_arranged'] / (float)$r['units_needed'] * 100))
                     : 0;
          $is_live = in_array($r['status'], ['open', 'partially_arranged'], true);
        ?>
        <div class="col-lg-4 col-md-6">
          <div class="need-card urgency-<?= h($r['urgency']) ?> h-100 d-flex flex-column">

            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <span class="need-group"><?= h($r['blood_group']) ?></span>
                <div style="font-size:0.72rem;color:var(--stm-text-muted);margin-top:2px;">
                  <?= h($r['request_no'] ?: '#' . $r['id']) ?>
                </div>
              </div>
              <div class="text-end">
                <?= blood_status_badge($r['status']) ?>
                <?php if ($is_live): ?>
                  <div class="mt-1">
                    <span class="badge-gradient-<?= $urgency_tone[$r['urgency']] ?? 'info' ?>">
                      <?= h($urgency_label[$r['urgency']] ?? $r['urgency']) ?>
                    </span>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div style="font-weight:600;font-size:0.9rem;color:#2c3250;">
              <?php if ($is_live && $short > 0): ?>
                <?= units_text($short) ?> more unit<?= $short == 1 ? '' : 's' ?> needed
              <?php else: ?>
                <?= units_text($r['units_arranged']) ?> of <?= units_text($r['units_needed']) ?> unit(s) arranged
              <?php endif; ?>
            </div>
            <div class="fund-bar mb-2"><span style="width:<?= $pct ?>%;"></span></div>

            <div style="font-size:0.8rem;color:var(--stm-text-muted);line-height:1.7;" class="flex-grow-1">
              <?php if ($r['patient_name']): ?>
                <i class="mdi mdi-account-outline"></i> <?= h($r['patient_name']) ?>
                <?php if ($r['patient_age']): ?> · <?= h($r['patient_age']) ?><?php endif; ?><br>
              <?php endif; ?>
              <i class="mdi mdi-hospital-building"></i> <?= h($r['hospital']) ?><br>
              <?php if ($r['area'] || $r['city']): ?>
                <i class="mdi mdi-map-marker-outline"></i>
                <?= h(trim(($r['area'] ?: '') . ($r['area'] && $r['city'] ? ', ' : '') . ($r['city'] ?: ''))) ?><br>
              <?php endif; ?>
              <?php if ($r['needed_on']): ?>
                <i class="mdi mdi-calendar-clock"></i> by <?= nice_date($r['needed_on']) ?><br>
              <?php endif; ?>
              <i class="mdi mdi-phone-outline"></i> <?= h($r['contact_name']) ?> ·
              <a href="tel:<?= h($r['contact_mobile']) ?>"><?= h($r['contact_mobile']) ?></a>
            </div>

            <div class="mt-3 d-flex gap-2 flex-wrap">
              <a href="blood_request?rid=<?= (int)$r['id'] ?>" class="btn btn-gradient-info btn-xs">
                <i class="mdi mdi-account-search-outline me-1"></i>Donors
              </a>
              <a href="blood_poster?rid=<?= (int)$r['id'] ?>" class="btn btn-gradient-danger btn-xs">
                <i class="mdi mdi-image-outline me-1"></i>Poster
              </a>
              <?php if ($r['ticket_id']): ?>
                <a href="ticket?rid=<?= (int)$r['ticket_id'] ?>" class="btn btn-light border btn-xs">
                  <i class="mdi mdi-ticket-outline"></i>
                </a>
              <?php endif; ?>
            </div>

            <div style="font-size:0.7rem;color:var(--stm-muted);margin-top:10px;">
              Posted by <?= h($r['raised_by_name'] ?: '—') ?> · <?= time_ago($r['created_at']) ?>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1): ?>
      <div class="d-flex justify-content-center">
        <ul class="pagination pagination-sm mb-0">
          <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= h(needs_link(['page' => $page - 1])) ?>">‹ Prev</a></li>
          <?php endif; ?>
          <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= h(needs_link(['page' => $i])) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="<?= h(needs_link(['page' => $page + 1])) ?>">Next ›</a></li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endif; ?>

  <?php endif; ?>

</div>
<?php include 'includes/footer.php'; ?>
</div>
