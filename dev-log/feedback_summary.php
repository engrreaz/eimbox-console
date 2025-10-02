<?php
require_once '../core/config.php';
require_once '../core/db.php';

$page_name = $_GET['page_name'] ?? '';
$feature_name = $_GET['feature_name'] ?? '';

$fb_res = $conn->prepare("SELECT COUNT(*) AS cnt, IFNULL(ROUND(AVG(rating),1),0) AS avg_rating FROM page_feedback WHERE page_name=? AND feature_name=?");
$fb_res->bind_param("ss",$page_name,$feature_name);
$fb_res->execute();
$fb_stats = $fb_res->get_result()->fetch_assoc();
$fb_res->close();

$latest_comments=[];
$fb_res2 = $conn->prepare("SELECT feedback_type, notes, rating, logged_by, created_at FROM page_feedback WHERE page_name=? AND feature_name=? ORDER BY created_at DESC LIMIT 5");
$fb_res2->bind_param("ss",$page_name,$feature_name);
$fb_res2->execute();
$result2 = $fb_res2->get_result();
while($c=$result2->fetch_assoc()) $latest_comments[]=$c;
$fb_res2->close();
?>

<p><strong>Average Rating:</strong> <?= $fb_stats['avg_rating'] ?> ★ (<?= $fb_stats['cnt'] ?> feedbacks)</p>
<ul class="list-group list-group-flush">
<?php foreach($latest_comments as $c): ?>
<li class="list-group-item py-1">
[<?= $c['rating'] ?>★] <?= htmlspecialchars($c['feedback_type']) ?> - <?= htmlspecialchars($c['notes']) ?>
<small>(<?= htmlspecialchars($c['logged_by']) ?>, <?= date('Y-m-d H:i', strtotime($c['created_at'])) ?>)</small>
</li>
<?php endforeach; ?>
</ul>
