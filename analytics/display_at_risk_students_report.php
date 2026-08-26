<?php
/**
 * display_at_risk_students_report.php
 *
 * Fetches data from `analytics_at_risk_students` and renders it as a custom HTML block.
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
    $sql = "
        SELECT 
            ar.*, 
            COALESCE(s.stnameeng, CONCAT('Student ', ar.stid)) AS stnameeng,
            COALESCE(s.stnameben, '') AS stnameben,
            COALESCE(s.gender, si.gender, '') AS gender,
            COALESCE(s.guarmobile, si.guarmobile, '') AS guarmobile,
            COALESCE(asp.rollno, si.rollno, '-') AS rollno,
            COALESCE(asp.total_marks_obtained, 0) AS total_marks_obtained,
            COALESCE(asp.total_full_marks, 0) AS total_full_marks,
            COALESCE(asp.percentage, 0) AS percentage,
            COALESCE(asp.class_rank, 0) AS class_rank,
            COALESCE(asp.section_rank, 0) AS section_rank
        FROM analytics_at_risk_students AS ar
        LEFT JOIN students AS s ON ar.stid = s.stid AND ar.sccode = s.sccode
        LEFT JOIN sessioninfo AS si ON ar.stid = si.stid AND ar.sccode = si.sccode
        LEFT JOIN analytics_student_performance AS asp ON ar.dataset_id = asp.dataset_id AND ar.stid = asp.stid
        WHERE ar.dataset_id = ?
        GROUP BY ar.id
        ORDER BY ar.risk_score DESC, ar.failed_subject_count DESC, ar.classname ASC, ar.sectionname ASC;
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("i", $dataset_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '
        <div class="alert alert-success d-flex align-items-center shadow-sm p-3">
            <i class="bi bi-check-circle-fill fs-3 text-success me-3"></i>
            <div>
                <h5 class="mb-0 fw-bold">অভিনন্দন! কোনো ঝুঁকিপূর্ণ শিক্ষার্থী পাওয়া যায়নি।</h5>
                <small class="text-muted">এই পরীক্ষা ডেটাসেটে কোনো শিক্ষার্থী উচ্চ ঝুঁকি বা একাধিক বিষয়ে ফেল করেনি।</small>
            </div>
        </div>';
        exit;
    }

    // Calculate Summary Stats
    $total_at_risk = count($data);
    $critical_count = 0;
    $moderate_count = 0;
    $borderline_count = 0;
    $multi_fail_count = 0;

    foreach ($data as $r) {
        $score = (float)($r['risk_score'] ?? 0);
        $fail_cnt = (int)($r['failed_subject_count'] ?? 0);
        if ($score >= 60 || $fail_cnt >= 3) {
            $critical_count++;
        } elseif ($score >= 35 || $fail_cnt == 2) {
            $moderate_count++;
        } else {
            $borderline_count++;
        }

        if ($fail_cnt >= 2) {
            $multi_fail_count++;
        }
    }

    // --- Custom HTML Rendering ---
    echo "
    <!-- Summary Header Cards -->
    <div class='row text-center g-3 mb-4'>
        <div class='col-6 col-md-3'>
            <div class='card border shadow-sm p-2 bg-light'>
                <small class='text-muted d-block' style='font-size: 11px;'>Total At-Risk</small>
                <strong class='text-dark fs-4'>{$total_at_risk}</strong>
            </div>
        </div>
        <div class='col-6 col-md-3'>
            <div class='card border-danger shadow-sm p-2 bg-danger bg-opacity-10'>
                <small class='text-danger fw-bold d-block' style='font-size: 11px;'>Critical Risk (SRS &ge; 60)</small>
                <strong class='text-danger fs-4'>{$critical_count}</strong>
            </div>
        </div>
        <div class='col-6 col-md-3'>
            <div class='card border-warning shadow-sm p-2 bg-warning bg-opacity-10'>
                <small class='text-warning-emphasis fw-bold d-block' style='font-size: 11px;'>Moderate Risk (35 - 59)</small>
                <strong class='text-warning-emphasis fs-4'>{$moderate_count}</strong>
            </div>
        </div>
        <div class='col-6 col-md-3'>
            <div class='card border-info shadow-sm p-2 bg-info bg-opacity-10'>
                <small class='text-info fw-bold d-block' style='font-size: 11px;'>Multi-Subject Failed (&ge;2)</small>
                <strong class='text-info fs-4'>{$multi_fail_count}</strong>
            </div>
        </div>
    </div>

    <div class='row g-4'>
    ";

    foreach ($data as $student) {
        $risk_score = (float)($student['risk_score'] ?? 0);
        $stname = htmlspecialchars($student['stnameeng']);
        $stnameben = htmlspecialchars($student['stnameben'] ?? '');
        $stid = htmlspecialchars($student['stid'] ?? '');
        $rollno = htmlspecialchars($student['rollno'] ?? '-');
        $classname = htmlspecialchars($student['classname']);
        $sectionname = htmlspecialchars($student['sectionname']);
        $gender = htmlspecialchars($student['gender'] ?? '');
        $gender_icon = (strtolower($gender) === 'female' || $gender === 'ছাত্রী') ? '👧' : '👦';
        $guarmobile = htmlspecialchars($student['guarmobile'] ?? '');

        $failed_count = (int)($student['failed_subject_count'] ?? 0);
        $failed_list = htmlspecialchars($student['failed_subject_list'] ?? '');
        $total_marks = (float)($student['total_marks_obtained'] ?? 0);
        $full_marks = (float)($student['total_full_marks'] ?? 0);
        $pct = (float)($student['percentage'] ?? 0);

        // Determine Severity Level
        let_level:
        $severity_class = 'border-danger';
        $badge_class = 'bg-danger text-white';
        $severity_label = 'Critical Risk';

        if ($risk_score >= 60 || $failed_count >= 3) {
            $severity_class = 'border-danger';
            $badge_class = 'bg-danger text-white';
            $severity_label = 'Critical High Risk';
        } elseif ($risk_score >= 35 || $failed_count == 2) {
            $severity_class = 'border-warning';
            $badge_class = 'bg-warning text-dark';
            $severity_label = 'Moderate Risk';
        } else {
            $severity_class = 'border-info';
            $badge_class = 'bg-info text-dark';
            $severity_label = 'Borderline Risk';
        }

        echo "
        <div class='col-md-6'>
            <div class='card h-100 border-start border-4 {$severity_class} shadow-sm'>
                <div class='card-body p-3'>
                    <div class='d-flex justify-content-between align-items-start mb-2'>
                        <div>
                            <div class='fw-bold text-dark fs-6'>{$gender_icon} {$stname}</div>
                            " . ($stnameben ? "<small class='text-muted d-block lh-1' style='font-size: 11px;'>{$stnameben}</small>" : "") . "
                            <small class='text-muted'>
                                <i class='bi bi-mortarboard-fill me-1'></i>Class: <strong>{$classname} - {$sectionname}</strong> | Roll: <strong>{$rollno}</strong> (ID: {$stid})
                            </small>
                        </div>
                        <div class='text-end'>
                            <span class='badge {$badge_class} fs-6 px-2 py-1'>SRS: " . number_format($risk_score, 1) . "</span>
                            <small class='d-block text-muted mt-1 fw-semibold' style='font-size: 10px;'>{$severity_label}</small>
                        </div>
                    </div>

                    <div class='p-2 bg-light rounded border-sm mb-2' style='font-size: 11px;'>
                        <div class='row text-center g-1'>
                            <div class='col-4'>
                                <span class='text-muted d-block'>Failed In</span>
                                <strong class='text-danger fs-6'>{$failed_count} Subject(s)</strong>
                            </div>
                            <div class='col-4'>
                                <span class='text-muted d-block'>Total Marks</span>
                                <strong class='text-dark'>" . number_format($total_marks, 1) . "</strong>
                            </div>
                            <div class='col-4'>
                                <span class='text-muted d-block'>Score Rate</span>
                                <strong class='text-primary'>" . number_format($pct, 1) . "%</strong>
                            </div>
                        </div>
                    </div>

                    <div class='mb-2'>
                        <span class='small fw-bold text-danger'><i class='bi bi-x-circle-fill me-1'></i>Failed Subject(s):</span>
                        <div class='small text-dark mt-1 p-2 bg-danger bg-opacity-10 rounded border border-danger-subtle'>
                            <strong>{$failed_list}</strong>
                        </div>
                    </div>

                    " . ($guarmobile ? "
                    <div class='d-flex justify-content-between align-items-center mt-2 pt-2 border-top' style='font-size: 11px;'>
                        <span class='text-muted'><i class='bi bi-telephone-fill me-1 text-primary'></i>Guardian Contact:</span>
                        <a href='tel:{$guarmobile}' class='fw-bold text-decoration-none text-primary font-monospace'>{$guarmobile}</a>
                    </div>" : "") . "
                </div>
            </div>
        </div>
        ";
    }

    echo "</div>";
} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}