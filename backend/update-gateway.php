<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

if (empty($_POST['gateways']) || empty($_POST['sccode'])) {
    echo json_encode(["status" => "error", "msg" => "Missing parameters"]);
    exit;
}

$sccode = trim($_POST['sccode']);
$input = json_decode($_POST['gateways'], true);

if (!is_array($input) || !isset($input['index'], $input['data'])) {
    echo json_encode(["status" => "error", "msg" => "Invalid gateway data"]);
    exit;
}

$index = intval($input['index']);
$new_gateway = $input['data'];

// Fetch existing admin_data
$stmt = $conn->prepare("SELECT admin_data FROM scinfo WHERE sccode = ? LIMIT 1");
$stmt->bind_param("s", $sccode);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "msg" => "School not found"]);
    exit;
}

$row = $res->fetch_assoc();

// SAFE DECODE
$raw_json = trim($row['admin_data']);
$admin_data = json_decode($raw_json, true);

// JSON decode error check
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["status" => "error", "msg" => "admin_data JSON is invalid"]);
    exit;
}

// Ensure payment gateway array exists
if (
    !isset($admin_data['settings']['payment_gateway']) ||
    !is_array($admin_data['settings']['payment_gateway'])
) {
    echo json_encode(["status" => "error", "msg" => "payment_gateway not found"]);
    exit;
}

// Ensure index exists
if (!isset($admin_data['settings']['payment_gateway'][$index])) {
    echo json_encode(["status" => "error", "msg" => "Gateway index not found"]);
    exit;
}

// Update only selected gateway
$admin_data['settings']['payment_gateway'][$index] = $new_gateway;
$admin_data['settings']['payment_gateway'] = array_values($admin_data['settings']['payment_gateway']);


// Encode WITHOUT PRETTY PRINT (compact JSON)
$new_json = json_encode($admin_data, JSON_UNESCAPED_UNICODE);

// Update DB
$update_stmt = $conn->prepare("UPDATE scinfo SET admin_data = ? WHERE sccode = ?");
$update_stmt->bind_param("ss", $new_json, $sccode);

if ($update_stmt->execute()) {
    $_SESSION['admin_data'] = $new_json;
    echo json_encode(["status" => "success", "msg" => "Gateway updated successfully"]);
} else {
    echo json_encode(["status" => "error", "msg" => $update_stmt->error]);
}

$update_stmt->close();
$stmt->close();
$conn->close();
?>