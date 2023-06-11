<?php
include 'includes/header.php';

// show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['pid']) && $_POST['pid']!=''){
    $data=get_api_data_post('https://apis.stmorg.in/medical/investigations/blood',$_POST);
    $data=json_decode($data,true);
    // Check for API errors
    if ($data['status'] == 'error') {
        echo '<script>alert("'.$data['data'].'");</script>';
    } else {
        $insert_id=$data['data'];
    }
}else{
    $pid = '';
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
                Blood Investigations
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
                            echo 'Record Added Successfully with ID: '.$insert_id.'';
                        } ?></h4>
                        <form class="forms-sample" method="POST">
                            <div class="form-group">
                                <label for="exampleInputUsername1">Patient Id :</label>
                                <input type="text" class="form-control" id="exampleInputUsername1" placeholder="Name"
                                    name="pid">
                            </div>

                            <h4 class="card-title">Complete Blood Picture</h4>

                            <!-- Heamoglobin -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Heamoglobin :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Heamoglobin" name="heamoglobin">
                            </div>
                            <!-- RBC Count -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">RBC Count :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="RBC Count" name="rbc_count">
                            </div>
                            <!-- WBC Count -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">WBC Count :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="WBC Count" name="wbc_count">
                            </div>
                            <h4 class="card-title">Differential Count</h4>
                            <!-- Neutrophils -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Neutrophils :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Neutrophils" name="neutrophils">
                            </div>
                            <!-- Lymphocytes -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Lymphocytes :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Lymphocytes" name="lymphocytes">
                            </div>
                            <!-- Eosinophils -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Eosinophils :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Eosinophils" name="eosinophils">
                            </div>
                            <!-- Monocytes -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Monocytes :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Monocytes" name="monocytes">
                            </div>
                            <!-- Basophils -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Basophils :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Basophils" name="basophils">
                            </div>
                            <!-- Platelet Count -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Platelet Count :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Platelet Count" name="platelet_count">
                            </div>
                            <!-- Haemotocrit(PCV) -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Haemotocrit(PCV) :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Haemotocrit(PCV)" name="haemotocrit">
                            </div>
                            <!-- Mean Cell Volume(MCV) : -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Mean Cell Volume(MCV) :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Mean Cell Volume(MCV)" name="mcv">
                            </div>
                            <!-- Mean Cell Haemoglobin(MCH) : -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Mean Cell Haemoglobin(MCH) :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Mean Cell Haemoglobin(MCH)" name="mch">
                            </div>
                            <!-- Mean Cell Haemoglobin Concentration(MCHC) : -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Mean Cell Haemoglobin Concentration(MCHC) :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="Mean Cell Haemoglobin Concentration(MCHC)" name="mchc">
                            </div>
                            <!-- RDW-CV -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">RDW-CV :</label>
                                <input type="number" class="form-control" id="exampleInputUsername1"
                                    placeholder="RDW-CV" name="rdw_cv">
                            </div>
                            <!-- Band forms -->
                            <div class="form-group">
                                <label for="exampleInputUsername1">Band forms :</label>
                                <input type="text" class="form-control" id="exampleInputUsername1"
                                    placeholder="Band forms" name="band_forms">
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