<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$accno = trim($_POST['accno'] ?? '');
$acctype = trim($_POST['acctype'] ?? 'General');
$bankname = trim($_POST['bankname'] ?? '');
$branch = trim($_POST['branch'] ?? '');
$slot = trim($_POST['slot'] ?? '');
$routing_no = trim($_POST['routing_no'] ?? '');
$opening_balance = floatval($_POST['opening_balance'] ?? 0);
$openingdate = trim($_POST['openingdate'] ?? '');
$closingdate = trim($_POST['closingdate'] ?? '');
$is_closed = intval($_POST['is_closed'] ?? 0);

if (empty($accno) || empty($bankname)) {
    echo json_encode(['status' => 'error', 'message' => 'Account Number and Bank Name are required.']);
    exit;
}

$openingdate_val = (!empty($openingdate) && $openingdate != '0000-00-00') ? $openingdate : null;

// Determine status and closingdate
if ($is_closed == 1 || (!empty($closingdate) && $closingdate != '0000-00-00')) {
    $status = 0;
    $closingdate_val = (!empty($closingdate) && $closingdate != '0000-00-00') ? $closingdate : date('Y-m-d');
} else {
    $status = 1;
    $closingdate_val = null;
}

if ($id <= 0) {
    // Check duplicate account number for same institution
    $chk = $conn->prepare("SELECT id FROM bankinfo WHERE sccode = ? AND accno = ?");
    $chk->bind_param("is", $sccode, $accno);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $chk->close();
        echo json_encode(['status' => 'error', 'message' => 'An account with this account number already exists.']);
        exit;
    }
    $chk->close();

    // ADD NEW ACCOUNT
    $stmt = $conn->prepare("INSERT INTO bankinfo 
        (sccode, slot, accno, acctype, bankname, branch, routing_no, opening_balance, openingdate, closingdate, status, modifieddate)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issssssdssi", $sccode, $slot, $accno, $acctype, $bankname, $branch, $routing_no, $opening_balance, $openingdate_val, $closingdate_val, $status);
    
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'New Bank Account created successfully.', 'id' => $new_id]);
    } else {
        $err = $stmt->error;
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to create account: ' . $err]);
    }
} else {
    // UPDATE EXISTING ACCOUNT
    $stmt = $conn->prepare("UPDATE bankinfo SET 
        slot = ?,
        accno = ?,
        acctype = ?,
        bankname = ?,
        branch = ?,
        routing_no = ?,
        opening_balance = ?,
        openingdate = ?,
        closingdate = ?,
        status = ?,
        modifieddate = NOW()
        WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ssssssdsiiii", $slot, $accno, $acctype, $bankname, $branch, $routing_no, $opening_balance, $openingdate_val, $closingdate_val, $status, $id, $sccode);

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['status' => 'success', 'message' => 'Bank Account updated successfully.', 'id' => $id]);
    } else {
        $err = $stmt->error;
        $stmt->close();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update account: ' . $err]);
    }
}
