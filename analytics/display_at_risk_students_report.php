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

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);

if (!$dataset_id) {
    echo '<div class="alert alert-danger">Invalid Dataset ID.</div>';
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            ar.*, 
            COALESCE(s.stnameeng, CONCAT('Student ', ar.stid)) AS stnameeng 
        FROM analytics_at_risk_students ar
        LEFT JOIN students s ON ar.stid = s.stid AND ar.sccode = s.sccode
        WHERE ar.dataset_id = ?
        ORDER BY ar.risk_score DESC, ar.failed_subject_count DESC
    ");
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("i", $dataset_id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '<div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>No high-risk students found for this examination dataset.</div>';
        exit;
    }

    // --- Custom HTML Rendering ---
    echo "<div class='row g-4'>";

    foreach ($data as $student) {
        $risk_score = (float)($student['risk_score'] ?? 0);
        $stname = htmlspecialchars($student['stnameeng']);
        $classname = htmlspecialchars($student['classname']);
        $sectionname = htmlspecialchars($student['sectionname']);
        $reason = htmlspecialchars($student['reason'] ?? '');
        $failed_list = htmlspecialchars($student['failed_subject_list'] ?? '');
        $failed_count = (int)($student['failed_subject_count'] ?? 0);

        echo "
        <div class='col-md-6'>
            <div class='card h-100 border-start border-danger border-4 shadow-sm'>
                <div class='card-body p-3'>
                    <div class='d-flex justify-content-between align-items-start'>
                        <div>
                            <h6 class='card-title fw-bold text-dark mb-1'>{$stname}</h6>
                            <small class='text-muted'><i class='bi bi-mortarboard me-1'></i>Class: {$classname} - {$sectionname}</small>
                        </div>
                        <div class='text-end'>
                            <span class='badge bg-danger'>Risk Score: " . number_format($risk_score, 2) . "</span>
                            <small class='d-block text-muted mt-1'>Failed: {$failed_count} Subject(s)</small>
                        </div>
                    </div>
                    <hr class='my-2'>
                    <p class='mb-1 small'><strong>Reason:</strong> <span class='text-dark'>{$reason}</span></p>
                    <p class='mb-0 small text-danger'><strong>Failed Subjects:</strong> {$failed_list}</p>
                </div>
            </div>
        </div>
        ";
    }

    echo "</div>";
} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}