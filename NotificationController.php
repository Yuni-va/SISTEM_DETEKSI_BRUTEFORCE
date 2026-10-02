<?php

session_start();

define("AUTH_LOGIN_PATH","../../login.php");
require "../middleware/Admin.php";

require "../../config/database.php";
require "../models/Notification.php";

require_post();
require_csrf();

$notificationModel = new Notification($conn);
$notificationModel->markAllRead((int) $_SESSION['id']);

header("Location: ../../dashboard.php");
exit;
