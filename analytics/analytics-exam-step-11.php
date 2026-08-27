<?php
/**
 * Step 11: GPA & Grade Calculation
 *
 * - Calculates GPA and Grade for each student based on their marks in each subject.
 * - Updates the `analytics_student_performance` table.
 *
 * @var mysqli $conn
 * @var int $dataset_id
 * @var string $examid_list_str
 */

if (!isset($dataset_id) || !isset($examid_list_str)) {
    die("This script cannot be accessed directly.");
}

function getGradePoint(float $percentage) {
    if ($percentage >= 80) return 5.0;
    if ($percentage >= 70) return 4.0;
    if ($percentage >= 60) return 3.5;
    if ($percentage >= 50) return 3.0;
    if ($percentage >= 40) return 2.0;
    if ($percentage >= 33) return 1.0;
    return 0.0;
}

function getFinalGrade(float $gpa) {
    if ($gpa == 5.0) return 'A+';
    if ($gpa >= 4.0) return 'A';
    if ($gpa >= 3.5) return 'A-';
    if ($gpa >= 3.0) return 'B';
    if ($gpa >= 2.0) return 'C';
    if ($gpa >= 1.0) return 'D';
    return 'F';
}

// Fetch all subjects and marks for the dataset
// Join with subsetup to identify optional subjects
$sql = "
    SELECT sm.stid, sm.markobt, sm.fullmark, sm.gp, ss.fourth
    FROM stmark sm
    JOIN subsetup ss ON sm.sccode = ss.sccode AND sm.sessionyear = ss.sessionyear AND sm.classname = ss.classname AND sm.sectionname = ss.sectionname AND sm.subject = ss.subject
    WHERE sm.sccode = ? AND sm.sessionyear = ? AND sm.examid IN (" . $examid_list_str . ")
      AND (sm.presence = 1 OR sm.markobt > 0)
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $sccode, $sessionyear);
$stmt->execute();
$result = $stmt->get_result();

$student_points = [];
while ($row = $result->fetch_assoc()) {
    $stid = $row['stid'];
    $percentage = ($row['fullmark'] > 0) ? ($row['markobt'] / $row['fullmark']) * 100 : 0;
    $point = getGradePoint($percentage);
    $is_optional = $row['fourth'] ?? 0;

    // Check if separate subject/objective/practical fail caused gp <= 0 in stmark
    $sm_gp = isset($row['gp']) ? (float)$row['gp'] : null;
    if ($sm_gp !== null && $sm_gp <= 0.0) {
        $point = 0.0;
    }

    if (!isset($student_points[$stid])) {
        $student_points[$stid] = ['total_points' => 0, 'subject_count' => 0, 'has_failed' => false, 'optional_points' => 0];
    }

    if ($point == 0.0) {
        $student_points[$stid]['has_failed'] = true;
    }

    if ($is_optional == 1) {
        // Optional subject points (above 2.0) are added
        if ($point > 2.0) {
            $student_points[$stid]['optional_points'] = $point - 2.0;
        }
    } else {
        // Compulsory subject
        $student_points[$stid]['total_points'] += $point;
        $student_points[$stid]['subject_count']++;
    }
}
$stmt->close();

if (empty($student_points)) {
    // No students to update, so we can exit early.
    return;
}

// Prepare for a bulk UPDATE using CASE statement for better performance
$gpa_cases = '';
$grade_cases = '';
$stids_to_update = [];

// Build the CASE statements for GPA and Grade
foreach ($student_points as $stid => $data) {
    $stids_to_update[] = "'" . $conn->real_escape_string($stid) . "'";
    if ($data['has_failed']) {
        $final_gpa = 0.0;
    } else {
        if ($data['subject_count'] > 0) {
            $total_points_with_optional = $data['total_points'] + $data['optional_points'];
            $final_gpa = $total_points_with_optional / $data['subject_count'];
            if ($final_gpa > 5.0) $final_gpa = 5.0; // Cap GPA at 5.0
        } else {
            $final_gpa = 0.0;
        }
    }
    $final_grade = getFinalGrade($final_gpa);

    $gpa_cases .= " WHEN stid = '" . $conn->real_escape_string($stid) . "' THEN " . $final_gpa;
    $grade_cases .= " WHEN stid = '" . $conn->real_escape_string($stid) . "' THEN '" . $conn->real_escape_string($final_grade) . "'";
}

$stid_list_for_sql = implode(',', $stids_to_update);

$bulk_update_sql = "UPDATE analytics_student_performance SET 
                        gpa = (CASE $gpa_cases END), 
                        grade = (CASE $grade_cases END) 
                    WHERE dataset_id = $dataset_id AND stid IN ($stid_list_for_sql)";

$conn->query($bulk_update_sql);
?>
?>