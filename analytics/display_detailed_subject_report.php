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
    // Fetch the data
    $stmt = $conn->prepare("SELECT * FROM analytics_subject_performance WHERE dataset_id = ? ORDER BY classname, sectionname, subject_code");
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
            echo "<h3 class='mt-4' style='border-bottom: 2px solid #eee; padding-bottom: 5px;'>Class: {$classname} - Section: {$sectionname}</h3>";
            echo "<div class='row g-3'>";

            foreach ($subjects as $subject) {
                $pass_rate = $subject['pass_rate'] ?? 0;
                $marks_percentage = $subject['marks_percentage'] ?? 0;
                $excellent_rate = $subject['excellent_rate'] ?? 0;

                echo "
                <div class='col-md-6 col-lg-4'>
                    <div class='card h-100'>
                        <div class='card-header'>
                            <h6 class='card-title mb-0'>{$subject['subject_code']}</h6>
                        </div>
                        <div class='card-body'>
                            <p><strong>Appeared:</strong> {$subject['appeared_student_count']}</p>
                            <p><strong>Avg. Marks:</strong> " . number_format($marks_percentage, 2) . "%</p>
                            <div class='mb-2'>
                                <span>Pass Rate:</span>
                                <div class='progress' style='height: 12px;'>
                                    <div class='progress-bar bg-success' style='width: {$pass_rate}%' title='{$pass_rate}%'></div>
                                </div>
                            </div>
                            <div class='mb-2'>
                                <span>Excellent Rate (70%+):</span>
                                <div class='progress' style='height: 12px;'>
                                    <div class='progress-bar bg-info' style='width: {$excellent_rate}%' title='{$excellent_rate}%'></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ";
            }

            echo "</div>"; // End .row
        }
    }

    // --- Custom HTML Rendering Ends Here ---

} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . $e->getMessage() . '</div>';
}

?>