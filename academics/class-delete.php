<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$id     = intval($_POST['id']);
$sccode = $_SESSION['sccode'];

$q = mysqli_query($conn,"
    DELETE FROM areas 
    WHERE id='$id'
    AND sccode='$sccode'
");

if(mysqli_affected_rows($conn)){
    echo json_encode(['status'=>'ok']);
}else{
    echo json_encode(['status'=>'err','msg'=>'Delete failed']);
}
