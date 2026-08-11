<?php
/**
 * Step 8a: Teacher Subject Performance Index (TSPI) Calculation
 *
 * এই স্ক্রিপ্টটি প্রতিটি ক্লাস-বিষয়ের জন্য শিক্ষকের পারফরম্যান্স ইনডেক্স (TSPI) গণনা করে।
 * TSPI = (marks_percentage * 0.5) + (pass_rate * 0.5)
 * ফলাফল `analytics_subject_performance` টেবিলের `tspi` কলামে আপডেট করা হয়।
 *
 * @var mysqli $conn
 * @var int $dataset_id
 */

// সরাসরি অ্যাক্সেস রোধ করার জন্য একটি নিরাপত্তা চেক
if (!isset($dataset_id)) {
    die("This script cannot be accessed directly.");
}

// ওজন নির্ধারণ
$marks_weight = 0.5;
$pass_rate_weight = 0.5;

// TSPI গণনা এবং টেবিল আপডেট করার কোয়েরি
$query = "
    UPDATE analytics_subject_performance
    SET
        tspi = (COALESCE(marks_percentage, 0) * ?) + (COALESCE(pass_rate, 0) * ?)
    WHERE
        dataset_id = ?;
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    throw new Exception("Failed to prepare statement for TSPI calculation: " . $conn->error);
}

$stmt->bind_param("ddi", $marks_weight, $pass_rate_weight, $dataset_id);

if (!$stmt->execute()) {
    throw new Exception("Failed to execute TSPI calculation: " . $stmt->error);
}

$stmt->close();
?>