<?php
$page_title = "Polls & Feedback";
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center mt-4'>Please login to participate.</div>";
    include 'includes/footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Poll Vote Submit
if (isset($_POST['poll_id'], $_POST['option_id'])) {
    $poll_id = intval($_POST['poll_id']);
    $option_id = intval($_POST['option_id']);

    // Check if user already voted
    $check = $conn->query("SELECT * FROM poll_votes WHERE poll_id=$poll_id AND user_id=$user_id");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO poll_votes (poll_id, option_id, user_id, voted_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iii", $poll_id, $option_id, $user_id);
        $stmt->execute();
        $message = "<div class='alert alert-success'>✅ Your vote has been recorded.</div>";
    } else {
        $message = "<div class='alert alert-warning'>You have already voted on this poll.</div>";
    }
}

// Feedback Submit
if (isset($_POST['target_type'], $_POST['target_id'], $_POST['feedback_text'], $_POST['rating'])) {
    $target_type = $conn->real_escape_string($_POST['target_type']);
    $target_id = intval($_POST['target_id']);
    $feedback_text = $conn->real_escape_string($_POST['feedback_text']);
    $rating = intval($_POST['rating']);

    $stmt = $conn->prepare("INSERT INTO feedbacks (email, target_type, target_id, feedback, rating, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isisi", $user_id, $target_type, $target_id, $feedback_text, $rating);
    $stmt->execute();
    $message = "<div class='alert alert-success'>✅ Feedback submitted successfully.</div>";
}

// Load polls
$pollsRes = $conn->query("SELECT * FROM polls ORDER BY created_at DESC");

// Load pages/features for feedback
$pagesRes = $conn->query("SELECT id, page_name FROM pages ORDER BY page_name ASC");
$featuresRes = $conn->query("SELECT id, feature_name FROM features ORDER BY feature_name ASC");
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">📊 Polls & Feedback</h2>
    <?= $message ?>

    <!-- Polls -->
    <h4>Active Polls</h4>
    <?php while ($poll = $pollsRes->fetch_assoc()): 
        $optionsRes = $conn->query("SELECT * FROM poll_options WHERE poll_id={$poll['id']}");
        $voted = $conn->query("SELECT * FROM poll_votes WHERE poll_id={$poll['id']} AND user_id=$user_id")->num_rows > 0;
    ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5><?= htmlspecialchars($poll['question']) ?></h5>
                <?php if (!$voted): ?>
                    <form method="POST">
                        <input type="hidden" name="poll_id" value="<?= $poll['id'] ?>">
                        <?php while ($opt = $optionsRes->fetch_assoc()): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="option_id" id="opt<?= $opt['id'] ?>" value="<?= $opt['id'] ?>" required>
                                <label class="form-check-label" for="opt<?= $opt['id'] ?>"><?= htmlspecialchars($opt['option_text']) ?></label>
                            </div>
                        <?php endwhile; ?>
                        <button type="submit" class="btn btn-success btn-sm mt-2">Vote</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info mt-2">You already voted on this poll.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>

    <!-- Feedback Form -->
    <h4 class="mt-5">Submit Feedback / Rating</h4>
    <form method="POST" class="mb-5">
        <div class="row mb-3">
            <div class="col-md-4">
                <select name="target_type" class="form-select" required>
                    <option value="">Select Target Type</option>
                    <option value="page">Page</option>
                    <option value="feature">Feature</option>
                    <option value="module">Module</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="target_id" class="form-select" required>
                    <option value="">Select Page / Feature / Module</option>
                    <?php while ($p = $pagesRes->fetch_assoc()): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['page_name']) ?> (Page)</option>
                    <?php endwhile; ?>
                    <?php while ($f = $featuresRes->fetch_assoc()): ?>
                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['feature_name']) ?> (Feature)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select name="rating" class="form-select" required>
                    <option value="">Rating</option>
                    <?php for($i=1;$i<=5;$i++): ?>
                        <option value="<?= $i ?>"><?= $i ?> Star<?= $i>1?'s':'' ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <textarea name="feedback_text" rows="3" class="form-control" placeholder="Write your feedback here..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Feedback</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
