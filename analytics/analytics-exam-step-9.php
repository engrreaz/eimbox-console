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
    gpa, grade, class_rank, section_rank,
    predicted_gpa, predicted_grade, a_plus_probability,
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
    COALESCE(SUM(CASE WHEN (sm.presence = 1 OR sm.markobt > 0) THEN sm.markobt ELSE 0 END), 0) AS total_marks_obtained,
    COALESCE(SUM(CASE WHEN (sm.presence = 1 OR sm.markobt > 0) THEN sm.fullmark ELSE 0 END), 0) AS total_full_marks,
    COALESCE((SUM(CASE WHEN (sm.presence = 1 OR sm.markobt > 0) THEN sm.markobt ELSE 0 END) / NULLIF(SUM(CASE WHEN (sm.presence = 1 OR sm.markobt > 0) THEN sm.fullmark ELSE 0 END), 0)) * 100, 0) AS percentage,
    COUNT(DISTINCT CASE WHEN (sm.presence = 1 OR sm.markobt > 0) AND ((sm.markobt / NULLIF(sm.fullmark, 1) * 100) < 33 OR sm.gp <= 0 OR sm.gp IS NULL) THEN sm.subject END) AS failed_subjects,
    COALESCE(ts.gpa, 0) AS gpa,
    COALESCE(ts.gla, 'F') AS grade,
    COALESCE(ts.meritnumcomb, 0) AS class_rank,
    COALESCE(ts.meritnum, 0) AS section_rank,
    0 AS predicted_gpa,
    NULL AS predicted_grade,
    0 AS a_plus_probability,
    GROUP_CONCAT(DISTINCT CASE WHEN (sm.presence = 1 OR sm.markobt > 0) AND ((sm.markobt / NULLIF(sm.fullmark, 1) * 100) < 33 OR sm.gp <= 0 OR sm.gp IS NULL) THEN sm.subject ELSE NULL END ORDER BY sm.subject SEPARATOR ', ') AS failed_subject_codes,
    GROUP_CONCAT(DISTINCT CASE WHEN (sm.presence = 1 OR sm.markobt > 0) AND ((sm.markobt / NULLIF(sm.fullmark, 1) * 100) < 33 OR sm.gp <= 0 OR sm.gp IS NULL) THEN sub.subject ELSE NULL END ORDER BY sm.subject SEPARATOR ', ') AS failed_subject_names
FROM stmark sm
JOIN sessioninfo si ON sm.stid = si.stid AND sm.sccode = si.sccode AND sm.sessionyear = si.sessionyear AND sm.slot = si.slot
LEFT JOIN subjects sub ON sm.subject = sub.subcode
    AND (sub.sccode = sm.sccode OR sub.sccode = '0')
    AND sub.sccategory = '$sctype'
    AND sub.id = (SELECT id FROM subjects s2
                  WHERE s2.subcode = sm.subject AND (s2.sccode = sm.sccode OR s2.sccode = '0') AND s2.sccategory = '$sctype'
                  ORDER BY s2.sccode DESC LIMIT 1)
LEFT JOIN examlist el ON el.id IN (" . $examid_list_str . ")
LEFT JOIN tabulatingsheet ts ON sm.stid = ts.stid 
    AND sm.sccode = ts.sccode 
    AND sm.sessionyear = ts.sessionyear 
    AND sm.slot = ts.slot
    AND si.classname = ts.classname
    AND si.sectionname = ts.sectionname
    AND el.examtitle = ts.exam
WHERE sm.sccode = ?
  AND sm.sessionyear = ?
  AND sm.examid IN (" . $examid_list_str . ")
  AND sm.slot = ?
GROUP BY sm.sccode, sm.sessionyear, sm.stid, si.classname, si.sectionname, si.rollno
HAVING SUM(CASE WHEN (sm.presence = 1 OR sm.markobt > 0) THEN 1 ELSE 0 END) > 0
ON DUPLICATE KEY UPDATE
    total_marks_obtained = VALUES(total_marks_obtained),
    total_full_marks = VALUES(total_full_marks),
    percentage = VALUES(percentage),
    failed_subjects = VALUES(failed_subjects),
    gpa = VALUES(gpa),
    grade = VALUES(grade),
    class_rank = VALUES(class_rank),
    section_rank = VALUES(section_rank),
    failed_subject_codes = VALUES(failed_subject_codes),
    failed_subject_names = VALUES(failed_subject_names);
";






$stmt_insert = $conn->prepare($insert_sql);
if (!$stmt_insert) throw new Exception("Prepare failed (insert): " . $conn->error);
$stmt_insert->bind_param("issss", $dataset_id, $examid_list_str, $sccode, $sessionyear, $slot);
$stmt_insert->execute();
$affected_rows = $stmt_insert->affected_rows;
$stmt_insert->close();

?>