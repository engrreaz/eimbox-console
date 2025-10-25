<?php
$page_title = "Titles List";
include 'header.php';

// -------------------- Add / Update Logic --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $title_name = trim($_POST['title_name'] ?? '');
    $min_points = intval($_POST['min_points'] ?? 0);
    $badge_color = trim($_POST['badge_color'] ?? 'secondary');
    $description = trim($_POST['description'] ?? '');
    $required_achievements = $_POST['required_achievements'] ?? '[]';

    if ($title_name !== '') {
        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE titles_list 
                SET title_name=?, min_points=?, required_achievements=?, badge_color=?, description=? 
                WHERE id=?");
            $stmt->bind_param("sisssi", $title_name, $min_points, $required_achievements, $badge_color, $description, $id);
            $stmt->execute();
            $message = "Title updated successfully!";
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO titles_list (title_name, min_points, required_achievements, badge_color, description) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sisss", $title_name, $min_points, $required_achievements, $badge_color, $description);
            $stmt->execute();
            $message = "New title added successfully!";
        }
    }
}

// -------------------- Delete Logic --------------------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM titles_list WHERE id=$id");
    $message = "Title deleted successfully!";
}

// -------------------- Fetch All --------------------
$result = $conn->query("SELECT * FROM titles_list ORDER BY min_points ASC");
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= htmlspecialchars($page_title) ?></h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#titleModal">Add New</button>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Title Name</th>
                <th>Min Points</th>
                <th>Badge</th>
                <th>Required Achievements</th>
                <th>Description</th>
                <th width="120">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): 
                $req_ach = json_decode($row['required_achievements'], true);
                $ach_list = !empty($req_ach) ? implode(', ', $req_ach) : '<span class="text-muted">None</span>';
            ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><strong><?= htmlspecialchars($row['title_name']) ?></strong></td>
                    <td><?= $row['min_points'] ?></td>
                    <td><span class="badge bg-<?= htmlspecialchars($row['badge_color']) ?>"><?= ucfirst($row['badge_color']) ?></span></td>
                    <td><?= $ach_list ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn"
                            data-id="<?= $row['id'] ?>"
                            data-title_name="<?= htmlspecialchars($row['title_name']) ?>"
                            data-min_points="<?= $row['min_points'] ?>"
                            data-badge_color="<?= htmlspecialchars($row['badge_color']) ?>"
                            data-required_achievements='<?= htmlspecialchars($row['required_achievements']) ?>'
                            data-description="<?= htmlspecialchars($row['description']) ?>"
                            data-bs-toggle="modal" data-bs-target="#titleModal">
                            Edit
                        </button>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                            onclick="return confirm('Are you sure to delete this title?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="titleModal" tabindex="-1" aria-labelledby="titleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="titleModalLabel">Add Title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="id" id="title_id">

                    <div class="col-md-6">
                        <label class="form-label">Title Name</label>
                        <input type="text" name="title_name" id="title_name" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Min Points</label>
                        <input type="number" name="min_points" id="min_points" class="form-control" value="0" min="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Badge Color</label>
                        <select name="badge_color" id="badge_color" class="form-select">
                            <option value="primary">Primary</option>
                            <option value="secondary">Secondary</option>
                            <option value="success">Success</option>
                            <option value="danger">Danger</option>
                            <option value="warning">Warning</option>
                            <option value="info">Info</option>
                            <option value="dark">Dark</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Required Achievements (JSON format)</label>
                        <textarea name="required_achievements" id="required_achievements" class="form-control" rows="2" placeholder='["achv_code_1","achv_code_2"]'></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('title_id').value = btn.dataset.id;
        document.getElementById('title_name').value = btn.dataset.title_name;
        document.getElementById('min_points').value = btn.dataset.min_points;
        document.getElementById('badge_color').value = btn.dataset.badge_color;
        document.getElementById('required_achievements').value = btn.dataset.required_achievements;
        document.getElementById('description').value = btn.dataset.description;
        document.getElementById('titleModalLabel').innerText = 'Edit Title';
    });
});

document.getElementById('titleModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('titleModalLabel').innerText = 'Add Title';
    document.querySelector('#titleModal form').reset();
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
