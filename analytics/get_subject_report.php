<?php
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

$sql = "
    SELECT 
        COALESCE(s.subject, CONCAT('Subject ', aosp.subject_code)) AS subject_name,
        aosp.*,
        COALESCE(sub_stats.total_enrolled, aosp.total_students_appeared) AS total_enrolled,
        COALESCE(sub_stats.total_passed, aosp.total_students_appeared - aosp.fail_count) AS total_passed,
        COALESCE(sub_stats.pass_rate, 100 - aosp.failure_rate) AS pass_rate,
        COALESCE(sub_stats.excellent_count, 0) AS excellent_count,
        COALESCE(sub_stats.excellent_rate, 0) AS excellent_rate,
        COALESCE(sub_stats.classes_count, 0) AS classes_count,
        COALESCE(sub_stats.teachers_count, 0) AS teachers_count,
        COALESCE(sub_stats.male_count, 0) AS male_count,
        COALESCE(sub_stats.female_count, 0) AS female_count,
        COALESCE(sub_stats.male_passed, 0) AS male_passed,
        COALESCE(sub_stats.female_passed, 0) AS female_passed,
        COALESCE(sub_stats.male_avg, 0) AS male_avg,
        COALESCE(sub_stats.female_avg, 0) AS female_avg,
        COALESCE(sub_stats.above_avg_count, 0) AS above_avg_count,
        COALESCE(sub_stats.below_avg_count, 0) AS below_avg_count
    FROM 
        analytics_overall_subject_performance AS aosp
    LEFT JOIN 
        subjects AS s ON aosp.subject_code = s.subcode 
        AND (s.sccode = aosp.sccode OR s.sccode = '0')
        AND (s.sccategory = ? OR ? = '')
        AND s.id = (
            SELECT s2.id FROM subjects s2 
            WHERE s2.subcode = aosp.subject_code 
              AND (s2.sccode = aosp.sccode OR s2.sccode = '0')
              AND (s2.sccategory = ? OR ? = '')
            ORDER BY (s2.sccode = aosp.sccode) DESC, s2.sccode DESC, s2.id DESC 
            LIMIT 1
        )
    LEFT JOIN (
        SELECT 
            dataset_id,
            subject_code,
            SUM(student_count) AS total_enrolled,
            SUM(pass_count) AS total_passed,
            COALESCE(SUM(pass_count) * 100 / NULLIF(SUM(appeared_student_count), 0), 0) AS pass_rate,
            SUM(excellent_count) AS excellent_count,
            COALESCE(SUM(excellent_count) * 100 / NULLIF(SUM(appeared_student_count), 0), 0) AS excellent_rate,
            COUNT(DISTINCT CONCAT(classname, '|', sectionname)) AS classes_count,
            COUNT(DISTINCT tid) AS teachers_count,
            SUM(male_count) AS male_count,
            SUM(female_count) AS female_count,
            SUM(male_pass_count) AS male_passed,
            SUM(female_pass_count) AS female_passed,
            AVG(male_avg_marks) AS male_avg,
            AVG(female_avg_marks) AS female_avg,
            SUM(count_above_avg) AS above_avg_count,
            SUM(count_below_avg) AS below_avg_count
        FROM analytics_subject_performance
        WHERE dataset_id = ?
        GROUP BY dataset_id, subject_code
    ) AS sub_stats 
        ON aosp.dataset_id = sub_stats.dataset_id 
        AND aosp.subject_code COLLATE utf8mb4_unicode_ci = sub_stats.subject_code COLLATE utf8mb4_unicode_ci
    WHERE 
        aosp.dataset_id = ?
    ORDER BY 
        aosp.subject_difficulty_factor DESC, aosp.failure_rate DESC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssii", $sctype, $sctype, $sctype, $sctype, $dataset_id, $dataset_id);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>