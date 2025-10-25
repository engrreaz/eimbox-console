<?php
require_once '../core/config.php';
require_once '../core/db.php';

echo 'Available Users';
$result = $conn->query("SELECT * FROM users ORDER BY name");
while ($row = $result->fetch_assoc()) {
    echo '
    <li class="chat-contact-item d-flex align-items-center py-2 border-bottom" data-id="'.$row['id'].'">
        <img src="'.$row['avatar'].'" class="rounded-circle me-3" width="40" height="40">
        <div>
            <div class="fw-bold">'.$row['name'].'</div>
            <small class="text-muted">'.ucfirst($row['status']).'</small>
        </div>
    </li>';
}
?>
