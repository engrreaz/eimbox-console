<?php
/**
 * display_overall_subject_report.php
 *
 * Fetches data from `analytics_overall_subject_performance` and renders it as a custom HTML block.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);
$sctype = $_SESSION['sccategory'] ?? ($sctype ?? '');

if (!$dataset_id) {
    echo '<div class="alert alert-danger">Invalid Dataset ID.</div>';
    exit;
}

try {
    $query = "
        SELECT 
            COALESCE(s.subject, CONCAT('Subject ', aosp.subject_code)) AS subject_name,
            aosp.*
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
        WHERE aosp.dataset_id = ?
        GROUP BY aosp.id
        ORDER BY aosp.subject_difficulty_factor DESC
    ";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("ssssi", $sctype, $sctype, $sctype, $sctype, $dataset_id);
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
        $pass_rate = 100 - $failure_rate;
        $sdf = (float)($subject['subject_difficulty_factor'] ?? 0);
        $avg_marks = (float)($subject['overall_marks_percentage'] ?? 0);
        $appeared = (int)($subject['total_students_appeared'] ?? 0);
        $fail_count = (int)($subject['fail_count'] ?? 0);
        $pass_count = max(0, $appeared - $fail_count);

        echo "
        <div class='col-md-6 col-lg-4'>
            <div class='card h-100 shadow-sm border'>
                <div class='card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center'>
                    <h6 class='card-title mb-0 fw-bold text-dark'>{$sub_name}</h6>
                    <span class='badge bg-secondary'>Code: {$sub_code}</span>
                </div>
                <div class='card-body p-3'>
                    <div class='row text-center g-2 mb-3'>
                        <div class='col-4'>
                            <div class='p-1 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10px;'>Appeared</small>
                                <strong class='text-dark'>{$appeared}</strong>
                            </div>
                        </div>
                        <div class='col-4'>
                            <div class='p-1 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10px;'>Avg Marks</small>
                                <strong class='text-primary'>" . number_format($avg_marks, 1) . "%</strong>
                            </div>
                        </div>
                        <div class='col-4'>
                            <div class='p-1 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10px;'>Difficulty (SDF)</small>
                                <strong class='text-danger'>" . number_format($sdf, 1) . "</strong>
                            </div>
                        </div>
                    </div>

                    <div class='mb-2'>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span class='text-muted'>Pass Rate ({$pass_count} Passed):</span>
                            <span class='fw-bold text-success'>" . number_format($pass_rate, 2) . "%</span>
                        </div>
                        <div class='progress' style='height: 12px; min-height: 12px;'>
                            <div class='progress-bar bg-success' role='progressbar' style='width: {$pass_rate}%;'></div>
                        </div>
                    </div>

                    <div>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span class='text-muted'>Failure Rate ({$fail_count} Failed):</span>
                            <span class='fw-bold text-danger'>" . number_format($failure_rate, 2) . "%</span>
                        </div>
                        <div class='progress' style='height: 12px; min-height: 12px;'>
                            <div class='progress-bar bg-danger' role='progressbar' style='width: {$failure_rate}%;'></div>
                        </div>
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