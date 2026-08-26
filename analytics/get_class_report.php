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
        acp.*,
        COALESCE(sub_stats.total_enrolled, acp.total_students_appeared) AS total_enrolled,
        COALESCE(sub_stats.total_passed, 0) AS total_passed,
        COALESCE(sub_stats.total_failed, 0) AS total_failed,
        COALESCE(sub_stats.male_count, 0) AS male_count,
        COALESCE(sub_stats.female_count, 0) AS female_count,
        COALESCE(sub_stats.male_passed, 0) AS male_passed,
        COALESCE(sub_stats.female_passed, 0) AS female_passed,
        COALESCE(sub_stats.male_avg, 0) AS male_avg,
        COALESCE(sub_stats.female_avg, 0) AS female_avg,
        COALESCE(sub_stats.avg_variance, 0) AS avg_variance,
        COALESCE(sub_stats.avg_std_dev, 0) AS avg_std_dev,
        COALESCE(sub_stats.above_avg_count, 0) AS above_avg_count,
        COALESCE(sub_stats.below_avg_count, 0) AS below_avg_count
    FROM 
        analytics_class_performance AS acp
    LEFT JOIN (
        SELECT 
            dataset_id,
            classname,
            sectionname,
            MAX(student_count) AS total_enrolled,
            SUM(pass_count) AS total_passed,
            SUM(fail_count) AS total_failed,
            MAX(male_count) AS male_count,
            MAX(female_count) AS female_count,
            SUM(male_pass_count) AS male_passed,
            SUM(female_pass_count) AS female_passed,
            AVG(male_avg_marks) AS male_avg,
            AVG(female_avg_marks) AS female_avg,
            AVG(variance) AS avg_variance,
            AVG(std_deviation) AS avg_std_dev,
            SUM(count_above_avg) AS above_avg_count,
            SUM(count_below_avg) AS below_avg_count
        FROM analytics_subject_performance
        WHERE dataset_id = ?
        GROUP BY dataset_id, classname, sectionname
    ) AS sub_stats 
        ON acp.dataset_id = sub_stats.dataset_id
        AND acp.classname COLLATE utf8mb4_unicode_ci = sub_stats.classname COLLATE utf8mb4_unicode_ci
        AND acp.sectionname COLLATE utf8mb4_unicode_ci = sub_stats.sectionname COLLATE utf8mb4_unicode_ci
    WHERE 
        acp.dataset_id = ?
    ORDER BY 
        acp.class_rank ASC, acp.cpi_score DESC, acp.classname ASC, acp.sectionname ASC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $dataset_id, $dataset_id);
$stmt->execute();
$result = $stmt->get_result();
$report_data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>