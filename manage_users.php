<?php
// show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'includes/header.php';

if(isset($_GET['deluid'])){
    $deluid = $_GET['deluid'];
    $url = "https://apis.stmorg.in/global/manage_users?deluid=".$deluid;
    $data = get_api_data($url);
    $data = json_decode($data, true);
    echo "<script>window.location.href='users'</script>";
}

if(isset($_GET['uid'])){
    $uid = $_GET['uid'];
    $url = "https://apis.stmorg.in/global/users?uid=".$uid;
    $data = get_api_data($url);
    $data = json_decode($data, true);
    $data = $data['data'];
    $name = $data[0]['name'];
    $mail = $data[0]['mail'];
    $mobile = $data[0]['mobile'];
    $gender = $data[0]['gender'];
    $role = $data[0]['role_id'];
    $status = $data[0]['status'];
}else{
    $uid = "";
    $name = "";
    $mail = "";
    $mobile = "";
    $gender = "";
    $role = "";
    $status = "";
}
if(isset($_POST['uid'])){
    $uid= $_POST['uid'];
    $data2= get_api_data_post("https://apis.stmorg.in/global/manage_users",$_POST);
    $data2 = json_decode($data2, true);
    echo "<script>window.location.href='manage_users?uid=".$uid."'</script>";
}else{
    echo "no";
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
                Manage Users
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">

                </ul>
            </nav>
        </div>
        <!-- form  -->
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Manage Users</h4>
                    <p class="card-description"> edit/enter details </p>
                    <form class="forms-sample" method="POST" action="manage_users">
                        <!-- uid  -->
                        <input type="hidden" name="uid" value="<?php echo $uid; ?>">
                        <div class="form-group">
                            <label for="exampleInputUsername1">Name</label>
                            <input type="text" name="name" value="<?php echo $name; ?>" class="form-control"
                                id="exampleInputUsername1" placeholder="Username">
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Email address</label>
                            <input type="email" name="mail" value="<?php echo $mail; ?>" class="form-control"
                                id="exampleInputEmail1" placeholder="Email">
                        </div>
                        <!-- mobile  -->
                        <div class="form-group">
                            <label for="exampleInputMobile">Mobile</label>
                            <input type="number" name="mobile" value="<?php echo $mobile; ?>" class="form-control"
                                id="exampleInputMobile" placeholder="Mobile">
                        </div>
                        <!-- gender  -->
                        <div class="form-group">
                            <label for="exampleSelectGender">Gender</label>
                            <select class="form-control" name="gender" value="<?php echo $gender; ?>"
                                id="exampleSelectGender">
                                <option value="0" <?php if($gender=='0'){echo "selected";}?>>Other</option>
                                <option value="1" <?php if($gender=='1'){echo "selected";} ?>>Male</option>
                                <option value="2" <?php if($gender=='2'){echo "selected";}?>>Female</option>
                            </select>
                        </div>
                        <!-- role  -->
                        <div class="form-group">
                            <label for="exampleSelectGender">Role</label>
                            <select class="form-control" name="role" value="<?php echo $role; ?>"
                                id="exampleSelectGender">
                                <option value="1" <?php if($role=='1'){echo "selected";}?>>Volunteer</option>
                                <option value="2" <?php if($role=='2'){echo "selected";}?>>Coordinator</option>
                            </select>
                        </div>
                        <!-- status  -->
                        <div class="form-group">
                            <label for="exampleSelectGender">Status</label>
                            <select class="form-control" name="status" value="<?php echo $status; ?>"
                                id="exampleSelectGender">
                                <option value="1" <?php if($status=='1'){echo "selected";}?>>Active</option>
                                <option value="2" <?php if($status=='2'){echo "selected";}?>>Inactive</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-gradient-primary me-2">Submit</button>
                        <a href="users" class="btn btn-light">Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php
include 'includes/footer.php';
?>