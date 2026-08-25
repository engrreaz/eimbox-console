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
        classname,
        sectionname,
        total_subjects,
        avg_of_subject_averages,
        total_students_appeared,
        overall_marks_percentage,
        difficulty_factor,
        cpi_score,
        class_rank
    FROM 
        analytics_class_performance
    WHERE 
        dataset_id = ?
    ORDER BY 
        class_rank ASC, classname ASC, sectionname ASC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dataset_id);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>