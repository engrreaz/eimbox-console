<?php
require_once '../core/config.php';
require_once '../core/db.php';

$sccode = $_POST['sccode'] ?? '';
$gw_name = $_POST['gateway_name'] ?? '';
$json = $_POST['data'] ?? '';

if (!$sccode || !$gw_name || !$json) {
    echo json_encode(["status" => "error", "msg" => "Missing data"]);
    exit;
}

$data = json_decode($json, true);

$str = implode(" | ", [
    $data['gateway'],
    $data['active'],
    $data['type'],
    $data['app_key'],
    $data['app_secret'],
    $data['username'],
    $data['password']
]);

$sql = "UPDATE scinfo SET $gw_name='$str' WHERE sccode='$sccode' LIMIT 1";

if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "msg" => "Updated"]);
} else {
    echo json_encode(["status" => "error", "msg" => $conn->error]);
}
