<?php
/**
 * Fetches all analysis dataset versions for a given examination.
 *
 * This script queries the `analytics_dataset` table to find all records
 * matching the provided examid, sccode, slot, and sessionyear from the session/cookies.
 * It returns a JSON array of these datasets, ordered by the most recent first.
 */

header('Content-Type: application/json');
session_start();
require_once '../core/config.php';
require_once '../core/db.php';

// Get parameters from the session and cookies, similar to analytics-exam.php
$examid = $_GET['examid'] ?? 0;
$sccode = $_SESSION['sccode'] ?? null;
$slot = $_GET['slot'] ?? null;
$sessionyear = $_GET['sessionyear'] ?? null;

if (empty($examid) || empty($sccode) || empty($slot) || empty($sessionyear)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

$query = "
    SELECT datasetid, dataset_name, created_at
    FROM analytics_dataset
    WHERE examid = ? AND sccode = ? AND slot = ? AND sessionyear = ?
    ORDER BY created_at DESC;
";

$stmt = $conn->prepare($query);
$stmt->bind_param("isss", $examid, $sccode, $slot, $sessionyear);
$stmt->execute();
$result = $stmt->get_result();
$history_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $history_data]);
?>