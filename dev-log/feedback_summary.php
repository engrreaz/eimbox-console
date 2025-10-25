<?php
require_once '../core/config.php';
require_once '../core/db.php';

$page_name = $_GET['page_name'] ?? '';
$feature_name = $_GET['feature'] ?? '';
$offset = intval($_GET['offset'] ?? 0);
$limit = 5;

// শুধু feedback list ফেরত দিব
$fb_res = $conn->prepare("
    SELECT feedback_type, notes, rating, logged_by, created_at 
    FROM page_feedback
    WHERE page_name=? AND feature_name=?
    ORDER BY created_at DESC
    LIMIT ?, ?
");
$fb_res->bind_param("ssii", $page_name, $feature_name, $offset, $limit);
$fb_res->execute();
$result = $fb_res->get_result();

while ($c = $result->fetch_assoc()) {
    echo '<li class="list-group-item py-1">';
    echo '[' . (int) $c['rating'] . '★] ' . htmlspecialchars($c['feedback_type']) . ' - ' . htmlspecialchars($c['notes']);
    echo ' <small>(' . htmlspecialchars($c['logged_by']) . ', ' . date('Y-m-d H:i', strtotime($c['created_at'])) . ')</small>';
    echo '</li>';
}
$fb_res->close();
