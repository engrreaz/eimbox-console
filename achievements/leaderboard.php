<?php
$page_title = "Leaderboard";
include 'includes/header.php';
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">🏆 Leaderboard</h2>

    <?php
    // Leaderboard query
    $sql = "
        SELECT 
            u.id AS user_id,
            u.name AS user_name,
            IFNULL(up.total_points, 0) AS total_points,
            IFNULL(t.title_name, 'New Recruit') AS title_name,
            up.level,
            up.updated_at
        FROM users u
        LEFT JOIN user_points up ON u.id = up.user_id
        LEFT JOIN titles_list t ON t.id = up.current_title_id
        ORDER BY up.total_points DESC
        LIMIT 100
    ";

    $res = $conn->query($sql);

    if ($res && $res->num_rows > 0):
    ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Rank</th>
                        <th>User</th>
                        <th>Title</th>
                        <th>Total Points</th>
                        <th>Level</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rank = 1;
                    while ($row = $res->fetch_assoc()):
                        $rankBadge = "";
                        if ($rank == 1) $rankBadge = "🥇";
                        elseif ($rank == 2) $rankBadge = "🥈";
                        elseif ($rank == 3) $rankBadge = "🥉";
                    ?>
                        <tr>
                            <td><strong><?= $rankBadge ?> <?= $rank ?></strong></td>
                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                            <td><?= htmlspecialchars($row['title_name']) ?></td>
                            <td><strong><?= $row['total_points'] ?></strong></td>
                            <td><?= $row['level'] ?></td>
                            <td><?= date("d M Y, h:i A", strtotime($row['updated_at'] ?? '')) ?></td>
                        </tr>
                    <?php
                        $rank++;
                    endwhile;
                    ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            😕 No users found on leaderboard yet!
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
