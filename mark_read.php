<?php
// ajax/mark_read.php
include_once 'header.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

if (isset($input['notification_id']) && $input['notification_id']) {
    $nid = (int)$input['notification_id'];
    $ok = markNotificationAsRead($nid, $user_id);
    echo json_encode(['ok' => $ok]);
    exit;
}

if (isset($input['all']) && $input['all'] == 1) {
    $ok = markAllAsRead($user_id);
    echo json_encode(['ok' => $ok]);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
