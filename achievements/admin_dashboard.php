<?php
$page_title = "Admin Dashboard";
include 'includes/header.php';

// শুধু অ্যাডমিন ব্যবহারকারী
if (!isset($_SESSION['user_id'])  || $is_admin == 0) {
    echo "<div class='alert alert-danger text-center mt-4'>Access denied. Admins only.</div>";
    include 'includes/footer.php';
    exit;
}

// মোট ইউজার সংখ্যা
$totalUsers = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'];

// মোট পয়েন্ট
$totalPoints = $conn->query("SELECT SUM(total_points) as sum_points FROM user_points")->fetch_assoc()['sum_points'];

// সর্বশেষ 5 কার্যক্রম
$recentActivities = $conn->query("SELECT a.*, u.profilename AS user_name 
                                  FROM activity_log a 
                                  LEFT JOIN usersapp u ON u.id=a.user_id 
                                  ORDER BY a.created_at DESC 
                                  LIMIT 5");

// সর্বশেষ 5 ফিডব্যাক
$recentFeedback = $conn->query("SELECT f.*, u.username AS user_name 
                                FROM feedbacks f 
                                LEFT JOIN usersapp u ON u.id=f.username 
                                ORDER BY f.created_at DESC 
                                LIMIT 5");
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">🛠️ Admin Dashboard</h2>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Users</h5>
                    <h3><?= $totalUsers ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Points Awarded</h5>
                    <h3><?= $totalPoints ?: 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5>Total Achievements</h5>
                    <h3><?= $conn->query("SELECT COUNT(*) as cnt FROM user_achievements")->fetch_assoc()['cnt'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- সাম্প্রতিক কার্যক্রম -->
    <h4>Recent Activities</h4>
    <?php if ($recentActivities && $recentActivities->num_rows > 0): ?>
        <ul class="list-group mb-4">
            <?php while ($act = $recentActivities->fetch_assoc()): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($act['user_name']) ?></strong> did <em><?= htmlspecialchars($act['action_type']) ?></em> 
                    (+<?= $act['points'] ?> points)
                    <br><small class="text-muted"><?= date("d M Y, h:i A", strtotime($act['created_at'])) ?></small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-info">No recent activities.</div>
    <?php endif; ?>

    <!-- সাম্প্রতিক ফিডব্যাক -->
    <h4>Recent Feedback</h4>
    <?php if ($recentFeedback && $recentFeedback->num_rows > 0): ?>
        <ul class="list-group mb-4">
            <?php while ($fb = $recentFeedback->fetch_assoc()): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($fb['username']) ?></strong> on
                     <?= htmlspecialchars($fb['target_type']) ?>: 
                    <?= htmlspecialchars($fb['feedback']) ?>
                    <br><small class="text-muted"><?= date("d M Y, h:i A", strtotime($fb['created_at'])) ?></small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-info">No recent feedback.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
