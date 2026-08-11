<?php 
/**
 * Step 2: Class Academic Index (CAI) Calculation
 *
 * এই স্ক্রিপ্টটি ধাপ-১ এ গণনাকৃত বিষয়-ভিত্তিক পারফরম্যান্স ডেটা ব্যবহার করে প্রতিটি ক্লাস ও সেকশনের সামগ্রিক পারফরম্যান্স গণনা করে।
 * এটি `analytics_subject_performance` টেবিল থেকে ডেটা নিয়ে ক্লাস-ভিত্তিক গড় (CAI) এবং অন্যান্য মেট্রিক্স গণনা করে।
 * গণনাকৃত ডেটা একটি নতুন টেবিল `analytics_class_performance`-এ সংরক্ষণ করা হয়।
 *
 * Variables available from the parent script (run_analysis_step.php):
 * @var mysqli $conn The database connection object.
 * @var string $sccode The school code from the session.
 * @var string $slot The selected slot.
 * @var string $sessionyear The selected session year.
 * @var int $examid The ID of the selected exam.
 */

// সরাসরি অ্যাক্সেস রোধ করার জন্য একটি নিরাপত্তা চেক
if (!isset($sccode) || !isset($examid_list_str)) {
    die("This script cannot be accessed directly. Required parameters are missing.");
}

// অ্যানালাইসিস ডেটাসেট আইডি সেশন থেকে আনা হচ্ছে।
$dataset_id = $_SESSION['analytics_dataset_id'] ?? 0;
if ($dataset_id === 0) {
    throw new Exception("Dataset ID not found in session. Please start from step 0.");
}

// মূল SQL কোয়েরি: এটি `analytics_subject_performance` থেকে ডেটা নিয়ে ক্লাস-ভিত্তিক অ্যাগ্রিগেট গণনা করে।
$query = "
    INSERT INTO analytics_class_performance (
        dataset_id, sccode, sessionyear, examid, classname, sectionname,
        total_subjects, avg_of_subject_averages, total_students_appeared, overall_marks_percentage, difficulty_factor, teacher_impact_index
    )
    SELECT
        ? AS dataset_id,
        sccode,
        sessionyear,
        ? AS examid, -- This will now be a string list of IDs
        classname,
        sectionname,
        COUNT(DISTINCT subject_code) AS total_subjects,
        COALESCE(AVG(marks_percentage), 0) AS avg_of_subject_averages, -- Average of subject percentages
        COALESCE(SUM(appeared_student_count), 0) AS total_students_appeared, -- Sum of students who appeared for exams in this class/section
        COALESCE(SUM(total_marks_obtained) / NULLIF(SUM(total_full_marks), 0) * 100, 0) AS overall_marks_percentage,
        100 - COALESCE(SUM(total_marks_obtained) / NULLIF(SUM(total_full_marks), 0) * 100, 0) AS difficulty_factor,
        1  + (100 - COALESCE(SUM(total_marks_obtained) / NULLIF(SUM(total_full_marks), 0) * 100, 0)) / 100 AS teacher_impact_index
    FROM
        analytics_subject_performance
    WHERE
        dataset_id = ?
    GROUP BY
        sccode, sessionyear, classname, sectionname
    ON DUPLICATE KEY UPDATE
        total_subjects = VALUES(total_subjects),
        avg_of_subject_averages = VALUES(avg_of_subject_averages),
        total_students_appeared = VALUES(total_students_appeared),
        overall_marks_percentage = VALUES(overall_marks_percentage),
        difficulty_factor = VALUES(difficulty_factor),
        teacher_impact_index = VALUES(teacher_impact_index)
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    throw new Exception("Failed to prepare statement: " . $conn->error);
}

$stmt->bind_param("isi", $dataset_id, $examid_list_str, $dataset_id);
$stmt->execute();

?>
