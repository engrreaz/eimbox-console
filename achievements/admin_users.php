<?php
$page_title = "Manage Users";
include 'includes/header.php';

// শুধুমাত্র অ্যাডমিন
if (!isset($_SESSION['user_id']) || $is_admin===0) {
    echo "<div class='alert alert-danger text-center mt-4'>Access denied. Admins only.</div>";
    include 'includes/footer.php';
    exit;
}

$message = "";

// ইউজারের পয়েন্ট / টাইটেল আপডেট ফর্ম সাবমিট
if (isset($_POST['user_id'], $_POST['points'], $_POST['title_id'])) {
    $user_id = intval($_POST['user_id']);
    $points = intval($_POST['points']);
    $title_id = intval($_POST['title_id']);

    require_once 'functions.php';
    addPoints($user_id, "admin_adjust", $points, ['reason'=>'Admin adjustment']);
    $conn->query("UPDATE user_points SET current_title_id = $title_id WHERE user_id = $user_id");
    $message = "<div class='alert alert-success'>✅ User updated successfully.</div>";
}

// সব ইউজার লোড
$usersRes = $conn->query("
    SELECT u.id, u.name, u.role, up.total_points, up.level, t.title_name 
    FROM users u 
    LEFT JOIN user_points up ON u.id = up.user_id
    LEFT JOIN titles_list t ON up.current_title_id = t.id
    ORDER BY u.name ASC
");

// সব টাইটেল লোড
$titlesRes = $conn->query("SELECT id, title_name FROM titles_list ORDER BY min_points ASC");
$titles = [];
while ($t = $titlesRes->fetch_assoc()) {
    $titles[$t['id']] = $t['title_name'];
}
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">👥 User Management</h2>
    <?= $message ?>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Points</th>
                <th>Level</th>
                <th>Title</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($u = $usersRes->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['name']) ?></td>
                    <td><?= $u['role'] ?></td>
                    <td><?= $u['total_points'] ?: 0 ?></td>
                    <td><?= $u['level'] ?: 1 ?></td>
                    <td><?= htmlspecialchars($u['title_name'] ?: '-') ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u['id'] ?>">Edit</button>

                        <!-- Modal -->
                        <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <form method="POST">
                                  <div class="modal-header">
                                    <h5 class="modal-title">Edit User: <?= htmlspecialchars($u['name']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                  </div>
                                  <div class="modal-body">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Points</label>
                                        <input type="number" name="points" class="form-control" value="<?= $u['total_points'] ?: 0 ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <select name="title_id" class="form-select">
                                            <?php foreach ($titles as $id => $title): ?>
                                                <option value="<?= $id ?>" <?= ($id == $u['current_title_id']) ? 'selected' : '' ?>><?= htmlspecialchars($title) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="submit" class="btn btn-success w-100">Save Changes</button>
                                  </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
