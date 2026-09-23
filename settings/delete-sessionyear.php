<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

$year = trim($_POST['year'] ?? '');

if (!empty($year)) {
    $year = $conn->real_escape_string($year);
    $conn->query("DELETE FROM sessionyear WHERE sccode='$sccode' AND syear='$year'");
    echo json_encode(['status' => 'success', 'message' => 'Session year deleted successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session year']);
}
?>
