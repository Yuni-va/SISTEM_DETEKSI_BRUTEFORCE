<?php

require "../config/database.php";
require "../app/helpers/Security.php";
require "../app/models/LoginAttempt.php";
require "../app/models/BlockedIP.php";
require "../app/models/ThresholdLog.php";
require "../app/models/DetectionLog.php";
require "../app/models/SystemLog.php";
require "../app/models/Setting.php";
require "../app/services/ThresholdEngine.php";

if($_SERVER['REQUEST_METHOD'] !== "POST"){
    api_json(["success" => false, "message" => "Gunakan method POST."],405);
}

$settingModel = new Setting($conn);
require_api_token($settingModel->securityConfig()['api_token']);

$input = api_input();
$username = trim((string) ($input['username'] ?? "unknown"));
$ip = trim((string) ($input['ip_address'] ?? $input['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? "")));

if($ip === "" || filter_var($ip,FILTER_VALIDATE_IP) === false){
    api_json(["success" => false, "message" => "IP address tidak valid."],422);
}

$attemptModel = new LoginAttempt($conn);
$blockedModel = new BlockedIP($conn);
$thresholdModel = new ThresholdLog($conn);
$detectionModel = new DetectionLog($conn);
$systemModel = new SystemLog($conn);

$engine = new ThresholdEngine(
    $attemptModel,
    $blockedModel,
    $thresholdModel,
    $detectionModel,
    $systemModel,
    $settingModel->securityConfig()
);

$blockedModel->unblockExpired();
$detected = $engine->check($username,$ip);
$blocked = $blockedModel->isBlocked($ip);

api_json([
    "success" => true,
    "detected" => $detected,
    "blocked" => (bool) $blocked,
    "ip_address" => $ip,
    "username" => $username,
    "blocked_until" => $blocked['blocked_until'] ?? null
]);
