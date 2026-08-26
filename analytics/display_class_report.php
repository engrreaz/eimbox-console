<?php
/**
 * display_class_report.php
 *
 * Fetches data from `analytics_class_performance` and renders it as a custom HTML block.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/global_values.php';

$dataset_id = (int)($_GET['dataset_id'] ?? 0);

if (!$dataset_id) {
    echo '<div class="alert alert-danger">Invalid Dataset ID.</div>';
    exit;
}

try {
    // Fetch comprehensive class performance data
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
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("ii", $dataset_id, $dataset_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '<div class="alert alert-warning">No class performance data available.</div>';
        exit;
    }

    // --- Custom HTML Rendering ---
    echo "<div class='row g-4'>";

    foreach ($data as $class) {
        $avg_marks = (float)($class['overall_marks_percentage'] ?? 0);
        $sub_avg = (float)($class['avg_of_subject_averages'] ?? 0);
        $pass_rate = (float)($class['pass_rate'] ?? 0);
        $exc_rate = (float)($class['excellent_rate'] ?? 0);
        $cpi = (float)($class['cpi_score'] ?? 0);
        $difficulty = (float)($class['difficulty_factor'] ?? 0);
        $tii = (float)($class['teacher_impact_index'] ?? 1);
        $rank = $class['class_rank'] ?? '-';
        $classname = htmlspecialchars($class['classname']);
        $sectionname = htmlspecialchars($class['sectionname']);

        $enrolled = (int)($class['total_enrolled'] ?? $class['total_students_appeared']);
        $appeared = (int)($class['total_students_appeared'] ?? 0);
        $subjects = (int)($class['total_subjects'] ?? 0);

        $male_count = (int)($class['male_count'] ?? 0);
        $female_count = (int)($class['female_count'] ?? 0);
        $male_passed = (int)($class['male_passed'] ?? 0);
        $female_passed = (int)($class['female_passed'] ?? 0);
        $male_avg = (float)($class['male_avg'] ?? 0);
        $female_avg = (float)($class['female_avg'] ?? 0);

        $std_dev = (float)($class['avg_std_dev'] ?? 0);
        $variance = (float)($class['avg_variance'] ?? 0);
        $above_avg = (int)($class['above_avg_count'] ?? 0);
        $below_avg = (int)($class['below_avg_count'] ?? 0);

        echo "
        <div class='col-md-6 col-lg-6'>
            <div class='card h-100 shadow-sm border'>
                <div class='card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center'>
                    <div>
                        <h6 class='card-title mb-0 fw-bold text-dark'><i class='bi bi-mortarboard-fill text-primary me-2'></i>Class: {$classname} - {$sectionname}</h6>
                        <small class='text-muted'>{$subjects} Subjects | {$appeared}/{$enrolled} Appeared</small>
                    </div>
                    <span class='badge bg-primary fs-6 px-3 py-1'><i class='bi bi-trophy-fill me-1'></i>Rank: #{$rank}</span>
                </div>
                <div class='card-body p-3'>
                    <div class='row text-center g-2 mb-3'>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm border-primary'>
                                <small class='text-primary fw-bold d-block' style='font-size: 10.5px;'>CPI Score</small>
                                <strong class='text-primary fs-5'>" . number_format($cpi, 1) . "</strong>
                            </div>
                        </div>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10.5px;'>Avg Marks</small>
                                <strong class='text-info fs-6'>" . number_format($avg_marks, 1) . "%</strong>
                            </div>
                        </div>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10.5px;'>Pass Rate</small>
                                <strong class='text-success fs-6'>" . number_format($pass_rate, 1) . "%</strong>
                            </div>
                        </div>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10.5px;'>Exc. (70%+)</small>
                                <strong class='text-dark fs-6'>" . number_format($exc_rate, 1) . "%</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bars with Labels -->
                    <div class='mb-2'>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span class='text-muted'>Pass Rate:</span>
                            <span class='fw-bold text-success'>" . number_format($pass_rate, 2) . "%</span>
                        </div>
                        <div class='progress' style='height: 10px; min-height: 10px;'>
                            <div class='progress-bar bg-success' role='progressbar' style='width: {$pass_rate}%;'></div>
                        </div>
                    </div>

                    <div class='mb-3'>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span class='text-muted'>Class Difficulty Factor (CDF):</span>
                            <span class='fw-bold text-danger'>" . number_format($difficulty, 2) . " (TII: " . number_format($tii, 2) . "x)</span>
                        </div>
                        <div class='progress' style='height: 10px; min-height: 10px;'>
                            <div class='progress-bar bg-danger' role='progressbar' style='width: " . min(100, $difficulty) . "%;'></div>
                        </div>
                    </div>

                    <!-- Breakdown Table -->
                    <div class='table-responsive'>
                        <table class='table table-sm table-bordered mb-0' style='font-size: 11px;'>
                            <tbody>
                                <tr>
                                    <td class='bg-light text-muted' style='width: 32%;'>Gender Performance</td>
                                    <td>
                                        <div><span class='text-primary fw-semibold'>👦 Boys:</span> {$male_count} Enrolled • Avg: <strong>" . number_format($male_avg, 1) . "%</strong></div>
                                        <div><span class='text-danger fw-semibold'>👧 Girls:</span> {$female_count} Enrolled • Avg: <strong>" . number_format($female_avg, 1) . "%</strong></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='bg-light text-muted'>Score Distribution</td>
                                    <td>Above Avg: <strong>{$above_avg}</strong> | Below Avg: <strong>{$below_avg}</strong></td>
                                </tr>
                                <tr>
                                    <td class='bg-light text-muted'>Variation Metrics</td>
                                    <td>Avg SD: <strong>" . number_format($std_dev, 2) . "</strong> | Avg Var: <strong>" . number_format($variance, 1) . "</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        ";
    }

    echo "</div>";
} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}