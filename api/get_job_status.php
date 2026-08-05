<?php
header('Content-Type: application/json');

require_once '../core/config.php';

$jobid = $_GET['jobid'] ?? 0;

if (!$jobid) {
    echo json_encode(['status' => 'error', 'message' => 'Job ID is required.']);
    exit;
}

$analytics_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, 'eimbox_analytics', 3306);
if ($analytics_conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Analytics DB Connection failed.']);
    exit;
}

$stmt = $analytics_conn->prepare("SELECT jobid, status, progress, errmsg, started_at, finished_at FROM analytics_jobs WHERE jobid = ?");
$stmt->bind_param("i", $jobid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $job = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'job' => $job]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Job not found.']);
}

$stmt->close();
$analytics_conn->close();
?>