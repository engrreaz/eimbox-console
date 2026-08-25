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
        COALESCE(s.subject, CONCAT('Subject ', aosp.subject_code)) AS subject_name,
        aosp.subject_code,
        aosp.total_students_appeared,
        aosp.overall_avg_marks,
        aosp.overall_marks_percentage,
        aosp.failure_rate,
        aosp.subject_difficulty_factor AS sdf
    FROM 
        analytics_overall_subject_performance AS aosp
    LEFT JOIN 
        subjects AS s ON aosp.subject_code = s.subcode AND (s.sccode = aosp.sccode OR s.sccode = '0')
    WHERE 
        aosp.dataset_id = ?
    GROUP BY aosp.id
    ORDER BY 
        aosp.subject_difficulty_factor DESC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dataset_id);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>