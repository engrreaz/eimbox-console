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

$dataset_id = filter_input(INPUT_GET, 'dataset_id', FILTER_VALIDATE_INT);

if (!$dataset_id) {
    echo '<div class="alert alert-danger">Invalid Dataset ID.</div>';
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(s.stnameeng, CONCAT('Student ', asp.stid)) AS stnameeng, 
            asp.*
        FROM analytics_student_performance AS asp
        LEFT JOIN students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode
        WHERE asp.dataset_id = ?
        ORDER BY asp.class_rank ASC, asp.total_marks_obtained DESC
        LIMIT 50
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

    // --- Custom HTML Rendering ---
    echo "<h5 class='mb-3'><i class='bi bi-trophy-fill text-warning me-2'></i>Top 50 Students (Merit Ranking)</h5>";
    echo "<div class='table-responsive'><table class='table table-bordered table-striped table-hover table-sm' style='font-size: 13px;'>";
    echo "<thead class='table-light'><tr>
            <th style='width: 60px;' class='text-center'>Rank</th>
            <th>Student Name</th>
            <th>Class - Section</th>
            <th class='text-center'>Roll</th>
            <th class='text-center'>Total Marks</th>
            <th class='text-center'>GPA</th>
            <th class='text-center'>Grade</th>
            <th class='text-center'>Status</th>
          </tr></thead><tbody>";

    foreach ($data as $student) {
        $is_fail = (int)($student['failed_subjects'] ?? 0) > 0;
        $rank = $is_fail ? 'F' : '#' . $student['class_rank'];
        $rank_badge = $is_fail ? 'bg-danger' : 'bg-primary';
        $grade = htmlspecialchars($student['grade'] ?? 'F');
        $gpa = (float)($student['gpa'] ?? 0);
        $total_marks = (float)($student['total_marks_obtained'] ?? 0);
        $stname = htmlspecialchars($student['stnameeng']);
        $classname = htmlspecialchars($student['classname']);
        $sectionname = htmlspecialchars($student['sectionname']);
        $rollno = htmlspecialchars($student['rollno'] ?? '-');

        $status_badge = $is_fail ? '<span class="badge bg-danger">Failed (' . (int)$student['failed_subjects'] . ')</span>' : '<span class="badge bg-success">Passed</span>';

        echo "
        <tr>
            <td class='text-center fw-bold'><span class='badge {$rank_badge}'>{$rank}</span></td>
            <td class='fw-bold text-dark'>{$stname}</td>
            <td>{$classname} ({$sectionname})</td>
            <td class='text-center'>{$rollno}</td>
            <td class='text-center fw-bold text-primary'>" . number_format($total_marks, 1) . "</td>
            <td class='text-center fw-bold'>" . number_format($gpa, 2) . "</td>
            <td class='text-center'><span class='badge bg-secondary'>{$grade}</span></td>
            <td class='text-center'>{$status_badge}</td>
        </tr>
        ";
    }

    echo "</tbody></table></div>";
} catch (Exception $e) {
    echo '<div class="alert alert-danger">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
}