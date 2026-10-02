<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/models/Notification.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
}

$userId = (int) ($_SESSION['id'] ?? 0);

if($userId <= 0){
    echo json_encode(['success'=>false,'message'=>'Not authenticated']);
    exit;
}

$n = new Notification($conn);
$ok = $n->markAllRead($userId);

echo json_encode(['success' => (bool)$ok]);
