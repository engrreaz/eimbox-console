<?php
/**
 * Step 6: Subject Difficulty Factor (SDF) Calculation
 *
 * এই স্ক্রিপ্টটি প্রতিটি বিষয়ের সামগ্রিক কাঠিন্য ফ্যাক্টর (SDF) গণনা করে।
 * এটি `analytics_overall_subject_performance` টেবিল থেকে ডেটা নিয়ে `plan.md`-এ উল্লেখিত সূত্র অনুযায়ী
 * SDF গণনা করে এবং ফলাফল একই টেবিলে `subject_difficulty_factor` কলামে আপডেট করে।
 *
 * SDF Formula:
 * SDF = (0.40 * failure_rate) + (0.30 * (100 - overall_marks_percentage)) + (0.20 * std_deviation) + (0.10 * low_gpa_ratio)
 *
 * Variables available from the parent script (run_analysis_step.php):
 * @var mysqli $conn The database connection object.
 * @var int $dataset_id The ID of the current analysis dataset.
 */


// সরাসরি অ্যাক্সেস রোধ করার জন্য একটি নিরাপত্তা চেক
if (!isset($dataset_id)) {
    die("This script cannot be accessed directly.");
}


// ধাপ ২: SDF গণনা এবং টেবিল আপডেট করার কোয়েরি
$query = "
    UPDATE analytics_overall_subject_performance
    SET
        subject_difficulty_factor = (
            -- 1. Weight for Failure Rate: 35%
            (0.35 * COALESCE(failure_rate, 0)) +
            
            -- 2. Weight for Median Marks Deficit: 25%
            -- Using median instead of average for robustness against outliers.
            (0.25 * COALESCE((100 - (median / 100 * 100)), 0)) +
            
            -- 3. Weight for Coefficient of Variation (CV): 25%
            -- CV normalizes standard deviation, making it comparable across subjects.
            (0.25 * COALESCE((std_deviation / NULLIF(overall_avg_marks, 0)) * 100, 0)) +
            
            -- 4. Weight for Low GPA Ratio: 15%
            -- This value is now calculated in Step 3.
            (0.15 * COALESCE(low_gpa_ratio, 0))
        )
    WHERE
        dataset_id = ?;
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    throw new Exception("Failed to prepare statement for SDF calculation: " . $conn->error);
}

$stmt->bind_param("i", $dataset_id);

if (!$stmt->execute()) {
    throw new Exception("Failed to execute SDF calculation: " . $stmt->error);
}

$stmt->close();

?>