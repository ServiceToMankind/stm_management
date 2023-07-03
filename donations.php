<?php
include 'includes/header.php';
if(isset($_GET['page'])){
    $page = $_GET['page'];
}else{
    $page = 1;
}
// Set the timezone to Asia/Kolkata
date_default_timezone_set("Asia/Kolkata");

// Get the current date and time
$date = date('Y-m-d h:i:s');

// Initialize variables for the month and year
$month = date('m', strtotime("$date"));
$year = date('Y', strtotime("$date"));

if(isset($_GET['month'])){
    $month = $_GET['month'];
    if(isset($_GET['year'])){
        $year = $_GET['year'];
    }
    $url = "http://localhost/apis.stmorg.in/logs/donations?month=".$month."&year=".$year."&page=".$page;
    $data = get_api_data($url);
    $data = json_decode($data, true);
    $total_pages = $data['total_pages'];
    $data = $data['data'];
}else{
    $url = "http://localhost/apis.stmorg.in/logs/donations?page=".$page;
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
                Donation Records
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb">
                    <!-- search bar  -->
                    <li>
                        <form method="GET">
                            <div class="form-group">
                                <div class="input-group">
                                    <h5>Search by month & year : </h5>
                                    <!-- month  -->
                                    <div class="form-group">
                                        <select class="form-control" name="month" id="exampleSelectGender">
                                            <!-- Keep the month and year as default to get all the records  -->
                                            <option value="<?php echo $month; ?>"><?php echo $month; ?></option>
                                            <?php
                                                for($i=1; $i<=12; $i++){
                                                    ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php
                                                }
                                                ?>
                                        </select>
                                    </div>
                                    <!-- year  -->
                                    <div class="form-group">
                                        <select class="form-control" name="year" id="exampleSelectGender">
                                            <!-- keep $year as default to get all the records  -->
                                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                            <?php
                                                for($i=2018; $i<=2030; $i++){
                                                    ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php
                                                }
                                                ?>
                                        </select>
                                    </div>
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
                    <!-- print button  -->
                    <a href="printdonations?month=<?php echo $month; ?>&year=<?php echo $year; ?>"
                        class="btn btn-gradient-primary me-2" style="margin: 1em">Print</a>
                    <div class="card-body">
                        <h4 class="card-title">Donation records of <?php echo $month;?>th month of
                            <?php echo $year; ?>th
                            year</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tx. Id</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Amount</th>
                                        <th>Message</th>
                                        <th>DateTime</th>
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
                                        <td><?php echo $data[$i]['txid']; ?></td>
                                        <td><?php echo $data[$i]['name']; ?></td>
                                        <td><?php echo $data[$i]['mail']; ?></td>
                                        <td><?php echo $data[$i]['amount']; ?></td>
                                        <td><?php echo $data[$i]['message']; ?></td>
                                        <td><?php echo $data[$i]['added_on']; ?></td>
                                        <td>
                                            <?php
                                                if($data[$i]['payment_status'] == 1){
                                                    ?>
                                            <label class="badge badge-gradient-success">success</label>
                                            <?php
                                                }else{
                                                    ?>
                                            <label class="badge badge-gradient-danger">fail</label>
                                            <?php
                                                }
                                                ?>
                                        </td>
                                        <td>
                                            <a href="manage_donations?rid=<?php echo $data[$i]['id']; ?>"
                                                class="btn btn-gradient-info btn-sm"
                                                onclick="return confirm('Are you sure ?');">Edit</a>
                                            <a href="manage_donations?rid=<?php echo $data[$i]['id']; ?>&action=delete"
                                                class="btn btn-gradient-danger btn-sm"
                                                onclick="return confirm('Are you sure you want to delete this record?');">
                                                Delete
                                            </a>

                                        </td>
                                    </tr>
                                    <?php
                                    }
                                }
                                    ?>

                                </tbody>
                            </table>
                            <!-- pagination -->
                            <!-- pagination -->
                            <div class="btn-group page-group" role="group" aria-label="Basic example">
                                <?php if ($total_pages): ?>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                    <?php if (isset($_GET['month']) && isset($_GET['year'])): ?>
                                    <li class="prev"><a
                                            href="?page=<?php echo $page - 1 ?>&month=<?php echo $month ?>&year=<?php echo $year ?>">Prev</a>
                                    </li>
                                    <?php else: ?>
                                    <li class="prev"><a href="?page=<?php echo $page - 1 ?>">Prev</a></li>
                                    <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($page > 3): ?>
                                    <?php if (isset($_GET['month']) && isset($_GET['year'])): ?>
                                    <li class="start"><a
                                            href="?page=1&month=<?php echo $month ?>&year=<?php echo $year ?>">1</a>
                                    </li>
                                    <?php else: ?>
                                    <li class="start"><a href="?page=1">1</a></li>
                                    <?php endif; ?>
                                    <li class="dots">...</li>
                                    <?php endif; ?>

                                    <?php if ($page - 2 > 0): ?>
                                    <?php if (isset($_GET['month']) && isset($_GET['year'])): ?>
                                    <li class="page"><a
                                            href="?page=<?php echo $page - 2 ?>&month=<?php echo $month ?>&year=<?php echo $year ?>"><?php echo $page - 2 ?></a>
                                    </li>
                                    <?php else: ?>
                                    <li class="page"><a href="?page=<?php echo $page - 2 ?>"><?php echo $page - 2 ?></a>
                                    </li>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($page - 1 > 0): ?>
                                    <?php if (isset($_POST['month']) && isset($_POST['year'])): ?>
                                    <li class="page"><a
                                            href="?page=<?php echo $page - 1 ?>&month=<?php echo $month ?>&year=<?php echo $year ?>"><?php echo $page - 1 ?></a>
                                    </li>
                                    <?php else: ?>
                                    <li class="page"><a href="?page=<?php echo $page - 1 ?>"><?php echo $page - 1 ?></a>
                                    </li>
                                    <?php endif; ?>
                                    <?php endif; ?>

                                    <li class="currentpage"><a href="?page=<?php echo $page ?>"><?php echo $page ?></a>
                                    </li>

                                    <?php if ($page + 1 < $total_pages + 1): ?>
                                    <?php if (isset($_GET['month']) && isset($_GET['year'])): ?>
                                    <li class="page"><a
                                            href="?page=<?php echo $page + 1 ?>&month=<?php echo $month ?>&year=<?php echo $year ?>"><?php echo $page + 1 ?></a>
                                    </li>
                                    <?php else: ?>
                                    <li class="page"><a href="?page=<?php echo $page + 1 ?>"><?php echo $page + 1 ?></a>
                                    </li>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($page + 2 < $total_pages + 1): ?>
                                    <?php if (isset($_GET['month']) && isset($_GET['year'])): ?>
                                    <li class="page"><a
                                            href="?page=<?php echo $page + 2 ?>&month=<?php echo $month ?>&year=<?php echo $year ?>"><?php echo $page + 2 ?></a>
                                    </li>
                                    <?php else: ?>
                                    <li class="page"><a href="?page=<?php echo $page + 2 ?>"><?php echo $page + 2 ?></a>
                                    </li>
                                    <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($page < $total_pages - 2): ?>
                                    <li class="dots">...</li>
                                    <?php if (isset($_GET['month']) && isset($_GET['year'])): ?>
                                    <li class="end"><a
                                            href="?page=<?php echo $total_pages ?>&month=<?php echo $month ?>&year=<?php echo $year ?>"><?php echo $total_pages ?></a>
                                    </li>
                                    <?php else: ?>
                                    <li class="end"><a
                                            href="?page=<?php echo $total_pages ?>"><?php echo $total_pages ?></a></li>
                                    <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($page < $total_pages): ?>
                                    <?php if (isset($_GET['month']) && isset($_GET['year'])): ?>
                                    <li class="next"><a
                                            href="?page=<?php echo $page + 1 ?>&month=<?php echo $month ?>&year=<?php echo $year ?>">Next</a>
                                    </li>
                                    <?php else: ?>
                                    <li class="next"><a href="?page=<?php echo $page + 1 ?>">Next</a></li>
                                    <?php endif; ?>
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