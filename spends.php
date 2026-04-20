<?php
include 'includes/header.php';

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Set the timezone to Asia/Kolkata
date_default_timezone_set("Asia/Kolkata");

// Get the current date and time
$now = date('Y-m-d H:i:s');

// Initialize variables for the month and year
$month = isset($_GET['month']) ? $_GET['month'] : date('m', strtotime($now));
$year  = isset($_GET['year'])  ? $_GET['year']  : date('Y', strtotime($now));
$q     = isset($_GET['q'])     ? $_GET['q']     : '';

// Construct API URL
$query_params = [
    'page' => $page,
    'month' => $month,
    'year' => $year,
    'q' => $q
];
$url = "$api_url/logs/spends?" . http_build_query($query_params);

$resp = get_api_data($url);
$resp_data = json_decode($resp, true);

$data = [];
$total_pages = 0;
$total_amount = 0;

if (isset($resp_data['status']) && $resp_data['status'] === 'success') {
    $data = $resp_data['data'];
    $total_pages = $resp_data['total_pages'];
    $total_amount = $resp_data['total_amount'];
}
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-cash-multiple"></i>
                </span>
                Expenditure Records
            </h3>
            <nav aria-label="breadcrumb">
                <div class="d-flex align-items-center">
                    <form method="GET" class="d-flex align-items-center me-3">
                        <input type="text" name="q" class="form-control form-control-sm me-2" placeholder="Search title/desc..." value="<?= htmlspecialchars($q) ?>">
                        <select name="month" class="form-select form-select-sm me-2" style="width: auto;">
                            <?php for($i=1; $i<=12; $i++): $m = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                <option value="<?= $m ?>" <?= $month == $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $i, 10)) ?></option>
                            <?php endfor; ?>
                            <option value="all" <?= $month == 'all' ? 'selected' : '' ?>>All Months</option>
                        </select>
                        <select name="year" class="form-select form-select-sm me-2" style="width: auto;">
                            <?php for($i=2018; $i<=date('Y')+2; $i++): ?>
                                <option value="<?= $i ?>" <?= $year == $i ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <button class="btn btn-sm btn-gradient-primary" type="submit">Filter</button>
                    </form>
                    <a href="manage_spends" class="btn btn-sm btn-gradient-success">
                        <i class="mdi mdi-plus me-1"></i> Add New
                    </a>
                </div>
            </nav>
        </div>

        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">
                                Expenditures for 
                                <?= $month == 'all' ? 'All Time' : date('F', mktime(0, 0, 0, $month, 10)) ?> 
                                <?= $year ?>
                            </h4>
                            <div class="text-end">
                                <p class="text-muted small mb-0">Total Amount Spent</p>
                                <h3 class="text-danger fw-bold mb-0">₹<?= number_format($total_amount, 2) ?></h3>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($data)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No expenditure records found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($data as $row): ?>
                                            <tr>
                                                <td class="text-muted small">
                                                    <?= date('d M, Y', strtotime($row['date'])) ?>
                                                </td>
                                                <td class="fw-bold"><?= htmlspecialchars($row['title']) ?></td>
                                                <td class="text-wrap" style="max-width: 300px;">
                                                    <small class="text-muted"><?= htmlspecialchars($row['des']) ?></small>
                                                </td>
                                                <td class="fw-bold text-danger">₹<?= number_format($row['amount'], 2) ?></td>
                                                <td class="text-center">
                                                    <a href="manage_spends?rid=<?= $row['id'] ?>" class="btn btn-gradient-info btn-xs py-1">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <a href="manage_spends?rid=<?= $row['id'] ?>&action=delete" 
                                                       class="btn btn-gradient-danger btn-xs py-1"
                                                       onclick="return confirm('Delete this record? This action cannot be undone.');">
                                                        <i class="mdi mdi-delete"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination pagination-sm justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>&month=<?= $month ?>&year=<?= $year ?>&q=<?= $q ?>">Prev</a></li>
                                <?php endif; ?>

                                <?php for($i=1; $i<=$total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <li class="page-item active"><span class="page-link"><?= $i ?></span></li>
                                    <?php elseif ($i > $page - 3 && $i < $page + 3): ?>
                                        <li class="page-item"><a class="page-link" href="?page=<?= $i ?>&month=<?= $month ?>&year=<?= $year ?>&q=<?= $q ?>"><?= $i ?></a></li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>&month=<?= $month ?>&year=<?= $year ?>&q=<?= $q ?>">Next</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
    .table-hover tbody tr:hover { background-color: rgba(0,0,0,0.02); }
</style>

<?php include 'includes/footer.php'; ?>
