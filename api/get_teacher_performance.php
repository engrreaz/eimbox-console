<?php
header('Content-Type: application/json');

require_once '../core/config.php';
session_start();

$analytics_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, 'eimbox_analytics', 3306);
if ($analytics_conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Analytics DB Connection failed.']);
    exit;
}

$sccode = $_SESSION['sccode'] ?? 0;
$sessionyear = $_GET['sessionyear'] ?? 0;
$examid = $_GET['examid'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;
$user_level = $_SESSION['userlevel'] ?? 'Guest';
$is_admin = $_SESSION['is_admin'] ?? 0;

if (!$sccode || !$sessionyear || !$examid) {
    echo json_encode(['status' => 'error', 'message' => 'Required parameters are missing.']);
    exit;
}

// প্রথমে ডেটাসেট আইডি খুঁজে বের করা
$stmt_dataset = $analytics_conn->prepare("SELECT datasetid FROM analytics_dataset WHERE sccode = ? AND sessionyear = ? AND examid = ? LIMIT 1");
$stmt_dataset->bind_param("iii", $sccode, $sessionyear, $examid);
$stmt_dataset->execute();
$dataset_result = $stmt_dataset->get_result();

if ($dataset_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No analysis data found for this selection. Please run the analysis first.']);
    exit;
}
$datasetid = $dataset_result->fetch_assoc()['datasetid'];

// পারফরম্যান্স ডেটা আনা
$query = "
    SELECT 
        tp.*,
        t.tname AS teacher_name,
        s.subject AS subject_name
    FROM analytics_teacher_performance tp
    LEFT JOIN eimbox.teacher t ON tp.teacherid = t.tid AND tp.sccode = t.sccode
    LEFT JOIN eimbox.subjects s ON tp.subjectid = s.subcode
    WHERE tp.datasetid = ? 
";

$params = [$datasetid];
$types = "i";

// যদি ব্যবহারকারী অ্যাডমিন না হন (ধরে নিচ্ছি is_admin < 4 মানে সাধারণ ব্যবহারকারী)
if ($is_admin < 4) {
    $query .= " AND tp.teacherid = ?";
    $params[] = $user_id;
    $types .= "i";
}

$query .= " ORDER BY t.tname, tp.classname, s.subject";

$stmt = $analytics_conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode(['status' => 'success', 'data' => $result]);

$stmt->close();
$analytics_conn->close();
?>