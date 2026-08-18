<?php
/**
 * display_student_report.php
 *
 * Fetches data from `analytics_student_performance` and renders it as a custom HTML block.
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
        SELECT s.stnameeng, asp.*
        FROM analytics_student_performance AS asp
        JOIN students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode
        WHERE asp.dataset_id = ? AND asp.sccode = ?
        ORDER BY asp.class_rank ASC, asp.total_marks_obtained DESC
        LIMIT 20
    "); // Showing top 20 students in custom view
    if (!$stmt) throw new Exception("DB query preparation failed: " . $conn->error);

    $stmt->bind_param("is", $dataset_id, $sccode);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($data)) {
        echo '<div class="alert alert-warning">No student performance data available.</div>';
        exit;
    }

    // --- Custom HTML Rendering ---
    echo "<h5>Top 20 Students</h5>";
    echo "<ul class='list-group'>";

    foreach ($data as $student) {
        $is_fail = (int)($student['failed_subjects'] ?? 0) > 0;
        $rank = $is_fail ? 'F' : $student['class_rank'];
        $badge_color = $is_fail ? 'bg-danger' : 'bg-success';

        echo "
        <li class='list-group-item d-flex justify-content-between align-items-center'>
            <div>
                <h6 class='mb-0'>{$student['stnameeng']}</h6>
                <small class='text-muted'>Class: {$student['classname']}-{$student['sectionname']} | Roll: {$student['rollno']}</small>
            </div>
            <span class='badge {$badge_color} rounded-pill p-2'>Rank: {$rank} | GPA: ".number_format($student['gpa'], 2)."</span>
        </li>
        ";
    }

    echo "</ul>";
} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>