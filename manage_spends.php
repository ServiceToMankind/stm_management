<?php
include 'includes/header.php';

// ── Initialize form values ────────────────────────────────────────────────────
$rid          = '';
$title        = '';
$amount       = '';
$date         = date('Y-m-d');
$des          = '';
$is_edit      = false;
$alert_msg    = '';
$alert_type   = '';

// ── Handle DELETE ─────────────────────────────────────────────────────────────
if (isset($_GET['rid']) && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $del_rid  = $_GET['rid'];
    $resp     = get_api_data("$api_url/logs/manage_spends?rid=" . urlencode($del_rid) . "&action=delete");
    $resp     = json_decode($resp, true);
    if (isset($resp['status']) && $resp['status'] === 'success') {
        echo "<script>window.location.href='spends'</script>";
        exit;
    }
}

// ── Handle POST (insert / update) ────────────────────────────────────────────
if (isset($_POST['_submit'])) {
    $post_data = [
        'rid'   => $_POST['rid']   ?? '',
        'title' => $_POST['title'] ?? '',
        'amt'   => $_POST['amt']   ?? '',
        'date'  => $_POST['date']  ?? '',
        'des'   => $_POST['des']   ?? '',
    ];

    $resp = get_api_data_post("$api_url/logs/manage_spends", $post_data);
    $resp = json_decode($resp, true);

    if (isset($resp['status']) && $resp['status'] === 'success') {
        echo "<script>window.location.href='spends'</script>";
        exit;
    } else {
        $alert_msg  = 'Error saving expenditure: ' . ($resp['message'] ?? 'Unknown error');
        $alert_type = 'danger';
    }
}

// ── Handle EDIT (load existing record) ───────────────────────────────────────
if (isset($_GET['rid']) && !isset($_GET['action'])) {
    $rid  = $_GET['rid'];
    $url  = "$api_url/logs/spends?rid=" . urlencode($rid);
    $data = get_api_data($url);
    $data = json_decode($data, true);

    if (!empty($data['data'][0])) {
        $row     = $data['data'][0];
        $rid     = $row['id'];
        $title   = $row['title']  ?? '';
        $amount  = $row['amount'] ?? '';
        $date    = $row['date']   ?? date('Y-m-d');
        $des     = $row['des']    ?? '';
        $is_edit = true;
    }
}

$page_title = $is_edit ? 'Edit Expenditure' : 'Log New Expenditure';
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-cash-multiple"></i>
                </span>
                <?= $page_title ?>
            </h3>
            <nav aria-label="breadcrumb">
                <a href="spends" class="btn btn-sm btn-light border">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to List
                </a>
            </nav>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8 grid-margin stretch-card">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <?php if ($alert_msg): ?>
                            <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show" role="alert">
                                <?= $alert_msg ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="manage_spends" class="forms-sample" id="spendForm">
                            <input type="hidden" name="_submit" value="1">
                            <input type="hidden" name="rid" value="<?= htmlspecialchars($rid) ?>">

                            <div class="form-group mb-3">
                                <label for="title" class="form-label fw-bold">Title / Purpose <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" id="title" placeholder="What was this for?" value="<?= htmlspecialchars($title) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="amt" class="form-label fw-bold">Amount (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="amt" class="form-control" id="amt" placeholder="0.00" value="<?= htmlspecialchars($amount) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" id="date" value="<?= htmlspecialchars($date) ?>" required>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="des" class="form-label fw-bold">Detailed Description</label>
                                <textarea name="des" class="form-control" id="des" rows="6" placeholder="Provide more context about this expenditure..."><?= htmlspecialchars($des) ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-light me-md-2">Reset</button>
                                <button type="submit" class="btn btn-gradient-primary btn-lg px-5 fw-bold">
                                    <i class="mdi mdi-content-save me-1"></i> 
                                    <?= $is_edit ? 'Update Changes' : 'Save Expenditure' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
