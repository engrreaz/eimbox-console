<?php
session_start();
header('Content-Type: application/json');

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$raw_ids = $_POST['voucher_ids'] ?? [];
if (is_string($raw_ids)) {
    $raw_ids = explode(',', $raw_ids);
}

$voucher_ids = array_filter(array_map('intval', (array)$raw_ids));
$target_month = intval($_POST['target_month'] ?? 0);
$target_year = intval($_POST['target_year'] ?? 0);
$refno = trim($_POST['refno'] ?? '');

if (empty($voucher_ids)) {
    echo json_encode(['status' => 'error', 'message' => 'No vouchers selected.']);
    exit;
}

if ($target_month < 1 || $target_month > 12) {
    echo json_encode(['status' => 'error', 'message' => 'Please select a valid month (1-12).']);
    exit;
}

if ($target_year < 2000 || $target_year > 2100) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter a valid 4-digit year.']);
    exit;
}

$id_list = implode(',', $voucher_ids);
$refno_esc = mysqli_real_escape_string($conn, $refno);

$sql = "UPDATE cashbook SET 
            month = '$target_month', 
            year = '$target_year', 
            refno = '$refno_esc', 
            is_locked = 1, 
            locked_at = NOW(), 
            modifieddate = NOW() 
        WHERE id IN ($id_list) 
          AND sccode = '$sccode' 
          AND (is_locked = 0 OR is_locked IS NULL)";

if ($conn->query($sql)) {
    $affected = $conn->affected_rows;
    echo json_encode([
        'status' => 'success', 
        'message' => "Successfully bound and finalized $affected voucher(s) for Month $target_month, Year $target_year.",
        'affected' => $affected
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed: ' . $conn->error]);
}
