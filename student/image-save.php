<?php
include_once dirname(__DIR__) . '/core/core-val.php';
// -----------------------------------------
// Basic Security & File Upload Handler
// -----------------------------------------
if (!isset($_FILES['file'])) {
    echo "no_file";
    exit;
}

$stid = $_POST['stid'] ?? '';
if ($stid == "") {
    echo "no_stid";
    exit;
}

// Students folder (root/students/)
$folder = dirname(dirname(__DIR__)) . "/students/";
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

// -----------------------------------------
// CREATE SAFE FILENAME
// -----------------------------------------
$newName = $stid . ".jpg";
$targetFile = $folder . $newName;

// -----------------------------------------
// ACCEPT CROPPED BLOB / NORMAL FILE BOTH
// -----------------------------------------
$file_tmp = $_FILES['file']['tmp_name'];

// Validate Image
$mime = mime_content_type($file_tmp);
$allowed = ["image/jpeg", "image/jpg", "image/png"];

if (!in_array($mime, $allowed)) {
    echo "invalid_file";
    exit;
}

// -----------------------------------------
// JPG OUTPUT (For Consistency)
// -----------------------------------------
$imageData = file_get_contents($file_tmp);
$srcImage = imagecreatefromstring($imageData);

if (!$srcImage) {
    echo "corrupt";
    exit;
}

$w = imagesx($srcImage);
$h = imagesy($srcImage);

// Create new JPG image
$final = imagecreatetruecolor($w, $h);

// White BG for PNG uploads (no transparency)
$bg = imagecolorallocate($final, 255, 255, 255);
imagefill($final, 0, 0, $bg);

// Copy
imagecopy($final, $srcImage, 0, 0, 0, 0, $w, $h);

// Save as JPG
imagejpeg($final, $targetFile, 90);

// Free memory
// imagedestroy($final);
// imagedestroy($srcImage);

$final = null;
$srcImage = null;
// -----------------------------------------
// RETURN NEW FILE NAME
// -----------------------------------------
echo $newName;
exit;