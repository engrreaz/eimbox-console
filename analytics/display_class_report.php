<?php
/**
 * display_class_report.php
 *
 * Fetches data from `analytics_class_performance` and renders it as a custom HTML block.
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
    // Fetch the data, ordered by rank
    $stmt = $conn->prepare("
        SELECT * FROM analytics_class_performance
        WHERE dataset_id = ?
        ORDER BY class_rank ASC, cpi_score DESC
    ");
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("i", $dataset_id);
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
        $pass_rate = (float)($class['pass_rate'] ?? 0);
        $cpi = (float)($class['cpi_score'] ?? 0);
        $difficulty = (float)($class['difficulty_factor'] ?? 0);
        $rank = $class['class_rank'] ?? 'N/A';
        $classname = htmlspecialchars($class['classname']);
        $sectionname = htmlspecialchars($class['sectionname']);

        echo "
        <div class='col-md-6 col-lg-4'>
            <div class='card h-100 shadow-sm border'>
                <div class='card-header py-2 px-3 bg-light border-bottom d-flex justify-content-between align-items-center'>
                    <h6 class='card-title mb-0 fw-bold text-dark'>Class: {$classname} - {$sectionname}</h6>
                    <span class='badge bg-secondary'>Rank: #{$rank}</span>
                </div>
                <div class='card-body p-3'>
                    <div class='row text-center g-2 mb-3'>
                        <div class='col-4'>
                            <div class='p-1 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10px;'>Appeared</small>
                                <strong class='text-dark'>{$class['total_students_appeared']}</strong>
                            </div>
                        </div>
                        <div class='col-4'>
                            <div class='p-1 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10px;'>Pass Rate</small>
                                <strong class='text-success'>" . number_format($pass_rate, 1) . "%</strong>
                            </div>
                        </div>
                        <div class='col-4'>
                            <div class='p-1 bg-light rounded border-sm'>
                                <small class='text-muted d-block' style='font-size: 10px;'>CPI Score</small>
                                <strong class='text-primary'>" . number_format($cpi, 1) . "</strong>
                            </div>
                        </div>
                    </div>

                    <div class='mb-2'>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span class='text-muted'>Avg. Marks:</span>
                            <span class='fw-bold text-primary'>" . number_format($avg_marks, 2) . "%</span>
                        </div>
                        <div class='progress' style='height: 12px; min-height: 12px;'>
                            <div class='progress-bar bg-primary' role='progressbar' style='width: {$avg_marks}%;'></div>
                        </div>
                    </div>

                    <div>
                        <div class='d-flex justify-content-between mb-1' style='font-size: 11px;'>
                            <span class='text-muted'>Difficulty Factor (CDF):</span>
                            <span class='fw-bold text-danger'>" . number_format($difficulty, 2) . "</span>
                        </div>
                        <div class='progress' style='height: 12px; min-height: 12px;'>
                            <div class='progress-bar bg-danger' role='progressbar' style='width: " . min(100, $difficulty) . "%;'></div>
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