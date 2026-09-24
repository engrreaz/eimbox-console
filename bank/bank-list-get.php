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
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$banks = [];
$stmt = $conn->prepare("SELECT id, bname, sccode FROM banklist WHERE sccode = ? OR sccode = 0 OR sccode IS NULL ORDER BY bname ASC");
if ($stmt) {
    $stmt->bind_param("i", $sccode);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $banks[] = $row;
    }
    $stmt->close();
}

echo json_encode($banks);
