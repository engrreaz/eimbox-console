<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../core/config.php';
require_once '../core/db.php';

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);

if (!$dataset_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid dataset_id parameter.']);
    exit;
}

$sql = "
    SELECT 
        asp.*,
        COALESCE(s.stnameeng, CONCAT('Student ', asp.stid)) AS stnameeng,
        COALESCE(s.stnameben, '') AS stnameben,
        COALESCE(s.gender, si.gender, '') AS gender,
        COALESCE(s.guarmobile, si.guarmobile, '') AS guarmobile
    FROM 
        analytics_student_performance AS asp
    LEFT JOIN 
        students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode
    LEFT JOIN 
        sessioninfo AS si ON asp.stid = si.stid AND asp.sccode = si.sccode AND asp.sessionyear = si.sessionyear
    WHERE 
        asp.dataset_id = ?
    ORDER BY 
        asp.classname ASC, asp.sectionname ASC, 
        CASE WHEN asp.failed_subjects > 0 THEN 1 ELSE 0 END ASC,
        asp.class_rank ASC, asp.section_rank ASC, 
        asp.total_marks_obtained DESC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dataset_id);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>