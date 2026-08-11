<?php
/**
 * get_generated_reports.php
 *
 * Fetches a list of previously generated analysis reports based on slot and session year.
 * This allows users to select a specific report instance to view, rather than
 * trying to match the exact exam combination.
 */

header('Content-Type: application/json');

require_once '../core/config.php';
require_once '../core/db.php';

$sccode = $_SESSION['sccode'] ?? '';
$slot = $_GET['slot'] ?? '';
$sessionyear = $_GET['sessionyear'] ?? '';

if (empty($sccode) || empty($slot) || empty($sessionyear)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

$response = ['status' => 'success', 'reports' => []];

try {
    $stmt = $conn->prepare(
        "SELECT id, dataset_name, created_at 
         FROM analytics_datasets 
         WHERE sccode = ? AND slot = ? AND sessionyear = ? 
         ORDER BY created_at DESC"
    );
    $stmt->bind_param("sss", $sccode, $slot, $sessionyear);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $created_date = date("d M, Y h:i A", strtotime($row['created_at']));
        $response['reports'][] = [
            'dataset_id' => $row['id'],
            'report_name' => "{$row['dataset_name']} (on {$created_date})"
        ];
    }
    $stmt->close();
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>