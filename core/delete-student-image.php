<?php
include_once('core-val.php');

$file = $_GET['file'] ?? '';
$path = dirname(dirname(__DIR__)) . "/students/" . $file;

if (!$file || !file_exists($path)) {
    echo "File not found!";
    exit;
}

unlink($path);

echo "Image deleted successfully.";
