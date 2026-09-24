<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

if (!isset($_SESSION['user_id']) || empty($sccode)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access or session expired']);
    exit;
}

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

// 1. Get accno
$stmt = $conn->prepare("SELECT accno FROM bankinfo WHERE id = ? AND sccode = ? LIMIT 1");
$stmt->bind_param("ii", $id, $sccode);
$stmt->execute();
$res = $stmt->get_result();
$acc = $res->fetch_assoc();
$stmt->close();

if (!$acc) {
    echo json_encode(['status' => 'error', 'message' => 'Account not found']);
    exit;
}

$accno = $acc['accno'];

// 2. Check if transactions exist in banktrans
$t_chk = $conn->prepare("SELECT COUNT(*) AS total_trx FROM banktrans WHERE sccode = ? AND accno = ?");
$t_chk->bind_param("is", $sccode, $accno);
$t_chk->execute();
$trx_count = $t_chk->get_result()->fetch_assoc()['total_trx'] ?? 0;
$t_chk->close();

if ($trx_count > 0) {
    echo json_encode([
        'status' => 'has_transactions',
        'trx_count' => $trx_count,
        'message' => "এই অ্যাকাউন্টে ইতোমধ্যে {$trx_count} টি লেনদেন রেকর্ড রয়েছে। অডিট ও হিসাবের নিরাপত্তার স্বার্থে এটি ডিলিট করা যাবে না। আপনি চাইলে অ্যাকাউন্টটি ক্লোজ (Close / Deactivate) করে রাখতে পারেন।"
    ]);
    exit;
}

// 3. Delete account
$del = $conn->prepare("DELETE FROM bankinfo WHERE id = ? AND sccode = ?");
$del->bind_param("ii", $id, $sccode);
if ($del->execute()) {
    $del->close();
    echo json_encode(['status' => 'success', 'message' => 'Bank account deleted successfully']);
} else {
    $err = $del->error;
    $del->close();
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete account: ' . $err]);
}
