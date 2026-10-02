<?php

require "../config/database.php";
require "../app/helpers/Security.php";
require "../app/models/Setting.php";
require "../app/models/Report.php";

$settingModel = new Setting($conn);
require_api_token($settingModel->securityConfig()['api_token']);

$from = trim((string) ($_GET['from'] ?? ""));
$to = trim((string) ($_GET['to'] ?? ""));

if($from !== "" && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$from)){
    api_json(["success" => false, "message" => "Format from harus YYYY-MM-DD."],422);
}

if($to !== "" && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$to)){
    api_json(["success" => false, "message" => "Format to harus YYYY-MM-DD."],422);
}

$reportModel = new Report($conn);
$detections = [];
$result = $reportModel->detections($from,$to);

while($row = mysqli_fetch_assoc($result)){
    $detections[] = $row;
}

api_json([
    "success" => true,
    "period" => [
        "from" => $from,
        "to" => $to
    ],
    "summary" => $reportModel->summary($from,$to),
    "detections" => $detections
]);
