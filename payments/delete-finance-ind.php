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

$rowid = intval($_POST['rowid'] ?? 0);
$stid = trim($_POST['stid'] ?? '');
$itemcode = trim($_POST['itemcode'] ?? '');
$cls = trim($_POST['cls'] ?? '');
$sec = trim($_POST['sec'] ?? '');
$sy = trim($_POST['session'] ?? date('Y'));

if ($rowid <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid concession record ID']);
    exit;
}

// 1. Get itemcode and stid if not provided
if (empty($itemcode) || empty($stid)) {
    $infoStmt = $conn->prepare("SELECT itemcode, stid, sessionyear, classname, sectionname FROM financesetupind WHERE id = ? AND sccode = ? LIMIT 1");
    if ($infoStmt) {
        $infoStmt->bind_param("ii", $rowid, $sccode);
        $infoStmt->execute();
        $infoRes = $infoStmt->get_result();
        if ($irow = $infoRes->fetch_assoc()) {
            $itemcode = $irow['itemcode'];
            $stid = $irow['stid'];
            $cls = $irow['classname'] ?: $cls;
            $sec = $irow['sectionname'] ?: $sec;
            $sy = $irow['sessionyear'] ?: $sy;
        }
        $infoStmt->close();
    }
}

// 2. Delete the record from financesetupind
$delStmt = $conn->prepare("DELETE FROM financesetupind WHERE id = ? AND sccode = ?");
if (!$delStmt) {
    echo json_encode(['status' => 'error', 'message' => 'Database prepare error: ' . $conn->error]);
    exit;
}

$delStmt->bind_param("ii", $rowid, $sccode);
if ($delStmt->execute()) {
    $delStmt->close();

    // 3. Find fallback default amount from financesetupvalue
    $default_amount = 0;
    $valQuery = "
        SELECT amount
        FROM financesetupvalue
        WHERE sccode = ?
          AND sessionyear LIKE ?
          AND itemcode = ?
          AND ((sectionname = ? OR sectionname IS NULL OR sectionname = '')
               AND (classname = ? OR classname IS NULL OR classname = ''))
        ORDER BY 
            CASE WHEN sectionname = ? THEN 3 WHEN sectionname IS NULL OR sectionname = '' THEN 2 ELSE 1 END DESC,
            CASE WHEN classname = ? THEN 3 WHEN classname IS NULL OR classname = '' THEN 2 ELSE 1 END DESC,
            id DESC
        LIMIT 1
    ";
    $valStmt = $conn->prepare($valQuery);
    if ($valStmt) {
        $syParam = "%$sy%";
        $valStmt->bind_param("issssss", $sccode, $syParam, $itemcode, $sec, $cls, $sec, $cls);
        $valStmt->execute();
        $valRes = $valStmt->get_result();
        if ($vrow = $valRes->fetch_assoc()) {
            $default_amount = (int)$vrow['amount'];
        }
        $valStmt->close();
    }

    echo json_encode([
        'status' => 'success',
        'default_amount' => $default_amount,
        'tag' => 'DEF',
        'itemcode' => $itemcode,
        'message' => 'Individual concession removed. Reverted to class default amount.'
    ]);
} else {
    $err = $delStmt->error;
    $delStmt->close();
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete concession: ' . $err]);
}
