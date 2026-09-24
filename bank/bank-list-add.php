<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$bname = trim($_POST['bname'] ?? '');

if (empty($bname)) {
    echo json_encode(['status' => 'error', 'message' => 'Bank name is required']);
    exit;
}

// Check if already exists for this institution or globally
$chk = $conn->prepare("SELECT id, bname FROM banklist WHERE LOWER(bname) = LOWER(?) AND (sccode = ? OR sccode = 0)");
$chk->bind_param("si", $bname, $sccode);
$chk->execute();
$chk_res = $chk->get_result();
if ($chk_res->num_rows > 0) {
    $existing = $chk_res->fetch_assoc();
    $chk->close();
    echo json_encode(['status' => 'success', 'id' => $existing['id'], 'bname' => $existing['bname'], 'message' => 'Bank already exists in directory']);
    exit;
}
$chk->close();

// Insert new bank
$ins = $conn->prepare("INSERT INTO banklist (sccode, bname, modifieddate) VALUES (?, ?, NOW())");
$ins->bind_param("is", $sccode, $bname);
if ($ins->execute()) {
    $new_id = $conn->insert_id;
    $ins->close();
    echo json_encode(['status' => 'success', 'id' => $new_id, 'bname' => $bname, 'message' => 'New bank added successfully']);
} else {
    $err = $ins->error;
    $ins->close();
    echo json_encode(['status' => 'error', 'message' => 'Failed to save bank to database: ' . $err]);
}
