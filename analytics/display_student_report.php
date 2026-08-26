<?php
/**
 * display_student_report.php
 *
 * Fetches data from `analytics_student_performance` and renders it as a custom HTML block.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);

if (!$dataset_id) {
    echo '<div class="alert alert-danger">Invalid Dataset ID.</div>';
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            asp.*,
            COALESCE(s.stnameeng, CONCAT('Student ', asp.stid)) AS stnameeng,
            COALESCE(s.stnameben, '') AS stnameben,
            COALESCE(s.gender, si.gender, '') AS gender,
            COALESCE(s.guarmobile, si.guarmobile, '') AS guarmobile
        FROM analytics_student_performance AS asp
        LEFT JOIN students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode
        LEFT JOIN sessioninfo AS si ON asp.stid = si.stid AND asp.sccode = si.sccode AND asp.sessionyear = si.sessionyear
        WHERE asp.dataset_id = ?
        ORDER BY 
            asp.classname ASC, asp.sectionname ASC, 
            CASE WHEN asp.failed_subjects > 0 THEN 1 ELSE 0 END ASC,
            asp.class_rank ASC, asp.section_rank ASC, 
            asp.total_marks_obtained DESC;
    ");
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("i", $dataset_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '<div class="alert alert-warning">No student performance data available.</div>';
        exit;
    }

    // Calculate Summary Stats
    $total_students = count($data);
    $passed_count = 0;
    $failed_count = 0;
    $aplus_count = 0;
    $total_score_pct = 0;

    // Group by Class and Section
    $grouped_students = [];

    foreach ($data as $row) {
        $is_fail = (int)($row['failed_subjects'] ?? 0) > 0;
        if ($is_fail) {
            $failed_count++;
        } else {
            $passed_count++;
            if ((float)($row['gpa'] ?? 0) == 5.0 || ($row['grade'] ?? '') === 'A+') {
                $aplus_count++;
            }
        }
        $total_score_pct += (float)($row['percentage'] ?? 0);

        $group_key = ($row['classname'] ?? 'Unknown') . ' - ' . ($row['sectionname'] ?? 'Section');
        if (!isset($grouped_students[$group_key])) {
            $grouped_students[$group_key] = [];
        }
        $grouped_students[$group_key][] = $row;
    }

    $pass_rate = $total_students > 0 ? ($passed_count / $total_students) * 100 : 0;
    $avg_score = $total_students > 0 ? ($total_score_pct / $total_students) : 0;

    // --- Custom HTML Rendering ---
    echo "
    <!-- Summary Header Cards -->
    <div class='row text-center g-3 mb-4'>
        <div class='col-6 col-md-2'>
            <div class='card border shadow-sm p-2'>
                <small class='text-muted d-block' style='font-size: 11px;'>Total Students</small>
                <strong class='text-dark fs-5'>{$total_students}</strong>
            </div>
        </div>
        <div class='col-6 col-md-2'>
            <div class='card border shadow-sm p-2'>
                <small class='text-muted d-block' style='font-size: 11px;'>Passed</small>
                <strong class='text-success fs-5'>{$passed_count}</strong>
            </div>
        </div>
        <div class='col-6 col-md-2'>
            <div class='card border shadow-sm p-2'>
                <small class='text-muted d-block' style='font-size: 11px;'>Pass Rate</small>
                <strong class='text-success fs-5'>" . number_format($pass_rate, 1) . "%</strong>
            </div>
        </div>
        <div class='col-6 col-md-2'>
            <div class='card border shadow-sm p-2'>
                <small class='text-muted d-block' style='font-size: 11px;'>GPA 5.00 / A+</small>
                <strong class='text-primary fs-5'>{$aplus_count}</strong>
            </div>
        </div>
        <div class='col-6 col-md-2'>
            <div class='card border shadow-sm p-2'>
                <small class='text-muted d-block' style='font-size: 11px;'>Avg Score</small>
                <strong class='text-info fs-5'>" . number_format($avg_score, 1) . "%</strong>
            </div>
        </div>
        <div class='col-6 col-md-2'>
            <div class='card border shadow-sm p-2'>
                <small class='text-muted d-block' style='font-size: 11px;'>Failed</small>
                <strong class='text-danger fs-5'>{$failed_count}</strong>
            </div>
        </div>
    </div>
    ";

    // Render Grouped Tables
    foreach ($grouped_students as $class_title => $students) {
        $sec_count = count($students);
        $sec_passed = count(array_filter($students, fn($s) => (int)($s['failed_subjects'] ?? 0) === 0));
        $sec_pass_rate = $sec_count > 0 ? ($sec_passed / $sec_count) * 100 : 0;

        echo "
        <div class='card shadow-sm border mb-4'>
            <div class='card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center'>
                <h6 class='card-title mb-0 fw-bold text-dark'><i class='bi bi-mortarboard-fill text-primary me-2'></i>Class: {$class_title}</h6>
                <span class='badge bg-primary fs-6 px-3 py-1'>{$sec_count} Students | Passed: {$sec_passed} (" . number_format($sec_pass_rate, 1) . "%)</span>
            </div>
            <div class='table-responsive'>
                <table class='table table-bordered table-striped table-hover table-sm mb-0 align-middle' style='font-size: 12px;'>
                    <thead class='table-light text-center'>
                        <tr>
                            <th style='width: 75px;'>Rank</th>
                            <th style='width: 60px;'>Roll</th>
                            <th class='text-start' style='min-width: 180px;'>Student Details</th>
                            <th style='width: 110px;'>Marks & %</th>
                            <th style='width: 85px;'>GPA</th>
                            <th style='width: 65px;'>Grade</th>
                            <th class='text-start' style='min-width: 160px;'>Status & Remarks</th>
                        </tr>
                    </thead>
                    <tbody>";

        foreach ($students as $student) {
            $is_fail = (int)($student['failed_subjects'] ?? 0) > 0;
            $class_rank = $student['class_rank'] ? '#' . $student['class_rank'] : '-';
            $sec_rank = $student['section_rank'] ? '#' . $student['section_rank'] : '-';

            $grade = htmlspecialchars($student['grade'] ?? 'F');
            $gpa = (float)($student['gpa'] ?? 0);
            $total_marks = (float)($student['total_marks_obtained'] ?? 0);
            $full_marks = (float)($student['total_full_marks'] ?? 0);
            $pct = (float)($student['percentage'] ?? 0);
            $risk_score = (float)($student['risk_score'] ?? 0);

            $stname = htmlspecialchars($student['stnameeng']);
            $stnameben = htmlspecialchars($student['stnameben'] ?? '');
            $rollno = htmlspecialchars($student['rollno'] ?? '-');
            $stid = htmlspecialchars($student['stid'] ?? '');
            $gender = htmlspecialchars($student['gender'] ?? '');
            $gender_icon = (strtolower($gender) === 'female' || $gender === 'ছাত্রী') ? '👧' : '👦';

            $failed_names = htmlspecialchars($student['failed_subject_names'] ?? '');

            $grade_badge_class = 'bg-secondary';
            if ($grade === 'A+') $grade_badge_class = 'bg-primary';
            elseif ($grade === 'A' || $grade === 'A-') $grade_badge_class = 'bg-success';
            elseif ($grade === 'F') $grade_badge_class = 'bg-danger';

            $status_html = '';
            if ($is_fail) {
                $failed_cnt = (int)$student['failed_subjects'];
                $status_html = "<span class='badge bg-danger'>Failed ({$failed_cnt})</span>";
                if ($failed_names) {
                    $status_html .= "<div class='text-danger mt-1' style='font-size: 10.5px;'><i class='bi bi-exclamation-triangle me-1'></i>{$failed_names}</div>";
                }
                if ($risk_score > 0) {
                    $status_html .= "<div class='text-muted' style='font-size: 9.5px;'>Risk Score: <strong>" . number_format($risk_score, 1) . "</strong></div>";
                }
            } else {
                $status_html = "<span class='badge bg-success'><i class='bi bi-check-circle me-1'></i>Passed</span>";
            }

            echo "
                        <tr>
                            <td class='text-center'>
                                <span class='badge " . ($is_fail ? 'bg-danger' : 'bg-primary') . " fs-6 py-1 px-2'>" . ($is_fail ? 'Fail' : $class_rank) . "</span>
                                <small class='text-muted d-block mt-1' style='font-size: 9px;'>Sec: {$sec_rank}</small>
                            </td>
                            <td class='text-center fw-bold text-dark font-monospace'>{$rollno}</td>
                            <td class='text-start'>
                                <div class='fw-bold text-dark'>{$gender_icon} {$stname}</div>
                                " . ($stnameben ? "<small class='text-muted d-block lh-1' style='font-size: 10.5px;'>{$stnameben}</small>" : "") . "
                                <small class='text-muted font-monospace' style='font-size: 9.5px;'>ID: {$stid}</small>
                            </td>
                            <td class='text-center'>
                                <div class='fw-bold text-primary lh-sm'>" . number_format($total_marks, 1) . "</div>
                                <small class='text-muted d-block' style='font-size: 9.5px;'>" . number_format($pct, 1) . "% (" . number_format($full_marks, 0) . ")</small>
                            </td>
                            <td class='text-center fw-bold fs-6 " . ($is_fail ? 'text-danger' : 'text-primary') . "'>" . number_format($gpa, 2) . "</td>
                            <td class='text-center'><span class='badge {$grade_badge_class} fs-6 px-2'>{$grade}</span></td>
                            <td class='text-start'>{$status_html}</td>
                        </tr>";
        }

        echo "
                    </tbody>
                </table>
            </div>
        </div>";
    }

} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}