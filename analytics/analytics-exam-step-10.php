<?php
/**
 * Step 10: Identify At-Risk Students
 *
 * - Finds students who have failed in one or more subjects.
 * - Inserts them into the `analytics_at_risk_students` table.
 *
 * @var mysqli $conn
 * @var int $dataset_id
 */

if (!isset($dataset_id)) {
    die("This script cannot be accessed directly.");
}

$sql = "
INSERT INTO analytics_at_risk_students (
    dataset_id, sccode, stid, classname, sectionname, failed_subject_count, reason
)
SELECT
    dataset_id,
    sccode,
    stid,
    classname,
    sectionname,
    failed_subjects,
    CONCAT('Failed in ', failed_subjects, ' subject(s)') AS reason
FROM
    analytics_student_performance
WHERE
    dataset_id = ? AND failed_subjects > 0
ON DUPLICATE KEY UPDATE
    failed_subject_count = VALUES(failed_subject_count),
    reason = VALUES(reason);
";

$stmt = $conn->prepare($sql);
if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $dataset_id);
$stmt->execute();
$stmt->close();
?>