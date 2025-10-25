<?php
require_once '../core/config.php';
require_once '../core/db.php';

$user_id = intval($_GET['user_id']);
$result = $conn->query("SELECT * FROM tickets WHERE user_id=$user_id ORDER BY created_at DESC");

while ($row = $result->fetch_assoc()) {
    $badge = match($row['status']) {
        'open' => 'badge bg-success',
        'in_progress' => 'badge bg-warning',
        'closed' => 'badge bg-secondary',
        default => 'badge bg-light'
    };
    echo '
    <li class="ticket-item border-bottom py-2 px-2" data-id="'.$row['id'].'" data-status="'.$row['status'].'">
      <div class="d-flex justify-content-between">
        <span>'.$row['subject'].'</span>
        <span class="'.$badge.'">'.ucfirst($row['status']).'</span>
      </div>
      <small class="text-muted">#'.$row['id'].' • '.$row['created_at'].'</small>
    </li>';
}
?>
