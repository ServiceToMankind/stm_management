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

$current_page = basename($_SERVER['PHP_SELF'], '.php');
if ($current_page === '' || $current_page === 'index') $current_page = 'index';

$nav_groups = [
    'community' => ['users', 'manage_users', 'coordinators', 'manage_coordinators', 'volunteers', 'manage_volunteers'],
    'finance'   => ['donations', 'manage_donations', 'spends', 'manage_spends', 'printdonations'],
    'ops'       => ['activities', 'volunteering_activities', 'add_vol_activity', 'camps', 'manage_camps'],
    'admin'     => ['documents', 'manage_documents'],
];
$active_group = '';
foreach ($nav_groups as $g => $pages) {
    if (in_array($current_page, $pages, true)) { $active_group = $g; break; }
}
function nav_active($page, $current) { return $page === $current ? 'active' : ''; }
function group_active($group, $active_group) { return $group === $active_group ? 'active' : ''; }
function group_show($group, $active_group)   { return $group === $active_group ? 'show' : ''; }
function group_expanded($group, $active_group){ return $group === $active_group ? 'true' : 'false'; }
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
    <link rel="stylesheet" href="assets/css/custom_override.css?v=13" />

    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" />
</head>

<body>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <button class="navbar-toggler navbar-toggler-mobile d-lg-none" type="button" data-toggle="offcanvas" aria-label="Open menu">
                <span class="mdi mdi-menu"></span>
            </button>
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                <a class="navbar-brand brand-logo" href="index">
                    <img src="https://stmorg.in/accesories/service_to_man_kind-20200709-0001.jpg" alt="STM logo" class="brand-logo-img">
                    <span class="brand-logo-text">STM &middot; Admin</span>
                </a>
                <a class="navbar-brand brand-logo-mini" href="index"><img
                        src="https://stmorg.in/accesories/service_to_man_kind-20200709-0001.jpg" width="28px"
                        height="28px" alt="logo" /></a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-stretch">
                <button class="navbar-toggler align-self-center d-none d-lg-inline-flex" type="button" data-toggle="minimize" aria-label="Toggle sidebar">
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
                <ul class="navbar-nav navbar-nav-right d-none d-lg-flex">
                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <span class="nav-profile-icon">
                                <i class="mdi mdi-account-circle"></i>
                            </span>
                            <div class="nav-profile-text">
                                <p class="mb-1 text-black"><?= htmlspecialchars($user_name) ?></p>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown" aria-labelledby="profileDropdown">
                            <div class="dropdown-header px-3 py-2">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($user_name) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($user_role) ?></div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">
                                <i class="mdi mdi-cached me-2 text-success"></i> Activity Log
                            </a>
                            <a class="dropdown-item" href="logout">
                                <i class="mdi mdi-logout me-2 text-danger"></i> Sign out
                            </a>
                        </div>
                    </li>
                    <li class="nav-item full-screen-link">
                        <a class="nav-link">
                            <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-section-label">Main</li>

                    <li class="nav-item <?= nav_active('index', $current_page) ?>">
                        <a class="nav-link" href="index">
                            <i class="mdi mdi-view-dashboard-outline menu-icon"></i>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-section-label">Manage</li>

                    <li class="nav-item <?= group_active('community', $active_group) ?>">
                        <a class="nav-link" data-bs-toggle="collapse" href="#community-menu" aria-expanded="<?= group_expanded('community', $active_group) ?>" aria-controls="community-menu">
                            <i class="mdi mdi-account-group-outline menu-icon"></i>
                            <span class="menu-title">Community Hub</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse <?= group_show('community', $active_group) ?>" id="community-menu">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item <?= nav_active('users', $current_page) ?>"><a class="nav-link" href="users">List Users</a></li>
                                <li class="nav-item <?= nav_active('manage_users', $current_page) ?>"><a class="nav-link" href="manage_users">Manage Users</a></li>
                                <li class="nav-item <?= nav_active('coordinators', $current_page) ?>"><a class="nav-link" href="coordinators">Coordinators</a></li>
                                <li class="nav-item <?= nav_active('manage_coordinators', $current_page) ?>"><a class="nav-link" href="manage_coordinators">Manage Coordinators</a></li>
                                <li class="nav-item <?= nav_active('volunteers', $current_page) ?>"><a class="nav-link" href="volunteers">Volunteers</a></li>
                                <li class="nav-item <?= nav_active('manage_volunteers', $current_page) ?>"><a class="nav-link" href="manage_volunteers">Manage Volunteers</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item <?= group_active('finance', $active_group) ?>">
                        <a class="nav-link" data-bs-toggle="collapse" href="#finance-menu" aria-expanded="<?= group_expanded('finance', $active_group) ?>" aria-controls="finance-menu">
                            <i class="mdi mdi-cash-multiple menu-icon"></i>
                            <span class="menu-title">Financial Ledger</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse <?= group_show('finance', $active_group) ?>" id="finance-menu">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item <?= nav_active('donations', $current_page) ?>"><a class="nav-link" href="donations">Donations</a></li>
                                <li class="nav-item <?= nav_active('manage_donations', $current_page) ?>"><a class="nav-link" href="manage_donations">Log Donation</a></li>
                                <li class="nav-item <?= nav_active('spends', $current_page) ?>"><a class="nav-link" href="spends">Expenditures</a></li>
                                <li class="nav-item <?= nav_active('manage_spends', $current_page) ?>"><a class="nav-link" href="manage_spends">Log Spend</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item <?= group_active('ops', $active_group) ?>">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ops-menu" aria-expanded="<?= group_expanded('ops', $active_group) ?>" aria-controls="ops-menu">
                            <i class="mdi mdi-calendar-check-outline menu-icon"></i>
                            <span class="menu-title">Core Operations</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse <?= group_show('ops', $active_group) ?>" id="ops-menu">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item <?= nav_active('activities', $current_page) ?>"><a class="nav-link" href="activities">NGO Activities</a></li>
                                <li class="nav-item <?= nav_active('volunteering_activities', $current_page) ?>"><a class="nav-link" href="volunteering_activities">Volunteer Efforts</a></li>
                                <li class="nav-item <?= nav_active('camps', $current_page) ?>"><a class="nav-link" href="camps">Medical Camps</a></li>
                                <li class="nav-item <?= nav_active('manage_camps', $current_page) ?>"><a class="nav-link" href="manage_camps">Manage Camps</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item <?= group_active('admin', $active_group) ?>">
                        <a class="nav-link" data-bs-toggle="collapse" href="#admin-menu" aria-expanded="<?= group_expanded('admin', $active_group) ?>" aria-controls="admin-menu">
                            <i class="mdi mdi-shield-account-outline menu-icon"></i>
                            <span class="menu-title">Administration</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse <?= group_show('admin', $active_group) ?>" id="admin-menu">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item <?= nav_active('documents', $current_page) ?>"><a class="nav-link" href="documents">Document Registry</a></li>
                                <li class="nav-item <?= nav_active('manage_documents', $current_page) ?>"><a class="nav-link" href="manage_documents">Register New Doc</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-section-label d-lg-none">Account</li>
                    <li class="nav-item nav-item-signout d-lg-none">
                        <a class="nav-link" href="logout">
                            <i class="mdi mdi-logout menu-icon"></i>
                            <span class="menu-title">Sign out</span>
                        </a>
                    </li>
                </ul>
            </nav>