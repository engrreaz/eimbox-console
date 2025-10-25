<?php

//page function not fully completed.....
// construct again.................

$billing_check = true;
$_SESSION['billing_check'] = 1;

$sql = "SELECT *
        FROM settings_ins
        WHERE sccode = ? AND settings_key = 'billing' 
        ORDER BY id DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $sccode);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $billing_json = $row['settings_value'];
    $billing_info = json_decode($billing_json, true);
}

// GET DATA -------------- package_id, package_name, billing_cycle, payment_mode, unit_price, actual_bill
$sccode_current_package = $billing_info['package_id'];
$sccode_current_package_name = $billing_info['package_name'] ?? '-';



$billing_info = [];
$sql = "SELECT *
        FROM invoices
        WHERE sccode = ? AND payment_date < '$cur' 
        ORDER BY payment_date ASC LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sccode);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $billing_info = $row;
}

// echo "<pre>";
// print_r($billing_info);
// echo "</pre>";
if (count($billing_info) > 0) {
    $billing_check = false;
    $_SESSION['billing_check'] = 0;
    // echo 'UNPAID';
    // exit;
}


if (!$billing_check) {
    // echo '/' . $_SESSION['billing_check']  . '/';
    // echo 'Billing Dues or something page open';
    // exit;
    // sleep(5);
}