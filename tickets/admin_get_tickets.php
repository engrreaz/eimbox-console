<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$query = "
SELECT 
    tickets.*, 
    usersapp.profilename AS username, 
    scinfo.scname AS scname
FROM tickets
JOIN usersapp ON usersapp.id = tickets.user_id
JOIN scinfo ON scinfo.sccode = tickets.sccode
ORDER BY tickets.created_at DESC
LIMIT 7;
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
                <small class="text-muted">'.$row['username'].'</small><br>
                <small class="text-muted">'.$row['scname'].'</small>
            </div>
            <span class="badge '.$badge.'">'.ucfirst($row['status']).'</span>
        </div>
    </li>';
}
?>
