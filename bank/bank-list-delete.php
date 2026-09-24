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

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Bank ID']);
    exit;
}

// 1. Fetch bank name
$stmt = $conn->prepare("SELECT id, bname, sccode FROM banklist WHERE id = ? AND (sccode = ? OR sccode = 0 OR sccode IS NULL) LIMIT 1");
$stmt->bind_param("ii", $id, $sccode);
$stmt->execute();
$res = $stmt->get_result();
$bank = $res->fetch_assoc();
$stmt->close();

if (!$bank) {
    echo json_encode(['status' => 'error', 'message' => 'Bank not found in directory']);
    exit;
}

$bname = $bank['bname'];

// 2. Check if bank is currently linked to any bank account in bankinfo
$chk = $conn->prepare("SELECT COUNT(*) AS in_use FROM bankinfo WHERE sccode = ? AND LOWER(bankname) = LOWER(?)");
$chk->bind_param("is", $sccode, $bname);
$chk->execute();
$in_use = $chk->get_result()->fetch_assoc()['in_use'] ?? 0;
$chk->close();

if ($in_use > 0) {
    echo json_encode([
        'status' => 'in_use',
        'message' => "এই ব্যাংকটির নামে ইতোমধ্যে {$in_use} টি ব্যাংক অ্যাকাউন্ট তৈরি করা রয়েছে। তাই এটি ডিরেক্টরি থেকে মুছে ফেলা যাবে না।"
    ]);
    exit;
}

// 3. Delete from banklist
$del = $conn->prepare("DELETE FROM banklist WHERE id = ? AND (sccode = ? OR sccode = 0 OR sccode IS NULL)");
$del->bind_param("ii", $id, $sccode);
if ($del->execute()) {
    $del->close();
    echo json_encode(['status' => 'success', 'message' => "'{$bname}' ডিরেক্টরি থেকে মুছে ফেলা হয়েছে।", 'bname' => $bname, 'id' => $id]);
} else {
    $err = $del->error;
    $del->close();
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete bank: ' . $err]);
}
