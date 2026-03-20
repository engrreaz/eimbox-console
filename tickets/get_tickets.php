<?php
require_once '../core/config.php';
require_once '../core/db.php';

session_start();

$user_id = $_GET['user_id'] ?? $_SESSION['user_id'];
$sccode = $_SESSION['sccode'];
$userlevel = $_SESSION['userlevel'];
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

// Query preparation
if ($userlevel == 'Administrator' || $userlevel == 'Super Administrator' || $userlevel == 'Chief') {
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE sccode=? ORDER BY id DESC LIMIT ?");
    $stmt->bind_param('si', $sccode, $limit);
} else {
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE user_id=? OR ticket_for='Institute' ORDER BY id DESC LIMIT ?");
    $stmt->bind_param('ii', $user_id, $limit);
}

$stmt->execute();
$res = $stmt->get_result();

// Output tickets
$count = 0;
while ($row = $res->fetch_assoc()) {
    $count++;
    $statusColor = $row['status'] == 'closed' ? 'secondary' :
                   ($row['status'] == 'in_progress' ? 'warning' : 'success');

    echo "
    <li class='ticket-item p-2 border-bottom' data-id='{$row['id']}' data-status='{$row['status']}' style='cursor:pointer'>
      <div class='d-flex justify-content-between'>
        <span class='fw-bold'>{$row['subject']}</span>
        <span class='badge bg-{$statusColor}'>{$row['status']}</span>
      </div>
      <small class='text-muted'>{$row['created_at']}</small>
    </li>";
}

// Show "Load more" only if fetched rows equal the limit
if ($count == $limit) {
    echo "<li class='text-center py-2'><button class='btn btn-dark' id='load-more'>Load more...</button></li>";
}

$stmt->close();
?>