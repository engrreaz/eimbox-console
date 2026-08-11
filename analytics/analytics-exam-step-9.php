<?php
/**
 * Step 9: Student Overall Performance & Merit List
 *
 * - Calculates total marks, percentage for each student.
 * - Inserts the data into `analytics_student_performance`.
 * - Calculates class-wise and section-wise ranks.
 *
 * @var mysqli $conn
 * @var int $dataset_id
 * @var string $examid_list_str
 * @var string $sccode
 * @var string $sessionyear
 */

if (!isset($dataset_id) || !isset($examid_list_str)) {
    die("This script cannot be accessed directly.");
}
// 1. Insert student's overall marks

$insert_sql = "
INSERT INTO analytics_student_performance (
    dataset_id, sccode, sessionyear, examid, stid, classname, sectionname, rollno,
    total_marks_obtained, total_full_marks, percentage, failed_subjects,
    failed_subject_codes, failed_subject_names
)
SELECT
    ? AS dataset_id,
    sm.sccode,
    sm.sessionyear,
    ? AS examid,
    sm.stid,
    si.classname,
    si.sectionname,
    si.rollno,
    SUM(sm.markobt) AS total_marks_obtained,
    SUM(sm.fullmark) AS total_full_marks,
    (SUM(sm.markobt) / SUM(sm.fullmark)) * 100 AS percentage,
    SUM(CASE WHEN (sm.markobt / sm.fullmark * 100) < 33 THEN 1 ELSE 0 END) AS failed_subjects,
    GROUP_CONCAT(DISTINCT CASE WHEN (sm.markobt / sm.fullmark * 100) < 33 THEN sm.subject ELSE NULL END SEPARATOR ', ') AS failed_subject_codes,
    GROUP_CONCAT(DISTINCT CASE WHEN (sm.markobt / sm.fullmark * 100) < 33 THEN sub.subject ELSE NULL END SEPARATOR ', ') AS failed_subject_names
FROM stmark sm
JOIN sessioninfo si ON sm.stid = si.stid AND sm.sccode = si.sccode AND sm.sessionyear = si.sessionyear AND sm.slot = si.slot
LEFT JOIN subjects sub ON sm.subject = sub.subcode AND (sub.sccode = sm.sccode OR sub.sccode = '0')
WHERE sm.sccode = ?
  AND sm.sessionyear = ?
  AND sm.examid IN (" . $examid_list_str . ")
  AND sm.slot = ?
GROUP BY sm.sccode, sm.sessionyear, sm.stid, si.classname, si.sectionname, si.rollno
ON DUPLICATE KEY UPDATE
    total_marks_obtained = VALUES(total_marks_obtained),
    total_full_marks = VALUES(total_full_marks),
    percentage = VALUES(percentage),
    failed_subjects = VALUES(failed_subjects),
    failed_subject_codes = VALUES(failed_subject_codes),
    failed_subject_names = VALUES(failed_subject_names);
";






$stmt_insert = $conn->prepare($insert_sql);
if (!$stmt_insert) throw new Exception("Prepare failed (insert): " . $conn->error);
$stmt_insert->bind_param("issss", $dataset_id, $examid_list_str, $sccode, $sessionyear, $slot);
$stmt_insert->execute();
$affected_rows = $stmt_insert->affected_rows;
$stmt_insert->close();

// 2. Update ranks for students who have not failed
$rank_sql = "
UPDATE analytics_student_performance AS asp
JOIN (
    SELECT
        id,
        -- Rank within the entire class
        RANK() OVER (PARTITION BY classname ORDER BY total_marks_obtained DESC) as calculated_class_rank,
        -- Rank within the specific section
        RANK() OVER (PARTITION BY classname, sectionname ORDER BY total_marks_obtained DESC) as calculated_section_rank
    FROM analytics_student_performance
    WHERE dataset_id = ? AND failed_subjects = 0
) AS ranked_students ON asp.id = ranked_students.id
SET
    asp.class_rank = ranked_students.calculated_class_rank,
    asp.section_rank = ranked_students.calculated_section_rank
WHERE
    asp.dataset_id = ?;
";


$stmt_rank = $conn->prepare($rank_sql);
if (!$stmt_rank) throw new Exception("Prepare failed (rank): " . $conn->error);
$stmt_rank->bind_param("ii", $dataset_id, $dataset_id);
$stmt_rank->execute();
$stmt_rank->close();

// Set rank to 0 for failed students
$fail_rank_sql = "UPDATE analytics_student_performance SET class_rank = 0, section_rank = 0 WHERE dataset_id = ? AND failed_subjects > 0";
$stmt_fail_rank = $conn->prepare($fail_rank_sql);
if (!$stmt_fail_rank) throw new Exception("Prepare failed (fail_rank): " . $conn->error);
$stmt_fail_rank->bind_param("i", $dataset_id);
$stmt_fail_rank->execute();
$stmt_fail_rank->close();

?>