<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'] ?? '';
$session = $_POST['session'] ?? '';

if (!$slot || !$session) {
    echo json_encode(['success' => false, 'message' => 'Slot or Session missing']);
    exit;
}

// Reset grand_merged
$sql = "UPDATE sessioninfo SET grand_merged = 0 WHERE slot='$slot' AND sessionyear='$session' AND sccode='$sccode'";
if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Reset successful']);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}