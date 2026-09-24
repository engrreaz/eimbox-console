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
$action = trim($_POST['action'] ?? ''); // 'close' or 'reopen'
$closingdate = trim($_POST['closingdate'] ?? date('Y-m-d'));

if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Account ID']);
    exit;
}

if ($action === 'close') {
    $closingdate_val = !empty($closingdate) ? $closingdate : date('Y-m-d');
    $stmt = $conn->prepare("UPDATE bankinfo SET status = 0, closingdate = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
    $stmt->bind_param("sii", $closingdate_val, $id, $sccode);
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Account closed successfully.']);
    } else {
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to close account: ' . $conn->error]);
    }
} elseif ($action === 'reopen') {
    $stmt = $conn->prepare("UPDATE bankinfo SET status = 1, closingdate = NULL, modifieddate = NOW() WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $id, $sccode);
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Account reopened successfully.']);
    } else {
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to reopen account: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
