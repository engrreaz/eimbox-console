<?php
$page_title = "Achievements";
include '../header.php';

// যদি ইউজার লগইন করা না থাকে
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning text-center mt-4'>Please login to view your achievements.</div>";
    include '../footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];

// ইউজারের অর্জিত অ্যাচিভমেন্টস
$earned = [];
$earnedRes = $conn->query("SELECT achievement_id FROM user_achievements WHERE user_id = $user_id");
if ($earnedRes) {
    while ($row = $earnedRes->fetch_assoc()) {
        $earned[] = $row['achievement_id'];
    }
}

// সব অ্যাচিভমেন্ট লোড করো
$sql = "SELECT id, name, description, points, type, level_requirement FROM achievements_list ORDER BY id ASC";
$res = $conn->query($sql);
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">🎯 My Achievements</h2>

    <?php if ($res && $res->num_rows > 0): ?>
        <div class="row">
            <?php while ($row = $res->fetch_assoc()): 
                $unlocked = in_array($row['id'], $earned);
                $cardClass = $unlocked ? 'border-success bg-light' : 'border-secondary';
                $icon = $unlocked ? 'fa-solid fa-trophy text-success' : 'fa-regular fa-lock text-muted';
            ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card <?= $cardClass ?> shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="<?= $icon ?> fa-2x mb-2"></i>
                            <h6 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h6>
                            <p class="small text-muted"><?= htmlspecialchars($row['description']) ?></p>
                            <span class="badge bg-primary">+<?= $row['points'] ?> pts</span>
                            <?php if ($row['level_requirement'] > 0): ?>
                                <div class="mt-1"><small>Required Level: <?= $row['level_requirement'] ?></small></div>
                            <?php endif; ?>
                            <?php if ($unlocked): ?>
                                <div class="mt-2 text-success fw-semibold"><i class="fa-solid fa-check"></i> Unlocked</div>
                            <?php else: ?>
                                <div class="mt-2 text-muted"><i class="fa-solid fa-lock"></i> Locked</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">No achievements found yet.</div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>
