<?php
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

$sql = "
    SELECT 
        asp.*,
        COALESCE(s.stnameeng, CONCAT('Student ', asp.stid)) AS stnameeng,
        COALESCE(s.stnameben, '') AS stnameben,
        COALESCE(s.gender, '') AS gender,
        COALESCE(s.guarmobile, '') AS guarmobile
    FROM 
        analytics_student_performance AS asp
    LEFT JOIN 
        students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode
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