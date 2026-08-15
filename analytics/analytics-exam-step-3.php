<?php 
/**
 * Step 3: Subject Performance Index (SPI) Calculation
 *
 * এই স্ক্রিপ্টটি প্রতিটি বিষয়ের সামগ্রিক পারফরম্যান্স (সকল ক্লাস ও সেকশন মিলিয়ে) গণনা করে।
 * এটি `analytics_subject_performance` টেবিল থেকে ডেটা একত্রিত করে প্রতিটি বিষয়ের জন্য একটি গড় স্কোর (SPI) তৈরি করে।
 * গণনাকৃত ডেটা `analytics_overall_subject_performance` টেবিলে সংরক্ষণ করা হয়।
 *
 * Variables available from the parent script (run_analysis_step.php):
 * @var mysqli $conn The database connection object.
 * @var string $sccode The school code from the session.
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

// মূল SQL কোয়েরি: এটি `analytics_subject_performance` থেকে ডেটা নিয়ে বিষয়-ভিত্তিক সামগ্রিক অ্যাগ্রিগেট গণনা করে।
$query = "
    INSERT INTO analytics_overall_subject_performance (
        dataset_id, sccode, sessionyear, examid, subject_code,
        total_students_appeared, overall_avg_marks, overall_marks_percentage,
        difficulty_factor, fail_count, failure_rate, median, variance, std_deviation, low_gpa_ratio
    )
    SELECT
        ? AS dataset_id,
        sccode,
        sessionyear,
        ? AS examid, -- This will now be a string list of IDs
        subject_code,
        COALESCE(SUM(appeared_student_count), 0) AS total_students_appeared, -- মোট ছাত্র যারা পরীক্ষায় অংশ নিয়েছে
        COALESCE(SUM(total_marks_obtained) / NULLIF(SUM(appeared_student_count), 0), 0) AS overall_avg_marks, -- সামগ্রিক গড় নম্বর (যারা অংশ নিয়েছে তাদের মধ্যে)
        COALESCE(SUM(total_marks_obtained) / NULLIF(SUM(total_full_marks), 0) * 100, 0) AS overall_marks_percentage, -- সামগ্রিক নম্বরের হার
        100 - COALESCE(SUM(total_marks_obtained) / NULLIF(SUM(total_full_marks), 0) * 100, 0) AS difficulty_factor, -- কাঠিন্য ফ্যাক্টর
        COALESCE(SUM(fail_count), 0) AS fail_count, -- মোট ফেল
        COALESCE(SUM(fail_count) * 100 / NULLIF(SUM(appeared_student_count), 0), 0) AS failure_rate, -- ফেলের হার (যারা অংশ নিয়েছে তাদের মধ্যে) - Corrected
        -- Median Calculation: This is a simplified median for grouped data.
        -- For a more precise median, a more complex query or procedural approach would be needed.
        COALESCE(AVG(marks_percentage), 0) AS median, -- Using average of percentages as an approximation for median
        COALESCE(AVG(variance), 0) AS variance, -- ভ্যারিয়েন্স (গড়)
        COALESCE(AVG(std_deviation), 0) AS std_deviation, -- স্ট্যান্ডার্ড ডেভিয়েশন (গড়)
        -- Low Performance Ratio Calculation (assuming marks < 50% is low performance) - Corrected
        COALESCE( -- Low performance ratio (among those who appeared)
            SUM(CASE WHEN marks_percentage < 50 THEN appeared_student_count ELSE 0 END) * 100 / NULLIF(SUM(appeared_student_count), 0),
            0
        ) AS low_gpa_ratio
    FROM
        analytics_subject_performance
    WHERE
        dataset_id = ?
    GROUP BY
        sccode, sessionyear, subject_code
    ON DUPLICATE KEY UPDATE
        total_students_appeared = VALUES(total_students_appeared),
        overall_avg_marks = VALUES(overall_avg_marks),
        overall_marks_percentage = VALUES(overall_marks_percentage),
        difficulty_factor = VALUES(difficulty_factor),
        fail_count = VALUES(fail_count),
        failure_rate = VALUES(failure_rate),
        median = VALUES(median),
        variance = VALUES(variance),
        std_deviation = VALUES(std_deviation),
        low_gpa_ratio = VALUES(low_gpa_ratio)
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    throw new Exception("Failed to prepare statement: " . $conn->error);
}

$stmt->bind_param("isi", $dataset_id, $examid_list_str, $dataset_id);
$stmt->execute();

?>