<?php
include 'includes/header.php';
if(isset($_GET['page'])){
    $page = $_GET['page'];
}else{
    $page = 1;
}
    $url = "https://apis.stmorg.in/activities/activities";
    $data = get_api_data($url);
    $data = json_decode($data, true);
    $data = $data['data'];
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
                                    <input type="text" class="form-control" placeholder="Query"
                                        aria-label="Recipient's username" name="pquery" aria-describedby="basic-addon2">
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-gradient-primary" type="submit">Search</button>
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
                        <h4 class="card-title">Active Activities</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Activity ame</th>
                                        <th>Description</th>
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
                                        <td><?php echo $data[$i]['title']; ?></td>
                                        <td><?php echo $data[$i]['description']; ?></td>
                                        <td>
                                            <a href="add_vol_activity?id=<?php echo $data[$i]['id']; ?>"
                                                class="btn btn-gradient-info btn-sm">Add Volunteers data</a>
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