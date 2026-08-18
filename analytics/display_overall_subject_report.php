<?php
/**
 * display_overall_subject_report.php
 *
 * Fetches data from `analytics_overall_subject_performance` and renders it as a custom HTML block.
 */

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);
$sccode = $_SESSION['sccode'] ?? null;

if (!$dataset_id || !$sccode) {
    echo '<div class="alert alert-danger">Invalid Parameters.</div>';
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT s.subject as subject_name, aosp.*
        FROM analytics_overall_subject_performance AS aosp
        JOIN subjects AS s ON aosp.subject_code = s.subcode AND (s.sccode = ? OR s.sccode = '0')
        WHERE aosp.dataset_id = ? AND s.sccategory = ?
        GROUP BY aosp.id
        ORDER BY aosp.subject_difficulty_factor DESC
    ");
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("sis", $sccode, $dataset_id, $sctype);
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
        $pass_rate = 100 - (float)($subject['failure_rate'] ?? 0);
        $sdf = (float)($subject['subject_difficulty_factor'] ?? 0);

        echo "
        <div class='col-md-6 col-lg-4'>
            <div class='card h-100 shadow-sm'>
                <div class='card-header'>
                    <h6 class='card-title mb-0'>{$subject['subject_name']}</h6>
                    <small class='text-muted'>Code: {$subject['subject_code']}</small>
                </div>
                <div class='card-body'>
                    <p><strong>Appeared:</strong> {$subject['total_students_appeared']}</p>
                    <p><strong>Avg. Marks:</strong> ".number_format($subject['overall_marks_percentage'], 2)."%</p>
                    <p><strong>Pass Rate:</strong> <span class='text-success'>".number_format($pass_rate, 2)."%</span></p>
                    <p><strong>Difficulty (SDF):</strong> <span class='fw-bold text-danger'>".number_format($sdf, 2)."</span></p>
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