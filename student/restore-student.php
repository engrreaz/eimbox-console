<?php
require_once __DIR__ . '/../core/init.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized access";
    exit;
}

$stid = $_POST['stid'] ?? '';

if (empty($stid)) {
    http_response_code(400);
    echo "Student ID is required";
    exit;
}

// Restore student status to 1
$stmt = $conn->prepare("UPDATE sessioninfo SET status = 1, modifieddate = NOW() WHERE stid = ? AND sccode = ?");
$stmt->bind_param("si", $stid, $sccode);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

$stmt2 = $conn->prepare("UPDATE students SET modifieddate = NOW() WHERE stid = ? AND sccode = ?");
if ($stmt2) {
    $stmt2->bind_param("si", $stid, $sccode);
    $stmt2->execute();
    $stmt2->close();
}

if ($affected > 0) {
    echo "Student restored successfully.";
} else {
    echo "Student restored (or already active).";
}
