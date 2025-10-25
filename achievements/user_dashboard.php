<?php
$page_title = "User Dashboard";
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center mt-4'>Please login to view your dashboard.</div>";
    include 'includes/footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user points, level & title
$upRes = $conn->query("SELECT u.total_points, u.level, t.title_name AS title_name
                       FROM user_points u
                       LEFT JOIN titles_list t ON u.current_title_id = t.id
                       WHERE u.user_id=$user_id");
$userData = $upRes->fetch_assoc();

// Fetch achievements
$achRes = $conn->query("SELECT a.name, a.description, a.points, a.type, ua.achieved_at
                        FROM user_achievements ua
                        JOIN achievements_list a ON ua.achievement_id = a.id
                        WHERE ua.user_id=$user_id
                        ORDER BY ua.achieved_at DESC");
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">🎖️ <?= htmlspecialchars($_SESSION['username'] ?? '' ) ?>'s Dashboard</h2>

    <!-- User Summary -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <h4>Title: <span class="text-primary"><?= htmlspecialchars($userData['title_name'] ?? 'Novice') ?></span></h4>
            <h5>Level: <span class="text-success"><?= $userData['level'] ?? 'Explorer' ?></span></h5>
            <h5>Total Points: <span class="text-warning"><?= $userData['total_points'] ?? 0 ?></span></h5>
        </div>
    </div>

    <!-- Achievements -->
    <h4>🏆 Achievements</h4>
    <?php if ($achRes->num_rows > 0): ?>
        <div class="row">
            <?php while ($ach = $achRes->fetch_assoc()): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5><?= htmlspecialchars($ach['name']) ?> <span class="text-muted small">(<?= $ach['type'] ?>)</span></h5>
                            <p class="mb-1"><?= htmlspecialchars($ach['description']) ?></p>
                            <p class="mb-0"><strong>Points:</strong> <?= $ach['points'] ?></p>
                            <p class="mb-0"><small>Awarded: <?= date("d M Y", strtotime($ach['awarded_at'])) ?></small></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You have not earned any achievements yet.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
