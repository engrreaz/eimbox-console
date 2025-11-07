<?php
require_once '../core/config.php';
require_once '../core/db.php';

$ref_id = intval($_GET['ref_id'] ?? 0);
if ($ref_id <= 0) exit('<div class="text-muted small">Invalid reference.</div>');

$q = $conn->prepare("
    SELECT dn.*, u.profilename AS admin_name
    FROM dev_notes dn
    LEFT JOIN usersapp u ON dn.admin_id = u.id
    WHERE dn.ref_id = ? OR dn.id = ?
    ORDER BY dn.created_at DESC
");
$q->bind_param("ii", $ref_id, $ref_id);
$q->execute();
$res = $q->get_result();

if ($res->num_rows > 0) {
    echo '<ul class="list-group list-group-flush m-0 p-0">';
    while ($r = $res->fetch_assoc()) {
        echo '<li class="list-group-item small">';
        echo '<strong>' . htmlspecialchars($r['status']) . '</strong> — ';
        echo nl2br(htmlspecialchars($r['note_line'])) . '<br>';
        echo '<small class="text-muted">' . htmlspecialchars($r['created_at']) . ' by ' . htmlspecialchars($r['admin_name'] ?? 'Unknown') . '</small>';
        echo '</li>';
    }
    echo '</ul>';
} else {
    echo '<div class="text-muted small">No history found.</div>';
}
$q->close();
