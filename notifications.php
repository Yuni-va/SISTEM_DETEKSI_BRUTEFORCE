<?php
header('Content-Type: application/json');

session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/DetectionLog.php';
require_once __DIR__ . '/../../app/models/BlockedIP.php';
require_once __DIR__ . '/../../app/models/Notification.php';

$d = new DetectionLog($conn);
$b = new BlockedIP($conn);
$n = new Notification($conn);

$userId = (int) ($_SESSION['id'] ?? 0);

$unread = $n->unreadCount($userId);

$detections = $d->latest(5);
$blocked = $b->latestBlocked(5);

$payload = [
    'unread' => $unread,
    'detections' => [],
    'blocked' => []
];

while($row = mysqli_fetch_assoc($detections)){
    $payload['detections'][] = [
        'id' => (int)$row['id'],
        'ip_address' => $row['ip_address'],
        'detected_at' => $row['detected_at']
    ];
}

while($row = mysqli_fetch_assoc($blocked)){
    $payload['blocked'][] = [
        'id' => (int)$row['id'],
        'ip_address' => $row['ip_address'],
        'blocked_until' => $row['blocked_until']
    ];
}

echo json_encode($payload);
