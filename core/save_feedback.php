<?php
// ডাটাবেজ কনফিগ
include_once('config.php');
include_once('db.php');

// POST ডেটা রিসিভ
$rating   = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : '';

if ($rating > 0 && !empty($feedback)) {
    $stmt = $conn->prepare("INSERT INTO feedbacks (rating, feedback, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $rating, $feedback);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "invalid input";
}

$conn->close();
