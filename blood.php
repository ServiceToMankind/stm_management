<?php
include 'includes/header.php';

$stats = json_decode(get_api_data("$api_url/blood/donors?stats=1"), true);
$groups   = $stats['groups']   ?? [];
$headline = $stats['headline'] ?? [];
$req_c    = $stats['requests'] ?? [];
$recent   = $stats['recent']   ?? [];
$gap_days = $stats['gap_days'] ?? 90;

// The live board — what still needs donors found.
$needs = json_decode(get_api_data("$api_url/blood/requests?status=live&limit=6"), true);
$needs = is_array($needs['data'] ?? null) ? $needs['data'] : [];

$open_needs = ($req_c['open'] ?? 0) + ($req_c['partially_arranged'] ?? 0);

$urgency_label = ['critical' => 'Critical', 'urgent' => 'Urgent', 'routine' => 'Routine'];
?>

<div class="main-panel">
<div class="content-wrapper">

  <!-- Hero -->
  <div class="welcome-hero mb-4" style="background:linear-gradient(130deg,#8e2318,#c0392b);">
    <div style="position:relative; z-index:2;">
      <p class="mb-1" style="font-size:0.85rem; font-weight:600; opacity:0.75; text-transform:uppercase; letter-spacing:1px;">
        <i class="mdi mdi-water me-1"></i>STM Blood Bank
      </p>
      <h2 style="font-size:1.8rem; font-weight:800; margin-bottom:0.25rem;">
        <?= number_format((int)($headline['ready'] ?? 0)) ?> donors ready to give today
      </h2>
      <p style="opacity:0.8; font-size:0.9rem; margin:0;">
        <?= number_format((int)($headline['donors'] ?? 0)) ?> on the registry ·
        <?= number_format((int)($headline['donations'] ?? 0)) ?> donations logged ·
        <?= $open_needs ?> requirement<?= $open_needs == 1 ? '' : 's' ?> still open
      </p>
    </div>
  </div>

  <!-- Quick actions -->
  <div class="d-flex gap-2 flex-wrap mb-4">
    <a href="manage_blood_request" class="btn btn-gradient-danger">
      <i class="mdi mdi-bullhorn-outline me-1"></i>Post a Requirement
    </a>
    <a href="manage_blood_donor" class="btn btn-gradient-success">
      <i class="mdi mdi-account-plus-outline me-1"></i>Add a Donor
    </a>
    <a href="blood_donors" class="btn btn-light border">
      <i class="mdi mdi-account-group-outline me-1"></i>Donor Registry
    </a>
    <a href="blood_log" class="btn btn-light border">
      <i class="mdi mdi-history me-1"></i>Donation Log
    </a>
  </div>

  <!-- Headline numbers -->
  <div class="row mb-4">
    <div class="col-md-3 mb-3">
      <div class="stat-card blood p-4 h-100">
        <i class="mdi mdi-water stat-icon"></i>
        <div class="stat-label">Donors on Register</div>
        <div class="stat-value"><?= number_format((int)($headline['donors'] ?? 0)) ?></div>
        <div class="stat-note">Members and well-wishers</div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="stat-card green p-4 h-100">
        <i class="mdi mdi-check-decagram stat-icon"></i>
        <div class="stat-label">Eligible Right Now</div>
        <div class="stat-value"><?= number_format((int)($headline['ready'] ?? 0)) ?></div>
        <div class="stat-note"><?= $gap_days ?>+ days since their last donation</div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="stat-card teal p-4 h-100">
        <i class="mdi mdi-heart-outline stat-icon"></i>
        <div class="stat-label">Units Donated</div>
        <div class="stat-value"><?= units_text($headline['units'] ?? 0) ?></div>
        <div class="stat-note"><?= number_format((int)($headline['last_year'] ?? 0)) ?> donations this past year</div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="stat-card <?= $open_needs > 0 ? 'amber' : 'navy' ?> p-4 h-100">
        <i class="mdi mdi-bullhorn-outline stat-icon"></i>
        <div class="stat-label">Open Requirements</div>
        <div class="stat-value"><?= number_format($open_needs) ?></div>
        <div class="stat-note"><?= number_format((int)($req_c['fulfilled'] ?? 0)) ?> met so far</div>
      </div>
    </div>
  </div>

  <!-- Group availability -->
  <div class="card border-0 mb-4">
    <div class="card-body px-4 py-4">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="card-title mb-0">
          <i class="mdi mdi-water-outline me-2" style="color:var(--stm-blood);"></i>Who We Can Call
        </h5>
        <span class="text-muted" style="font-size:0.78rem;">
          Big number is the group · green is how many are eligible today
        </span>
      </div>

      <?php if (empty($groups)): ?>
        <div class="stm-empty">
          <i class="mdi mdi-water-off"></i>
          <p>The registry is empty.</p>
          <small>Add donors by hand, or run the one-time import to pull in members who already have a blood group on file.</small>
          <div class="mt-4"><a href="manage_blood_donor" class="btn btn-gradient-danger">Add the First Donor</a></div>
        </div>
      <?php else: ?>
        <div class="bg-chip-grid">
          <?php foreach ($groups as $g): ?>
            <a class="bg-chip" href="blood_donors?group=<?= urlencode($g['blood_group']) ?>&eligible=1">
              <span class="bg-chip-group"><?= h($g['blood_group']) ?></span>
              <div class="bg-chip-count"><?= (int)$g['total'] ?> on register</div>
              <div class="bg-chip-ready"><?= (int)$g['ready'] ?> ready</div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">

    <!-- Live requirements -->
    <div class="col-lg-7 mb-4">
      <div class="card border-0 h-100">
        <div class="card-body px-4 py-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">
              <i class="mdi mdi-bullhorn-outline me-2" style="color:var(--stm-blood);"></i>Open Requirements
            </h5>
            <a href="blood_requests" class="btn btn-sm btn-gradient-primary">
              <i class="mdi mdi-arrow-right me-1"></i>View All
            </a>
          </div>

          <?php if (empty($needs)): ?>
            <div class="stm-empty">
              <i class="mdi mdi-check-circle-outline"></i>
              <p>Nothing outstanding.</p>
              <small>Post a requirement when a patient needs blood, and share the poster with the volunteers.</small>
            </div>
          <?php else: ?>
            <div class="row g-3">
              <?php foreach ($needs as $n): ?>
                <div class="col-sm-6">
                  <div class="need-card urgency-<?= h($n['urgency']) ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <span class="need-group"><?= h($n['blood_group']) ?></span>
                      <span class="badge-gradient-<?= $n['urgency'] === 'critical' ? 'danger' : ($n['urgency'] === 'urgent' ? 'warning' : 'info') ?>">
                        <?= h($urgency_label[$n['urgency']] ?? $n['urgency']) ?>
                      </span>
                    </div>
                    <div style="font-weight:600;font-size:0.88rem;color:#2c3250;">
                      <?= units_text($n['units_short']) ?> of <?= units_text($n['units_needed']) ?> unit(s) still needed
                    </div>
                    <div style="font-size:0.78rem;color:var(--stm-text-muted);margin-top:4px;">
                      <i class="mdi mdi-hospital-building"></i> <?= h($n['hospital']) ?>
                      <?php if ($n['area']): ?><br><i class="mdi mdi-map-marker-outline"></i> <?= h($n['area']) ?><?php endif; ?>
                      <?php if ($n['needed_on']): ?><br><i class="mdi mdi-calendar-clock"></i> by <?= nice_date($n['needed_on']) ?><?php endif; ?>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                      <a href="blood_request?rid=<?= (int)$n['id'] ?>" class="btn btn-gradient-info btn-xs">
                        <i class="mdi mdi-account-search-outline me-1"></i>Find Donors
                      </a>
                      <a href="blood_poster?rid=<?= (int)$n['id'] ?>" class="btn btn-gradient-danger btn-xs">
                        <i class="mdi mdi-image-outline me-1"></i>Poster
                      </a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Recent donations -->
    <div class="col-lg-5 mb-4">
      <div class="card border-0 h-100">
        <div class="card-body px-4 py-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">
              <i class="mdi mdi-history me-2" style="color:var(--stm-navy);"></i>Lately
            </h5>
            <a href="blood_log" class="btn btn-sm btn-light border">Full log</a>
          </div>

          <?php if (empty($recent)): ?>
            <div class="stm-empty">
              <i class="mdi mdi-water-off"></i>
              <p>No donations logged yet.</p>
              <small>Record each donation so we know who is eligible next time.</small>
            </div>
          <?php else: ?>
            <div class="stm-timeline">
              <?php foreach ($recent as $d): ?>
                <div class="stm-timeline-item is-status">
                  <div class="d-flex justify-content-between align-items-baseline gap-2">
                    <span class="stm-timeline-who"><?= h($d['name']) ?></span>
                    <span class="stm-timeline-when"><?= nice_date($d['donated_on']) ?></span>
                  </div>
                  <p class="stm-timeline-note">
                    <strong style="color:var(--stm-blood);"><?= h($d['blood_group']) ?></strong>
                    · <?= units_text($d['units']) ?> unit(s)<?php if ($d['hospital']): ?> at <?= h($d['hospital']) ?><?php endif; ?>
                    <?php if ($d['area']): ?><br><span style="font-size:0.78rem;color:var(--stm-muted);"><?= h($d['area']) ?></span><?php endif; ?>
                  </p>
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
