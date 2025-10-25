<?php
$page_title = "Manage Achievements";
include 'includes/header.php';

// শুধুমাত্র অ্যাডমিন
if (!isset($_SESSION['user_id']) || $is_admin == 0) {
    echo "<div class='alert alert-danger text-center mt-4'>Access denied. Admins only.</div>";
    include 'includes/footer.php';
    exit;
}

$message = "";

// অ্যাচিভমেন্ট ফর্ম সাবমিট
if (isset($_POST['name'], $_POST['points'], $_POST['description'], $_POST['type'])) {
    $name = trim($_POST['name']);
    $points = intval($_POST['points']);
    $description = trim($_POST['description']);
    $type = trim($_POST['type']);
    $level_req = isset($_POST['level_requirement']) ? intval($_POST['level_requirement']) : 0;

    if (!empty($name) && $points > 0) {
        if (isset($_POST['ach_id']) && $_POST['ach_id'] > 0) {
            // Update existing
            $ach_id = intval($_POST['ach_id']);
            $stmt = $conn->prepare("UPDATE achievements_list SET name=?, points=?, description=?, type=?, level_requirement=? WHERE id=?");
            $stmt->bind_param("sissii", $name, $points, $description, $type, $level_req, $ach_id);
            $stmt->execute();
            $message = "<div class='alert alert-success'>✅ Achievement updated successfully.</div>";
        } else {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO achievements_list (name, points, description, type, level_requirement) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sissi", $name, $points, $description, $type, $level_req);
            $stmt->execute();
            $message = "<div class='alert alert-success'>✅ New achievement created.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please fill all required fields.</div>";
    }
}

// ডিলিট
if (isset($_GET['delete']) && $_GET['delete'] > 0) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM achievements_list WHERE id=$delete_id");
    $message = "<div class='alert alert-success'>✅ Achievement deleted.</div>";
}

// সব অ্যাচিভমেন্ট লোড
$achRes = $conn->query("SELECT * FROM achievements_list ORDER BY id DESC");
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">🏆 Achievements Management</h2>
    <?= $message ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="ach_id" id="ach_id" value="">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="text" name="name" id="ach_name" class="form-control" placeholder="Achievement Name" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <input type="number" name="points" id="ach_points" class="form-control" placeholder="Points" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <input type="text" name="type" id="ach_type" class="form-control" placeholder="Type (feedback, bug, etc.)" required>
                    </div>
                    <div class="col-md-2 mb-3">
                        <input type="number" name="level_requirement" id="ach_level" class="form-control" placeholder="Level Req" min="0">
                    </div>
                    <div class="col-md-2 mb-3">
                        <button type="submit" class="btn btn-success w-100">Save</button>
                    </div>
                </div>
                <div class="mb-3">
                    <textarea name="description" id="ach_description" rows="2" class="form-control" placeholder="Description"></textarea>
                </div>
            </form>
        </div>
    </div>

    <h4>Existing Achievements</h4>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Points</th>
                <th>Type</th>
                <th>Level Req</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($ach = $achRes->fetch_assoc()): ?>
                <tr>
                    <td><?= $ach['id'] ?></td>
                    <td><?= htmlspecialchars($ach['name']) ?></td>
                    <td><?= $ach['points'] ?></td>
                    <td><?= htmlspecialchars($ach['type']) ?></td>
                    <td><?= $ach['level_requirement'] ?></td>
                    <td><?= htmlspecialchars($ach['description']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-btn" 
                            data-id="<?= $ach['id'] ?>" 
                            data-name="<?= htmlspecialchars($ach['name'], ENT_QUOTES) ?>" 
                            data-points="<?= $ach['points'] ?>" 
                            data-type="<?= htmlspecialchars($ach['type'], ENT_QUOTES) ?>" 
                            data-level="<?= $ach['level_requirement'] ?>" 
                            data-description="<?= htmlspecialchars($ach['description'], ENT_QUOTES) ?>">Edit</button>
                        <a href="?delete=<?= $ach['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this achievement?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
// Edit button fill form
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('ach_id').value = this.dataset.id;
        document.getElementById('ach_name').value = this.dataset.name;
        document.getElementById('ach_points').value = this.dataset.points;
        document.getElementById('ach_type').value = this.dataset.type;
        document.getElementById('ach_level').value = this.dataset.level;
        document.getElementById('ach_description').value = this.dataset.description;
        window.scrollTo({top:0, behavior:'smooth'});
    });
});
</script>

<?php include 'includes/footer.php'; ?>
