<?php
/**
 * Step 12: Teacher Impact Comparison (TCI & TSI)
 *
 * This script calculates two comparative impact scores for each teacher:
 * 1. Teacher Class Impact (TCI): Compares the teacher's average marks in a subject
 *    against the overall average of all subjects in that specific class.
 *    Formula: AVG(teacher_subject_avg - class_overall_avg)
 *
 * 2. Teacher Subject Impact (TSI): Compares the teacher's average marks in a subject
 *    against the overall average of that same subject across all classes.
 *    Formula: AVG(teacher_subject_avg - subject_overall_avg)
 *
 * The results are updated in the `analytics_teacher_performance` table.
 *
 * @var mysqli $conn The database connection object.
 * @var int $dataset_id The ID of the current analysis dataset.
 */

// Security check to prevent direct access
if (!isset($dataset_id)) {
    die("This script cannot be accessed directly.");
}

// This query calculates the average TCI and TSI for each teacher and updates the main teacher performance table.
$query = "
    UPDATE analytics_teacher_performance AS atp
    JOIN (
        SELECT
            asp.tid,
            -- Calculate the average TCI score for the teacher
            AVG(asp.avg_marks - acp.avg_of_subject_averages) as avg_tci,
            -- Calculate the average TSI score for the teacher
            AVG(asp.avg_marks - aosp.overall_avg_marks) as avg_tsi
        FROM
            analytics_subject_performance AS asp
        JOIN
            analytics_class_performance AS acp ON asp.dataset_id = acp.dataset_id
                AND asp.classname COLLATE utf8mb4_unicode_ci = acp.classname COLLATE utf8mb4_unicode_ci
                AND asp.sectionname COLLATE utf8mb4_unicode_ci = acp.sectionname COLLATE utf8mb4_unicode_ci
        JOIN
            analytics_overall_subject_performance AS aosp ON asp.dataset_id = aosp.dataset_id
                AND asp.subject_code = aosp.subject_code
        WHERE
            asp.dataset_id = ?
            AND asp.tid IS NOT NULL AND asp.tid != ''
        GROUP BY
            asp.tid
    ) AS impact_scores ON atp.tid = impact_scores.tid
    SET
        atp.tci_score = COALESCE(impact_scores.avg_tci, 0),
        atp.tsi_score = COALESCE(impact_scores.avg_tsi, 0)
    WHERE
        atp.dataset_id = ?;
";

$stmt = $conn->prepare($query);
if (!$stmt) throw new Exception("Prepare failed for TCI/TSI calculation: " . $conn->error);

$stmt->bind_param("ii", $dataset_id, $dataset_id);
$stmt->execute();
$stmt->close();
?>