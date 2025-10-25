<?php
// createNotification(5, "New Message", "You have received a new message from Admin", "messages.php");
// createNotification($to_user_id, $title, $message, $link, 'message');


include 'header.php';


$notifications = getNotifications($user_id_no, 200); // show many
?>
<div class="container mt-4">
    <h3>All Notifications</h3>
    <div class="list-group">
        <?php if (empty($notifications)): ?>
            <div class="text-muted p-3">No notifications yet.</div>
        <?php else:
            foreach ($notifications as $n): ?>
                <a href="<?= htmlspecialchars($n['link'] ?: '#') ?>"
                    class="list-group-item list-group-item-action <?= $n['is_read'] == 0 ? 'list-group-item-warning' : '' ?>"
                    onclick="fetch('mark_read.php', {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({notification_id: <?= (int) $n['id'] ?>})})">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?= htmlspecialchars($n['title']) ?></h5>
                        <small><?= htmlspecialchars($n['created_at']) ?></small>
                    </div>
                    <p class="mb-1 small"><?= nl2br(htmlspecialchars($n['message'])) ?></p>
                </a>
            <?php endforeach; endif; ?>
    </div>
</div>
<?php include 'footer.php'; ?>

</body>

</html>