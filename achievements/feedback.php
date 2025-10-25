<?php
$page_title = "Feedback & Suggestions";
include 'includes/header.php';

// লগইন চেক
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-warning text-center mt-4'>Please login to give feedback.</div>";
    include 'includes/footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// ফিডব্যাক সাবমিট
if (isset($_POST['target_type'], $_POST['target_id'], $_POST['rating'], $_POST['feedback_text'])) {
    $target_type = $_POST['target_type']; // page / feature / module
    $target_id = intval($_POST['target_id']);
    $rating = intval($_POST['rating']); // 1-5
    $feedback_text = trim($_POST['feedback_text']);

    if ($target_id > 0 && $rating >=1 && $rating <=5 && !empty($feedback_text)) {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, target_type, target_id, rating, feedback_text, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isiss", $user_id, $target_type, $target_id, $rating, $feedback_text);
        if ($stmt->execute()) {
            // পয়েন্ট যোগ (উদাহরণ: 5 রেটিং = 5 পয়েন্ট)
            require_once 'functions.php';
            addPoints($user_id, "feedback", $rating, ['target_type'=>$target_type, 'target_id'=>$target_id]);

            $message = "<div class='alert alert-success'>✅ Your feedback has been submitted. You earned +$rating points.</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Failed to submit feedback.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please fill all fields correctly.</div>";
    }
}

// টার্গেট লিস্ট (যেমন পেজ/ফিচার/মডিউল)
$targets = $conn->query("SELECT id, target_name, target_type FROM feedback_target ORDER BY target_type, target_name ASC");

// পূর্ববর্তী ফিডব্যাক লোড
$userFeedbackRes = $conn->query("SELECT f.*, t.target_name AS target_name FROM feedbacks f LEFT JOIN feedback_target t ON f.target_type=t.id WHERE f.username='$usr' ORDER BY f.created_at DESC");
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">💬 Feedback & Suggestions</h2>

    <?= $message ?>

    <!-- ফিডব্যাক ফর্ম -->
    <div class="card mb-4 mx-auto" style="max-width:600px;">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="target_id" class="form-label">Select Page / Feature / Module</label>
                    <select name="target_id" id="target_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php while ($t = $targets->fetch_assoc()): ?>
                            <option value="<?= $t['id'] ?>" data-type="<?= $t['type'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= $t['type'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                    <input type="hidden" name="target_type" id="target_type">
                </div>

                <div class="mb-3">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-select" required>
                        <option value="">-- Select Rating --</option>
                        <option value="1">1 ⭐</option>
                        <option value="2">2 ⭐⭐</option>
                        <option value="3">3 ⭐⭐⭐</option>
                        <option value="4">4 ⭐⭐⭐⭐</option>
                        <option value="5">5 ⭐⭐⭐⭐⭐</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="feedback_text" class="form-label">Your Feedback</label>
                    <textarea name="feedback_text" id="feedback_text" rows="3" class="form-control" placeholder="Type your suggestions..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">Submit Feedback</button>
            </form>
        </div>
    </div>

    <!-- পূর্ববর্তী ফিডব্যাক -->
    <h4 class="mb-3">Your Previous Feedback</h4>
    <?php if ($userFeedbackRes && $userFeedbackRes->num_rows > 0): ?>
        <ul class="list-group mb-4">
            <?php while ($fb = $userFeedbackRes->fetch_assoc()): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($fb['target_name'] ?? '') ?> (<?= $fb['target_type'] ?? '' ?>)</strong> 
                    - Rating: <?= $fb['rating'] ?> ⭐<br>
                    <?= htmlspecialchars($fb['feedback_text'] ?? '') ?><br>
                    <small class="text-muted">Submitted on <?= date("d M Y, h:i A", strtotime($fb['created_at'])) ?></small>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-info">No feedback submitted yet.</div>
    <?php endif; ?>
</div>

<script>
// নির্বাচন করা টার্গেট অনুযায়ী hidden field আপডেট
document.getElementById('target_id').addEventListener('change', function() {
    var selected = this.options[this.selectedIndex];
    document.getElementById('target_type').value = selected.dataset.type || '';
});
</script>

<?php include 'includes/footer.php'; ?>
