<?php
header('Content-Type: application/json');
session_start();
require_once '../core/config.php';
require_once '../core/db.php';

$dataset_id = (int)($_GET['dataset_id'] ?? 0);
$sccode = $_SESSION['sccode'] ?? null;

if (empty($dataset_id) || empty($sccode)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
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
        dataset_id = ? AND sccode = ?
    ORDER BY 
        class_rank ASC, classname ASC, sectionname ASC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $dataset_id, $sccode);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>