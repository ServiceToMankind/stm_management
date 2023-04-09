<?php
include 'includes/header.php';
?>
<!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-danger text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span>
                Dashboard
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                        <span></span>Overview
                        <i class="mdi mdi-alert-circle-outline icon-sm text-primary align-middle"></i>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="row">
            <div class="col-md-3 stretch-card grid-margin">
                <div class="card bg-danger bg-gradient card-img-holder text-white">
                    <div class="card-body">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">
                            Total Patients
                            <i class="mdi mdi-heart mdi-24px float-right"></i>
                        </h4>
                        <h2 class="mb-5">0</h2>
                        <h6 class="card-text">Increased by 60%</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 stretch-card grid-margin">
                <div class="card bg-danger bg-gradient card-img-holder text-white">
                    <div class="card-body">
                        <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                        <h4 class="font-weight-normal mb-3">
                            Total Camps
                            <i class="mdi mdi-heart mdi-24px float-right"></i>
                        </h4>
                        <h2 class="mb-5">0</h2>
                        <h6 class="card-text">Decreased by 10%</h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Recent records</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Blood Group</th>
                                        <th>Status</th>
                                        <th>Last Update</th>
                                        <th>Verified By</th>
                                        <th>Tracking ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- pranay Grahambell -->
                                    <tr>
                                        <td>Pranay Grahambell</td>
                                        <td>B+</td>
                                        <td>
                                            <label class="badge badge-gradient-success">DONE</label>
                                        </td>
                                        <td>Dec 5, 2017</td>
                                        <td>Shivani Chowdarapu</td>
                                        <td>WD-12345</td>
                                    </tr>
                                    <!-- Revanth kumar -->
                                    <tr>
                                        <td>Revanth Kumar</td>
                                        <td>O+</td>
                                        <td>
                                            <label class="badge badge-gradient-warning">PENDING</label>
                                        </td>
                                        <td>Dec 12, 2017</td>
                                        <td>Shivani Chowdarapu</td>
                                        <td>WD-12346</td>
                                    </tr>
                                    <!-- Jyoshna chari  -->
                                    <tr>
                                        <td>Jyoshna Chari</td>
                                        <td>A+</td>
                                        <td>
                                            <label class="badge badge-gradient-warning">PENDING</label>
                                        </td>
                                        <td>Dec 14, 2017</td>
                                        <td>Shivani Chowdarapu</td>
                                        <td>WD-12347</td>
                                    </tr>
                                    <!-- Surya kumar -->
                                    <tr>
                                        <td>Surya Kumar</td>
                                        <td>B+</td>
                                        <td>
                                            <label class="badge badge-gradient-success">DONE</label>
                                        </td>
                                        <td>Dec 15, 2017</td>
                                        <td>Shivani Chowdarapu</td>
                                        <td>WD-12348</td>
                                    </tr>
                                    <!-- Geetha siri  -->
                                    <tr>
                                        <td>Geetha Siri</td>
                                        <td>O+</td>
                                        <td>
                                            <label class="badge badge-gradient-warning">PENDING</label>
                                        </td>
                                        <td>Dec 18, 2017</td>
                                        <td>Shivani Chowdarapu</td>
                                        <td>WD-12349</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php
    include 'includes/footer.php';
    ?>