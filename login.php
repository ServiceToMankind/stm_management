<?php
session_start();
include 'includes/functions.php';

$error_msg = '';

if(isset($_POST['name']) && isset($_POST['pass'])){
    $name = $_POST['name'];
    $pass = $_POST['pass'];

    $data = get_api_data("$api_url/common/login?username=".urlencode($name)."&password=".urlencode($pass));
    $data = json_decode($data, true);

    if (isset($data['status']) && $data['status'] === "success") {
        $user_data = $data['data'];
        $_SESSION['ID']   = $user_data['id'];
        $_SESSION['NAME'] = $user_data['name'];
        $_SESSION['EMAIL'] = $user_data['mail'];
        $_SESSION['ROLE'] = "STM Administrator"; // Map role if available in API
    } else {
        $error_msg = $data['data'] ?? 'Invalid credentials. Please try again.';
    }
}

if(isset($_SESSION['ID']) && $_SESSION['ID'] != ''){
    echo "<script>window.location.href='index'</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login | STM Management Portal</title>
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/custom_override.css?v=2">
    <link rel="shortcut icon" href="assets/images/favicon.ico" />
    <style>
        .auth.login-bg {
            background: linear-gradient(135deg, #24285b 0%, #3a3f7a 100%);
            min-height: 100vh;
        }
        .auth-form-light {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .brand-logo img {
            width: 100px !important;
            height: 100px !important;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 4px solid #f8f9fa;
        }
        .btn-gradient-primary {
            background: linear-gradient(to right, #24285b, #3a3f7a) !important;
            padding: 12px;
            font-size: 1rem;
            letter-spacing: 1px;
        }
        h4 { color: #24285b; font-weight: 800; }
        .form-control-lg {
            border-radius: 10px;
            border: 1px solid #eee;
            padding: 1.2rem 1.5rem;
        }
        .form-control-lg:focus {
            border-color: #24285b;
            box-shadow: 0 0 0 0.2rem rgba(36, 40, 91, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth login-bg">
                <div class="row flex-grow w-100">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-center p-5">
                            <div class="brand-logo">
                                <img src="https://stmorg.in/accesories/service_to_man_kind-20200709-0001.jpg" alt="STM Logo">
                            </div>
                            <h4 class="mb-2">STM Management</h4>
                            <h6 class="font-weight-light text-muted mb-4">Dedicated to Service. Driven by Compassion.</h6>
                            
                            <?php if ($error_msg): ?>
                                <div class="alert alert-danger py-2 small mb-3"><?= htmlspecialchars($error_msg) ?></div>
                            <?php endif; ?>

                            <form class="pt-3" method="POST" action="login">
                                <div class="form-group mb-3">
                                    <input type="email" class="form-control form-control-lg" placeholder="Official Email" name="name" required>
                                </div>
                                <div class="form-group mb-4">
                                    <input type="password" class="form-control form-control-lg" placeholder="Security Password" name="pass" required>
                                </div>
                                <div class="mt-3 d-grid">
                                    <button type="submit" class="btn btn-gradient-primary btn-lg font-weight-bold auth-form-btn shadow">
                                        SECURE ACCESS
                                    </button>
                                </div>
                                <div class="mt-4 text-center">
                                    <small class="text-muted">Managed with ❤️ by <a href="https://weberq.in" class="text-primary fw-bold" target="_blank">WeberQ Global</a></small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
</body>
</html>