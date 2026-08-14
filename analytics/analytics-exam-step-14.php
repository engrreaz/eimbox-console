<?php
/**
 * Step 14: Update Dataset Summary
 *
 * - Calculates total students and total subjects for the entire dataset.
 * - Updates the `analytics_dataset` table with these summary figures.
 *
 * @var mysqli $conn
 * @var int $dataset_id
 */

if (!isset($dataset_id)) {
    die("This script cannot be accessed directly.");
}

// 1. Calculate total unique students and subjects from the performance table
$summary_sql = "
    SELECT
        COUNT(DISTINCT stid) AS total_students,
        (SELECT COUNT(DISTINCT subject_code) FROM analytics_subject_performance WHERE dataset_id = ?) AS total_subjects
    FROM
        analytics_student_performance
    WHERE
        dataset_id = ?;
";

$stmt_summary = $conn->prepare($summary_sql);
if (!$stmt_summary) throw new Exception("Prepare failed (summary): " . $conn->error);
$stmt_summary->bind_param("ii", $dataset_id, $dataset_id);
$stmt_summary->execute();
$result = $stmt_summary->get_result()->fetch_assoc();
$stmt_summary->close();

$total_students = $result['total_students'] ?? 0;
$total_subjects = $result['total_subjects'] ?? 0;

// 2. Update the analytics_dataset table
$update_sql = "
    UPDATE analytics_dataset
    SET total_students = ?, total_subjects = ?
    WHERE id = ?;
";

$stmt_update = $conn->prepare($update_sql);
if (!$stmt_update) throw new Exception("Prepare failed (update): " . $conn->error);
$stmt_update->bind_param("iii", $total_students, $total_subjects, $dataset_id);
$stmt_update->execute();
$stmt_update->close();
?>