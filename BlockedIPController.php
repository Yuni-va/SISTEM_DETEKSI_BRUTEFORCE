<?php

session_start();

define("AUTH_LOGIN_PATH","../../login.php");
require "../middleware/Admin.php";
require "../../config/database.php";
require "../models/BlockedIP.php";

$model = new BlockedIP($conn);

if(isset($_POST['unblock'])){

    require_csrf();

    $model->unblock((int) ($_POST['id'] ?? 0));

    $_SESSION['success']="IP berhasil di-unblock.";

    header("Location: ../../blocked_ips.php");

    exit;

}

header("Location: ../../blocked_ips.php");
exit;
