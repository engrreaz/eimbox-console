<?php
/**
 * Fetches the final teacher performance report for a given dataset.
 */

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/global_values.php';

$dataset_id = (int)($_GET['dataset_id'] ?? 0);
$sctype = $_SESSION['sccategory'] ?? ($sctype ?? '');

if (!$dataset_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid dataset_id parameter.']);
    exit;
}

$query = "
    SELECT
        COALESCE(t.tname, 'Unassigned Teacher') AS tname,
        COALESCE(t.position, 'Teacher') AS position,
        atp.*,
        COALESCE(sub_info.subjects_list, '') AS subjects_list,
        COALESCE(sub_info.classes_list, '') AS classes_list
    FROM
        analytics_teacher_performance AS atp
    LEFT JOIN
        teacher AS t ON atp.tid = t.tid AND (t.sccode = atp.sccode OR t.sccode = '0')
    LEFT JOIN (
        SELECT 
            asp.dataset_id,
            asp.tid,
            GROUP_CONCAT(DISTINCT COALESCE(s.subject, CONCAT('Sub ', asp.subject_code)) ORDER BY asp.subject_code SEPARATOR ', ') AS subjects_list,
            GROUP_CONCAT(DISTINCT CONCAT(asp.classname, ' (', asp.sectionname, ')') ORDER BY asp.classname, asp.sectionname SEPARATOR ', ') AS classes_list
        FROM analytics_subject_performance asp
        LEFT JOIN subjects s 
            ON asp.subject_code = s.subcode 
            AND (s.sccode = asp.sccode OR s.sccode = '0')
            AND (s.sccategory = ? OR ? = '')
        WHERE asp.dataset_id = ?
          AND asp.tid IS NOT NULL AND asp.tid != ''
        GROUP BY asp.dataset_id, asp.tid
    ) AS sub_info 
        ON atp.dataset_id = sub_info.dataset_id 
        AND atp.tid = sub_info.tid
    WHERE
        atp.dataset_id = ?
    ORDER BY
        atp.teacher_rank ASC, atp.teacher_impact_adjustment DESC;
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssii", $sctype, $sctype, $dataset_id, $dataset_id);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>