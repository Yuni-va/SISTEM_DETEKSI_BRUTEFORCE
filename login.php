<?php

require "../config/database.php";
require "../app/helpers/Security.php";
require "../app/models/User.php";
require "../app/models/LoginAttempt.php";
require "../app/models/BlockedIP.php";
require "../app/models/ThresholdLog.php";
require "../app/models/DetectionLog.php";
require "../app/models/SystemLog.php";
require "../app/models/ActivityLog.php";
require "../app/models/Setting.php";
require "../app/services/ThresholdEngine.php";

if($_SERVER['REQUEST_METHOD'] !== "POST"){
    api_json(["success" => false, "message" => "Gunakan method POST."],405);
}

session_start();

$input = api_input();
$username = trim((string) ($input['username'] ?? ""));
$password = (string) ($input['password'] ?? "");
$ip = $_SERVER['REMOTE_ADDR'] ?? "";

if($username === "" || $password === ""){
    api_json(["success" => false, "message" => "Username dan password wajib diisi."],422);
}

$userModel = new User($conn);
$attemptModel = new LoginAttempt($conn);
$blockedModel = new BlockedIP($conn);
$thresholdModel = new ThresholdLog($conn);
$detectionModel = new DetectionLog($conn);
$systemModel = new SystemLog($conn);
$activityModel = new ActivityLog($conn);
$settingModel = new Setting($conn);

$engine = new ThresholdEngine(
    $attemptModel,
    $blockedModel,
    $thresholdModel,
    $detectionModel,
    $systemModel,
    $settingModel->securityConfig()
);

$blockedModel->unblockExpired();
$blocked = $blockedModel->isBlocked($ip);

if($blocked){
    api_json([
        "success" => false,
        "message" => "IP diblokir sementara.",
        "blocked_until" => $blocked['blocked_until']
    ],423);
}

$user = $userModel->login($username);

if(!$user){
    $attemptModel->save($username,$ip,"failed");

    $detected = $engine->check($username,$ip);

    api_json([
        "success" => false,
        "message" => $detected ? "Brute force terdeteksi. IP diblokir sementara." : "Username atau password salah.",
        "bruteforce_detected" => $detected
    ],401);
}

$accountStatus = $user['status'] ?? (($user['is_active'] ?? 0) == 1 ? 'active' : 'inactive');

if($accountStatus !== "active"){
    $attemptModel->save($username,$ip,"failed");
    $activityModel->save($user['id'],"Login API gagal - akun tidak aktif");

    api_json([
        "success" => false,
        "message" => "Akun tidak aktif.",
        "bruteforce_detected" => false
    ],403);
}

if(!password_verify($password,$user['password'])){
    $attemptModel->save($username,$ip,"failed");
    $activityModel->save($user['id'],"Login API gagal");

    $detected = $engine->check($username,$ip);

    api_json([
        "success" => false,
        "message" => $detected ? "Brute force terdeteksi. IP diblokir sementara." : "Username atau password salah.",
        "bruteforce_detected" => $detected
    ],401);
}

session_regenerate_id(true);

$_SESSION['login'] = true;
$_SESSION['id'] = $user['id'];
$_SESSION['fullname'] = $user['fullName'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['profile_photo'] = $user['profile_photo'] ?? "";

$attemptModel->save($username,$ip,"success");
$attemptModel->clearFailedAttempt($ip);

$activityModel->save($user['id'],"Login API berhasil");
$systemModel->save("User ".$username." login API berhasil.");

api_json([
    "success" => true,
    "message" => "Login berhasil.",
    "user" => [
        "id" => $user['id'],
        "fullname" => $user['fullName'],
        "username" => $user['username'],
        "role" => $user['role']
    ]
]);
