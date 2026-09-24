<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

if (empty($_SESSION['user_id']) || empty($sccode)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or session expired']);
    exit;
}

$id = intval($_POST['rowid'] ?? 0);
$slot = trim($_POST['slot'] ?? 'School');
$sy = trim($_POST['session'] ?? $_POST['sessionyear'] ?? date('Y'));
$item = trim($_POST['itemcode'] ?? '');
$cls = trim($_POST['cls'] ?? $_POST['classname'] ?? '');
$sec = trim($_POST['sec'] ?? $_POST['sectionname'] ?? '');
$amt = intval($_POST['amount'] ?? 0);
$tag = trim($_POST['tag'] ?? '');
$stid = trim($_POST['stid'] ?? '');

if (empty($stid) || empty($item)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing student ID or Item Code']);
    exit;
}

// Check if a record already exists in financesetupind for this student and itemcode
$existing_id = 0;
$checkStmt = $conn->prepare("SELECT id FROM financesetupind WHERE sccode = ? AND sessionyear LIKE ? AND stid = ? AND itemcode = ? LIMIT 1");
if ($checkStmt) {
    $syParam = "%$sy%";
    $checkStmt->bind_param("isss", $sccode, $syParam, $stid, $item);
    $checkStmt->execute();
    $checkRes = $checkStmt->get_result();
    if ($row = $checkRes->fetch_assoc()) {
        $existing_id = (int)$row['id'];
    }
    $checkStmt->close();
}

$target_id = ($id > 0) ? $id : $existing_id;

if ($target_id > 0) {
    // UPDATE existing individual record
    $updateStmt = $conn->prepare("UPDATE financesetupind SET amount = ?, classname = ?, sectionname = ?, slot = ?, update_time = ?, modifieddate = ? WHERE id = ? AND sccode = ? AND stid = ?");
    if ($updateStmt) {
        $updateStmt->bind_param("isssssiis", $amt, $cls, $sec, $slot, $cur, $cur, $target_id, $sccode, $stid);
        if ($updateStmt->execute()) {
            $updateStmt->close();
            echo json_encode([
                'status' => 'success',
                'rowid' => $target_id,
                'amount' => $amt,
                'tag' => 'IND',
                'message' => 'Individual amount updated successfully'
            ]);
            exit;
        } else {
            $err = $updateStmt->error;
            $updateStmt->close();
            echo json_encode(['status' => 'error', 'message' => 'Failed to update: ' . $err]);
            exit;
        }
    }
} else {
    // INSERT new individual record
    $insertStmt = $conn->prepare("INSERT INTO financesetupind (sccode, slot, sessionyear, stid, itemcode, classname, sectionname, amount, update_time, modifieddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($insertStmt) {
        $insertStmt->bind_param("issssssiss", $sccode, $slot, $sy, $stid, $item, $cls, $sec, $amt, $cur, $cur);
        if ($insertStmt->execute()) {
            $new_id = $insertStmt->insert_id;
            $insertStmt->close();
            echo json_encode([
                'status' => 'success',
                'rowid' => $new_id,
                'amount' => $amt,
                'tag' => 'IND',
                'message' => 'Individual concession item saved successfully'
            ]);
            exit;
        } else {
            $err = $insertStmt->error;
            $insertStmt->close();
            echo json_encode(['status' => 'error', 'message' => 'Failed to insert: ' . $err]);
            exit;
        }
    }
}

echo json_encode(['status' => 'error', 'message' => 'Database operation could not be completed']);
