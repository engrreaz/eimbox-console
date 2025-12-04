<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'] ?? '';
$session = $_POST['session'] ?? '';
$cls = $_POST['sls'] ?? '';   // classname
$sec = $_POST['sec'] ?? '';   // sectionname

if (!$slot || !$session) {
    echo json_encode(['success' => false, 'message' => 'Slot or Session missing']);
    exit;
}

// Base condition
$where = "slot='$slot' AND sessionyear='$session' AND sccode='$sccode'";

// Add class filter if provided
if ($cls != '') {
    $where .= " AND classname='$cls'";
}

// Add section filter if provided
if ($sec != '') {
    $where .= " AND sectionname='$sec'";
}

// Final SQL
$sql = "UPDATE sessioninfo SET grand_merged = 0 WHERE $where";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Reset successful']);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}
