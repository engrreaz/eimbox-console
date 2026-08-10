<?php
/**
 * Fetches the final teacher performance report for a given dataset.
 *
 * This script queries the database for teacher performance data,
 * ranks them based on the Teacher Impact Adjustment (TIA) score,
 * and returns the results as a JSON object.
 */

header('Content-Type: application/json');
session_start();
require_once '../core/config.php';
require_once '../core/db.php';

$dataset_id = $_GET['dataset_id'] ?? 0;
$sccode = $_SESSION['sccode'] ?? null;

if (empty($dataset_id) || empty($sccode)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

$query = "
    SELECT
        t.tname,
        t.position,
        atp.total_students_taught,
        atp.total_subjects_taught,
        atp.overall_avg_marks,
        atp.overall_pass_rate,
        atp.teacher_performance_index AS tpi,
        atp.teacher_impact_adjustment AS tia,
        atp.tci_score,
        atp.tsi_score
    FROM
        analytics_teacher_performance AS atp
    JOIN
        teacher AS t ON atp.tid = t.tid AND atp.sccode = t.sccode
    WHERE
        atp.dataset_id = ? AND atp.sccode = ?
    ORDER BY
        tia DESC;
";


$stmt = $conn->prepare($query);
$stmt->bind_param("is", $dataset_id, $sccode);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>