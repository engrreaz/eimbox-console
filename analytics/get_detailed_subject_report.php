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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);
$sctype = $_SESSION['sccategory'] ?? ($sctype ?? '');

if (!$dataset_id) {
    $response['message'] = 'Invalid or missing dataset_id.';
    echo json_encode($response);
    exit;
}

try {
    $query = "
        SELECT 
            asp.*,
            COALESCE(s.subject, CONCAT('Subject ', asp.subject_code)) AS subject_name,
            COALESCE(t.tname, 'Unassigned') AS teacher_name,
            t.position AS teacher_position
        FROM analytics_subject_performance AS asp
        LEFT JOIN subjects AS s 
            ON asp.subject_code = s.subcode 
            AND (s.sccode = asp.sccode OR s.sccode = '0')
            AND (s.sccategory = ? OR ? = '')
            AND s.id = (
                SELECT s2.id FROM subjects s2 
                WHERE s2.subcode = asp.subject_code 
                  AND (s2.sccode = asp.sccode OR s2.sccode = '0')
                  AND (s2.sccategory = ? OR ? = '')
                ORDER BY (s2.sccode = asp.sccode) DESC, s2.sccode DESC, s2.id DESC 
                LIMIT 1
            )
        LEFT JOIN teacher AS t 
            ON asp.tid = t.tid 
            AND (t.sccode = asp.sccode OR t.sccode = '0')
        WHERE asp.dataset_id = ? 
        GROUP BY asp.id
        ORDER BY asp.classname, asp.sectionname, asp.subject_code
    ";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("ssssi", $sctype, $sctype, $sctype, $sctype, $dataset_id);
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