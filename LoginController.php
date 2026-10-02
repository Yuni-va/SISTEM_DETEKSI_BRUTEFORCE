<?php

session_start();

require "../../config/database.php";
require "../helpers/Security.php";

require "../models/User.php";
require "../models/LoginAttempt.php";
require "../models/BlockedIP.php";
require "../models/ThresholdLog.php";
require "../models/DetectionLog.php";
require "../models/SystemLog.php";
require "../models/ActivityLog.php";
require "../models/Setting.php";
require "../services/ThresholdEngine.php";

$userModel       = new User($conn);
$attemptModel    = new LoginAttempt($conn);
$blockedModel    = new BlockedIP($conn);
$thresholdModel  = new ThresholdLog($conn);
$detectionModel  = new DetectionLog($conn);
$systemModel     = new SystemLog($conn);
$activityModel   = new ActivityLog($conn);
$settingModel    = new Setting($conn);

$engine = new ThresholdEngine(
    $attemptModel,
    $blockedModel,
    $thresholdModel,
    $detectionModel,
    $systemModel,
    $settingModel->securityConfig()
);

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    require_csrf();

    $username = input_string($_POST,'username',100);
    $password = (string) ($_POST['password'] ?? "");

    $ip = $_SERVER['REMOTE_ADDR'];

    // membuka blokir yang sudah habis
    $blockedModel->unblockExpired();

    // cek apakah IP masih diblokir
    $blocked = $blockedModel->isBlocked($ip);

    if ($blocked) {

        $_SESSION['error'] =
            "Terlalu banyak percobaan login gagal atau terdeteksi serangan brute force. IP Anda diblokir sampai ".$blocked['blocked_until'].".";
        $_SESSION['alert_type']="bruteforce";

        header("Location: ../../login.php");
        exit;
    }

    // cari user
    $user = $userModel->login($username);

    if (!$user) {

        $attemptModel->save($username,$ip,"failed");

        $bruteForceDetected = $engine->check($username,$ip);

        if($bruteForceDetected){
            $_SESSION['error']="Terlalu banyak percobaan login gagal atau terdeteksi serangan brute force. IP Anda sementara diblokir.";
            $_SESSION['alert_type']="bruteforce";

            header("Location: ../../login.php");
            exit;
        }

        $_SESSION['error']="Username atau password salah.";

        header("Location: ../../login.php");
        exit;
    }

    // cek status akun

    $userStatus = $user['status'] ?? (($user['is_active'] ?? 0) == 1 ? 'active' : 'inactive');

    if($userStatus !== "active"){

        $_SESSION['error']="Akun tidak aktif.";

        header("Location: ../../login.php");
        exit;

    }

    // password salah

    if(!password_verify($password,$user['password'])){

        $attemptModel->save($username,$ip,"failed");

        $activityModel->save(
            $user['id'],
            "Login gagal"
        );

        $bruteForceDetected = $engine->check($username,$ip);

        if($bruteForceDetected){
            $_SESSION['error']="Terlalu banyak percobaan login gagal atau terdeteksi serangan brute force. IP Anda sementara diblokir.";
            $_SESSION['alert_type']="bruteforce";

            header("Location: ../../login.php");
            exit;
        }

        $_SESSION['error']="Username atau password salah.";

        header("Location: ../../login.php");
        exit;

    }

    // login berhasil

    session_regenerate_id(true);

    $_SESSION['login']=true;
    $_SESSION['id']=$user['id'];
    $_SESSION['fullname']=$user['fullName'];
    $_SESSION['username']=$user['username'];
    $_SESSION['role']=$user['role'];
    $_SESSION['profile_photo']=$user['profile_photo'] ?? "";

    $attemptModel->save(
        $username,
        $ip,
        "success"
    );

    $attemptModel->clearFailedAttempt($ip);

    $activityModel->save(
        $user['id'],
        "Login berhasil"
    );

    $systemModel->save(
        "User ".$username." login berhasil."
    );

    header("Location: ../../dashboard.php");
    exit;
}
