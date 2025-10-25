<?php
header('Content-Type: application/json');

// JSON input পড়া
$id = $_POST['id'] ?? 0;
$content = $_POST['content'] ?? 0;


echo "Bangladesh<br>";
echo "Received ID: " . htmlspecialchars($id);

echo $content;
// কিছু ডেটা প্রসেসিং
if ($id > 0) {
    // উদাহরণ: নতুন content generate করা
    $response = [
        'success' => true,
        'message' => "Content loaded for ID $id",
        'html' => "<p>New content for card ID $id at ".date('H:i:s')."</p>"
    ];
} else {
    $response = [
        'success' => false,
        'message' => "Invalid ID"
    ];
}

echo json_encode($response);
exit;
