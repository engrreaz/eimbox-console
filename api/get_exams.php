<?php
header('Content-Type: application/json');

// ডাটাবেস কানেকশন এবং সেশন শুরু করার জন্য প্রয়োজনীয় ফাইল
require_once '../core/config.php';
require_once '../core/db.php';
session_start();

// Get parameters and session data
$sccode = $_SESSION['sccode'] ?? 0;
$sessionyear = $_GET['sessionyear'] ?? 0;

if (!$sccode || !$sessionyear) {
    echo json_encode(['status' => 'error', 'message' => 'School Code or Session Year is missing.']);
    exit;
}

// Prepare and execute the query
// Assuming $conn is the main database connection from db.php
$stmt = $conn->prepare("SELECT id, examtitle FROM examlist WHERE sccode = ? AND sessionyear = ? ORDER BY id ASC");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Query preparation failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("is", $sccode, $sessionyear);
$stmt->execute();
$result = $stmt->get_result();

$exams = [];
while ($row = $result->fetch_assoc()) {
    $exams[] = $row;
}

$stmt->close();
$conn->close();

if (empty($exams)) {
    echo json_encode(['status' => 'error', 'message' => 'No exams found for this session.']);
} else {
    echo json_encode(['status' => 'success', 'exams' => $exams]);
}
?>