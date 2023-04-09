<?php
    session_start();
require('includes/functions.php');

    unset($_SESSION['ID']);
    unset($_SESSION['NAME']);
    unset($_SESSION['MAIL']);
    echo "<script>window.location.href='index.php'</script>";
    die();
?>