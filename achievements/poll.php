<?php
$page_title = "Poll & Feedback";
include 'includes/header.php';

// যদি ইউজার লগইন না থাকে
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning text-center mt-4'>Please login to participate in polls.</div>";
    include 'includes/footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];

// পোল সাবমিট করা হয়েছে কিনা
if (isset($_POST['poll_id']) && isset($_POST['option'])) {
    $poll_id = intval($_POST['poll_id']);
    $selected_option = intval($_POST['option']);

    // আগেই ভোট দিয়েছে কিনা
    $check = $conn->query("SELECT id FROM poll_votes WHERE user_id = $user_id AND poll_id = $poll_id");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO poll_votes (poll_id, user_id, selected_option, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iii", $poll_id, $user_id, $selected_option);
        $stmt->execute();

        // ভোটে অংশগ্রহণের জন্য পয়েন্ট যোগ
        $conn->query("INSERT INTO user_points (user_id, total_points, last_action) VALUES ($user_id, 2, 'Poll Vote')
                      ON DUPLICATE KEY UPDATE total_points = total_points + 2, last_action='Poll Vote', updated_at=NOW();");

        echo "<div class='alert alert-success text-center'>✅ Thanks for your vote! You've earned +2 points.</div>";
    } else {
        echo "<div class='alert alert-info text-center'>ℹ️ You’ve already voted on this poll.</div>";
    }
}

// সব সক্রিয় পোল লোড করো
$sql = "SELECT id, question, options_json, created_by, created_at FROM polls ORDER BY created_at DESC";
$res = $conn->query($sql);
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">🗳️ Active Polls</h2>

    <?php if ($res && $res->num_rows > 0): ?>
        <?php while ($poll = $res->fetch_assoc()): 
            $poll_id = $poll['id'];
            $options = json_decode($poll['options_json'], true);
            $totalVotes = $conn->query("SELECT COUNT(*) as total FROM poll_votes WHERE poll_id = $poll_id")->fetch_assoc()['total'];

            // ইউজার ভোট দিয়েছে কিনা
            $userVote = $conn->query("SELECT selected_option FROM poll_votes WHERE poll_id = $poll_id AND user_id = $user_id")->fetch_assoc();
        ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <?= htmlspecialchars($poll['question']) ?>
                </div>
                <div class="card-body">
                    <?php if (!$userVote): ?>
                        <form method="POST">
                            <input type="hidden" name="poll_id" value="<?= $poll_id ?>">
                            <?php foreach ($options as $index => $option): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="option" value="<?= $index ?>" required>
                                    <label class="form-check-label"><?= htmlspecialchars($option) ?></label>
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary mt-2">Submit Vote</button>
                        </form>
                    <?php else: ?>
                        <p class="fw-semibold text-success">✅ You voted for: <?= htmlspecialchars($options[$userVote['selected_option']]) ?></p>
                        <div class="progress mt-3" style="height: 25px;">
                            <?php
                            foreach ($options as $i => $opt) {
                                $count = $conn->query("SELECT COUNT(*) as c FROM poll_votes WHERE poll_id = $poll_id AND selected_option = $i")->fetch_assoc()['c'];
                                $percent = $totalVotes ? round(($count / $totalVotes) * 100, 1) : 0;
                                $color = ['bg-primary', 'bg-success', 'bg-warning', 'bg-danger'][$i % 4];
                                echo "<div class='progress-bar $color' style='width:$percent%'>$opt ($percent%)</div>";
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-muted small">
                    Created <?= date("d M Y", strtotime($poll['created_at'])) ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">No polls available right now.</div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
