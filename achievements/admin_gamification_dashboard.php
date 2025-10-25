<?php
$page_title = "Gamification Dashboard";
include 'includes/header.php';

// শুধুমাত্র অ্যাডমিন
if (!isset($_SESSION['user_id']) || $is_admin == 0) {
    echo "<div class='alert alert-danger text-center mt-4'>Access denied. Admins only.</div>";
    include 'includes/footer.php';
    exit;
}

// Total users
$totalUsers = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];

// Total polls
$totalPolls = $conn->query("SELECT COUNT(*) AS cnt FROM polls")->fetch_assoc()['cnt'];

// Total votes
$totalVotes = $conn->query("SELECT COUNT(*) AS cnt FROM poll_votes")->fetch_assoc()['cnt'];

// Total feedbacks
$totalFeedbacks = $conn->query("SELECT COUNT(*) AS cnt FROM feedbacks")->fetch_assoc()['cnt'];

// Top users by points
$topUsersRes = $conn->query("
    SELECT u.user_id, u.total_points, u.level, t.title_name AS title_name, users.email
    FROM user_points u
    LEFT JOIN titles_list t ON u.current_title_id = t.id
    JOIN users ON users.id = u.user_id
    ORDER BY u.total_points DESC, u.level DESC
    LIMIT 10
");

// Top achievements
$topAchRes = $conn->query("
    SELECT a.name, COUNT(ua.user_id) AS awarded_count
    FROM user_achievements ua
    JOIN achievements_list a ON ua.achievement_id = a.id
    GROUP BY ua.achievement_id
    ORDER BY awarded_count DESC
    LIMIT 10
");

?>

<div class="container my-4">
    <h2 class="mb-4 text-center">🎮 Admin Gamification Dashboard</h2>

    <div class="row text-center mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <h5>Total Users</h5>
                    <h3><?= $totalUsers ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <h5>Total Polls</h5>
                    <h3><?= $totalPolls ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark shadow-sm">
                <div class="card-body">
                    <h5>Total Votes</h5>
                    <h3><?= $totalVotes ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <h5>Total Feedbacks</h5>
                    <h3><?= $totalFeedbacks ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Users -->
    <h4>🏅 Top Users by Points</h4>
    <table class="table table-striped table-bordered text-center mb-4">
        <thead class="table-dark">
            <tr>
                <th>Rank</th>
                <th>Username</th>
                <th>Title</th>
                <th>Level</th>
                <th>Total Points</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rank = 1;
            while ($user = $topUsersRes->fetch_assoc()):
            ?>
            <tr>
                <td><?= $rank ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['title_name'] ?? 'Novice') ?></td>
                <td><?= $user['level'] ?></td>
                <td><?= $user['total_points'] ?></td>
            </tr>
            <?php
            $rank++;
            endwhile;
            ?>
        </tbody>
    </table>

    <!-- Top Achievements -->
    <h4>🏆 Most Awarded Achievements</h4>
    <table class="table table-striped table-bordered text-center">
        <thead class="table-dark">
            <tr>
                <th>Achievement</th>
                <th>Times Awarded</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($ach = $topAchRes->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($ach['name']) ?></td>
                <td><?= $ach['awarded_count'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php include 'includes/footer.php'; ?>
