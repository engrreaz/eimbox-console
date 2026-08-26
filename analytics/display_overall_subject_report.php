<?php
/**
 * display_overall_subject_report.php
 *
 * Fetches data from `analytics_overall_subject_performance` and renders it as a custom HTML block.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/global_values.php';

$dataset_id = (int)($_GET['dataset_id'] ?? 0);
$sctype = $_SESSION['sccategory'] ?? ($sctype ?? '');

if (!$dataset_id) {
    echo '<div class="alert alert-danger">Invalid Dataset ID.</div>';
    exit;
}

try {
    $query = "
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
        FROM analytics_overall_subject_performance AS aosp
        LEFT JOIN subjects AS s 
            ON aosp.subject_code = s.subcode 
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
        WHERE aosp.dataset_id = ?
        ORDER BY aosp.subject_difficulty_factor DESC, aosp.failure_rate DESC
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("ssssii", $sctype, $sctype, $sctype, $sctype, $dataset_id, $dataset_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '<div class="alert alert-warning">No overall subject performance data available.</div>';
        exit;
    }

    // --- Custom HTML Rendering ---
    echo "<div class='row g-4'>";

    foreach ($data as $subject) {
        $sub_name = htmlspecialchars($subject['subject_name']);
        $sub_code = htmlspecialchars($subject['subject_code']);
        $failure_rate = (float)($subject['failure_rate'] ?? 0);
        $pass_rate = (float)($subject['pass_rate'] ?? (100 - $failure_rate));
        $exc_rate = (float)($subject['excellent_rate'] ?? 0);
        $sdf = (float)($subject['subject_difficulty_factor'] ?? 0);
        $avg_marks = (float)($subject['overall_marks_percentage'] ?? 0);
        $median = (float)($subject['median'] ?? 0);
        $low_gpa = (float)($subject['low_gpa_ratio'] ?? 0);
        
        $enrolled = (int)($subject['total_enrolled'] ?? $subject['total_students_appeared']);
        $appeared = (int)($subject['total_students_appeared'] ?? 0);
        $fail_count = (int)($subject['fail_count'] ?? 0);
        $pass_count = (int)($subject['total_passed'] ?? max(0, $appeared - $fail_count));

        $classes_count = (int)($subject['classes_count'] ?? 0);
        $teachers_count = (int)($subject['teachers_count'] ?? 0);

        $male_count = (int)($subject['male_count'] ?? 0);
        $female_count = (int)($subject['female_count'] ?? 0);
        $male_avg = (float)($subject['male_avg'] ?? 0);
        $female_avg = (float)($subject['female_avg'] ?? 0);

        $std_dev = (float)($subject['std_deviation'] ?? 0);
        $variance = (float)($subject['variance'] ?? 0);
        $above_avg = (int)($subject['above_avg_count'] ?? 0);
        $below_avg = (int)($subject['below_avg_count'] ?? 0);

        // Badge color based on SDF
        $sdf_badge_class = $sdf >= 40 ? 'bg-danger' : ($sdf >= 25 ? 'bg-warning text-dark' : 'bg-success');

        echo "
        <div class='col-md-6 col-lg-6'>
            <div class='card h-100 shadow-sm border'>
                <div class='card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center'>
                    <div>
                        <h6 class='card-title mb-0 fw-bold text-dark'><i class='bi bi-book-half text-primary me-2'></i>{$sub_name}</h6>
                        <small class='text-muted'>{$classes_count} Classes/Secs • {$teachers_count} Teachers • {$appeared}/{$enrolled} Appeared</small>
                    </div>
                    <div>
                        <span class='badge bg-secondary me-1'>Code: {$sub_code}</span>
                        <span class='badge {$sdf_badge_class}'>SDF: " . number_format($sdf, 1) . "</span>
                    </div>
                </div>
                <div class='card-body p-3'>
                    <div class='row text-center g-2 mb-3'>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10.5px;'>Avg Marks</small>
                                <strong class='text-primary fs-6'>" . number_format($avg_marks, 1) . "%</strong>
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
                                <strong class='text-info fs-6'>" . number_format($exc_rate, 1) . "%</strong>
                            </div>
                        </div>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10.5px;'>Failure Rate</small>
                                <strong class='text-danger fs-6'>" . number_format($failure_rate, 1) . "%</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bars with Labels -->
                    <div class='mb-2'>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span class='text-muted'>Pass Rate ({$pass_count} passed):</span>
                            <span class='fw-bold text-success'>" . number_format($pass_rate, 2) . "%</span>
                        </div>
                        <div class='progress' style='height: 10px; min-height: 10px;'>
                            <div class='progress-bar bg-success' role='progressbar' style='width: {$pass_rate}%;'></div>
                        </div>
                    </div>

                    <div class='mb-3'>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span class='text-muted'>Subject Difficulty Factor (SDF):</span>
                            <span class='fw-bold text-danger'>" . number_format($sdf, 2) . " (Low Score &lt;50%: " . number_format($low_gpa, 1) . "%)</span>
                        </div>
                        <div class='progress' style='height: 10px; min-height: 10px;'>
                            <div class='progress-bar bg-danger' role='progressbar' style='width: " . min(100, $sdf) . "%;'></div>
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
                                    <td>Above Avg: <strong>{$above_avg}</strong> | Below Avg: <strong>{$below_avg}</strong> | Median: <strong>" . number_format($median, 1) . "%</strong></td>
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