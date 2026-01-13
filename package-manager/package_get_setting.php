<?php
include('../core/config.php');
include('../core/db.php');

$id = intval($_POST['data_id'] ?? 0);
$package_id = intval($_POST['package_id'] ?? 0);
$ins_tier   = $_POST['ins_tier'] ?? '';
$billing   = $_POST['billing_cycle'] ?? '';

if(!$package_id){
    echo json_encode([]);
    exit;
}

$q = $conn->prepare("SELECT * FROM package_settings 
                     WHERE id=? AND package_id=? AND ins_tier=? AND billing_cycle=?");
$q->bind_param("iiss",$id, $package_id,$ins_tier,$billing);
$q->execute();
$res = $q->get_result();

if($res->num_rows==0){
    echo json_encode(['package_id' => $package_id, 'ins_tier' => $ins_tier, 'billing_cycle' => $billing]);
    exit;
}

echo json_encode($res->fetch_assoc());
