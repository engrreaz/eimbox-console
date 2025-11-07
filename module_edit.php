<?php require_once 'header.php'; ?>

<?php
$id = intval($_GET['id'] ?? 0);
$mod = $conn->query("SELECT * FROM modulelist WHERE id=$id")->fetch_assoc();
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold mb-3">✏️ Edit Module</h4>
  <form action="ajax/save_module_edit.php" method="POST">
    <input type="hidden" name="id" value="<?= $mod['id'] ?>">
    <div class="mb-3">
      <label class="form-label">Module Name</label>
      <input type="text" name="module_name" class="form-control" value="<?= htmlspecialchars($mod['module_name']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Icon</label>
      <input type="text" name="module_icon" class="form-control" value="<?= htmlspecialchars($mod['module_icon']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="descrip" class="form-control"><?= htmlspecialchars($mod['descrip']) ?></textarea>
    </div>
    <button type="submit" class="btn btn-success">Save Changes</button>
  </form>
</div>

<?php require_once 'footer.php'; ?>
