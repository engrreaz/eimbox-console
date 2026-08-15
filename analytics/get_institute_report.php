<?php
header('Content-Type: application/json');
session_start();
require_once '../core/config.php';
require_once '../core/db.php';

$dataset_id = (int)($_GET['dataset_id'] ?? 0);
$sccode = $_SESSION['sccode'] ?? null;

if (empty($dataset_id) || empty($sccode)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

$report_data = [];

// 1. Performance Summary (Overall)
$sql_summary = "
    SELECT
        COUNT(DISTINCT asp.stid) AS total_students,
        SUM(CASE WHEN asp.failed_subjects = 0 THEN 1 ELSE 0 END) AS total_passed_students,
        SUM(asp.total_marks_obtained) AS total_marks_obtained_sum,
        SUM(asp.total_full_marks) AS total_full_marks_sum
    FROM
        analytics_student_performance AS asp
    WHERE
        asp.dataset_id = ? AND asp.sccode = ?;
";
$stmt_summary = $conn->prepare($sql_summary);
$stmt_summary->bind_param("ii", $dataset_id, $sccode);
$stmt_summary->execute();
$summary_result = $stmt_summary->get_result()->fetch_assoc();
$stmt_summary->close();

$total_students = (float)($summary_result['total_students'] ?? 0);
$total_passed_students = (float)($summary_result['total_passed_students'] ?? 0);
$total_marks_obtained_sum = (float)($summary_result['total_marks_obtained_sum'] ?? 0);
$total_full_marks_sum = (float)($summary_result['total_full_marks_sum'] ?? 0);

$report_data['summary'] = [
    'total_students' => $total_students,
    'total_passed_students' => $total_passed_students,
    'pass_rate' => ($total_students > 0) ? ($total_passed_students / $total_students) * 100 : 0,
    'overall_avg_marks_percentage' => ($total_full_marks_sum > 0) ? ($total_marks_obtained_sum / $total_full_marks_sum) * 100 : 0,
];

// 2. Gender-based Performance (Aggregated from subject performance)
// Corrected query to get accurate gender-based stats from student-level data
$sql_gender = "
    SELECT
        SUM(CASE WHEN s.gender IN ('Male', 'Boy') THEN 1 ELSE 0 END) AS total_males,
        SUM(CASE WHEN s.gender IN ('Female', 'Girl') THEN 1 ELSE 0 END) AS total_females,
        SUM(CASE WHEN s.gender IN ('Male', 'Boy') AND asp.failed_subjects = 0 THEN 1 ELSE 0 END) AS passed_males,
        SUM(CASE WHEN s.gender IN ('Female', 'Girl') AND asp.failed_subjects = 0 THEN 1 ELSE 0 END) AS passed_females,
        AVG(CASE WHEN s.gender IN ('Male', 'Boy') THEN asp.percentage END) AS avg_male_marks,
        AVG(CASE WHEN s.gender IN ('Female', 'Girl') THEN asp.percentage END) AS avg_female_marks
    FROM
        analytics_student_performance asp
    JOIN
        students s ON asp.stid = s.stid AND asp.sccode = s.sccode
    WHERE
        asp.dataset_id = ? AND asp.sccode = ?;
";
$stmt_gender = $conn->prepare($sql_gender);
$stmt_gender->bind_param("ii", $dataset_id, $sccode);
$stmt_gender->execute();
$gender_result = $stmt_gender->get_result()->fetch_assoc();
$stmt_gender->close();
$total_males_count = (float)($gender_result['total_males'] ?? 0);
$total_females_count = (float)($gender_result['total_females'] ?? 0);
$report_data['gender_performance'] = [
    'total_males' => $total_males_count,
    'total_females' => $total_females_count,
    'male_pass_rate' => (float)(($total_males_count > 0) ? (($gender_result['passed_males'] ?? 0) / $total_males_count) * 100 : 0),
    'female_pass_rate' => (float)(($total_females_count > 0) ? (($gender_result['passed_females'] ?? 0) / $total_females_count) * 100 : 0),
    'avg_male_marks' => (float)($gender_result['avg_male_marks'] ?? 0),
    'avg_female_marks' => (float)($gender_result['avg_female_marks'] ?? 0),
];

// 3. Top 10 Students
$sql_top_students = "
    SELECT asp.stid, s.stnameeng, asp.classname, asp.sectionname, asp.total_marks_obtained, asp.gpa
    FROM analytics_student_performance AS asp
    JOIN students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode
    WHERE asp.dataset_id = ? AND asp.sccode = ? AND asp.failed_subjects = 0
    ORDER BY asp.total_marks_obtained DESC
    LIMIT 10;
";
$stmt_top_students = $conn->prepare($sql_top_students);
$stmt_top_students->bind_param("ii", $dataset_id, $sccode);
$stmt_top_students->execute();
$top_students_result = $stmt_top_students->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($top_students_result as &$student) {
    $student['total_marks_obtained'] = (float)($student['total_marks_obtained'] ?? 0);
    $student['gpa'] = (float)($student['gpa'] ?? 0);
}
unset($student); // break the reference with the last element
$report_data['top_students'] = $top_students_result;
$stmt_top_students->close();

// 4. Top 3 Classes by CPI
$sql_top_classes = "
    SELECT classname, sectionname, cpi_score, class_rank
    FROM analytics_class_performance
    WHERE dataset_id = ? AND sccode = ?
    ORDER BY cpi_score DESC
    LIMIT 3;
";
$stmt_top_classes = $conn->prepare($sql_top_classes);
$stmt_top_classes->bind_param("ii", $dataset_id, $sccode);
$stmt_top_classes->execute();
$top_classes_result = $stmt_top_classes->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($top_classes_result as &$class) {
    $class['cpi_score'] = (float)($class['cpi_score'] ?? 0);
}
unset($class);
$report_data['top_classes'] = $top_classes_result;
$stmt_top_classes->close();

// 5. 3 Subjects with Lowest Pass Rate (Highest Failure Rate)
$sql_weakest_subjects = "
    SELECT s.subject AS subject_name, aosp.failure_rate
    FROM analytics_overall_subject_performance AS aosp
    JOIN subjects AS s ON aosp.subject_code = s.subcode AND (s.sccode = ? OR s.sccode = '0')
    WHERE aosp.dataset_id = ?
    GROUP BY aosp.subject_code, aosp.failure_rate
    ORDER BY aosp.failure_rate DESC
    LIMIT 5;
";
$stmt_weakest_subjects = $conn->prepare($sql_weakest_subjects);
$stmt_weakest_subjects->bind_param("ii", $sccode, $dataset_id);
$stmt_weakest_subjects->execute();
$weakest_subjects_result = $stmt_weakest_subjects->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($weakest_subjects_result as &$subject) {
    $subject['failure_rate'] = (float)($subject['failure_rate'] ?? 0);
}
unset($subject);
$report_data['weakest_subjects'] = $weakest_subjects_result;
$stmt_weakest_subjects->close();

// 6. Grade Distribution
$sql_grade_distribution = "
    SELECT grade, COUNT(stid) AS student_count
    FROM analytics_student_performance
    WHERE dataset_id = ? AND sccode = ?
    GROUP BY grade
    ORDER BY FIELD(grade, 'A+', 'A', 'A-', 'B', 'C', 'D', 'F');
";
$stmt_grade_distribution = $conn->prepare($sql_grade_distribution);
$stmt_grade_distribution->bind_param("ii", $dataset_id, $sccode);
$stmt_grade_distribution->execute();
$grade_distribution_result = $stmt_grade_distribution->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($grade_distribution_result as &$grade) {
    $grade['student_count'] = (int)($grade['student_count'] ?? 0);
}
unset($grade);
$report_data['grade_distribution'] = $grade_distribution_result;
$stmt_grade_distribution->close();

// 7. At-Risk Students Count
$sql_at_risk = "SELECT COUNT(*) AS at_risk_count FROM analytics_at_risk_students WHERE dataset_id = ? AND sccode = ?;";
$stmt_at_risk = $conn->prepare($sql_at_risk);
$stmt_at_risk->bind_param("ii", $dataset_id, $sccode);
$stmt_at_risk->execute();
$report_data['at_risk_count'] = (float)($stmt_at_risk->get_result()->fetch_assoc()['at_risk_count'] ?? 0);
$stmt_at_risk->close();

echo json_encode(['status' => 'success', 'data' => $report_data]);
?>