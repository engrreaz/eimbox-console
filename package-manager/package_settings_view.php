<?php
include('../core/config.php');
include('../core/db.php');

$package_id = $_POST['package_id'] ?? 0;

if (!$package_id) {
    echo "<div class='alert alert-warning'>Invalid package ID</div>";
    exit;
}

// package name
$pkgRes = mysqli_query($conn, "SELECT package_name FROM packages WHERE id='$package_id'");
$pkg = mysqli_fetch_assoc($pkgRes);

echo "<h6 class='mb-3'><i class='bi bi-box'></i> Package: <strong>{$pkg['package_name']}</strong></h6>";

$q = mysqli_query($conn, "SELECT * FROM package_settings WHERE package_id='$package_id' ORDER BY ins_cat ASC");

if (!mysqli_num_rows($q)) {
    echo "
    <div class='text-center text-muted py-3'>
      No settings found for this package.<br>
      <button class='btn btn-sm btn-outline-primary mt-2 btn-edit-setting' data-package-id='{$package_id}'>
        <i class='bi bi-plus-lg'></i> Add New Setting
      </button>
    </div>";
    exit;
}

echo "<table class='table table-sm table-bordered align-middle'>
<thead class='table-light'>
<tr>
  <th>Institution Category</th>
  <th>Billing Cycle</th>
  <th>Payment Model</th>
  <th>Status</th>
  <th>Cat A</th>
  <th>Cat B</th>
  <th>Cat C</th>
  <th>Cat D</th>
  <th>Cat E</th>
  <th>Action</th>
</tr>
</thead>
<tbody>";

while ($r = mysqli_fetch_assoc($q)) {
    echo "
    <tr>
      <td>{$r['ins_cat']}</td>
      <td>{$r['billing_cycle']}</td>
      <td>{$r['payment_model']}</td>
      <td><span class='badge bg-" . ($r['status']=='active'?'success':'secondary') . "'>{$r['status']}</span></td>
      <td>{$r['price_cat_a']}</td>
      <td>{$r['price_cat_b']}</td>
      <td>{$r['price_cat_c']}</td>
      <td>{$r['price_cat_d']}</td>
      <td>{$r['price_cat_e']}</td>
      <td>
        <button class='btn btn-sm btn-outline-primary btn-edit-setting' 
          data-package-id='{$r['id']}' 
          data-ins-cat='{$r['ins_cat']}'>
          <i class='bi bi-pencil-square'></i> Edit
        </button>
      </td>
    </tr>";
}

echo "</tbody></table>";

echo "
<div class='text-end'>
  <button class='btn btn-sm btn-outline-success btn-edit-setting' data-package-id='{$package_id}'>
    <i class='bi bi-plus'></i> Add New Setting
  </button>
</div>";
?>
