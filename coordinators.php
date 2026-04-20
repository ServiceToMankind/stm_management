<?php
include 'includes/header.php';

$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$pquery = isset($_POST['pquery']) ? $_POST['pquery'] : (isset($_GET['q']) ? $_GET['q'] : '');

$url  = "$api_url/global/users?page=$page&role=2";
if ($pquery !== '') $url .= "&pquery=" . urlencode($pquery);

$resp        = json_decode(get_api_data($url), true);
$data        = $resp['data'] ?? [];
$total_pages = $resp['total_pages'] ?? 0;
?>

<div class="main-panel">
<div class="content-wrapper">

  <!-- Page Header -->
  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-account-star-outline"></i></span>
      Coordinators
    </h3>
    <div class="d-flex align-items-center gap-2">
      <form method="POST" class="d-flex align-items-center gap-2">
        <input type="text" name="pquery" class="form-control form-control-sm" placeholder="Search coordinators…" value="<?= htmlspecialchars($pquery) ?>" style="width:220px;">
        <button class="btn btn-sm btn-gradient-primary" type="submit"><i class="mdi mdi-magnify me-1"></i>Search</button>
        <?php if($pquery): ?>
          <a href="coordinators" class="btn btn-sm btn-outline-secondary">Clear</a>
        <?php endif; ?>
      </form>
      <a href="manage_coordinators" class="btn btn-sm btn-gradient-success"><i class="mdi mdi-plus me-1"></i>Add Coordinator</a>
    </div>
  </div>

  <!-- Table Card -->
  <div class="card border-0">
    <div class="card-body px-0 pb-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="padding-left:20px;">ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Mobile</th>
              <th>Role</th>
              <th>Status</th>
              <th style="padding-right:20px;text-align:center;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($data) || isset($data['status'])): ?>
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="mdi mdi-account-search" style="font-size:2.5rem;display:block;margin-bottom:8px;opacity:0.25;"></i>
                  No coordinators found.
                </td>
              </tr>
            <?php else: foreach($data as $row): ?>
              <tr>
                <td style="padding-left:20px;font-size:0.78rem;color:var(--stm-text-muted);font-weight:600;"><?= htmlspecialchars($row['id']) ?></td>
                <td style="font-weight:600;color:#2c3250;"><?= htmlspecialchars($row['name']) ?></td>
                <td style="font-size:0.85rem;color:var(--stm-text-muted);"><?= htmlspecialchars($row['mail']) ?></td>
                <td style="font-size:0.85rem;"><?= htmlspecialchars($row['mobile']) ?></td>
                <td><span class="badge-gradient-info"><?= htmlspecialchars($row['role']) ?></span></td>
                <td>
                  <?php if($row['status']==1): ?>
                    <span class="badge-gradient-success">Active</span>
                  <?php else: ?>
                    <span class="badge-gradient-danger">Inactive</span>
                  <?php endif; ?>
                </td>
                <td style="padding-right:20px;text-align:center;white-space:nowrap;">
                  <a href="manage_coordinators?uid=<?= $row['id'] ?>" class="btn btn-gradient-info btn-xs me-1">
                    <i class="mdi mdi-pencil"></i>
                  </a>
                  <a href="manage_coordinators?deluid=<?= $row['id'] ?>" class="btn btn-gradient-danger btn-xs"
                     onclick="return confirm('Remove this coordinator?')">
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
            <li class="page-item"><a class="page-link" href="?page=<?=$page-1?>">‹ Prev</a></li>
          <?php endif; ?>
          <?php for($i=max(1,$page-2); $i<=min($total_pages,$page+2); $i++): ?>
            <li class="page-item <?=$i==$page?'active':''?>">
              <a class="page-link" href="?page=<?=$i?>"><?=$i?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?=$page+1?>">Next ›</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<?php include 'includes/footer.php'; ?>
</div>