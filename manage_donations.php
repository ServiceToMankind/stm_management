<?php

include 'includes/header.php';

// Initialize form values 
$uid = "";
$name = "";
$mail = "";
$mobile = "";
$amount = "";
$type = "";
$message = "";
$txid = "";
$payment_status = "";
$custid = "";
$rid = "";

if (isset($_GET['rid'])) {
    if (isset($_GET['action']) && $_GET['action'] == 'delete') {
        $rid = $_GET['rid'];
        $action = $_GET['action'];
        $data2 = get_api_data("https://apis.stmorg.in/logs/manage_donations?rid=" . $rid . "&action=" . $action);
    } else {
        $rid = $_GET['rid'];
        $url = "https://apis.stmorg.in/logs/donations?rid=" . $rid;
        $data = get_api_data($url);
        $data = json_decode($data, true);
        $data = $data['data'];
        $rid = $data[0]['id'];
        $name = $data[0]['name'];
        $mail = $data[0]['mail'];
        $mobile = $data[0]['mobile'];
        $amount = $data[0]['amount'];
        $type = $data[0]['type'];
        $message = $data[0]['message'];
        $txid = $data[0]['txid'];
        $payment_status = $data[0]['payment_status'];
        $custid = $data[0]['custid'];
        $added_on = $data[0]['added_on'];
    }
}

if (isset($_POST['rid'])) {
    $rid = $_POST['rid'];
    $data2 = get_api_data_post("https://apis.stmorg.in/logs/manage_donations", $_POST);
    $data2 = json_decode($data2, true);
    echo "<script>window.location.href='manage_donations?rid=" . $rid . "'</script>";
}else {
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
                Manage Donations
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
                    <h4 class="card-title">Manage Donations</h4>
                    <p class="card-description">edit/enter details</p>
                    <form class="forms-sample" method="POST" action="manage_donations">
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
                        <!-- amount  -->
                        <div class="form-group">
                            <label for="exampleInputAmount">Amount</label>
                            <input type="number" name="amount" value="<?php echo $amount; ?>" class="form-control"
                                id="exampleInputAmount" placeholder="Amount">
                        </div>
                        <!-- type  -->
                        <div class="form-group">
                            <label for="exampleSelectGender">Type</label>
                            <select class="form-control" name="type" id="exampleSelectGender">
                                <option value="1" <?php if ($type == '1') {
                                    echo "selected";
                                } ?>>Online</option>
                                <option value="2" <?php if ($type == '2') {
                                    echo "selected";
                                } ?>>Cash</option>
                            </select>
                        </div>
                        <!-- message  -->
                        <div class="form-group">
                            <label for="exampleInputMessage">Message</label>
                            <input type="text" name="message" value="<?php echo $message; ?>" class="form-control"
                                id="exampleInputMessage" placeholder="Message">
                        </div>
                        <!-- txid  -->
                        <div class="form-group">
                            <label for="exampleInputTxid">Txid</label>
                            <input type="text" name="txid" value="<?php echo $txid; ?>" class="form-control"
                                id="exampleInputTxid" placeholder="Txid">
                        </div>
                        <!-- status  -->
                        <div class="form-group">
                            <label for="exampleSelectGender">Status</label>
                            <select class="form-control" name="payment_status" id="exampleSelectGender">
                                <option value="1" <?php if ($payment_status == '1') {
                                    echo "selected";
                                } ?>>success</option>
                                <option value="2" <?php if ($payment_status == '2') {
                                    echo "selected";
                                } ?>>fail</option>
                            </select>
                        </div>
                        <!-- custid  -->
                        <div class="form-group">
                            <label for="exampleInputCustid">Custid</label>
                            <input type="text" name="custid" value="<?php echo $custid; ?>" class="form-control"
                                id="exampleInputCustid" placeholder="Custid">
                        </div>
                        <!-- Record id  -->
                        <div class="form-group">
                            <label for="exampleInputRid">Record id</label>
                            <input type="text" name="rid" value="<?php echo $rid; ?>" class="form-control"
                                id="exampleInputRid" placeholder="Record id">
                        </div>
                        <!-- date  -->
                        <div class="form-group">
                            <label for="exampleInputDate">Date</label>
                            <input type="datetime" name="added_on" value="<?php echo $added_on; ?>" class="form-control"
                                id="exampleInputDate" placeholder="Date">
                        </div>

                        <button type="submit" class="btn btn-gradient-primary me-2">Submit</button>
                        <a href="donations" class="btn btn-light">Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php
include 'includes/footer.php';
?>