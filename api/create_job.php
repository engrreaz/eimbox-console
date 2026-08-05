<?php
header('Content-Type: application/json');

// ডাটাবেস কানেকশন এবং সেশন শুরু করার জন্য প্রয়োজনীয় ফাইল
require_once '../core/config.php';
require_once '../core/db.php';
session_start();

// অ্যানালিটিক্স ডাটাবেসের জন্য নতুন কানেকশন
$analytics_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, 'eimbox_analytics', 3306);
if ($analytics_conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Analytics DB Connection failed.']);
    exit;
}

$sccode = $_SESSION['sccode'] ?? 0;
$userid = $_SESSION['user_id'] ?? 0;
$sessionyear = $_POST['sessionyear'] ?? 0;
$examid = $_POST['examid'] ?? 0;

if (!$sccode || !$sessionyear || !$examid) {
    echo json_encode(['status' => 'error', 'message' => 'Required parameters are missing.']);
    exit;
}

$analytics_conn->begin_transaction();

try {
    // ধাপ ১: analytics_dataset টেবিলে ডেটাসেট তৈরি বা খুঁজে বের করা
    $dataset_name = "Teacher Performance for Exam ID: $examid, Session: $sessionyear";

    // প্রথমে চেক করা হচ্ছে ডেটাসেটটি আগে থেকেই আছে কিনা
    $stmt = $analytics_conn->prepare("SELECT datasetid FROM analytics_dataset WHERE sccode = ? AND sessionyear = ? AND examid = ?");
    $stmt->bind_param("iii", $sccode, $sessionyear, $examid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $datasetid = $result->fetch_assoc()['datasetid'];
    } else {
        // নতুন ডেটাসেট তৈরি করা
        $stmt = $analytics_conn->prepare(
            "INSERT INTO analytics_dataset (sccode, sessionyear, examid, dataset_name, createdby) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iiisi", $sccode, $sessionyear, $examid, $dataset_name, $userid);
        $stmt->execute();
        $datasetid = $stmt->insert_id;
    }
    $stmt->close();

    // ধাপ ২: analytics_jobs টেবিলে নতুন জব তৈরি করা
    $jobtype = 'teacher_performance';
    $stmt = $analytics_conn->prepare(
        "INSERT INTO analytics_jobs (datasetid, jobtype, status) VALUES (?, ?, 'Pending')"
    );
    $stmt->bind_param("is", $datasetid, $jobtype);
    $stmt->execute();
    $jobid = $stmt->insert_id;
    $stmt->close();

    // সব ঠিক থাকলে কমিট করা
    $analytics_conn->commit();

    echo json_encode(['status' => 'success', 'message' => 'Job created successfully.', 'jobid' => $jobid, 'datasetid' => $datasetid]);

} catch (Exception $e) {
    $analytics_conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Failed to create job: ' . $e->getMessage()]);
}

$analytics_conn->close();
?>