<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

$year = trim($_POST['year'] ?? '');

if (!empty($year)) {
    $year = $conn->real_escape_string($year);
    $chk = $conn->query("SELECT id FROM sessionyear WHERE sccode='$sccode' AND syear='$year' LIMIT 1");
    if ($chk && $chk->num_rows > 0) {
        $conn->query("UPDATE sessionyear SET active=1 WHERE sccode='$sccode' AND syear='$year'");
    } else {
        $conn->query("INSERT INTO sessionyear (sccode, syear, active) VALUES ('$sccode', '$year', 1)");
    }
    echo json_encode(['status' => 'success', 'message' => 'Session year added successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session year']);
}
?>
