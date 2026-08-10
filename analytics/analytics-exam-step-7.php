<?php
/**
 * Step 7: Combined Difficulty Index (CDI) Calculation
 *
 * এই স্ক্রিপ্টটি প্রতিটি ক্লাসের প্রতিটি বিষয়ের জন্য একটি সমন্বিত কাঠিন্য সূচক (CDI) গণনা করে।
 * CDI = (CDF × WC) + (SDF × WS)
 * যেখানে,
 * CDF = Class Difficulty Factor (analytics_class_performance.difficulty_factor)
 * SDF = Subject Difficulty Factor (analytics_overall_subject_performance.subject_difficulty_factor)
 * WC = Class Weight (ধরা যাক 0.4)
 * WS = Subject Weight (ধরা যাক 0.6)
 *
 * ফলাফল `analytics_subject_performance` টেবিলে `cdi` কলামে আপডেট করা হয়।
 *
 * Variables available from the parent script (run_analysis_step.php):
 * @var mysqli $conn The database connection object.
 * @var int $dataset_id The ID of the current analysis dataset.
 */

// সরাসরি অ্যাক্সেস রোধ করার জন্য একটি নিরাপত্তা চেক
if (!isset($dataset_id)) {
    die("This script cannot be accessed directly.");
}

// ওজন (Weights) নির্ধারণ
$class_weight_val = 0.4;
$subject_weight_val = 0.6;

// মূল কোয়েরি: CDI গণনা এবং `analytics_subject_performance` টেবিল আপডেট করা
$query = "
    UPDATE analytics_subject_performance AS asp
    JOIN analytics_class_performance AS acp
        ON asp.dataset_id = acp.dataset_id
        AND asp.classname COLLATE utf8mb4_unicode_ci = acp.classname COLLATE utf8mb4_unicode_ci
        AND asp.sectionname COLLATE utf8mb4_unicode_ci = acp.sectionname COLLATE utf8mb4_unicode_ci
    JOIN analytics_overall_subject_performance AS aosp
        ON asp.dataset_id = aosp.dataset_id
        AND asp.subject_code = aosp.subject_code
    SET
        asp.cdi = (COALESCE(acp.difficulty_factor, 0) * ?) + (COALESCE(aosp.subject_difficulty_factor, 0) * ?)+1
    WHERE
        asp.dataset_id = ?;
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    throw new Exception("Failed to prepare statement for CDI calculation: " . $conn->error);
}

$stmt->bind_param("ddi", $class_weight_val, $subject_weight_val, $dataset_id);

if (!$stmt->execute()) {
    throw new Exception("Failed to execute CDI calculation: " . $stmt->error);
}

$stmt->close();

?>