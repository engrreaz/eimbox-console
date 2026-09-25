<?php
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

$type  = mysqli_real_escape_string($conn, $_POST['temp_type'] ?? 'general');
$title = mysqli_real_escape_string($conn, $_POST['temp_title'] ?? '');
$text  = mysqli_real_escape_string($conn, $_POST['temp_text'] ?? '');
$audience = mysqli_real_escape_string($conn, $_POST['target_audience'] ?? 'student');
$lang = preg_match('/\p{Bengali}/u', $text) ? 'bn' : 'en';

if (empty($title) || empty($text)) {
    echo "Please provide both title and template text";
    exit;
}

$q = "INSERT INTO sms_templete 
      (sccode, temp_type, target_audience, temp_title, temp_text, language, is_default, status, created_by, created_time)
      VALUES 
      ('$sccode', '$type', '$audience', '$title', '$text', '$lang', 0, 1, '$usr', NOW())";

if (mysqli_query($conn, $q)) {
    echo "SUCCESS";
} else {
    echo "Error: " . mysqli_error($conn);
}
