<?php
ob_clean();
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'];
$session = $_POST['session'];
$exam = $_POST['exam'];
$class = $_POST['class'];
$section = $_POST['section'];
$subject = $_POST['subject'];


$where = "sccode='$sccode' AND slot='$slot' AND sessionyear='$session' ";

if (!empty($class)) {
    $where .= " AND classname='$class'";
}
if (!empty($section)) {
    $where .= " AND sectionname='$section'";
}
if (!empty($subject)) {
    $where .= " AND subject='$subject'";
}

$q_str = "SELECT DISTINCT stid FROM stmark WHERE $where ";
$q = mysqli_query($conn, $q_str);

$total = mysqli_num_rows($q);
// Save student IDs into array (session storage)
$students = [];
while ($r = mysqli_fetch_assoc($q)) {
    $students[] = $r['stid'];
}


$_SESSION['merge_students'] = $students;

ob_clean();
echo json_encode([
    "total" => $total,
    "cry" => $q_str
]);
exit;