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
error_log($sctype . '/' . $sccategory);
// Debugging: Log received parameters

$sql = "
    SELECT 
        asp.stid,
        s.stnameeng,
        asp.classname,
        asp.sectionname,
        asp.rollno,
        asp.total_marks_obtained,
        asp.percentage,
        asp.gpa,
        asp.grade,
        asp.class_rank, 
        asp.section_rank,
        asp.failed_subjects
    FROM 
        analytics_student_performance AS asp
    JOIN 
        students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode
    WHERE 
        asp.dataset_id = ? AND asp.sccode = ?
    ORDER BY 
        asp.class_rank ASC, asp.total_marks_obtained DESC;
";

// Debugging: Log the SQL query

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $dataset_id, $sccode);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>