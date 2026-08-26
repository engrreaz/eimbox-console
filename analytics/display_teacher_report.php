<?php
/**
 * display_teacher_report.php
 *
 * Fetches data from `analytics_teacher_performance` and renders it as a custom HTML block.
 * This is called via AJAX from the main report page.
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
    // Fetch the comprehensive teacher performance data
    $query = "
        SELECT
            COALESCE(t.tname, 'Unassigned Teacher') AS tname,
            COALESCE(t.position, 'Teacher') AS position,
            atp.*,
            COALESCE(sub_info.subjects_list, '') AS subjects_list,
            COALESCE(sub_info.classes_list, '') AS classes_list
        FROM analytics_teacher_performance AS atp
        LEFT JOIN teacher AS t ON atp.tid = t.tid AND (t.sccode = atp.sccode OR t.sccode = '0')
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
        WHERE atp.dataset_id = ?
        ORDER BY atp.teacher_rank ASC, atp.teacher_impact_adjustment DESC;
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("ssii", $sctype, $sctype, $dataset_id, $dataset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '<div class="alert alert-warning">No teacher performance data available for this report.</div>';
        exit;
    }

    // --- Custom HTML Rendering Starts Here ---
    echo "<div class='row g-4'>";

    foreach ($data as $teacher) {
        $rank = $teacher['teacher_rank'] ?? '-';
        $avg_marks = (float)($teacher['overall_avg_marks'] ?? 0);
        $pass_rate = (float)($teacher['overall_pass_rate'] ?? 0);
        $excellent_rate = (float)($teacher['overall_excellent_rate'] ?? 0);
        $tpi = (float)($teacher['teacher_performance_index'] ?? $teacher['tpi'] ?? 0);
        $tii = (float)($teacher['teacher_impact_index'] ?? 1);
        $tia = (float)($teacher['teacher_impact_adjustment'] ?? $teacher['tia'] ?? 0);
        $tci = (float)($teacher['tci_score'] ?? 0);
        $tsi = (float)($teacher['tsi_score'] ?? 0);

        $students = (int)($teacher['total_students_taught'] ?? 0);
        $subjects_count = (int)($teacher['total_subjects_taught'] ?? 0);
        $classes_count = (int)($teacher['total_classes_taught'] ?? 0);

        $std_dev = (float)($teacher['avg_std_deviation'] ?? 0);
        $variance = (float)($teacher['avg_variance'] ?? 0);

        $tci_color = $tci > 0 ? 'text-success' : ($tci < 0 ? 'text-danger' : 'text-muted');
        $tsi_color = $tsi > 0 ? 'text-success' : ($tsi < 0 ? 'text-danger' : 'text-muted');

        $tci_sign = $tci > 0 ? '+' : '';
        $tsi_sign = $tsi > 0 ? '+' : '';

        $tname = htmlspecialchars($teacher['tname']);
        $position = !empty($teacher['position']) ? htmlspecialchars($teacher['position']) : 'Teacher';
        $subjects_list = !empty($teacher['subjects_list']) ? htmlspecialchars($teacher['subjects_list']) : 'N/A';
        $classes_list = !empty($teacher['classes_list']) ? htmlspecialchars($teacher['classes_list']) : 'N/A';

        echo "
        <div class='col-md-6 col-lg-6'>
            <div class='card h-100 shadow-sm border'>
                <div class='card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center'>
                    <div>
                        <h6 class='card-title mb-0 fw-bold text-dark'>{$tname}</h6>
                        <small class='text-muted'><i class='bi bi-briefcase me-1'></i>{$position}</small>
                    </div>
                    <span class='badge bg-primary fs-6 px-3 py-1'><i class='bi bi-trophy-fill me-1'></i>Rank: #{$rank}</span>
                </div>
                <div class='card-body p-3'>
                    <div class='row text-center g-2 mb-3'>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10.5px;'>TPI (Base)</small>
                                <strong class='text-dark fs-6'>" . number_format($tpi, 1) . "</strong>
                            </div>
                        </div>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10.5px;'>TII (Boost)</small>
                                <strong class='text-warning fs-6'>" . number_format($tii, 2) . "x</strong>
                            </div>
                        </div>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm border-primary'>
                                <small class='text-primary fw-bold d-block' style='font-size: 10.5px;'>TIA (Final)</small>
                                <strong class='text-primary fs-5'>" . number_format($tia, 1) . "</strong>
                            </div>
                        </div>
                        <div class='col-3'>
                            <div class='p-2 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10.5px;'>TCI / TSI</small>
                                <strong class='{$tci_color} fs-6' style='font-size: 12px;'>{$tci_sign}" . number_format($tci, 1) . "</strong> / 
                                <strong class='{$tsi_color} fs-6' style='font-size: 12px;'>{$tsi_sign}" . number_format($tsi, 1) . "</strong>
                            </div>
                        </div>
                    </div>

                    <div class='row g-2 mb-3 text-center'>
                        <div class='col-4'>
                            <div class='p-1 bg-white border rounded'>
                                <small class='text-muted d-block' style='font-size: 10px;'>Avg Marks</small>
                                <strong class='text-info'>" . number_format($avg_marks, 1) . "%</strong>
                            </div>
                        </div>
                        <div class='col-4'>
                            <div class='p-1 bg-white border rounded'>
                                <small class='text-muted d-block' style='font-size: 10px;'>Pass Rate</small>
                                <strong class='text-success'>" . number_format($pass_rate, 1) . "%</strong>
                            </div>
                        </div>
                        <div class='col-4'>
                            <div class='p-1 bg-white border rounded'>
                                <small class='text-muted d-block' style='font-size: 10px;'>Exc. (70%+)</small>
                                <strong class='text-primary'>" . number_format($excellent_rate, 1) . "%</strong>
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
                            <span class='text-muted'>Average Marks:</span>
                            <span class='fw-bold text-info'>" . number_format($avg_marks, 2) . "%</span>
                        </div>
                        <div class='progress' style='height: 10px; min-height: 10px;'>
                            <div class='progress-bar bg-info' role='progressbar' style='width: {$avg_marks}%;'></div>
                        </div>
                    </div>

                    <!-- Details Table -->
                    <div class='table-responsive'>
                        <table class='table table-sm table-bordered mb-0' style='font-size: 11px;'>
                            <tbody>
                                <tr>
                                    <td class='bg-light text-muted' style='width: 32%;'>Workload</td>
                                    <td><strong>{$students}</strong> Students | <strong>{$classes_count}</strong> Classes | <strong>{$subjects_count}</strong> Subjects</td>
                                </tr>
                                <tr>
                                    <td class='bg-light text-muted'>Classes Taught</td>
                                    <td><span class='text-dark'>{$classes_list}</span></td>
                                </tr>
                                <tr>
                                    <td class='bg-light text-muted'>Subjects Taught</td>
                                    <td><span class='text-primary'>{$subjects_list}</span></td>
                                </tr>
                                <tr>
                                    <td class='bg-light text-muted'>Variation</td>
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

    echo "</div>"; // End .row

} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}