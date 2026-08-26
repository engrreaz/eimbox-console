<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/global_values.php';

// Increase execution time for large datasets
set_time_limit(300);

$dataset_id = (int)($_GET['dataset_id'] ?? 0);
$sccode = $_SESSION['sccode'] ?? null;
$sctype = $_SESSION['sccategory'] ?? ($sctype ?? '');

if (!$sccode && $dataset_id > 0) {
    $stmt_sc = $conn->prepare("SELECT sccode FROM analytics_dataset WHERE datasetid = ?");
    if ($stmt_sc) {
        $stmt_sc->bind_param("i", $dataset_id);
        $stmt_sc->execute();
        $res_sc = $stmt_sc->get_result()->fetch_assoc();
        $sccode = $res_sc['sccode'] ?? null;
        $stmt_sc->close();
    }
}

if (empty($dataset_id) || empty($sccode)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']);
    exit;
}

$report_data = [];

try {
    // 0. Dataset and Institutional Metadata
    $sql_meta = "
        SELECT 
            ad.datasetid,
            ad.sessionyear,
            ad.slot,
            ad.created_at,
            ad.examid,
            ad.dataset_name,
            COALESCE(sc.scname, 'Institution') AS scname,
            COALESCE(sc.sccode, ?) AS sccode,
            CONCAT_WS(', ', NULLIF(sc.scadd1, ''), NULLIF(sc.scadd2, ''), NULLIF(sc.ps, ''), NULLIF(sc.dist, '')) AS scaddress,
            COALESCE(sc.headname, '') AS headname,
            COALESCE(sc.headtitle, 'Head Teacher') AS headtitle
        FROM analytics_dataset ad
        LEFT JOIN scinfo sc ON ad.sccode = sc.sccode
        WHERE ad.datasetid = ?;
    ";
    $stmt_meta = $conn->prepare($sql_meta);
    $stmt_meta->bind_param("si", $sccode, $dataset_id);
    $stmt_meta->execute();
    $meta_result = $stmt_meta->get_result()->fetch_assoc();
    $stmt_meta->close();

    // Fetch exam titles
    $exam_names = [];
    if (!empty($meta_result['examid'])) {
        $exam_ids_clean = implode(',', array_map('intval', explode(',', $meta_result['examid'])));
        if (!empty($exam_ids_clean)) {
            $exam_q = $conn->query("SELECT examtitle FROM examlist WHERE id IN ({$exam_ids_clean})");
            if ($exam_q) {
                while ($erow = $exam_q->fetch_assoc()) {
                    $exam_names[] = $erow['examtitle'];
                }
            }
        }
    }
    $meta_result['exam_title'] = !empty($exam_names) ? implode(' + ', $exam_names) : (!empty($meta_result['dataset_name']) ? $meta_result['dataset_name'] : 'Terminal Examination');
    $report_data['meta'] = $meta_result;

    // 1. Overall Performance Summary & Workload
    $sql_summary = "
        SELECT
            COUNT(DISTINCT asp.stid) AS total_students_appeared,
            SUM(CASE WHEN asp.failed_subjects = 0 THEN 1 ELSE 0 END) AS total_passed_students,
            SUM(CASE WHEN asp.failed_subjects > 0 THEN 1 ELSE 0 END) AS total_failed_students,
            SUM(CASE WHEN asp.failed_subjects = 0 AND (asp.gpa = 5.0 OR asp.grade = 'A+') THEN 1 ELSE 0 END) AS total_aplus_students,
            SUM(CASE WHEN asp.percentage >= 70 THEN 1 ELSE 0 END) AS total_excellent_students,
            SUM(asp.total_marks_obtained) AS total_marks_obtained_sum,
            SUM(asp.total_full_marks) AS total_full_marks_sum,
            AVG(asp.percentage) AS avg_percentage_marks
        FROM
            analytics_student_performance AS asp
        WHERE
            asp.dataset_id = ? AND asp.sccode = ?;
    ";
    $stmt_summary = $conn->prepare($sql_summary);
    $stmt_summary->bind_param("is", $dataset_id, $sccode);
    $stmt_summary->execute();
    $summary_result = $stmt_summary->get_result()->fetch_assoc();
    $stmt_summary->close();

    $total_appeared = (int)($summary_result['total_students_appeared'] ?? 0);
    $total_passed = (int)($summary_result['total_passed_students'] ?? 0);
    $total_failed = (int)($summary_result['total_failed_students'] ?? 0);
    $total_aplus = (int)($summary_result['total_aplus_students'] ?? 0);
    $total_excellent = (int)($summary_result['total_excellent_students'] ?? 0);
    $total_marks_sum = (float)($summary_result['total_marks_obtained_sum'] ?? 0);
    $total_full_sum = (float)($summary_result['total_full_marks_sum'] ?? 0);

    // Total Enrolled from sessioninfo
    $sql_enrolled = "
        SELECT COUNT(DISTINCT stid) AS total_enrolled 
        FROM sessioninfo 
        WHERE sccode = ? AND sessionyear = ? AND slot = ?;
    ";
    $stmt_enrolled = $conn->prepare($sql_enrolled);
    $session_yr = $meta_result['sessionyear'] ?? '';
    $slot_val = $meta_result['slot'] ?? '';
    $stmt_enrolled->bind_param("sss", $sccode, $session_yr, $slot_val);
    $stmt_enrolled->execute();
    $enrolled_res = $stmt_enrolled->get_result()->fetch_assoc();
    $stmt_enrolled->close();
    $total_enrolled = max($total_appeared, (int)($enrolled_res['total_enrolled'] ?? $total_appeared));
    $absent_count = max(0, $total_enrolled - $total_appeared);

    // Workload Counts
    $sql_workload = "
        SELECT 
            (SELECT COUNT(DISTINCT CONCAT(classname, '|', sectionname)) FROM analytics_class_performance WHERE dataset_id = ?) AS total_classes,
            (SELECT COUNT(DISTINCT subject_code) FROM analytics_overall_subject_performance WHERE dataset_id = ?) AS total_subjects,
            (SELECT COUNT(DISTINCT tid) FROM analytics_teacher_performance WHERE dataset_id = ?) AS total_teachers,
            (SELECT COUNT(*) FROM analytics_at_risk_students WHERE dataset_id = ?) AS total_at_risk,
            (SELECT COUNT(*) FROM analytics_at_risk_students WHERE dataset_id = ? AND (risk_score >= 60 OR failed_subject_count >= 3)) AS critical_at_risk;
    ";
    $stmt_workload = $conn->prepare($sql_workload);
    $stmt_workload->bind_param("iiiii", $dataset_id, $dataset_id, $dataset_id, $dataset_id, $dataset_id);
    $stmt_workload->execute();
    $workload_res = $stmt_workload->get_result()->fetch_assoc();
    $stmt_workload->close();

    $report_data['summary'] = [
        'total_enrolled' => $total_enrolled,
        'total_appeared' => $total_appeared,
        'total_absent' => $absent_count,
        'attendance_rate' => $total_enrolled > 0 ? ($total_appeared / $total_enrolled) * 100 : 100,
        'total_passed' => $total_passed,
        'total_failed' => $total_failed,
        'pass_rate' => ($total_appeared > 0) ? ($total_passed / $total_appeared) * 100 : 0,
        'failure_rate' => ($total_appeared > 0) ? ($total_failed / $total_appeared) * 100 : 0,
        'total_aplus' => $total_aplus,
        'aplus_rate' => ($total_appeared > 0) ? ($total_aplus / $total_appeared) * 100 : 0,
        'total_excellent' => $total_excellent,
        'excellent_rate' => ($total_appeared > 0) ? ($total_excellent / $total_appeared) * 100 : 0,
        'overall_avg_marks_percentage' => ($total_full_sum > 0) ? ($total_marks_sum / $total_full_sum) * 100 : (float)($summary_result['avg_percentage_marks'] ?? 0),
        'total_classes' => (int)($workload_res['total_classes'] ?? 0),
        'total_subjects' => (int)($workload_res['total_subjects'] ?? 0),
        'total_teachers' => (int)($workload_res['total_teachers'] ?? 0),
        'total_at_risk' => (int)($workload_res['total_at_risk'] ?? 0),
        'critical_at_risk' => (int)($workload_res['critical_at_risk'] ?? 0)
    ];

    // 2. Gender-Based Comparative Performance
    $sql_gender = "
        SELECT
            SUM(CASE WHEN LOWER(COALESCE(s.gender, si.gender, '')) IN ('male', 'boy', 'ছেলে', 'ছাত্র') THEN 1 ELSE 0 END) AS total_males,
            SUM(CASE WHEN LOWER(COALESCE(s.gender, si.gender, '')) IN ('female', 'girl', 'মেয়ে', 'ছাত্রী') THEN 1 ELSE 0 END) AS total_females,
            SUM(CASE WHEN LOWER(COALESCE(s.gender, si.gender, '')) IN ('male', 'boy', 'ছেলে', 'ছাত্র') AND asp.failed_subjects = 0 THEN 1 ELSE 0 END) AS passed_males,
            SUM(CASE WHEN LOWER(COALESCE(s.gender, si.gender, '')) IN ('female', 'girl', 'মেয়ে', 'ছাত্রী') AND asp.failed_subjects = 0 THEN 1 ELSE 0 END) AS passed_females,
            SUM(CASE WHEN LOWER(COALESCE(s.gender, si.gender, '')) IN ('male', 'boy', 'ছেলে', 'ছাত্র') AND asp.failed_subjects = 0 AND (asp.gpa = 5.0 OR asp.grade = 'A+') THEN 1 ELSE 0 END) AS aplus_males,
            SUM(CASE WHEN LOWER(COALESCE(s.gender, si.gender, '')) IN ('female', 'girl', 'মেয়ে', 'ছাত্রী') AND asp.failed_subjects = 0 AND (asp.gpa = 5.0 OR asp.grade = 'A+') THEN 1 ELSE 0 END) AS aplus_females,
            AVG(CASE WHEN LOWER(COALESCE(s.gender, si.gender, '')) IN ('male', 'boy', 'ছেলে', 'ছাত্র') THEN asp.percentage END) AS avg_male_marks,
            AVG(CASE WHEN LOWER(COALESCE(s.gender, si.gender, '')) IN ('female', 'girl', 'মেয়ে', 'ছাত্রী') THEN asp.percentage END) AS avg_female_marks
        FROM
            analytics_student_performance asp
        LEFT JOIN
            students s ON asp.stid = s.stid AND asp.sccode = s.sccode
        LEFT JOIN 
            sessioninfo si ON asp.stid = si.stid AND asp.sccode = si.sccode AND asp.sessionyear = si.sessionyear
        WHERE
            asp.dataset_id = ? AND asp.sccode = ?;
    ";
    $stmt_gender = $conn->prepare($sql_gender);
    $stmt_gender->bind_param("is", $dataset_id, $sccode);
    $stmt_gender->execute();
    $gender_result = $stmt_gender->get_result()->fetch_assoc();
    $stmt_gender->close();

    $male_count = (int)($gender_result['total_males'] ?? 0);
    $female_count = (int)($gender_result['total_females'] ?? 0);
    $male_passed = (int)($gender_result['passed_males'] ?? 0);
    $female_passed = (int)($gender_result['passed_females'] ?? 0);

    $report_data['gender_performance'] = [
        'total_males' => $male_count,
        'total_females' => $female_count,
        'passed_males' => $male_passed,
        'passed_females' => $female_passed,
        'male_pass_rate' => ($male_count > 0) ? ($male_passed / $male_count) * 100 : 0,
        'female_pass_rate' => ($female_count > 0) ? ($female_passed / $female_count) * 100 : 0,
        'aplus_males' => (int)($gender_result['aplus_males'] ?? 0),
        'aplus_females' => (int)($gender_result['aplus_females'] ?? 0),
        'avg_male_marks' => (float)($gender_result['avg_male_marks'] ?? 0),
        'avg_female_marks' => (float)($gender_result['avg_female_marks'] ?? 0),
        'ratio' => "{$male_count}M : {$female_count}F"
    ];

    // 3. Complete Grade Distribution (A+, A, A-, B, C, D, F)
    $sql_grades = "
        SELECT 
            grade,
            COUNT(stid) AS student_count
        FROM analytics_student_performance
        WHERE dataset_id = ? AND sccode = ?
        GROUP BY grade;
    ";
    $stmt_grades = $conn->prepare($sql_grades);
    $stmt_grades->bind_param("is", $dataset_id, $sccode);
    $stmt_grades->execute();
    $grade_rows = $stmt_grades->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_grades->close();

    $grade_map = [];
    foreach ($grade_rows as $gr) {
        $grade_map[$gr['grade']] = (int)$gr['student_count'];
    }

    $all_grades = [
        ['grade' => 'A+', 'gpa' => '5.00', 'range' => '80% - 100%', 'badge' => 'primary'],
        ['grade' => 'A',  'gpa' => '4.00 - 4.99', 'range' => '70% - 79%', 'badge' => 'success'],
        ['grade' => 'A-', 'gpa' => '3.50 - 3.99', 'range' => '60% - 69%', 'badge' => 'info'],
        ['grade' => 'B',  'gpa' => '3.00 - 3.49', 'range' => '50% - 59%', 'badge' => 'warning'],
        ['grade' => 'C',  'gpa' => '2.00 - 2.99', 'range' => '40% - 49%', 'badge' => 'secondary'],
        ['grade' => 'D',  'gpa' => '1.00 - 1.99', 'range' => '33% - 39%', 'badge' => 'dark'],
        ['grade' => 'F',  'gpa' => '0.00', 'range' => '0% - 32%', 'badge' => 'danger']
    ];

    $formatted_grades = [];
    foreach ($all_grades as $g) {
        $cnt = $grade_map[$g['grade']] ?? 0;
        $pct = ($total_appeared > 0) ? ($cnt / $total_appeared) * 100 : 0;
        $formatted_grades[] = [
            'grade' => $g['grade'],
            'gpa_range' => $g['gpa'],
            'score_range' => $g['range'],
            'badge' => $g['badge'],
            'student_count' => $cnt,
            'percentage' => $pct
        ];
    }
    $report_data['grade_distribution'] = $formatted_grades;

    // 4. Class-by-Class Comparative Summary Matrix
    $sql_classes = "
        SELECT 
            acp.*,
            COALESCE(sub_stats.total_enrolled, acp.total_students_appeared) AS total_enrolled,
            COALESCE(sub_stats.total_passed, 0) AS total_passed,
            COALESCE(sub_stats.total_failed, 0) AS total_failed,
            COALESCE(sub_stats.aplus_count, 0) AS aplus_count
        FROM analytics_class_performance AS acp
        LEFT JOIN (
            SELECT 
                dataset_id, classname, sectionname,
                MAX(student_count) AS total_enrolled,
                SUM(pass_count) AS total_passed,
                SUM(fail_count) AS total_failed,
                SUM(excellent_count) AS aplus_count
            FROM analytics_subject_performance
            WHERE dataset_id = ?
            GROUP BY dataset_id, classname, sectionname
        ) AS sub_stats 
            ON acp.dataset_id = sub_stats.dataset_id
            AND acp.classname COLLATE utf8mb4_unicode_ci = sub_stats.classname COLLATE utf8mb4_unicode_ci
            AND acp.sectionname COLLATE utf8mb4_unicode_ci = sub_stats.sectionname COLLATE utf8mb4_unicode_ci
        WHERE acp.dataset_id = ?
        ORDER BY acp.class_rank ASC, acp.cpi_score DESC;
    ";
    $stmt_classes = $conn->prepare($sql_classes);
    $stmt_classes->bind_param("ii", $dataset_id, $dataset_id);
    $stmt_classes->execute();
    $report_data['classes_summary'] = $stmt_classes->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_classes->close();

    // 5. Subject Difficulty & Benchmark Highlights
    $sql_weakest = "
        SELECT 
            COALESCE(s.subject, CONCAT('Subject ', aosp.subject_code)) AS subject_name,
            aosp.subject_code,
            aosp.failure_rate,
            aosp.overall_marks_percentage,
            aosp.subject_difficulty_factor AS sdf
        FROM analytics_overall_subject_performance AS aosp
        LEFT JOIN subjects AS s ON aosp.subject_code = s.subcode 
            AND (s.sccode = ? OR s.sccode = '0')
            AND (s.sccategory = ? OR ? = '')
            AND s.id = (
                SELECT s2.id FROM subjects s2 
                WHERE s2.subcode = aosp.subject_code 
                  AND (s2.sccode = ? OR s2.sccode = '0')
                  AND (s2.sccategory = ? OR ? = '')
                ORDER BY (s2.sccode = ?) DESC, s2.sccode DESC, s2.id DESC 
                LIMIT 1
            )
        WHERE aosp.dataset_id = ?
        ORDER BY aosp.subject_difficulty_factor DESC, aosp.failure_rate DESC
        LIMIT 5;
    ";
    $stmt_weakest = $conn->prepare($sql_weakest);
    $stmt_weakest->bind_param("sssssssi", $sccode, $sctype, $sctype, $sccode, $sctype, $sctype, $sccode, $dataset_id);
    $stmt_weakest->execute();
    $report_data['weakest_subjects'] = $stmt_weakest->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_weakest->close();

    // 6. Top Scoring Subjects
    $sql_top_subjects = "
        SELECT 
            COALESCE(s.subject, CONCAT('Subject ', aosp.subject_code)) AS subject_name,
            aosp.subject_code,
            (100 - aosp.failure_rate) AS pass_rate,
            aosp.overall_marks_percentage,
            aosp.subject_difficulty_factor AS sdf
        FROM analytics_overall_subject_performance AS aosp
        LEFT JOIN subjects AS s ON aosp.subject_code = s.subcode 
            AND (s.sccode = ? OR s.sccode = '0')
            AND (s.sccategory = ? OR ? = '')
            AND s.id = (
                SELECT s2.id FROM subjects s2 
                WHERE s2.subcode = aosp.subject_code 
                  AND (s2.sccode = ? OR s2.sccode = '0')
                  AND (s2.sccategory = ? OR ? = '')
                ORDER BY (s2.sccode = ?) DESC, s2.sccode DESC, s2.id DESC 
                LIMIT 1
            )
        WHERE aosp.dataset_id = ?
        ORDER BY aosp.overall_marks_percentage DESC, aosp.failure_rate ASC
        LIMIT 5;
    ";
    $stmt_top_sub = $conn->prepare($sql_top_subjects);
    $stmt_top_sub->bind_param("sssssssi", $sccode, $sctype, $sctype, $sccode, $sctype, $sctype, $sccode, $dataset_id);
    $stmt_top_sub->execute();
    $report_data['top_subjects'] = $stmt_top_sub->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_top_sub->close();

    // 7. Top 5 Ranked Students
    $sql_top_students = "
        SELECT 
            asp.stid, 
            COALESCE(s.stnameeng, CONCAT('Student ', asp.stid)) AS stnameeng,
            COALESCE(s.stnameben, '') AS stnameben,
            asp.classname, 
            asp.sectionname, 
            asp.rollno,
            asp.total_marks_obtained, 
            asp.percentage,
            asp.gpa,
            asp.grade,
            asp.class_rank
        FROM analytics_student_performance AS asp
        LEFT JOIN students AS s ON asp.stid = s.stid AND asp.sccode = s.sccode
        WHERE asp.dataset_id = ? AND asp.sccode = ? AND asp.failed_subjects = 0
        ORDER BY asp.class_rank ASC, asp.gpa DESC, asp.total_marks_obtained DESC
        LIMIT 5;
    ";
    $stmt_top_st = $conn->prepare($sql_top_students);
    $stmt_top_st->bind_param("is", $dataset_id, $sccode);
    $stmt_top_st->execute();
    $report_data['top_students'] = $stmt_top_st->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_top_st->close();

    // 8. Top Ranked Teacher
    $sql_top_teacher = "
        SELECT 
            atp.*,
            COALESCE(t.tname, CONCAT('Teacher ', atp.tid)) AS teacher_name,
            COALESCE(t.position, 'Teacher') AS teacher_position
        FROM analytics_teacher_performance atp
        LEFT JOIN teacher t ON atp.tid = t.tid AND (t.sccode = atp.sccode OR t.sccode = '0')
        WHERE atp.dataset_id = ?
        ORDER BY atp.teacher_impact_adjustment DESC, atp.teacher_performance_index DESC
        LIMIT 1;
    ";
    $stmt_teacher = $conn->prepare($sql_top_teacher);
    $stmt_teacher->bind_param("i", $dataset_id);
    $stmt_teacher->execute();
    $report_data['top_teacher'] = $stmt_teacher->get_result()->fetch_assoc();
    $stmt_teacher->close();

    echo json_encode(['status' => 'success', 'data' => $report_data]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>