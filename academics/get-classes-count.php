<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sccode = $_SESSION['sccode'];
$sessions = $_POST['sessions'] ?? [];


$class  = $_POST['class'];
$sccode = $_SESSION['sccode'];

$q = mysqli_query($conn,"
    SELECT COUNT(id) as total
    FROM students
    WHERE sccode='$sccode'
    AND class='$class'
    AND status=1
");

echo json_encode(mysqli_fetch_assoc($q));
