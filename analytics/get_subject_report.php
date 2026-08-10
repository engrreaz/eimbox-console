<?php
header('Content-Type: application/json');
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sctype = $_GET['sctype'] ?? null;

$dataset_id = (int)($_GET['dataset_id'] ?? 0);
$sccode = $_SESSION['sccode'] ?? null;

if (empty($dataset_id) || empty($sccode)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

$sql = "
    SELECT 
        s.subject as subject_name,
        MAX(aosp.subject_code) as subject_code,
        MAX(aosp.total_students_appeared) as total_students_appeared,
        MAX(aosp.overall_avg_marks) as overall_avg_marks,
        MAX(aosp.overall_marks_percentage) as overall_marks_percentage,
        MAX(aosp.failure_rate) as failure_rate,
        MAX(aosp.subject_difficulty_factor) AS sdf
    FROM 
        analytics_overall_subject_performance AS aosp
    JOIN 
        subjects AS s ON aosp.subject_code = s.subcode AND (s.sccode = ? OR s.sccode = '0')
    WHERE 
        aosp.dataset_id = ? AND s.sccategory = ?
    GROUP BY s.subject
    ORDER BY 
        sdf DESC;
";
error_log($sql);
$stmt = $conn->prepare($sql);
$stmt->bind_param("sis", $sccode, $dataset_id, $sctype);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>