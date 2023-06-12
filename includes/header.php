<?php
session_start();
include 'includes/functions.php';

// $root = "http://localhost/management.stmorg.in/";

if(isset($_SESSION['ID']) && $_SESSION['ID']!=''){
}else{
  echo "<script>window.location.href='login'</script>";
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
    <!-- ovveride css -->
    <link rel="stylesheet" href="assets/css/custom_override.css?v=1" />
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css" />

    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" />
</head>

<body>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                <a class="navbar-brand brand-logo" href="index.html">STM Management</a>
                <a class="navbar-brand brand-logo-mini" href="index.html"><img
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
                                <p class="mb-1 text-black">Pranay Kiran</p>
                            </div>
                        </a>
                        <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                            <a class="dropdown-item" href="#">
                                <i class="mdi mdi-cached me-2 text-success"></i> Activity Log
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">
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
                                <span class="font-weight-bold mb-2">Pranay Kiran</span>
                                <span class="text-secondary text-small">Secretary</span>
                            </div>
                            <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">
                            <span class="menu-title">Dashboard</span>
                            <i class="mdi mdi-home menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic8" aria-expanded="false"
                            aria-controls="ui-basic8">
                            <span class="menu-title">Users</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-comment-plus-outline menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic8">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="users">List Users</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="manage_users">Manage Users</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false"
                            aria-controls="ui-basic">
                            <span class="menu-title">Coordinators</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-comment-plus-outline menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="coordinators">Coordinators</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="manage_coordinators">Manage Coordinators</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic2" aria-expanded="false"
                            aria-controls="ui-basic2">
                            <span class="menu-title">Volunteers</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-comment-plus-outline menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic2">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="volunteers">Volunteers</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="manage_volunteers">Manage Volunteers</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic3" aria-expanded="false"
                            aria-controls="ui-basic3">
                            <span class="menu-title">Donations</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-comment-plus-outline menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic3">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="donations">List Donations</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="manage_donations">Manage Donations</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic4" aria-expanded="false"
                            aria-controls="ui-basic4">
                            <span class="menu-title">Spends</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-comment-plus-outline menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic4">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="spends">List Spends</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="manage_spends">Manage Spends</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic5" aria-expanded="false"
                            aria-controls="ui-basic5">
                            <span class="menu-title">Document Registration</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-comment-plus-outline menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic5">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="documents">List Documents</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="manage_documents">Manage Documents</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic6" aria-expanded="false"
                            aria-controls="ui-basic6">
                            <span class="menu-title">Activities</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-comment-plus-outline menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic6">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="activities">List Activities</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="manage_activities">Manage Activities</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic7" aria-expanded="false"
                            aria-controls="ui-basic7">
                            <span class="menu-title">Camps</span>
                            <i class="menu-arrow"></i>
                            <i class="mdi mdi-comment-plus-outline menu-icon"></i>
                        </a>
                        <div class="collapse" id="ui-basic7">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="camps">List Camps</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="manage_camps">Manage Camps</a>
                                </li>
                            </ul>
                        </div>
                    </li>


                </ul>
            </nav>