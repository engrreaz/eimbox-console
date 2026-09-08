<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Item ID is missing']);
    exit;
}

$id = intval($_POST['id']);

// Ensure 'active' column exists in financesetup
$conn->query("ALTER TABLE financesetup ADD COLUMN IF NOT EXISTS active TINYINT(1) DEFAULT 1");

$q = $conn->query("SELECT id, particulareng, COALESCE(active, 1) AS active FROM financesetup WHERE id='$id' AND sccode='$sccode'");
if (!$q || $q->num_rows == 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Payment item not found.']);
    exit;
}

$row = $q->fetch_assoc();
$currentStatus = intval($row['active']);
$newStatus = ($currentStatus == 1) ? 0 : 1;
$itemName = $row['particulareng'];

$upd = $conn->query("UPDATE financesetup SET active='$newStatus' WHERE id='$id' AND sccode='$sccode'");

if ($upd) {
    $statusText = ($newStatus == 1) ? 'Activated' : 'Deactivated / Archived';
    echo json_encode([
        'status' => 'success',
        'active' => $newStatus,
        'msg' => "Item '{$itemName}' is now {$statusText}."
    ]);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Failed to update status: ' . $conn->error]);
}
