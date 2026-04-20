<?php
session_start();
include 'includes/functions.php';

// $root = "http://localhost/management.stmorg.in/";

if(isset($_SESSION['ID']) && $_SESSION['ID']!=''){
    $user_id   = $_SESSION['ID'];
    $user_name = $_SESSION['NAME'] ?? 'Administrator';
    $user_role = $_SESSION['ROLE'] ?? 'STM Official';
} else {
    echo "<script>window.location.href='login'</script>";
    exit;
}

function getGreeting() {
    $hour = date('H');
    if ($hour < 12) return 'Good Morning ☀️';
    if ($hour < 17) return 'Good Afternoon 🌤️';
    return 'Good Evening 🌙';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>STM Management</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css" />
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css" />
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css?v=2" />
    <!-- STM Brand Override (must load AFTER base style) -->
    <link rel="stylesheet" href="assets/css/custom_override.css?v=4" />

    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" />
</head>

<body>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                <a class="navbar-brand brand-logo" href="index">
                    <img src="https://stmorg.in/accesories/service_to_man_kind-20200709-0001.jpg" alt="logo" style="width: 40px; height: 40px; margin-right: 10px; border-radius: 50%;">
                    <span style="font-weight: 800; letter-spacing: 1px; font-size: 1.1rem; vertical-align: middle; color: white;">STM PORTAL</span>
                </a>
                <a class="navbar-brand brand-logo-mini" href="index"><img
                        src="https://stmorg.in/accesories/service_to_man_kind-20200709-0001.jpg" width="28px"
                        height="28px" alt="logo" /></a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-stretch">
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="mdi mdi-menu"></span>
                </button>
                <div class="search-field d-none d-md-block">
                    <form class="d-flex align-items-center h-100" action="#">
                        <div class="input-group">
                            <div class="input-group-prepend bg-transparent">
                                <i class="input-group-text border-0 mdi mdi-magnify"></i>
                            </div>
                            <input type="text" class="form-control bg-transparent border-0"
                                placeholder="Search projects" />
                        </div>
                    </form>
                </div>
                <ul class="navbar-nav navbar-nav-right">
                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <div class="nav-profile-img">
                                <img src="https://stmorg.in/accesories/service_to_man_kind-20200709-0001.jpg"
                                    alt="image" />
                                <span class="availability-status online"></span>
                            </div>
                            <div class="nav-profile-text">
                                <p class="mb-1 text-black"><?= htmlspecialchars($user_name) ?></p>
                            </div>
                        </a>
                        <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                            <a class="dropdown-item" href="#">
                                <i class="mdi mdi-cached me-2 text-success"></i> Activity Log
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout">
                                <i class="mdi mdi-logout me-2 text-primary"></i> Signout
                            </a>
                        </div>
                    </li>
                    <li class="nav-item d-none d-lg-block full-screen-link">
                        <a class="nav-link">
                            <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
                        </a>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item nav-profile">
                        <a href="#" class="nav-link">
                            <div class="nav-profile-image">
                                <img src="https://stmorg.in/accesories/service_to_man_kind-20200709-0001.jpg"
                                    alt="profile" />
                                <span class="login-status online"></span>
                                <!--change to offline or busy as needed-->
                            </div>
                            <div class="nav-profile-text d-flex flex-column">
                                <span class="font-weight-bold mb-2"><?= htmlspecialchars($user_name) ?></span>
                                <span class="text-secondary text-small"><?= htmlspecialchars($user_role) ?></span>
                            </div>
                            <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
                        </a>
                    </li>
                                  <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link" href="index">
                            <span class="menu-title">Dashboard</span>
                            <i class="mdi mdi-home menu-icon"></i>
                        </a>
                    </li>

                    <!-- Community Management -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#community-menu" aria-expanded="false" aria-controls="community-menu">
                            <span class="menu-title">Community Hub</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-account-group menu-icon"></i>
                        </a>
                        <div class="collapse" id="community-menu">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"> <a class="nav-link" href="users">List Users</a></li>
                                <li class="nav-item"> <a class="nav-link" href="manage_users">Manage Users</a></li>
                                <li class="nav-item"> <a class="nav-link" href="coordinators">Coordinators</a></li>
                                <li class="nav-item"> <a class="nav-link" href="manage_coordinators">Manage Coordinators</a></li>
                                <li class="nav-item"> <a class="nav-link" href="volunteers">Volunteers</a></li>
                                <li class="nav-item"> <a class="nav-link" href="manage_volunteers">Manage Volunteers</a></li>
                            </ul>
                        </div>
                    </li>

                    <!-- Financial Management -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#finance-menu" aria-expanded="false" aria-controls="finance-menu">
                            <span class="menu-title">Financial Ledger</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-cash-multiple menu-icon"></i>
                        </a>
                        <div class="collapse" id="finance-menu">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"> <a class="nav-link" href="donations">Donations</a></li>
                                <li class="nav-item"> <a class="nav-link" href="manage_donations">Log Donation</a></li>
                                <li class="nav-item"> <a class="nav-link" href="spends">Expenditures</a></li>
                                <li class="nav-item"> <a class="nav-link" href="manage_spends">Log Spend</a></li>
                            </ul>
                        </div>
                    </li>

                    <!-- Operations & Events -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ops-menu" aria-expanded="false" aria-controls="ops-menu">
                            <span class="menu-title">Core Operations</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-calendar-check menu-icon"></i>
                        </a>
                        <div class="collapse" id="ops-menu">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"> <a class="nav-link" href="activities">NGO Activities</a></li>
                                <li class="nav-item"> <a class="nav-link" href="volunteering_activities">Volunteer Efforts</a></li>
                                <li class="nav-item"> <a class="nav-link" href="camps">Medical Camps</a></li>
                                <li class="nav-item"> <a class="nav-link" href="manage_camps">Manage Camps</a></li>
                            </ul>
                        </div>
                    </li>

                    <!-- Administration -->
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#admin-menu" aria-expanded="false" aria-controls="admin-menu">
                            <span class="menu-title">Administration</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-file-document-edit-outline menu-icon"></i>
                        </a>
                        <div class="collapse" id="admin-menu">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"> <a class="nav-link" href="documents">Document Registry</a></li>
                                <li class="nav-item"> <a class="nav-link" href="manage_documents">Register New Doc</a></li>
                            </ul>
                        </div>
                    </li>


                </ul>
            </nav>