<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

if (empty($sccode)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or session expired']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$mode = trim($_POST['mode'] ?? 'add');

$sessionyear = trim($_POST['sessionyear'] ?? date('Y'));
$slot = trim($_POST['slot'] ?? 'School');
$examtitle = trim($_POST['examtitle'] ?? '');
$examcode = trim($_POST['examcode'] ?? '');
$classname = trim($_POST['classname'] ?? '');
$sectionname = trim($_POST['sectionname'] ?? '');
$datestart = !empty($_POST['datestart']) ? $_POST['datestart'] : date('Y-m-d');
$result_publish = !empty($_POST['result_publish']) ? str_replace('T', ' ', $_POST['result_publish']) : null;
$status = intval($_POST['status'] ?? 1);

if (empty($examtitle)) {
    echo json_encode(['status' => 'error', 'message' => 'Exam title is required']);
    exit;
}

if ($mode === 'add') {
    if (empty($examcode)) {
        $examcode = uniqid('ex_');
    }
    $stmt = $conn->prepare("INSERT INTO examlist 
        (sccode, sessionyear, slot, examtitle, examcode, classname, sectionname, datestart, result_publish, status, createdby, createtime, modifieddate) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("issssssssss", $sccode, $sessionyear, $slot, $examtitle, $examcode, $classname, $sectionname, $datestart, $result_publish, $status, $usr);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Exam added successfully', 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add exam: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    $stmt = $conn->prepare("UPDATE examlist SET 
        sessionyear = ?, 
        slot = ?, 
        examtitle = ?, 
        examcode = ?, 
        classname = ?, 
        sectionname = ?, 
        datestart = ?, 
        result_publish = ?, 
        status = ?, 
        modifieddate = NOW() 
        WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ssssssssiii", $sessionyear, $slot, $examtitle, $examcode, $classname, $sectionname, $datestart, $result_publish, $status, $id, $sccode);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Exam updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update exam: ' . $stmt->error]);
    }
    $stmt->close();
}