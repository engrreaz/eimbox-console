<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold mb-4">🧩 Module Management</h4>

  <div id="module-list" class="row g-3">
    <?php
    $modules = $conn->query("SELECT * FROM modulelist ORDER BY slno ASC")->fetch_all(MYSQLI_ASSOC);
    foreach ($modules as $m):
    ?>
      <div class="col-md-3">
        <div class="card module-card h-100 text-center p-3" data-id="<?= $m['id'] ?>">
          <div class="card-body">
            <i class="bx bx-<?= htmlspecialchars($m['module_icon']) ?> fs-1 mb-2"></i>
            <h5 class="card-title"><?= htmlspecialchars($m['module_name']) ?></h5>
            <p class="card-text text-muted"><?= htmlspecialchars($m['descrip']) ?></p>
            <button class="btn btn-sm btn-primary edit-btn" data-id="<?= $m['id'] ?>">Edit</button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function() {

  // 🟦 Drag & Drop sort
  $("#module-list").sortable({
    items: '.col-md-3',
    update: function() {
      let order = [];
      $(".module-card").each(function(index) {
        order.push({
          id: $(this).data("id"),
          slno: index + 1
        });
      });

      $.ajax({
        url: "ajax/update_module_order.php",
        method: "POST",
        data: { order: JSON.stringify(order) },
        success: function(res) {
          console.log(res);
          toastr.success("Module order updated successfully!");
        },
        error: function() {
          toastr.error("Failed to update order!");
        }
      });
    }
  });

  // 🟦 Edit button click
  $(document).on("click", ".edit-btn", function() {
    const id = $(this).data("id");
    window.location.href = "module_edit.php?id=" + id;
  });

});
</script>
<!-- ----------------------------------- -->
</body>
</html>
