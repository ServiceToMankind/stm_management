<?php
// show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'includes/header.php';
if(isset($_GET['pquery'])){
    $pquery = $_GET['pquery'];
    $url = "https://apis.stmorg.in/medical/patients/patients?pquery=".$pquery;
    $data = get_api_data($url);
    $data = json_decode($data, true);
    $data = $data['data'];
}
?>
<!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-danger text-white me-2">
                    <i class="mdi mdi-home"></i>
                </span>
                Add Patients
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
        <!-- form  -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Add Patients</h4>
                    <p class="card-description">Data must be verified.</p>
                    <form class="forms-sample" method="GET">
                        <div class="form-group">
                            <label for="pquery">Mobile No.</label>
                            <input type="text" class="form-control" name="pquery" id="pquery" placeholder="Username" />
                        </div>
                        <button type="submit" class="btn btn-gradient-primary me-2">
                            Fetch
                        </button>
                    </form>

                    <div class="mobile_checked">

                    </div>

                    <?php 
                    if(isset($data)){
                        foreach ($data as $row) {
                    ?>
                    <div class="row">
                        <div class="col-12 grid-margin">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Patient Records</h4>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Id</th>
                                                    <th>Name</th>
                                                    <th>Age</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- pranay Grahambell -->
                                                <tr>
                                                    <td><?php echo $row['id']; ?></td>
                                                    <td><?php echo $row['name']; ?></td>
                                                    <td><?php echo $row['age']; ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        }
                    }else{
                        echo "No Data Found";
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php
include 'includes/footer.php';
?>