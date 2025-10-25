<?php
$page_title = "Leaderboard";
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center mt-4'>Please login to view the leaderboard.</div>";
    include 'includes/footer.php';
    exit;
}

// Fetch top users ordered by points DESC
$usersRes = $conn->query("
    SELECT u.user_id, u.total_points, u.level, t.title_name AS title_name, users.email
    FROM user_points u
    LEFT JOIN titles_list t ON u.current_title_id = t.id
    JOIN users ON users.id = u.user_id
    ORDER BY u.total_points DESC, u.level DESC
    LIMIT 50
");
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">🏆 Leaderboard - Top 50 Users</h2>

    <table class="table table-striped table-bordered table-hover text-center">
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
            while ($user = $usersRes->fetch_assoc()):
            ?>
            <tr <?= $user['user_id'] == $_SESSION['user_id'] ? 'class="table-success"' : '' ?>>
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
</div>

<?php include 'includes/footer.php'; ?>
