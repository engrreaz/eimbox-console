<?php
require_once '../core/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve POST data
    $stid = $_POST['stid'] ?? null;
    $stnameeng = trim($_POST['stnameeng'] ?? '');
    $stnameben = trim($_POST['stnameben'] ?? '');
    $fname = trim($_POST['fname'] ?? '');
    $mname = trim($_POST['mname'] ?? '');
    $sscroll = trim($_POST['rollno'] ?? '');
    $regdno = trim($_POST['regdno'] ?? '');
    $gpa = trim($_POST['gpa'] ?? '');

    // Basic validation
    if (empty($stid) || empty($sscroll) || empty($regdno)) {
        http_response_code(400);
        echo "Error: Missing required fields.";
        exit;
    }

    // Prepare the update statement
    $stmt = $conn->prepare("
        UPDATE students 
        SET 
            stnameeng = ?, stnameben = ?, fname = ?, mname = ?, 
            roll = ?, regdno = ?, gpa = ?
        WHERE 
            stid = ? AND sccode = ?
    ");

    $stmt->bind_param("sssssssss", $stnameeng, $stnameben, $fname, $mname, $sscroll, $regdno, $gpa, $stid, $sccode);

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>