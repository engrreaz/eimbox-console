<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

if (empty($_SESSION['user_id']) || empty($sccode)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access or session expired']);
    exit;
}

$stid = trim($_POST['stid'] ?? '');
$rate = intval($_POST['rate'] ?? 100);
$sy = trim($_POST['session'] ?? $_POST['sessionyear'] ?? date('Y'));

if (empty($stid)) {
    echo json_encode(['status' => 'error', 'message' => 'Student ID is required']);
    exit;
}

if ($rate < 0 || $rate > 100) {
    $rate = max(0, min(100, $rate));
}

$syParam = "%$sy%";
$stmt = $conn->prepare("UPDATE sessioninfo SET rate = ?, modifieddate = ? WHERE sccode = ? AND sessionyear LIKE ? AND stid = ?");
if ($stmt) {
    $stmt->bind_param("isiss", $rate, $cur, $sccode, $syParam, $stid);
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode([
            'status' => 'success',
            'rate' => $rate,
            'message' => "Student concession rate updated to {$rate}% successfully"
        ]);
    } else {
        $err = $stmt->error;
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update concession rate: ' . $err]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
}
