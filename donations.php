<?php
include 'includes/header.php';

$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
date_default_timezone_set("Asia/Kolkata");

$month  = isset($_GET['month']) ? $_GET['month'] : date('m');
$year   = isset($_GET['year'])  ? $_GET['year']  : date('Y');
$q      = isset($_GET['q'])     ? $_GET['q']     : '';

$url    = "$api_url/logs/donations?page=$page&month=$month&year=$year";
if ($q !== '') $url .= "&q=" . urlencode($q);

$resp        = json_decode(get_api_data($url), true);
$data        = $resp['data']        ?? [];
$total_pages = $resp['total_pages'] ?? 0;
$total_amt   = $resp['total_amount']?? 0;

$month_name = $month === 'all' ? 'All Time' : date('F', mktime(0,0,0,$month,1));
?>

<div class="main-panel">
<div class="content-wrapper">

  <!-- Page Header -->
  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-heart-outline"></i></span>
      Donation Records
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form method="GET" class="d-flex align-items-center gap-2">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search donor…" value="<?= htmlspecialchars($q) ?>" style="width:180px;">
        <select name="month" class="form-select form-select-sm" style="width:130px;">
          <option value="all" <?= $month=='all'?'selected':'' ?>>All Months</option>
          <?php for($i=1;$i<=12;$i++): $m=str_pad($i,2,'0',STR_PAD_LEFT); ?>
          <option value="<?=$m?>" <?= $month==$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$i,1)) ?></option>
          <?php endfor; ?>
        </select>
        <select name="year" class="form-select form-select-sm" style="width:100px;">
          <?php for($i=2019;$i<=date('Y');$i++): ?>
          <option value="<?=$i?>" <?= $year==$i?'selected':'' ?>><?=$i?></option>
          <?php endfor; ?>
        </select>
        <button class="btn btn-sm btn-gradient-primary" type="submit"><i class="mdi mdi-magnify me-1"></i>Filter</button>
      </form>
      <a href="manage_donations" class="btn btn-sm btn-gradient-success"><i class="mdi mdi-plus me-1"></i>Add New</a>
      <a href="printdonations?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="mdi mdi-printer me-1"></i>Print</a>
    </div>
  </div>

  <!-- Summary Row -->
  <div class="row mb-3">
    <div class="col-md-4">
      <div class="card border-0 p-3 d-flex flex-row align-items-center gap-3">
        <div style="width:48px;height:48px;border-radius:12px;background:var(--stm-navy-muted);display:flex;align-items:center;justify-content:center;">
          <i class="mdi mdi-currency-inr" style="font-size:1.5rem;color:var(--stm-navy);"></i>
        </div>
        <div>
          <p class="mb-0 text-muted" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;">Total for <?= $month_name ?> <?= $year ?></p>
          <h4 class="mb-0 fw-bold" style="color:var(--stm-navy);">₹<?= number_format($total_amt) ?></h4>
        </div>
      </div>
    </div>
  </div>

  <!-- Table Card -->
  <div class="card border-0">
    <div class="card-body px-0 pb-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:20px;">Donor Name</th>
              <th>Email</th>
              <th>Amount</th>
              <th>Message</th>
              <th>Date & Time</th>
              <th>Status</th>
              <th style="padding-right:20px;text-align:center;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($data)): ?>
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="mdi mdi-inbox-arrow-down" style="font-size:2.5rem;display:block;margin-bottom:8px;opacity:0.25;"></i>
                  No donation records found for this period.
                </td>
              </tr>
            <?php else: foreach($data as $row): ?>
              <tr>
                <td style="padding-left:20px;">
                  <span style="font-weight:600;color:#2c3250;"><?= htmlspecialchars($row['name']) ?></span>
                  <?php if($row['txid']): ?>
                    <br><small class="text-muted" style="font-size:0.72rem;"><?= htmlspecialchars($row['txid']) ?></small>
                  <?php endif; ?>
                </td>
                <td style="color:var(--stm-text-muted);font-size:0.85rem;"><?= htmlspecialchars($row['mail']) ?></td>
                <td style="font-weight:700;color:#2a7d4f;">₹<?= number_format($row['amount']) ?></td>
                <td style="max-width:180px;font-size:0.82rem;color:var(--stm-text-muted);"><?= htmlspecialchars($row['message']) ?></td>
                <td style="font-size:0.8rem;white-space:nowrap;"><?= date('d M Y, h:i A', strtotime($row['added_on'])) ?></td>
                <td>
                  <?php if($row['payment_status']==1): ?>
                    <span class="badge-gradient-success">Success</span>
                  <?php else: ?>
                    <span class="badge-gradient-danger">Failed</span>
                  <?php endif; ?>
                </td>
                <td style="padding-right:20px;text-align:center;white-space:nowrap;">
                  <?php if (filter_var($row['mail'] ?? '', FILTER_VALIDATE_EMAIL) && $row['payment_status']==1): ?>
                  <button type="button" class="btn btn-gradient-success btn-xs me-1 js-resend-receipt"
                          data-rid="<?= (int)$row['id'] ?>"
                          data-mail="<?= htmlspecialchars($row['mail']) ?>"
                          title="Resend receipt email to <?= htmlspecialchars($row['mail']) ?>">
                    <i class="mdi mdi-email-outline"></i>
                  </button>
                  <?php endif; ?>
                  <a href="manage_donations?rid=<?= $row['id'] ?>" class="btn btn-gradient-info btn-xs me-1">
                    <i class="mdi mdi-pencil"></i>
                  </a>
                  <a href="manage_donations?rid=<?= $row['id'] ?>&action=delete" class="btn btn-gradient-danger btn-xs"
                     onclick="return confirm('Delete this donation record?')">
                    <i class="mdi mdi-delete"></i>
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
            <li class="page-item"><a class="page-link" href="?page=<?=$page-1?>&month=<?=$month?>&year=<?=$year?>&q=<?=$q?>">‹ Prev</a></li>
          <?php endif; ?>
          <?php for($i=max(1,$page-2); $i<=min($total_pages,$page+2); $i++): ?>
            <li class="page-item <?=$i==$page?'active':''?>">
              <a class="page-link" href="?page=<?=$i?>&month=<?=$month?>&year=<?=$year?>&q=<?=$q?>"><?=$i?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?=$page+1?>&month=<?=$month?>&year=<?=$year?>&q=<?=$q?>">Next ›</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<?php include 'includes/footer.php'; ?>
