<?php
header('Content-Type: application/json');
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

function fetch_data($conn, $sql, $params = [], $types = "") {
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $data;
}

$dataset_id = (int)($_GET['dataset_id'] ?? 0);
$sccode = $_SESSION['sccode'] ?? null;

if (empty($dataset_id) || empty($sccode)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

try {
    $full_report = [];

    // 1. Institute Summary (from get_institute_report.php)
    $sql_summary = "SELECT * FROM analytics_dataset WHERE datasetid = ? AND sccode = ?";
    $full_report['dataset_info'] = fetch_data($conn, $sql_summary, [$dataset_id, $sccode], "is")[0] ?? [];

    $sql_institute_summary = "
        SELECT
            COUNT(DISTINCT stid) AS total_students,
            SUM(CASE WHEN failed_subjects = 0 THEN 1 ELSE 0 END) AS total_passed_students,
            (SUM(CASE WHEN failed_subjects = 0 THEN 1 ELSE 0 END) / COUNT(DISTINCT stid)) * 100 AS pass_rate,
            AVG(percentage) AS overall_avg_marks_percentage
        FROM analytics_student_performance
        WHERE dataset_id = ? AND sccode = ?;
    ";
    $full_report['institute_summary'] = fetch_data($conn, $sql_institute_summary, [$dataset_id, $sccode], "is")[0] ?? [];

    // 2. Grade Distribution
    $sql_grade_distribution = "
        SELECT grade, COUNT(stid) AS student_count
        FROM analytics_student_performance
        WHERE dataset_id = ? AND sccode = ?
        GROUP BY grade ORDER BY FIELD(grade, 'A+', 'A', 'A-', 'B', 'C', 'D', 'F');
    ";
    $full_report['grade_distribution'] = fetch_data($conn, $sql_grade_distribution, [$dataset_id, $sccode], "is");

    // 3. Weakest Subjects
    $sql_weakest_subjects = "
        SELECT s.subject AS subject_name, aosp.failure_rate
        FROM analytics_overall_subject_performance AS aosp
        JOIN subjects AS s ON aosp.subject_code = s.subcode AND (s.sccode = ? OR s.sccode = '0')
        WHERE aosp.dataset_id = ?
        GROUP BY aosp.subject_code, s.subject, aosp.failure_rate
        ORDER BY aosp.failure_rate DESC LIMIT 5;
    ";
    $full_report['weakest_subjects'] = fetch_data($conn, $sql_weakest_subjects, [$sccode, $dataset_id], "si");

    // 4. Teacher Performance
    $sql_teachers = "
        SELECT t.tname, t.position, atp.*
        FROM analytics_teacher_performance AS atp
        JOIN teacher AS t ON atp.tid = t.tid AND atp.sccode = t.sccode
        WHERE atp.dataset_id = ? AND atp.sccode = ?
        ORDER BY atp.teacher_impact_adjustment DESC;
    ";
    $full_report['teacher_performance'] = fetch_data($conn, $sql_teachers, [$dataset_id, $sccode], "is");

    // 5. Class Performance
    $sql_classes = "
        SELECT * FROM analytics_class_performance
        WHERE dataset_id = ? AND sccode = ?
        ORDER BY class_rank ASC;
    ";
    $full_report['class_performance'] = fetch_data($conn, $sql_classes, [$dataset_id, $sccode], "is");

    // 6. Subject Performance
    $sql_subjects = "
        SELECT s.subject as subject_name, aosp.*
        FROM analytics_overall_subject_performance AS aosp
        JOIN subjects AS s ON aosp.subject_code = s.subcode AND (s.sccode = ? OR s.sccode = '0')
        WHERE aosp.dataset_id = ?
        GROUP BY aosp.id
        ORDER BY aosp.subject_difficulty_factor DESC;
    ";
    $full_report['subject_performance'] = fetch_data($conn, $sql_subjects, [$sccode, $dataset_id], "si");

    // 7. Student Merit List
    // Optimization: Fetch top 50 and bottom 50 students in separate queries to avoid slow UNION on large tables.
    $sql_top_students = "
        SELECT s.stnameeng, asp.*, 'top' as type
        FROM analytics_student_performance AS asp
        JOIN students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode
        WHERE asp.dataset_id = ? AND asp.sccode = ?
        ORDER BY asp.class_rank ASC, asp.total_marks_obtained DESC
        LIMIT 50;
    ";
    $top_students = fetch_data($conn, $sql_top_students, [$dataset_id, $sccode], "is");

    $sql_bottom_students = "
        SELECT s.stnameeng, asp.*, 'bottom' as type
        FROM analytics_student_performance AS asp
        JOIN students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode
        WHERE asp.dataset_id = ? AND asp.sccode = ?
        ORDER BY asp.class_rank DESC, asp.total_marks_obtained ASC
        LIMIT 50;
    ";
    $bottom_students = fetch_data($conn, $sql_bottom_students, [$dataset_id, $sccode], "is");

    $full_report['student_merit_list'] = array_merge($top_students, $bottom_students);

    echo json_encode(['status' => 'success', 'data' => $full_report]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}