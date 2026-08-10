<?php
header('Content-Type: application/json');
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$examid_param = $_GET['examid'] ?? null;
$slot = $_GET['slot'] ?? null;
$sessionyear = $_GET['sessionyear'] ?? null;

if (empty($examid_param) || empty($sccode) || empty($slot) || empty($sessionyear)) {
    http_response_code(400);
    echo json_encode(['dataset_id' => null]);
    exit;
}

// Handle both array and string for examid
if (is_array($examid_param)) {
    $examid_list_str = implode(',', array_map('intval', $examid_param));
} else {
    $examid_list_str = $examid_param;
}

$stmt = $conn->prepare("SELECT datasetid FROM analytics_dataset WHERE examid = ? AND sccode = ? AND slot = ? AND sessionyear = ? ORDER BY created_at DESC LIMIT 1"); // Order by created_at for latest
$stmt->bind_param("ssss", $examid_list_str, $sccode, $slot, $sessionyear);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

echo json_encode(['dataset_id' => $row['datasetid'] ?? null]);
?>