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
                Blood Requests
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
        <!-- blood requests table  -->
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Recent Requests</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Blood & Units</th>
                                        <th>Status</th>
                                        <th>Required On</th>
                                        <th>Verified By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- pranay Grahambell -->
                                    <tr>
                                        <td>Pranay Grahambell</td>
                                        <td>B+, 5 units</td>
                                        <td>
                                            <label class="badge badge-gradient-success">DONE</label>
                                        </td>
                                        <td>Dec 5, 2017</td>
                                        <td>Shivani Chowdarapu</td>
                                        <td>
                                            <!-- small view and edit buttons -->
                                            <button type="button" class="btn btn-outline-primary btn-icon-text btn-sm">
                                                <i class="mdi mdi-eye btn-icon-prepend"></i>
                                                View
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-icon-text btn-sm">
                                                <i class="mdi mdi-pencil btn-icon-prepend"></i>
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Revanth kumar -->
                                    <tr>
                                        <td>Revanth Kumar</td>
                                        <td>O+, 1 units</td>
                                        <td>
                                            <label class="badge badge-gradient-warning">PENDING</label>
                                        </td>
                                        <td>Dec 12, 2017</td>
                                        <td>Shivani Chowdarapu</td>
                                        <td>
                                            <!-- small view and edit buttons -->
                                            <button type="button" class="btn btn-outline-primary btn-icon-text btn-sm">
                                                <i class="mdi mdi-eye btn-icon-prepend"></i>
                                                View
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-icon-text btn-sm">
                                                <i class="mdi mdi-pencil btn-icon-prepend"></i>
                                                Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Jyoshna chari  -->
                                    <tr>
                                        <td>Jyoshna Chari</td>
                                        <td>A+, 3 units</td>
                                        <td>
                                            <label class="badge badge-gradient-warning">PENDING</label>
                                        </td>
                                        <td>Dec 14, 2017</td>
                                        <td>Shivani Chowdarapu</td>
                                        <td>
                                            <!-- small view and edit buttons -->
                                            <button type="button" class="btn btn-outline-primary btn-icon-text btn-sm">
                                                <i class="mdi mdi-eye btn-icon-prepend"></i>
                                                View
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-icon-text btn-sm">
                                                <i class="mdi mdi-pencil btn-icon-prepend"></i>
                                                Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Surya kumar -->
                                    <tr>
                                        <td>Surya Kumar</td>
                                        <td>B+, 4 units</td>
                                        <td>
                                            <label class="badge badge-gradient-success">DONE</label>
                                        </td>
                                        <td>Dec 15, 2017</td>
                                        <td>Shivani Chowdarapu</td>
                                        <td>
                                            <!-- small view and edit buttons -->
                                            <button type="button" class="btn btn-outline-primary btn-icon-text btn-sm">
                                                <i class="mdi mdi-eye btn-icon-prepend"></i>
                                                View
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-icon-text btn-sm">
                                                <i class="mdi mdi-pencil btn-icon-prepend"></i>
                                                Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Geetha siri  -->
                                    <tr>
                                        <td>Geetha Siri</td>
                                        <td>O+, 1 units</td>
                                        <td>
                                            <label class="badge badge-gradient-warning">PENDING</label>
                                        </td>
                                        <td>Dec 18, 2017</td>
                                        <td>Shivani Chowdarapu</td>
                                        <td>
                                            <!-- small view and edit buttons -->
                                            <button type="button" class="btn btn-outline-primary btn-icon-text btn-sm">
                                                <i class="mdi mdi-eye btn-icon-prepend"></i>
                                                View
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-icon-text btn-sm">
                                                <i class="mdi mdi-pencil btn-icon-prepend"></i>
                                                Edit
                                            </button>
                                        </td>
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