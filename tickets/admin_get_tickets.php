<?php
require_once '../core/config.php';
require_once '../core/db.php';

$query = "
SELECT tickets.*, users.name AS username 
FROM tickets 
JOIN users ON users.id = tickets.user_id
ORDER BY tickets.created_at DESC
";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $badge = match($row['status']) {
        'open' => 'bg-success',
        'in_progress' => 'bg-warning',
        'closed' => 'bg-secondary',
        default => 'bg-light'
    };

    echo '
    <li class="ticket-item border-bottom py-2 px-2" 
        data-id="'.$row['id'].'" 
        data-status="'.$row['status'].'">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>#'.$row['id'].'</strong> '.$row['subject'].'<br>
                <small class="text-muted">'.$row['username'].'</small>
            </div>
            <span class="badge '.$badge.'">'.ucfirst($row['status']).'</span>
        </div>
    </li>';
}
?>
