<?php
/**
 * Step 0: Initialize Analysis
 *
 * - Fetches the name of the selected exam.
 * - Creates a new dataset record in the `analytics_datasets` table with 'Processing' status.
 * - Stores the new `dataset_id` in the session for subsequent steps to use.
 *
 * Variables from parent script (run_analysis_step.php):
 * @var mysqli $conn The database connection object.
 * @var string $sccode The school code from the session.
 * @var string $slot The selected slot (e.g., 'School', 'College').
 * @var string $sessionyear The selected session year.
 * @var array $examids Array of selected exam IDs.
 * @var string $examid_list_str Comma-separated string of exam IDs.
 */

// সরাসরি অ্যাক্সেস রোধ করার জন্য একটি নিরাপত্তা চেক
if (!isset($sccode) || !isset($examids)) {
    die("This script cannot be accessed directly.");
}

// ধাপ ২: অ্যানালাইসিস ডেটাসেটের জন্য একটি নাম তৈরি করা
$dataset_name = "Analysis for Exams (" . $examid_list_str . ") - " . $sessionyear . " (" . $slot . ")";
$status = 'Processing';
$created_by = $_SESSION['user_id'] ?? 0; // সেশন থেকে অ্যাডমিনের আইডি

// ধাপ ৩: `analytics_datasets` টেবিলে নতুন ডেটাসেট রেকর্ড ইনসার্ট করা
$insert_stmt = $conn->prepare(
    "INSERT INTO analytics_dataset (sccode, sessionyear, examid, slot, dataset_name, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)"
);
if (!$insert_stmt) {
    throw new Exception("Failed to prepare statement for analytics_datasets: " . $conn->error);
}
$insert_stmt->bind_param("ssssssi", $sccode, $sessionyear, $examid_list_str, $slot, $dataset_name, $status, $created_by);
$insert_stmt->execute();

$dataset_id = $insert_stmt->insert_id;
$insert_stmt->close();

// ধাপ ৪: নতুন তৈরি হওয়া ডেটাসেট আইডি সেশনে সংরক্ষণ করা
if ($dataset_id > 0) {
    $_SESSION['analytics_dataset_id'] = $dataset_id;
} else {
    throw new Exception("Failed to create a new dataset record.");
}

// The parent script (run_analysis_step.php) will automatically send a success JSON response
// if this script completes without throwing an exception.
?>