<?php include 'includes/header.php'; ?>
<?php
$user_id = $_SESSION['user_id'];
$q = $conn->query("
    SELECT p.total_points, t.title_name
    FROM user_points p
    LEFT JOIN user_titles ut ON ut.user_id=p.user_id
    LEFT JOIN titles_list t ON t.id=ut.title_id
    WHERE p.user_id=$user_id
");
$data = $q->fetch_assoc();
?>

<div class="container my-4">
    <h4>🎯 আপনার র‍্যাংকিং সারসংক্ষেপ</h4>
    <div class="card p-3">
        <p><b>মোট পয়েন্ট:</b> <?= $data['total_points'] ?? 0 ?></p>
        <p><b>বর্তমান উপাধি:</b> <?= $data['title'] ?? 'নবাগত' ?></p>

        <hr>
        <h5>🏅 আপনার অ্যাচিভমেন্টস</h5>
        <ul>
        <?php
        $ach = $conn->query("
            SELECT a.name FROM user_achievements ua
            JOIN achievements_list a ON a.id=ua.achievement_id
            WHERE ua.user_id=$user_id
        ");
        if ($ach->num_rows > 0) {
            while ($a = $ach->fetch_assoc()) echo "<li>{$a['title']}</li>";
        } else echo "<li>এখনও কিছু অর্জিত হয়নি।</li>";
        ?>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>