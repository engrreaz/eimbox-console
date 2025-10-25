<?php
$page_title = "Give Points";
include 'includes/header.php';

// শুধু অ্যাডমিন ইউজারদের অনুমতি
if (!isset($_SESSION['user_id']) || $is_admin===0) {
    echo "<div class='alert alert-danger text-center mt-4'>Access denied. Admins only.</div>";
    include 'includes/footer.php';
    exit;
}

$message = "";
if (isset($_POST['user_id'], $_POST['points'], $_POST['reason'])) {
    $user_id = intval($_POST['user_id']);
    $points = intval($_POST['points']);
    $reason = trim($_POST['reason']);

    if ($points > 0 && $user_id > 0 && !empty($reason)) {
        require_once 'functions.php';
        $ok = addPoints($user_id, "admin_award", $points, ['reason' => $reason, 'awarded_by' => $_SESSION['user_id']]);
        $message = $ok ? "<div class='alert alert-success'>✅ $points points added to user ID $user_id</div>"
                       : "<div class='alert alert-danger'>❌ Failed to add points.</div>";
    } else {
        $message = "<div class='alert alert-warning'>Please fill all fields correctly.</div>";
    }
}

// সব ইউজার লিস্ট
$usersRes = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">💰 Give Points</h2>

    <?= $message ?>

    <div class="card mx-auto" style="max-width:500px;">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="user_id" class="form-label">Select User</label>
                    <select name="user_id" id="user_id" class="form-select" required>
                        <option value="">-- Select User --</option>
                        <?php while ($u = $usersRes->fetch_assoc()): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (ID: <?= $u['id'] ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="points" class="form-label">Points to Add</label>
                    <input type="number" name="points" id="points" class="form-control" min="1" required>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason</label>
                    <input type="text" name="reason" id="reason" class="form-control" placeholder="Bug found, feedback, etc." required>
                </div>

                <button type="submit" class="btn btn-success w-100">Add Points</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
