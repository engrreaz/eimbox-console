<?php

session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$user_id = $_GET['user_id'] ?? ($_SESSION['user_id'] ?? null);
$ticket_id = $_GET['ticket_id'] ?? null;
$sccode = $_SESSION['sccode'] ?? null;
$userlevel = $_SESSION['userlevel'] ?? null;

if (!$user_id || !$ticket_id) {
    exit("<div class='text-danger p-2'>Invalid request!</div>");
}

// টিকেট যাচাই
$chk = $conn->prepare("SELECT * FROM tickets WHERE id=?");
$chk->bind_param("i", $ticket_id);
$chk->execute();
$ticket = $chk->get_result()->fetch_assoc();
$chk->close();

if (!$ticket || ($ticket['user_id'] != $user_id && $ticket['sccode'] != $sccode && $userlevel != 'Administrator' && $userlevel != 'Chief')) {
    exit("<div class='text-danger p-2'>Access denied!</div>");
}

// মেসেজ লোড
$q = $conn->prepare("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY id ASC");
$q->bind_param("i", $ticket_id);
$q->execute();
$res = $q->get_result();

while ($row = $res->fetch_assoc()) {
    $align = ($row['sender_id'] == $user_id) ? "text-end" : "text-start";
    $message = nl2br(htmlspecialchars($row['message'])); // নতুন লাইনকে <br> এ convert করবে
    echo "<div class='{$align} mb-2'>
            <div class='p-2 bg-white d-inline-block rounded'>{$message}</div>
          </div>";
}
?>