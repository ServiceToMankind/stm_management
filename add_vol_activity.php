<?php
include 'includes/header.php';

if(isset($_GET['rmid'])){
    $rmid = $_GET['rmid'];
    // remove 'stmo' from id
    $rmid = substr($rmid,4);
    $action="remove";
    $aid = $_GET['aid'];
    // add into an array
    $data2 = get_api_data_post("https://apis.stmorg.in/activities/manage_activity_vol",array('action'=>$action,'aid'=>$aid,'volunteers'=>$rmid));
    $data2 = json_decode($data2, true);
    $data2 = $data2['data'];
    if($data2['status']=='success'){
        echo "<script>window.location.href='add_vol_activity?id=".$aid."';</script>";
    }else{
        echo "<script>window.location.href='add_vol_activity?id=".$aid."';</script>";
    }
    
}

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $url = "https://apis.stmorg.in/activities/activity_volunteers?id=".$id;
    $data = get_api_data($url);
    $data = json_decode($data, true);
    $data = $data['data'];
}else{
    echo "<script>window.location.href='volunteering_activities';</script>";
}

if(isset($_POST['action'])){
    $data2 = get_api_data_post("https://apis.stmorg.in/activities/manage_activity_vol",$_POST);
    $data2 = json_decode($data2, true);
    $data2 = $data2['data'];
    if($data2['status']=='success'){
        echo "<script>window.location.href='add_vol_activity?id=".$id."';</script>";
    }else{
        echo "<script>window.location.href='add_vol_activity?id=".$id."';</script>";
    }
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
                Select Activity
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <!-- search bar  -->
                    <li>
                        <form method="POST">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Id's"
                                        aria-label="Recipient's username" name="volunteers"
                                        aria-describedby="basic-addon2">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="aid" value="<?php echo $id; ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-gradient-primary" type="submit">Add
                                            Volunteers</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
        <!-- form  -->
        <div class="row">
            <div class="col-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Attended Volunteers</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if(isset($data['status']) && $data['status']=='error'){
                                    }else{
                                    for($i=0; $i<count($data); $i++){
                                        ?>
                                    <tr>
                                        <td><?php echo $data[$i]['id']; ?></td>
                                        <td><?php echo $data[$i]['name']; ?></td>
                                        <td><?php echo $data[$i]['mobile']; ?></td>
                                        <td>
                                            <a href="add_vol_activity?rmid=<?php echo $data[$i]['id']; ?>&aid=<?php echo $id; ?>"
                                                class="btn btn-gradient-info btn-sm">Remove</a>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                }
                                    ?>

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