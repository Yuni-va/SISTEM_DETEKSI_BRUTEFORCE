<?php

session_start();

require "../../config/database.php";
require "../helpers/Security.php";
require "../models/Setting.php";

$settingModel = new Setting($conn);

require_post();
require_csrf();

$fullname = input_string($_POST,'fullname',100);
$username = input_string($_POST,'username',100);
$email = input_string($_POST,'email',150);
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];

if($fullname === "" || $username === "" || $email === "" || $password === ""){
    $_SESSION['error'] = "Semua field wajib diisi.";
    header("Location: ../../register.php");
    exit;
}

if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
    $_SESSION['error'] = "Format email tidak valid.";
    header("Location: ../../register.php");
    exit;
}

if(strlen($password) < 8){
    $_SESSION['error'] = "Password minimal 8 karakter.";
    header("Location: ../../register.php");
    exit;
}

if ($password != $confirm) {

    $_SESSION['error'] = "Konfirmasi password tidak sama.";

    header("Location: ../../register.php");

    exit;
}

$cek = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");

$cek->bind_param("ss",$username,$email);

$cek->execute();

if($cek->get_result()->num_rows>0){

$_SESSION['error']="Username atau Email sudah digunakan.";

header("Location: ../../register.php");

exit;

}

$hash=password_hash($password,PASSWORD_DEFAULT);

$stmt=$conn->prepare("INSERT INTO users(fullName,username,email,password,role,profile_photo,status)
VALUES(?,?,?,?,?,?,?)");

$adminCountResult = mysqli_query($conn,"SELECT COUNT(*) total FROM users WHERE role='Administrator'");
$adminCount = mysqli_fetch_assoc($adminCountResult)['total'] ?? 0;
$role = ((int) $adminCount === 0) ? "Administrator" : "User";

$photo="default.png";

$status="active";

$stmt->bind_param(
"sssssss",
$fullname,
$username,
$email,
$hash,
$role,
$photo,
$status
);

$stmt->execute();

$_SESSION['success']="Akun berhasil dibuat.";

header("Location: ../../login.php");
