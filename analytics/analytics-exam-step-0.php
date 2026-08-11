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

// ******************************************************************
// পূর্ববর্তী ডেটা মুছে ফেলার লজিক
// একই sccode, sessionyear, examid_list_str, slot এর জন্য যদি কোনো পূর্ববর্তী ডেটাসেট থাকে,
// তাহলে সেই ডেটাসেটের সাথে সম্পর্কিত সমস্ত ডেটা মুছে ফেলা হবে।
// ******************************************************************

// প্রথমে, বর্তমান প্যারামিটারগুলোর সাথে মিলে যাওয়া dataset_id গুলো খুঁজে বের করি
$find_old_datasets_sql = "SELECT datasetid FROM analytics_dataset WHERE sccode = ? AND sessionyear = ? AND examid = ? AND slot = ?";
$stmt_find_old = $conn->prepare($find_old_datasets_sql);
if (!$stmt_find_old) {
    throw new Exception("Failed to prepare statement for finding old datasets: " . $conn->error);
}
$stmt_find_old->bind_param("ssss", $sccode, $sessionyear, $examid_list_str, $slot);
$stmt_find_old->execute();
$old_datasets_result = $stmt_find_old->get_result();
$old_dataset_ids = [];
while ($row = $old_datasets_result->fetch_assoc()) {
    $old_dataset_ids[] = $row['datasetid'];
}
$stmt_find_old->close();

if (!empty($old_dataset_ids)) {
    $ids_to_delete = implode(',', $old_dataset_ids);

    // সংশ্লিষ্ট টেবিলগুলো থেকে ডেটা মুছে ফেলি
    $tables_to_clear = [
        'analytics_at_risk_students',
        'analytics_class_performance',
        'analytics_overall_subject_performance',
        'analytics_student_performance',
        'analytics_subject_performance',
        'analytics_teacher_performance'
    ];
    foreach ($tables_to_clear as $table) {
        $conn->query("DELETE FROM $table WHERE dataset_id IN ($ids_to_delete)");
    }
    // analytics_dataset টেবিল থেকে পুরনো রেকর্ডগুলো মুছে ফেলি
    $conn->query("DELETE FROM analytics_dataset WHERE datasetid IN ($ids_to_delete)");
}


// ******************************************************************
// পূর্ববর্তী ডেটা মুছে ফেলার লজিক
// একই sccode, sessionyear, examid_list_str, slot এর জন্য যদি কোনো পূর্ববর্তী ডেটাসেট থাকে,
// তাহলে সেই ডেটাসেটের সাথে সম্পর্কিত সমস্ত ডেটা মুছে ফেলা হবে।
// ******************************************************************




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