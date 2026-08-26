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
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/global_values.php';

$dataset_id = (int)($_GET['dataset_id'] ?? 0);

if (!$dataset_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid dataset_id parameter.']);
    exit;
}

try {
    $sql = "
        SELECT 
            ar.*,
            COALESCE(s.stnameeng, CONCAT('Student ', ar.stid)) AS stnameeng,
            COALESCE(s.stnameben, '') AS stnameben,
            COALESCE(s.gender, '') AS gender,
            COALESCE(s.guarmobile, '') AS guarmobile,
            COALESCE(asp.rollno, '-') AS rollno,
            COALESCE(asp.total_marks_obtained, 0) AS total_marks_obtained,
            COALESCE(asp.total_full_marks, 0) AS total_full_marks,
            COALESCE(asp.percentage, 0) AS percentage,
            COALESCE(asp.class_rank, 0) AS class_rank,
            COALESCE(asp.section_rank, 0) AS section_rank
        FROM 
            analytics_at_risk_students AS ar
        LEFT JOIN 
            students AS s ON ar.stid = s.stid AND ar.sccode = s.sccode
        LEFT JOIN 
            analytics_student_performance AS asp ON ar.dataset_id = asp.dataset_id AND ar.stid = asp.stid
        WHERE 
            ar.dataset_id = ?
        ORDER BY 
            ar.risk_score DESC, ar.failed_subject_count DESC, ar.classname ASC, ar.sectionname ASC;
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Database query preparation failed: " . $conn->error);

    $stmt->bind_param("i", $dataset_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>