<?php
/**
 * display_at_risk_students_report.php
 *
 * Fetches data from `analytics_at_risk_students` and renders it as a custom HTML block.
 */

require_once '../core/config.php';
require_once '../core/db.php';

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);
$sccode = $_SESSION['sccode'] ?? null;

if (!$dataset_id || !$sccode) {
    echo '<div class="alert alert-danger">Invalid Parameters.</div>';
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT ar.*, s.stnameeng FROM analytics_at_risk_students ar
        JOIN students s ON ar.stid = s.stid AND ar.sccode = s.sccode
        WHERE ar.dataset_id = ? AND ar.sccode = ?
        ORDER BY ar.risk_score DESC, ar.failed_subject_count DESC
    ");
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("is", $dataset_id, $sccode);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '<div class="alert alert-warning">No at-risk students found for this report.</div>';
        exit;
    }

    // --- Custom HTML Rendering ---
    echo "<div class='row g-4'>";

    foreach ($data as $student) {
        $risk_score = (float)($student['risk_score'] ?? 0);

        echo "
        <div class='col-md-6'>
            <div class='card h-100 border-start border-danger border-4 shadow-sm'>
                <div class='card-body'>
                    <h6 class='card-title'>{$student['stnameeng']}</h6>
                    <small class='text-muted'>Class: {$student['classname']}-{$student['sectionname']}</small>
                    <p class='mt-2'><strong>Risk Score:</strong> <span class='fw-bold fs-5 text-danger'>".number_format($risk_score, 2)."</span></p>
                    <p><strong>Reason:</strong> {$student['reason']}</p>
                    <p class='small'><strong>Failed Subjects:</strong> {$student['failed_subject_list']}</p>
                </div>
            </div>
        </div>
        ";
    }

    echo "</div>";
} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>