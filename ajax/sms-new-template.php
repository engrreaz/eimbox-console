<?php
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

$type  = mysqli_real_escape_string($conn, $_POST['temp_type']);
$title = mysqli_real_escape_string($conn, $_POST['temp_title']);
$text  = mysqli_real_escape_string($conn, $_POST['temp_text']);



$q = "INSERT INTO sms_templete (sccode, temp_type, temp_title, temp_text, created_by, created_time)
      VALUES ('$sccode', '$type', '$title', '$text', '$usr', '$cur')";

if(mysqli_query($conn, $q)){
    echo "SUCCESS";
} else {
    echo "ERROR";
}
