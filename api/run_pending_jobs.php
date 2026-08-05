<?php
// jobs/run_pending_jobs.php

// এই স্ক্রিপ্টটি ক্রন জব (Cron Job) হিসেবে চালানো যেতে পারে।
set_time_limit(0); // কোনো টাইম লিমিট ছাড়া চলবে

require_once '../core/config.php';
require_once '../core/db.php'; // main $conn
require_once 'process_teacher_performance.php'; // আমাদের তৈরি করা প্রসেসিং ইঞ্জিন

$analytics_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, 'eimbox_analytics', 3306);
if ($analytics_conn->connect_error) {
    // লগ ফাইলে এরর লেখা যেতে পারে
    die("Analytics DB Connection Failed: " . $analytics_conn->connect_error);
}

echo "Checking for pending jobs...\n";

// শুধুমাত্র 'teacher_performance' টাইপের পেন্ডিং জবগুলো আনা হচ্ছে
$result = $analytics_conn->query(
    "SELECT jobid, datasetid FROM analytics_jobs WHERE status = 'Pending' AND jobtype = 'teacher_performance' ORDER BY created_at ASC"
);

if ($result->num_rows === 0) {
    echo "No pending jobs found.\n";
    exit;
}

while ($job = $result->fetch_assoc()) {
    $jobid = $job['jobid'];
    $datasetid = $job['datasetid'];

    echo "Processing Job ID: $jobid for Dataset ID: $datasetid...\n";

    // জব স্ট্যাটাস 'Running' করা
    $stmt = $analytics_conn->prepare("UPDATE analytics_jobs SET status = 'Running', started_at = NOW() WHERE jobid = ?");
    $stmt->bind_param("i", $jobid);
    $stmt->execute();
    $stmt->close();

    // মূল প্রসেসিং ফাংশন কল করা
    $success = processTeacherPerformance($conn, $analytics_conn, $datasetid);

    echo "Job ID: $jobid finished with status: " . ($success ? "Completed" : "Failed") . "\n";
}

echo "All pending jobs processed.\n";
$analytics_conn->close();
?>