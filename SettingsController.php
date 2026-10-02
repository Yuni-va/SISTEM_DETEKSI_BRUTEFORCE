<?php

session_start();

define("AUTH_LOGIN_PATH","../../login.php");
require "../middleware/Admin.php";

require "../../config/database.php";
require "../models/Setting.php";

$settingModel = new Setting($conn);

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    require_csrf();

    if(isset($_POST['save_menus'])){
        $activeMenus = $_POST['menus'] ?? [];
        $settingModel->updateMenus($activeMenus);
        $_SESSION['success'] = "Pengaturan menu berhasil disimpan.";
    }

    if(isset($_POST['save_security'])){
        $settingModel->updateSecurityConfig($_POST);
        $_SESSION['success'] = "Pengaturan keamanan berhasil disimpan.";
    }

    header("Location: ../../settings.php");
    exit;
}

header("Location: ../../settings.php");
exit;