</div>

<!-- ── Resend receipt (inline, no page reload) ─────────────────────────── -->
<script>
(function () {
    "use strict";
    const API_BASE = "<?= rtrim($api_url, '/') ?>";

    function toast(message, ok) {
        const t = document.createElement('div');
        t.textContent = message;
        t.style.cssText =
            'position:fixed;z-index:3000;right:20px;bottom:20px;max-width:340px;' +
            'padding:12px 16px;border-radius:10px;color:#fff;font-size:.86rem;' +
            'box-shadow:0 6px 20px rgba(0,0,0,.18);' +
            (ok ? 'background:#2a7d4f;' : 'background:#c0392b;');
        document.body.appendChild(t);
        setTimeout(function () {
            t.style.transition = 'opacity .4s';
            t.style.opacity = '0';
            setTimeout(function () { t.remove(); }, 400);
        }, 3400);
    }

    document.querySelectorAll('.js-resend-receipt').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const rid  = btn.getAttribute('data-rid');
            const mail = btn.getAttribute('data-mail') || 'this donor';
            if (!rid) return;
            if (!confirm('Resend the donation receipt to ' + mail + '?')) return;

            const icon = btn.querySelector('i');
            const prev = icon ? icon.className : '';
            if (icon) icon.className = 'mdi mdi-loading mdi-spin';
            btn.disabled = true;

            fetch(API_BASE + '/logs/resend_receipt?rid=' + encodeURIComponent(rid))
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    toast(j.message || (j.status === 'success' ? 'Receipt sent.' : 'Could not send receipt.'),
                          j.status === 'success');
                })
                .catch(function () { toast('Network error. Please try again.', false); })
                .finally(function () {
                    if (icon) icon.className = prev;
                    btn.disabled = false;
                });
        });
    });
})();
</script>