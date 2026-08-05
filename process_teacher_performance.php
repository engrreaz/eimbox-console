<?php
/**
 * File: jobs/process_teacher_performance.php
 *
 * This script contains the core logic for processing and analyzing teacher performance
 * based on student exam results for a given dataset using a single, efficient query.
 */

/**
 * Processes teacher performance for a specific dataset.
 *
 * @param mysqli $main_conn Connection to the main application database (e.g., 'eimbox').
 * @param mysqli $analytics_conn Connection to the analytics database ('eimbox_analytics').
 * @param int $datasetid The ID of the dataset to process from `analytics_dataset` table.
 *
 * @return bool Returns true on success, false on failure.
 */
function processTeacherPerformance(mysqli $main_conn, mysqli $analytics_conn, int $datasetid): bool
{
    try {
        // --- ধাপ ১: ডেটাসেট এবং জব-এর তথ্য আনা ---
        $dataset_query = "SELECT * FROM analytics_dataset WHERE datasetid = ?";
        $stmt_dataset = $analytics_conn->prepare($dataset_query);
        $stmt_dataset->bind_param("i", $datasetid);
        $stmt_dataset->execute();
        $dataset = $stmt_dataset->get_result()->fetch_assoc();

        if (!$dataset) {
            updateJobStatus($analytics_conn, $datasetid, 'Failed', 'Dataset not found.');
            return false;
        }

        // জব স্ট্যাটাস 'Running' হিসেবে আপডেট করা
        updateJobStatus($analytics_conn, $datasetid, 'Running', '', true);

        // পরীক্ষার নাম (examtitle) বের করা
        $exam_query = "SELECT examtitle FROM examlist WHERE id = ? AND sccode = ?";
        $stmt_exam = $main_conn->prepare($exam_query);
        $stmt_exam->bind_param("ii", $dataset['examid'], $dataset['sccode']);
        $stmt_exam->execute();
        $examtitle = $stmt_exam->get_result()->fetch_assoc()['examtitle'] ?? null;

        if (!$examtitle) {
            updateJobStatus($analytics_conn, $datasetid, 'Failed', 'Exam title not found for the given examid.');
            return false;
        }

        // --- ধাপ ২: একটি মাত্র কোয়েরির মাধ্যমে পারফরম্যান্স গণনা ও সংরক্ষণ ---
        $main_query = "
            INSERT INTO eimbox_analytics.analytics_teacher_performance (
                datasetid, sccode, sessionyear, examid, classname, sectionname, subjectid, teacherid,
                total_students, total_pass, total_fail, pass_rate, average_gpa, average_marks,
                highest_gpa, lowest_gpa, a_plus_count
            )
            SELECT
                ? AS datasetid,
                ss.sccode,
                ss.sessionyear,
                ? AS examid,
                ss.classname,
                ss.sectionname,
                ss.subcode AS subjectid,
                ss.tid AS teacherid,
                COUNT(DISTINCT sm.stid) AS total_students,
                SUM(CASE WHEN sm.gpa > 0 THEN 1 ELSE 0 END) AS total_pass,
                SUM(CASE WHEN sm.gpa = 0 THEN 1 ELSE 0 END) AS total_fail,
                (SUM(CASE WHEN sm.gpa > 0 THEN 1 ELSE 0 END) * 100.0 / COUNT(DISTINCT sm.stid)) AS pass_rate,
                AVG(sm.gpa) AS average_gpa,
                AVG(sm.on100) AS average_marks,
                MAX(sm.gpa) AS highest_gpa,
                MIN(CASE WHEN sm.gpa > 0 THEN sm.gpa ELSE NULL END) AS lowest_gpa,
                SUM(CASE WHEN sm.gpa = 5.00 THEN 1 ELSE 0 END) AS a_plus_count
            FROM eimbox.subsetup ss
            JOIN eimbox.sessioninfo si ON ss.sccode = si.sccode AND ss.sessionyear = si.sessionyear AND ss.classname = si.classname AND ss.sectionname = si.sectionname
            JOIN eimbox.stmark sm ON si.stid = sm.stid AND ss.subcode = sm.subcode AND si.sccode = sm.sccode AND si.sessionyear = sm.sessionyear
            WHERE
                ss.sccode = ? AND ss.sessionyear = ? AND sm.exam = ?
            GROUP BY
                ss.sccode, ss.sessionyear, ss.classname, ss.sectionname, ss.subcode, ss.tid
            ON DUPLICATE KEY UPDATE
                total_students = VALUES(total_students), total_pass = VALUES(total_pass), total_fail = VALUES(total_fail),
                pass_rate = VALUES(pass_rate), average_gpa = VALUES(average_gpa), average_marks = VALUES(average_marks),
                highest_gpa = VALUES(highest_gpa), lowest_gpa = VALUES(lowest_gpa), a_plus_count = VALUES(a_plus_count)
        ";

        $stmt_main = $analytics_conn->prepare($main_query);
        $stmt_main->bind_param("iiiss", $datasetid, $dataset['examid'], $dataset['sccode'], $dataset['sessionyear'], $examtitle);
        $stmt_main->execute();

        // --- ধাপ ৩: জব সম্পন্ন হিসেবে মার্ক করা ---
        updateJobStatus($analytics_conn, $datasetid, 'Completed');
        updateJobProgress($analytics_conn, $datasetid, 100, 1, 1); // প্রোগ্রেস ১০০%

        return true;
    } catch (Exception $e) {
        // কোনো সমস্যা হলে জব ফেইল হিসেবে মার্ক করা
        updateJobStatus($analytics_conn, $datasetid, 'Failed', $e->getMessage());
        return false;
    }
}

// Helper ফাংশন (এগুলো একটি কমন ফাইলে রাখা যেতে পারে)
function updateJobStatus(mysqli $conn, int $datasetid, string $status, string $errmsg = '', bool $is_starting = false) {
    $finished_at_sql = ($status === 'Completed' || $status === 'Failed') ? ", finished_at = NOW()" : "";
    $started_at_sql = $is_starting ? ", started_at = NOW()" : "";

    $sql = "UPDATE analytics_jobs SET status = ?, errmsg = ? {$started_at_sql} {$finished_at_sql} WHERE datasetid = ? AND jobtype = 'teacher_performance'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $errmsg, $datasetid);
    $stmt->execute();
}

function updateJobProgress(mysqli $conn, int $datasetid, float $progress, int $totalstep, int $completedstep) {
    $stmt = $conn->prepare("UPDATE analytics_jobs SET progress = ?, totalstep = ?, completedstep = ? WHERE datasetid = ? AND jobtype = 'teacher_performance'");
    $stmt->bind_param("diii", $progress, $totalstep, $completedstep, $datasetid);
    $stmt->execute();
}
?>

