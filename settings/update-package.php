<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

$pkg = intval($_POST['package']);
$tier = intval($_POST['tier']);

$valid_module = $conn->real_escape_string($_POST['valid_module']);
$valid_panel = $conn->real_escape_string($_POST['valid_panel']);

$pq = $conn->query("SELECT package_name FROM packages WHERE id='$pkg'");
$pkgRow = $pq->fetch_assoc();
$package_name = $pkgRow['package_name'];

$q = $conn->query("SELECT * FROM package_settings WHERE id='$tier'");
if (!$q || $q->num_rows == 0) {
    echo json_encode(['status' => 'err', 'msg' => 'Invalid tier']);
    exit;
}

$t = $q->fetch_assoc();

$billing_data = $t['billing_cycle'] . " | " . $t['payment_model'] . " | " . $t['price'] . " | " . $t['price'];

$conn->query("
    UPDATE scinfo SET
        package_id    = '$pkg',
        tier  = '" . $t['ins_tier'] . "',
        package_name  = '" . $package_name . "',
        valid_module  = '$valid_module',
        valid_panel   = '$valid_panel',
        billing_data  = '$billing_data'
    WHERE sccode='$sccode'
");




$ttt = $t['ins_tier'];
$QL = "
    INSERT INTO subscription_history (id, sccode, package_id, package_name, tier, billing_data, subscribe_by, subscribe_time)
    VALUES (NULL, '$sccode', '$pkg', '$package_name', '$ttt', '$billing_data', '$usr', '$cur')";

$conn->query($QL);




echo json_encode(['status' => 'ok']);