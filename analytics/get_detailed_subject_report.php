<?php
/**
 * API Endpoint: get_detailed_subject_report.php
 *
 * Description:
 * Fetches all data from the `analytics_subject_performance` table for a given dataset_id.
 * This report provides detailed subject-wise performance metrics for each class and section.
 *
 * Request Method: GET
 *
 * Parameters:
 * - dataset_id (int, required): The ID of the analysis dataset to retrieve data for.
 *
 * Response:
 * - On Success: A JSON object with `status: 'success'` and a `data` array containing all rows
 *   from the `analytics_subject_performance` table.
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
    $stmt = $conn->prepare("SELECT * FROM analytics_subject_performance WHERE dataset_id = ? ORDER BY classname, sectionname, subject_code");
    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $dataset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $response = ['status' => 'success', 'data' => $data];
} catch (Exception $e) {
    $response['message'] = "An error occurred while fetching the report: " . $e->getMessage();
}

echo json_encode($response);
?>