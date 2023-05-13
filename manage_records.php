<?php
// show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'includes/header.php';
$pid='';
if(isset($_GET['pid'])){
    $pid = $_GET['pid'];
}
if(isset($_POST['pid']) && $_POST['pid'] != ''){
    $pid = $_POST['pid'];
    $diagnosis = $_POST['diagnosis'];
    $prescription = $_POST['prescription'];
    $url = "https://apis.stmorg.in/medical/records/add_records?pid=".$pid."&diagnosis=".$diagnosis."&prescription=".$prescription;
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
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Add Medical Record</h4>
                    <p class="card-description"> For proffesional use only </p>
                    <form class="forms-sample" method="POST">
                        <div class="form-group">
                            <label for="exampleInputUsername1">Patient Id</label>
                            <input type="text" class="form-control" name="pid" id="exampleInputUsername1"
                                placeholder="Patient Id" value="<?php echo $pid; ?>" />
                        </div>

                        <div class="form-group">
                            <label for="exampleTextarea1">Diagnosis</label>
                            <textarea class="form-control" name="diagnosis" id="exampleTextarea1" rows="6"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="exampleTextarea2">Prescription</label>
                            <textarea class="form-control" name="prescription" id="exampleTextarea2"
                                rows="6"></textarea>
                        </div>
                        <button type="submit" class="btn btn-gradient-primary me-2">Submit</button>
                        <button class="btn btn-light">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php
include 'includes/footer.php';
?>