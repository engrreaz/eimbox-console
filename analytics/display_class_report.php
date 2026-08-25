<?php
/**
 * display_class_report.php
 *
 * Fetches data from `analytics_class_performance` and renders it as a custom HTML block.
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
    // Fetch the data, ordered by rank
    $stmt = $conn->prepare("
        SELECT * FROM analytics_class_performance
        WHERE dataset_id = ? AND sccode = ?
        ORDER BY class_rank ASC, cpi_score DESC
    ");
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("is", $dataset_id, $sccode);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '<div class="alert alert-warning">No class performance data available.</div>';
        exit;
    }

    // --- Custom HTML Rendering ---
    echo "<div class='row g-4'>";

    foreach ($data as $class) {
        $avg_marks = (float)($class['overall_marks_percentage'] ?? 0);
        $cpi = (float)($class['cpi_score'] ?? 0);
        $difficulty = (float)($class['difficulty_factor'] ?? 0);

        echo "
        <div class='col-md-6 col-lg-4'>
            <div class='card h-100 shadow-sm'>
                <div class='card-header d-flex justify-content-between'>
                    <h6 class='card-title mb-0'>{$class['classname']} - {$class['sectionname']}</h6>
                    <span class='badge bg-secondary'>Rank: {$class['class_rank']}</span>
                </div>
                <div class='card-body'>
                    <p><strong>Students Appeared:</strong> {$class['total_students_appeared']}</p>
                    <p><strong>CPI Score:</strong> <span class='fw-bold fs-5'>".number_format($cpi, 2)."</span></p>
                    <div class='mb-2'>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span>Avg. Marks:</span>
                            <span class='fw-bold text-primary'>".number_format($avg_marks, 2)."%</span>
                        </div>
                        <div class='progress' style='height: 12px; min-height: 12px;'><div class='progress-bar bg-primary' role='progressbar' style='width: {$avg_marks}%'></div></div>
                    </div>
                    <div>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span>Difficulty Factor:</span>
                            <span class='fw-bold text-danger'>".number_format($difficulty, 2)."</span>
                        </div>
                        <div class='progress' style='height: 12px; min-height: 12px;'><div class='progress-bar bg-danger' role='progressbar' style='width: {$difficulty}%'></div></div>
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
?>