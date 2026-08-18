<?php
/**
 * API Endpoint: get_at_risk_students_report.php
 *
 * Description:
 * Fetches all data from the `analytics_at_risk_students` table for a given dataset_id.
 * This report lists students who are identified as being at risk of failing or dropping out.
 *
 * Request Method: GET
 *
 * Parameters:
 * - dataset_id (int, required): The ID of the analysis dataset to retrieve data for.
 *
 * Response:
 * - On Success: A JSON object with `status: 'success'` and a `data` array containing all rows
 *   from the `analytics_at_risk_students` table, joined with student names.
 * - On Error: A JSON object with `status: 'error'` and an error `message`.
 */

header('Content-Type: application/json');

require_once '../core/config.php';
require_once '../core/db.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);

if (!$dataset_id) {
    $response['message'] = 'Invalid or missing dataset_id.';
    echo json_encode($response);
    exit;
}

try {
    // Join with students table to get student name
    $stmt = $conn->prepare("
        SELECT ar.*, s.stnameeng FROM analytics_at_risk_students ar
        JOIN students s ON ar.stid = s.stid AND ar.sccode = s.sccode
        WHERE ar.dataset_id = ? 
        ORDER BY ar.risk_score DESC, ar.failed_subject_count DESC
    ");
    if (!$stmt) throw new Exception("Database query preparation failed: " . $conn->error);

    $stmt->bind_param("i", $dataset_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $response = ['status' => 'success', 'data' => $data];
} catch (Exception $e) {
    $response['message'] = "An error occurred while fetching the report: " . $e->getMessage();
}

echo json_encode($response);
?>