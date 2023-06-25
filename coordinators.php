<?php
include 'includes/header.php';
if(isset($_GET['page'])){
    $page = $_GET['page'];
}else{
    $page = 1;
}
if(isset($_POST['pquery'])){
    $pquery = $_POST['pquery'];
    $url = "https://apis.stmorg.in/global/users?pquery=".$pquery ."&page=".$page."&role=2";
    $data = get_api_data($url);
    $data = json_decode($data, true);
    $total_pages = $data['total_pages'];
    $data = $data['data'];
}else{
    $url = "https://apis.stmorg.in/global/users?page=".$page."&role=2";;
    $data = get_api_data($url);
    $data = json_decode($data, true);
    $total_pages = $data['total_pages'];
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
                List Users
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
                        <h4 class="card-title">Recent records</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile No.</th>
                                        <th>Designation</th>
                                        <th>Status</th>
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
                                        <td><?php echo $data[$i]['mail']; ?></td>
                                        <td><?php echo $data[$i]['mobile']; ?></td>
                                        <td><?php echo $data[$i]['role']; ?></td>
                                        <td>
                                            <?php
                                                if($data[$i]['status'] == 1){
                                                    ?>
                                            <label class="badge badge-gradient-success">Active</label>
                                            <?php
                                                }else{
                                                    ?>
                                            <label class="badge badge-gradient-danger">Inactive</label>
                                            <?php
                                                }
                                                ?>
                                        </td>
                                        <td>
                                            <a href="manage_users?uid=<?php echo $data[$i]['id']; ?>"
                                                class="btn btn-gradient-info btn-sm">Edit</a>
                                            <a href="manage_users?deluid=<?php echo $data[$i]['id']; ?>"
                                                class="btn btn-gradient-danger btn-sm">Delete</a>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                }
                                    ?>

                                </tbody>
                            </table>
                            <!-- pagination -->
                            <div class="btn-group page-group" role="group" aria-label="Basic example">
                                <?php if ($total_pages): ?>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                    <li class="prev"><a href="?page=<?php echo $page-1 ?>">Prev</a></li>
                                    <?php endif; ?>

                                    <?php if ($page > 3): ?>
                                    <li class="start"><a href="?page=1">1</a></li>
                                    <li class="dots">...</li>
                                    <?php endif; ?>

                                    <?php if ($page-2 > 0): ?><li class="page"><a
                                            href="?page=<?php echo $page-2 ?>"><?php echo $page-2 ?></a>
                                    </li><?php endif; ?>
                                    <?php if ($page-1 > 0): ?><li class="page"><a
                                            href="?page=<?php echo $page-1 ?>"><?php echo $page-1 ?></a>
                                    </li><?php endif; ?>

                                    <li class="currentpage"><a href="?page=<?php echo $page ?>"><?php echo $page ?></a>
                                    </li>

                                    <?php if ($page+1 < $total_pages+1): ?><li class="page"><a
                                            href="?page=<?php echo $page+1 ?>"><?php echo $page+1 ?></a>
                                    </li><?php endif; ?>
                                    <?php if ($page+2 < $total_pages+1): ?><li class="page"><a
                                            href="?page=<?php echo $page+2 ?>"><?php echo $page+2 ?></a>
                                    </li><?php endif; ?>

                                    <?php if ($page < $total_pages-2): ?>
                                    <li class="dots">...</li>
                                    <li class="end"><a
                                            href="?page=<?php echo $total_pages ?>"><?php echo $total_pages ?></a>
                                    </li>
                                    <?php endif; ?>

                                    <?php if ($page < $total_pages): ?>
                                    <li class="next"><a href="?page=<?php echo $page+1 ?>">Next</a></li>
                                    <?php endif; ?>
                                </ul>
                                <?php endif; ?>

                            </div>
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