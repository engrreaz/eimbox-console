<?php
include('../core/config.php');
include('../core/db.php');

$package_id = $_POST['package_id'] ?? 0;
$ins_cat = $_POST['ins_cat'] ?? '';



if (!$package_id || !$ins_cat) {
    echo json_encode([]);
    exit;
}

// Fetch setting for the given package & category
$q = mysqli_query($conn, "SELECT * FROM package_settings WHERE id='$package_id'  LIMIT 1");

if (mysqli_num_rows($q) == 0) {
    echo json_encode([]);
    exit;
}

$setting = mysqli_fetch_assoc($q);

// Prepare JSON data for form
$data = [
    'data_id' => $setting['id'],
    'package_id' => $setting['package_id'],
    'ins_cat' => $setting['ins_cat'],
    'billing_cycle' => $setting['billing_cycle'],
    'payment_model' => $setting['payment_model'],
    'status' => $setting['status'],
    'price_cat_a' => $setting['price_cat_a'],
    'price_cat_b' => $setting['price_cat_b'],
    'price_cat_c' => $setting['price_cat_c'],
    'price_cat_d' => $setting['price_cat_d'],
    'price_cat_e' => $setting['price_cat_e']
];

echo json_encode($data);
