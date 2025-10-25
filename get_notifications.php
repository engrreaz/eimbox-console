<?php
// ajax/get_notifications.php
// Include header for db + session
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/functions.php';
require_once 'core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

$notifications = [];
$unread_count = 0;

$sql = "SELECT id, title, message, link, is_read, created_at
        FROM notifications
        WHERE user_id='$user_id'
        ORDER BY created_at DESC
        LIMIT 10";

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $row['created_ago'] = timeAgo($row['created_at']); // ✅ relative time

    if ($row['is_read'] == 0) {
        $notifications[] = $row;
        $unread_count++;
    }
}

echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $unread_count
]);
