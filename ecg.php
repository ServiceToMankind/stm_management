<?php
include 'includes/header.php';

// show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-danger text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span>
                Manage Patients
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
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Blood Investigations</h4>
                        <p class="card-description"> Enter Details
                        </p>
                        <h4 style="color:red"> <?php if(isset($insert_id)){
                            echo 'Patient Added Successfully with ID: '.$insert_id.'';
                        } ?></h4>
                        <form class="forms-sample" method="POST">
                            <input type="hidden" name="pid" value="<?php if($pid!=''){
                                    echo $pid;
                                }else{
                                    echo '0';
                                } ?>">
                            <div class="form-group">
                                <label for="exampleInputUsername1">Name</label>
                                <input type="text" class="form-control" id="exampleInputUsername1" placeholder="Name"
                                    name="name">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputUsername1">Age</label>
                                <input type="number" class="form-control" id="exampleInputUsername1" placeholder="Age"
                                    name="age">
                            </div>
                            <div class="form-group">
                                <label for="exampleFormControlSelect3">Gender</label>
                                <select class="form-control form-control-sm" id="exampleFormControlSelect3"
                                    name="gender">
                                    <option value="1">Male</option>
                                    <option value="2">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputUsername1">Mobile</label>
                                <input type="number" class="form-control" id="exampleInputUsername1" value="<?php if($mobile!=''){
                                    echo $mobile;
                                } ?>" placeholder="Mobile" name="mobile">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Email address</label>
                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email"
                                    name="email">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Red address</label>
                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email"
                                    name="email">
                            </div>
                            <div class="form-group">
                                <label for="exampleFormControlSelect3">Blood Group</label>
                                <select class="form-control form-control-sm" id="exampleFormControlSelect3"
                                    name="blood_group">
                                    <option value="1">A+</option>
                                    <option value="2">A-</option>
                                    <option value="3">B+</option>
                                    <option value="4">B-</option>
                                    <option value="5">O+</option>
                                    <option value="6">O-</option>
                                    <option value="7">AB+</option>
                                    <option value="8">AB-</option>
                                    <option value="9">Other</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-gradient-primary me-2">Submit</button>
                            <button class="btn btn-light">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php
    include 'includes/footer.php';