<?php
/**
 * display_detailed_subject_report.php
 *
 * Fetches data from `analytics_subject_performance` and renders it as a custom HTML block.
 * This is called via AJAX from the main report page.
 */

require_once '../core/config.php';
require_once '../core/db.php';

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);

if (!$dataset_id) {
    echo '<div class="alert alert-danger">Invalid Dataset ID.</div>';
    exit;
}

try {
    // Fetch the data with subject name and teacher name
    $query = "
        SELECT 
            asp.*,
            COALESCE(s.subject, CONCAT('Subject ', asp.subject_code)) AS subject_name,
            COALESCE(t.tname, 'Unassigned') AS teacher_name,
            t.position AS teacher_position
        FROM analytics_subject_performance AS asp
        LEFT JOIN subjects AS s 
            ON asp.subject_code = s.subcode 
            AND (s.sccode = asp.sccode OR s.sccode = '0')
        LEFT JOIN teacher AS t 
            ON asp.tid = t.tid 
            AND (t.sccode = asp.sccode OR t.sccode = '0')
        WHERE asp.dataset_id = ? 
        ORDER BY asp.classname, asp.sectionname, asp.subject_code
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database query preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $dataset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '<div class="alert alert-warning">No detailed subject performance data available for this report.</div>';
        exit;
    }

    // --- Custom HTML Rendering Starts Here ---

    // Group data by Class > Section
    $grouped_data = [];
    foreach ($data as $row) {
        $grouped_data[$row['classname']][$row['sectionname']][] = $row;
    }

    // Loop through each class and section to display the data
    foreach ($grouped_data as $classname => $sections) {
        foreach ($sections as $sectionname => $subjects) {
            echo "<div class='section-group mb-4'>";
            echo "<h4 class='mt-4 mb-3 pb-2 border-bottom d-flex align-items-center text-primary'>
                    <i class='bi bi-mortarboard-fill me-2'></i> Class: <span class='text-dark ms-1 fw-bold'>{$classname}</span> 
                    <span class='mx-2 text-muted'>|</span> 
                    Section: <span class='text-dark ms-1 fw-bold'>{$sectionname}</span>
                  </h4>";
            echo "<div class='row g-3'>";

            foreach ($subjects as $subject) {
                $sub_name = htmlspecialchars($subject['subject_name']);
                $sub_code = htmlspecialchars($subject['subject_code']);
                $tname = htmlspecialchars($subject['teacher_name']);
                $tpos = !empty($subject['teacher_position']) ? ' (' . htmlspecialchars($subject['teacher_position']) . ')' : '';
                
                $enrolled = (int)($subject['student_count'] ?? 0);
                $appeared = (int)($subject['appeared_student_count'] ?? 0);
                $pass_count = (int)($subject['pass_count'] ?? 0);
                $fail_count = (int)($subject['fail_count'] ?? 0);
                $excellent_count = (int)($subject['excellent_count'] ?? 0);
                
                $pass_rate = (float)($subject['pass_rate'] ?? 0);
                $fail_rate = (float)($subject['fail_rate'] ?? 0);
                $marks_percentage = (float)($subject['marks_percentage'] ?? 0);
                $avg_marks = (float)($subject['avg_marks'] ?? 0);
                $excellent_rate = (float)($subject['excellent_rate'] ?? 0);
                
                $max_marks = (float)($subject['max_marks'] ?? 0);
                $min_marks = (float)($subject['min_marks'] ?? 0);
                $marks_range = (float)($subject['marks_range'] ?? ($max_marks - $min_marks));
                $variance = (float)($subject['variance'] ?? 0);
                $std_dev = (float)($subject['std_deviation'] ?? 0);
                $cdi = (float)($subject['cdi'] ?? 0);
                $tspi = (float)($subject['tspi'] ?? 0);
                
                $male_count = (int)($subject['male_count'] ?? 0);
                $female_count = (int)($subject['female_count'] ?? 0);
                $male_pass = (int)($subject['male_pass_count'] ?? 0);
                $female_pass = (int)($subject['female_pass_count'] ?? 0);
                $male_avg = (float)($subject['male_avg_marks'] ?? 0);
                $female_avg = (float)($subject['female_avg_marks'] ?? 0);
                
                $above_avg = (int)($subject['count_above_avg'] ?? 0);
                $below_avg = (int)($subject['count_below_avg'] ?? 0);

                echo "
                <div class='col-md-6 col-xl-4'>
                    <div class='card h-100 shadow-sm border'>
                        <div class='card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center'>
                            <div>
                                <h6 class='card-title mb-0 fw-bold text-dark'>{$sub_name}</h6>
                                <small class='text-muted'><i class='bi bi-person me-1'></i>{$tname}{$tpos}</small>
                            </div>
                            <span class='badge bg-secondary'>Code: {$sub_code}</span>
                        </div>
                        <div class='card-body p-3'>
                            <!-- Quick Highlights Grid -->
                            <div class='row g-2 text-center mb-3'>
                                <div class='col-3'>
                                    <div class='p-1 bg-light rounded border-sm'>
                                        <small class='text-muted d-block' style='font-size: 10px;'>Appeared</small>
                                        <strong class='text-dark'>{$appeared}/{$enrolled}</strong>
                                    </div>
                                </div>
                                <div class='col-3'>
                                    <div class='p-1 bg-light rounded border-sm'>
                                        <small class='text-muted d-block' style='font-size: 10px;'>Avg Marks</small>
                                        <strong class='text-primary'>" . number_format($marks_percentage, 1) . "%</strong>
                                    </div>
                                </div>
                                <div class='col-3'>
                                    <div class='p-1 bg-light rounded border-sm'>
                                        <small class='text-muted d-block' style='font-size: 10px;'>Pass Rate</small>
                                        <strong class='text-success'>" . number_format($pass_rate, 1) . "%</strong>
                                    </div>
                                </div>
                                <div class='col-3'>
                                    <div class='p-1 bg-light rounded border-sm'>
                                        <small class='text-muted d-block' style='font-size: 10px;'>A+ (80%+)</small>
                                        <strong class='text-info'>" . number_format($excellent_rate, 1) . "%</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bars with Labels -->
                            <div class='mb-2'>
                                <div class='d-flex justify-content-between align-items-center mb-1' style='font-size: 11px;'>
                                    <span class='text-muted'>Pass Rate ({$pass_count} Passed / {$fail_count} Failed):</span>
                                    <span class='fw-bold text-success'>" . number_format($pass_rate, 2) . "%</span>
                                </div>
                                <div class='progress' style='height: 14px; min-height: 14px;'>
                                    <div class='progress-bar bg-success' role='progressbar' style='width: {$pass_rate}%;'></div>
                                </div>
                            </div>

                            <div class='mb-3'>
                                <div class='d-flex justify-content-between align-items-center mb-1' style='font-size: 11px;'>
                                    <span class='text-muted'>A+ Excellence Rate ({$excellent_count} Students):</span>
                                    <span class='fw-bold text-info'>" . number_format($excellent_rate, 2) . "%</span>
                                </div>
                                <div class='progress' style='height: 14px; min-height: 14px;'>
                                    <div class='progress-bar bg-info' role='progressbar' style='width: {$excellent_rate}%;'></div>
                                </div>
                            </div>

                            <!-- Detailed Stats Table Breakdown -->
                            <div class='table-responsive'>
                                <table class='table table-sm table-bordered mb-0' style='font-size: 11px;'>
                                    <tbody>
                                        <tr>
                                            <td class='bg-light text-muted' style='width: 35%;'>Score Range</td>
                                            <td>
                                                <span class='text-success fw-bold'>Max: {$max_marks}</span> | 
                                                <span class='text-danger fw-bold'>Min: {$min_marks}</span>
                                                <small class='text-muted ms-1'>(Range: {$marks_range})</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class='bg-light text-muted'>Gender Stats</td>
                                            <td>
                                                <span>Boy: <strong>{$male_pass}/{$male_count}</strong> (" . number_format($male_avg, 1) . "%)</span><br>
                                                <span>Girl: <strong>{$female_pass}/{$female_count}</strong> (" . number_format($female_avg, 1) . "%)</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class='bg-light text-muted'>Distribution</td>
                                            <td>Above Avg: <strong>{$above_avg}</strong> | Below Avg: <strong>{$below_avg}</strong></td>
                                        </tr>
                                        <tr>
                                            <td class='bg-light text-muted'>Analysis Indices</td>
                                            <td>
                                                <span class='badge bg-warning text-dark me-1'>CDI: " . number_format($cdi, 2) . "</span>
                                                <span class='badge bg-primary me-1'>TSPI: " . number_format($tspi, 2) . "</span>
                                                <small class='text-muted d-block mt-1'>SD: " . number_format($std_dev, 2) . " | Var: " . number_format($variance, 1) . "</small>
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
            echo "</div>"; // End .section-group
        }
    }

    // --- Custom HTML Rendering Ends Here ---

} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}