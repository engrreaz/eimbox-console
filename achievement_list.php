<?php
$page_title = "Achievement List";
include 'header.php';

// -------------------- ADD / UPDATE --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $level_requirement = trim($_POST['level_requirement'] ?? '');
    $points = intval($_POST['points'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $type = trim($_POST['type'] ?? 'Basic');
    $tier = trim($_POST['tier'] ?? 'Bronze');

    if ($name !== '') {
        if ($id > 0) {
            // Update existing record
            try {
                $stmt = $conn->prepare("UPDATE achievements_list 
                SET code=?, name=?, description=?, level_requirement=?, points=?, category=?, type=?, tier=? 
                WHERE id=?");
                $stmt->bind_param("ssssisssi", $code, $name, $description, $level_requirement, $points, $category, $type, $tier, $id);
                $stmt->execute();
                $message = "Achievement updated successfully!";
                $cat = "success";
            } catch (mysqli_sql_exception $e) {
                error_log("SQL Error: " . $e->getMessage()); // লগে লিখে রাখবে
                $message = htmlspecialchars($e->getMessage());
                $cat = "danger";
            }

        } else {
            // Insert new record
            try {
                $stmt = $conn->prepare("INSERT INTO achievements_list 
                (code, name, description, level_requirement, points, category, type, tier) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssisss", $code, $name, $description, $level_requirement, $points, $category, $type, $tier);
                $stmt->execute();
                $message = "Achievement added successfully!";
                $cat = "success";
            } catch (mysqli_sql_exception $e) {
                error_log("SQL Error: " . $e->getMessage()); // লগে লিখে রাখবে
                $message = htmlspecialchars($e->getMessage());
                $cat = "danger";
            }

        }
    }
}

// -------------------- DELETE --------------------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM achievements_list WHERE id=$id");
    $message = "Achievement deleted successfully!";
}

// -------------------- FETCH ALL --------------------
$result = $conn->query("SELECT * FROM achievements_list ORDER BY id DESC");
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><?= htmlspecialchars($page_title) ?></h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#achievementModal">Add New</button>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $cat; ?>"><?= $message ?></div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle table-sm ">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Points</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Tier</th>
                    <th>Level Requirement</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['code']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['points'] ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($row['tier']) ?></span></td>
                        <td><?= htmlspecialchars($row['level_requirement'] ?? '') ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-btn" data-id="<?= $row['id'] ?>"
                                data-code="<?= htmlspecialchars($row['code']) ?>"
                                data-name="<?= htmlspecialchars($row['name']) ?>"
                                data-description="<?= htmlspecialchars($row['description']) ?>"
                                data-level_requirement="<?= htmlspecialchars($row['level_requirement'] ?? 0) ?>"
                                data-points="<?= $row['points'] ?>"
                                data-category="<?= htmlspecialchars($row['category']) ?>"
                                data-type="<?= htmlspecialchars($row['type']) ?>"
                                data-tier="<?= htmlspecialchars($row['tier']) ?>" data-bs-toggle="modal"
                                data-bs-target="#achievementModal">
                                Edit
                            </button>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure to delete this achievement?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>


<!-- Modal -->
<div class="modal fade" id="achievementModal" tabindex="-1" aria-labelledby="achievementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="achievementModalLabel">Add Achievement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="id" id="achv_id">
                    <div class="col-md-6">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" id="achv_code" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="achv_name" class="form-control" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="achv_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Level Requirement</label>
                        <input type="text" name="level_requirement" id="achv_level" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Points</label>
                        <input type="number" name="points" id="achv_points" class="form-control" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" id="achv_category" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="type" id="achv_type" class="form-select">
                            <option value="Basic">Basic</option>
                            <option value="Special">Special</option>
                            <option value="Hidden">Hidden</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tier</label>
                        <select name="tier" id="achv_tier" class="form-select">
                            <option>Bronze</option>
                            <option>Silver</option>
                            <option>Gold</option>
                            <option>Platinum</option>
                            <option>Diamond</option>
                        </select>
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
        btn.addEventListener('click', e => {
            document.getElementById('achv_id').value = btn.dataset.id;
            document.getElementById('achv_code').value = btn.dataset.code;
            document.getElementById('achv_name').value = btn.dataset.name;
            document.getElementById('achv_description').value = btn.dataset.description;
            document.getElementById('achv_level').value = btn.dataset.level_requirement;
            document.getElementById('achv_points').value = btn.dataset.points;
            document.getElementById('achv_category').value = btn.dataset.category;
            document.getElementById('achv_type').value = btn.dataset.type;
            document.getElementById('achv_tier').value = btn.dataset.tier;
            document.getElementById('achievementModalLabel').innerText = 'Edit Achievement';
        });
    });

    document.getElementById('achievementModal').addEventListener('hidden.bs.modal', () => {
        document.getElementById('achievementModalLabel').innerText = 'Add Achievement';
        document.querySelector('form').reset();
    });
</script>

<?php include 'footer.php'; ?>