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

    // Helper to generate SD badge with Material colors
    $get_sd_badge = function($sd) {
        if ($sd <= 0) {
            return '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="bi bi-dash-circle me-1"></i>N/A</span>';
        } elseif ($sd <= 15) {
            return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" title="SD ≤ 15: ফলাফল আদর্শ ও সুষম (Ideal)"><i class="bi bi-check-circle-fill me-1"></i>SD ' . number_format($sd, 2) . ' <span class="fw-normal">(Ideal)</span></span>';
        } elseif ($sd <= 20) {
            return '<span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1" title="SD 15.1 - 20: মধ্যম ব্যবধান (Moderate)"><i class="bi bi-exclamation-circle-fill text-warning me-1"></i>SD ' . number_format($sd, 2) . ' <span class="fw-normal">(Moderate)</span></span>';
        } else {
            return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" title="SD > 20: উচ্চ বৈষম্য / বিশেষ নজর প্রয়োজন (High Gap)"><i class="bi bi-exclamation-triangle-fill me-1"></i>SD ' . number_format($sd, 2) . ' <span class="fw-normal">(High Gap)</span></span>';
        }
    };

    // --- Explanation & Color Guide Card ---
    echo "
    <div class='card border-0 shadow-sm mb-4 bg-light-subtle border-start border-4 border-info'>
        <div class='card-body p-3'>
            <div class='d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2'>
                <h6 class='fw-bold text-dark mb-0 d-flex align-items-center'>
                    <i class='bi bi-info-circle-fill text-info me-2 fs-5'></i>
                    পারফরম্যান্স সূচক ও SD কালার গাইড (Analytics & Color Legend)
                </h6>
                <button class='btn btn-xs btn-outline-secondary py-0 px-2' type='button' data-bs-toggle='collapse' data-bs-target='#teacherGuideCollapse' aria-expanded='false' aria-controls='teacherGuideCollapse' style='font-size: 11px;'>
                    <i class='bi bi-chevron-down me-1'></i>গাইড বিস্তারিত
                </button>
            </div>

            <!-- Quick SD Legend Badges Always Visible -->
            <div class='d-flex flex-wrap align-items-center gap-2 pt-1 pb-1'>
                <span class='text-muted small fw-semibold me-1'><i class='bi bi-graph-up-arrow me-1'></i>SD (ধারাবাহিকতা):</span>
                <span class='badge bg-success-subtle text-success border border-success-subtle px-2 py-1' style='font-size: 11px;'>
                    <i class='bi bi-check-circle-fill me-1'></i>🟢 SD ≤ 15 (Ideal / সুষম)
                </span>
                <span class='badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1' style='font-size: 11px;'>
                    <i class='bi bi-exclamation-circle-fill text-warning me-1'></i>🟡 SD 15.1 – 20 (Moderate / মধ্যম)
                </span>
                <span class='badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1' style='font-size: 11px;'>
                    <i class='bi bi-exclamation-triangle-fill me-1'></i>🔴 SD > 20 (High Gap / উচ্চ বৈষম্য)
                </span>
            </div>

            <!-- Collapsible Detailed Explanation -->
            <div class='collapse mt-3 pt-2 border-top' id='teacherGuideCollapse'>
                <div class='row g-3' style='font-size: 12px;'>
                    <div class='col-md-6'>
                        <div class='p-2 bg-white rounded border'>
                            <strong class='text-primary d-block mb-1'><i class='bi bi-bar-chart-steps me-1'></i>Standard Deviation (SD) ব্যাখ্যা:</strong>
                            <ul class='mb-0 ps-3 text-muted'>
                                <li><strong>🟢 SD ≤ 15 (আদর্শ):</strong> ক্লাসের শিক্ষার্থীদের ফলাফল অত্যন্ত ভারসাম্যপূর্ণ ও ধারাবাহিক।</li>
                                <li><strong>🟡 SD 15.1 – 20 (মধ্যম):</strong> ফলাফলে সহনশীল ব্যবধান রয়েছে, পিছিয়ে পড়াদের মনিটর করা উচিত।</li>
                                <li><strong>🔴 SD > 20 (উচ্চ বৈষম্য):</strong> ক্লাসে ভালো ও দুর্বল ছাত্রদের মাঝে বড় ফারাক—রিমিডিয়াল ক্লাস ও বাড়তি নজর জরুরি।</li>
                            </ul>
                        </div>
                    </div>
                    <div class='col-md-6'>
                        <div class='p-2 bg-white rounded border'>
                            <strong class='text-primary d-block mb-1'><i class='bi bi-award me-1'></i>মূল্যায়ন সূচক (Key Metrics):</strong>
                            <ul class='mb-0 ps-3 text-muted'>
                                <li><strong>TPI (Base Index):</strong> পাস রেট (৪০%) + এ+ বা উৎকর্ষ (২৫%) + গড় নম্বর (৩৫%)।</li>
                                <li><strong>TII (Boost Multiplier):</strong> ক্লাসের কাঠিন্য সমন্বয়ক গুণক (কঠিন শ্রেণিতে TII > 1)।</li>
                                <li><strong>TIA (Final Score):</strong> চূড়ান্ত স্কোর (TPI × TII), যার ভিত্তিতে Rank নির্ধারিত হয়।</li>
                                <li><strong>TCI / TSI:</strong> শ্রেণি ও বিষয়ের সামগ্রিক গড়ের সাথে শিক্ষকের বিষয়ের তুলনামূলক মান।</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ";

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
        $sd_badge_html = $get_sd_badge($std_dev);

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
                                    <td class='bg-light text-muted align-middle'>Variation (SD)</td>
                                    <td class='align-middle'>
                                        <div class='d-flex align-items-center justify-content-between flex-wrap gap-1'>
                                            {$sd_badge_html}
                                            <small class='text-muted'>Avg Var: <strong>" . number_format($variance, 1) . "</strong></small>
                                        </div>
                                    </td>
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