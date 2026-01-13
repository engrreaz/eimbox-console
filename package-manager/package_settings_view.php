<?php
include('../core/config.php');
include('../core/db.php');

$package_id = intval($_POST['package_id'] ?? 0);

if (!$package_id) {
  echo "<div class='alert alert-warning'>Invalid package</div>";
  exit;
}

/* Package name */
$pkgRes = $conn->query("SELECT * FROM packages WHERE id=$package_id");
$pkg = $pkgRes->fetch_assoc();

$statusBadge = $pkg['status'] == 'active' ? 'success' : 'secondary';

echo "
<div class='card mb-3 shadow-sm'>
  <div class='card-body py-2'>
    <div class='d-flex justify-content-between align-items-center'>
      <div>
        <h6 class='mb-1'>
          <i class='bi bi-box'></i> {$pkg['package_name']}
          <span class='badge bg-{$statusBadge} ms-2'>{$pkg['status']}</span>
        </h6>
        <small class='text-muted'>
          Code: <b>{$pkg['package_code']}</b> | 
          Serial: {$pkg['serial']} <br>
          {$pkg['description']}
        </small>
      </div>
      <button class='btn btn-sm btn-outline-primary btn-edit-package'
        data-id='{$pkg['id']}'>
        <i class='bi bi-pencil'></i> Edit
      </button>
    </div>
  </div>
</div>
";



$q = $conn->query("
    SELECT * FROM package_settings 
    WHERE package_id=$package_id 
    ORDER BY ins_tier, billing_cycle
");

if ($q->num_rows == 0) {

  echo "
    <div class='text-center text-muted py-3'>
      No settings found for this package.<br>
      <button class='btn btn-sm btn-outline-primary mt-2 btn-edit-setting' 
        data-data-id='0'
          data-package-id='{$package_id}'
          data-ins-tier=''
          data-billing=''
       
       >
        <i class='bi bi-plus-lg'></i> Add New Setting
      </button>
    </div>";
  exit;
}

echo "
<table class='table table-sm table-bordered align-middle'>
<thead class='table-light'>
<tr>
  <th>Institution Tier</th>
  <th>Billing Cycle</th>
  <th>Payment Model</th>
  <th>Price</th>
  <th>Status</th>
  <th width='80'>Action</th>
</tr>
</thead>
<tbody>
";

while ($r = $q->fetch_assoc()) {

  $badge = $r['status'] == 'active' ? 'success' : 'secondary';

  echo "
    <tr>
      <td><strong>{$r['ins_tier']}</strong></td>
      <td>{$r['billing_cycle']}</td>
      <td>{$r['payment_model']}</td>
      <td>৳ " . number_format($r['price'], 2) . "</td>
      <td><span class='badge bg-{$badge}'>{$r['status']}</span></td>
      <td>
        <button class='btn btn-sm btn-outline-primary btn-edit-setting'
        data-data-id='{$r['id']}'
          data-package-id='{$package_id}'
          data-ins-tier='{$r['ins_tier']}'
          data-billing='{$r['billing_cycle']}'>
          <i class='bi bi-pencil'></i>
        </button>
      </td>
    </tr>";
}

echo "
</tbody>
</table>

<div class='text-end'>
  <button class='btn btn-sm btn-outline-success btn-edit-setting' 
  data-data-id='0'
          data-package-id='{$package_id}'
          data-ins-tier=''
          data-billing=''
  
  >
    <i class='bi bi-plus'></i> Add New Setting
  </button>
</div>
";
?>