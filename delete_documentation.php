<?php
ob_start(); // Output buffering চালু
require_once 'header.php';

// POST ছাড়া সরাসরি access এ redirect
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: documentation_list.php'); 
    exit; 
}

$id = intval($_POST['id'] ?? 0);
if ($id) {
    $stmt = $conn->prepare("DELETE FROM project_documentation WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Redirect safely
header('Location: documentation_list.php');
exit;
ob_end_flush(); // অবশিষ্ট buffered output পাঠানো