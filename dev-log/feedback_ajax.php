<?php
require_once '../core/config.php';
require_once '../core/db.php';

$page_name = $_POST['page_name'] ?? '';
$feature_name = $_POST['feature_name'] ?? '';
$status_name = $_POST['status_name'] ?? '';
$feedback_type = $_POST['feedback_type'] ?? '';
if($feedback_type==='Other') $feedback_type = $_POST['feedback_other'] ?? '';
$feedback_notes = $_POST['feedback_notes'] ?? '';
$rating = intval($_POST['rating'] ?? 0);
$logged_by = 'User'; // session username হলে সেট করুন

$stmt = $conn->prepare("
    INSERT INTO page_feedback 
    (page_name, feature_name, status_name, feedback_type, notes, rating, logged_by)
    VALUES (?,?,?,?,?,?,?)
");
$stmt->bind_param("sssssis",$page_name,$feature_name,$status_name,$feedback_type,$feedback_notes,$rating,$logged_by);
if($stmt->execute()) $resp = ['success'=>true];
else $resp = ['success'=>false,'message'=>$stmt->error];
$stmt->close();

echo json_encode($resp);
