<?php
include 'includes/header.php';

$year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$resp = json_decode(get_api_data("$api_url/ops/report?year=$year"), true);
$not_ready = ($resp['reason'] ?? '') === 'schema_missing';

$head        = $resp['headline']    ?? [];
$by_category = $resp['by_category'] ?? [];
$by_month    = $resp['by_month']    ?? [];
$top         = $resp['top']         ?? [];
$years       = $resp['years']       ?? [(int)date('Y')];

// Scale the month bars against the busiest month.
$peak = 0;
foreach ($by_month as $m) $peak = max($peak, (int)$m['activities']);
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-chart-bar"></i></span>
      Impact Report
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" class="d-flex align-items-center gap-2">
        <select name="year" class="form-select form-select-sm" style="width:120px;" onchange="this.form.submit()">
          <?php foreach ($years as $y): ?>
            <option value="<?= (int)$y ?>" <?= $year === (int)$y ? 'selected' : '' ?>><?= (int)$y ?></option>
          <?php endforeach; ?>
        </select>
        <noscript><button class="btn btn-sm btn-gradient-primary" type="submit">Show</button></noscript>
      </form>
      <a href="activities" class="btn btn-sm btn-light border"><i class="mdi mdi-arrow-left me-1"></i>Register</a>
      <button type="button" class="btn btn-sm btn-gradient-primary" onclick="window.print()">
        <i class="mdi mdi-printer me-1"></i>Print
      </button>
    </div>
  </div>

  <?php if ($not_ready): ?>
    <div class="gate-note">
      <i class="mdi mdi-database-alert"></i>
      <div>
        The activity register is not set up yet — run
        <code>db/migrations/2026-10-24_core_operations.sql</code> against the database.
      </div>
    </div>
  <?php else: ?>

  <!-- The year in one line -->
  <div class="welcome-hero mb-4">
    <div style="position:relative;z-index:2;">
      <p class="mb-1" style="font-size:0.85rem;font-weight:600;opacity:0.7;text-transform:uppercase;letter-spacing:1px;">
        Service To Mankind · <?= (int)$year ?>
      </p>
      <h2 style="font-size:1.8rem;font-weight:800;margin-bottom:0.25rem;">
        <?= number_format((int)($head['beneficiaries'] ?? 0)) ?> people reached across
        <?= number_format((int)($head['activities'] ?? 0)) ?> activit<?= ($head['activities'] ?? 0) == 1 ? 'y' : 'ies' ?>
      </h2>
      <p style="opacity:0.75;font-size:0.9rem;margin:0;">
        <?= number_format((int)($head['volunteers'] ?? 0)) ?> volunteers gave
        <?= units_text($head['hours'] ?? 0) ?> hours between them ·
        <?= money($head['spent'] ?? 0) ?> spent
      </p>
    </div>
  </div>

  <div class="row mb-4">
    <?php
      $tiles = [
        ['Activities Held',  number_format((int)($head['completed'] ?? 0)), 'of ' . number_format((int)($head['activities'] ?? 0)) . ' logged', 'mdi-calendar-check', 'var(--stm-navy-muted)', 'var(--stm-navy)'],
        ['People Reached',   number_format((int)($head['beneficiaries'] ?? 0)), 'Direct beneficiaries', 'mdi-heart-outline', '#e8f8f1', '#1a6b35'],
        ['Volunteer Hours',  units_text($head['hours'] ?? 0), number_format((int)($head['attendances'] ?? 0)) . ' attendances', 'mdi-clock-outline', '#e0f2f1', '#1a6b5e'],
        ['Spent',            money($head['spent'] ?? 0), 'On the year\'s work', 'mdi-currency-inr', '#fdf3f2', 'var(--stm-blood)'],
      ];
      foreach ($tiles as [$label, $value, $note, $icon, $bg, $fg]):
    ?>
      <div class="col-md-3 mb-3">
        <div class="mini-stat">
          <div class="mini-stat-icon" style="background:<?= $bg ?>;color:<?= $fg ?>;"><i class="mdi <?= $icon ?>"></i></div>
          <div>
            <p class="mini-stat-label"><?= h($label) ?></p>
            <p class="mini-stat-value"><?= $value ?></p>
            <p class="mini-stat-note"><?= h($note) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row">

    <!-- By programme -->
    <div class="col-lg-6 mb-4">
      <div class="card border-0 h-100">
        <div class="card-body">
          <h5 class="card-title mb-3">
            <i class="mdi mdi-shape-outline me-2" style="color:var(--stm-navy);"></i>By Programme
          </h5>

          <?php
            $cat_peak = 0;
            foreach ($by_category as $c) $cat_peak = max($cat_peak, (int)$c['activities']);
          ?>
          <?php if ($cat_peak === 0): ?>
            <div class="stm-empty">
              <i class="mdi mdi-calendar-blank-outline"></i>
              <p>Nothing logged for <?= (int)$year ?> yet.</p>
            </div>
          <?php else: foreach ($by_category as $c): if ((int)$c['activities'] === 0) continue; ?>
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-baseline mb-1">
                <span style="font-size:0.86rem;font-weight:600;color:#2c3250;">
                  <i class="mdi <?= h($c['icon'] ?: 'mdi-calendar-check') ?> me-1" style="color:var(--stm-navy);"></i>
                  <?= h($c['name']) ?>
                </span>
                <span style="font-size:0.8rem;color:var(--stm-text-muted);">
                  <?= (int)$c['activities'] ?> · <?= number_format((int)$c['beneficiaries']) ?> reached
                </span>
              </div>
              <div class="fund-bar">
                <span style="width:<?= $cat_peak > 0 ? round((int)$c['activities'] / $cat_peak * 100) : 0 ?>%;"></span>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>

    <!-- Month by month -->
    <div class="col-lg-6 mb-4">
      <div class="card border-0 h-100">
        <div class="card-body">
          <h5 class="card-title mb-3">
            <i class="mdi mdi-calendar-month-outline me-2" style="color:var(--stm-navy);"></i>Month by Month
          </h5>

          <?php if ($peak === 0): ?>
            <div class="stm-empty">
              <i class="mdi mdi-chart-bar"></i>
              <p>No activity recorded in <?= (int)$year ?>.</p>
            </div>
          <?php else: ?>
            <div class="d-flex align-items-end justify-content-between" style="height:190px;gap:6px;">
              <?php foreach ($by_month as $m): ?>
                <div class="text-center" style="flex:1 1 0;min-width:0;"
                     title="<?= h($m['label']) ?>: <?= (int)$m['activities'] ?> activities, <?= number_format((int)$m['beneficiaries']) ?> reached">
                  <div style="font-size:0.68rem;color:var(--stm-text-muted);margin-bottom:3px;">
                    <?= (int)$m['activities'] > 0 ? (int)$m['activities'] : '' ?>
                  </div>
                  <div style="height:<?= max(3, round((int)$m['activities'] / $peak * 140)) ?>px;
                              background:<?= (int)$m['activities'] > 0 ? 'linear-gradient(180deg,var(--stm-navy-soft),var(--stm-navy))' : '#e8eaf0' ?>;
                              border-radius:5px 5px 0 0;"></div>
                  <div style="font-size:0.66rem;color:var(--stm-text-muted);margin-top:5px;"><?= h($m['label']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Volunteers -->
  <div class="card border-0">
    <div class="card-body px-0 pb-0">
      <div class="px-4 pb-3">
        <h5 class="card-title mb-1">
          <i class="mdi mdi-trophy-outline me-2" style="color:var(--stm-navy);"></i>Volunteers Who Gave the Most Time
        </h5>
        <span class="text-muted" style="font-size:0.78rem;">
          Hours recorded against activities in <?= (int)$year ?>. Useful for certificates and recognition.
        </span>
      </div>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:20px;">#</th>
              <th>Volunteer</th>
              <th>Activities</th>
              <th>Attended</th>
              <th style="padding-right:20px;">Hours</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($top)): ?>
              <tr>
                <td colspan="5">
                  <div class="stm-empty">
                    <i class="mdi mdi-account-group-outline"></i>
                    <p>No volunteer hours recorded for <?= (int)$year ?>.</p>
                    <small>Mark attendance on each activity and this fills itself in.</small>
                  </div>
                </td>
              </tr>
            <?php else: foreach ($top as $i => $t): ?>
              <tr>
                <td style="padding-left:20px;font-weight:700;color:var(--stm-muted);"><?= $i + 1 ?></td>
                <td>
                  <div style="font-weight:600;color:#2c3250;"><?= h($t['name'] ?: $t['volunteer_id']) ?></div>
                  <div style="font-size:0.72rem;color:var(--stm-text-muted);">
                    <?= h($t['volunteer_id']) ?><?= $t['mobile'] ? ' · ' . h($t['mobile']) : '' ?>
                  </div>
                </td>
                <td><?= (int)$t['activities'] ?></td>
                <td><?= (int)$t['attended'] ?></td>
                <td style="padding-right:20px;font-weight:700;color:var(--stm-teal);"><?= units_text($t['hours']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php endif; ?>

</div>
<?php include 'includes/footer.php'; ?>
</div>
