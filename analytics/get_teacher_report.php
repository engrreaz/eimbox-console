<?php
/**
 * Fetches the final teacher performance report for a given dataset.
 */

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

$query = "
    SELECT
        COALESCE(t.tname, 'Unassigned') AS tname,
        COALESCE(t.position, '') AS position,
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
    LEFT JOIN
        teacher AS t ON atp.tid = t.tid AND (t.sccode = atp.sccode OR t.sccode = '0')
    WHERE
        atp.dataset_id = ?
    ORDER BY
        atp.teacher_rank ASC, atp.teacher_impact_adjustment DESC;
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $dataset_id);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>