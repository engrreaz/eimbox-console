<?php
/**
 * EIMBox - DBBL Rocket Bill Payment: Confirmation API
 * Endpoint: /api/payment/v1/paymentConfirmation
 * Method: POST
 * Documentation: DBBL Rocket Bill Payment Reconciliation API Document V1.1 (Section 4.1.b & 4.2.c)
 */

require_once __DIR__ . '/bootstrap.php';

// 1. Get and Log Request
$input = get_rocket_input();
log_rocket_transaction('CONFIRMATION_REQUEST', $input, null);

$txnId = trim((string)($input['txnid'] ?? $input['txnId'] ?? ''));
$txnDate = $txnDate_rocket = trim((string)($input['txndate'] ?? $input['txnDate'] ?? date('Y-m-d H:i:s')));
$refNo1 = trim((string)($input['refno1'] ?? $input['refNo1'] ?? ''));
$refNo2 = trim((string)($input['refno2'] ?? $input['refNo2'] ?? ''));
$refNo3 = trim((string)($input['refno3'] ?? $input['refNo3'] ?? ''));
$amount = (float)($input['amount'] ?? 0.0);

if (empty($txnId)) {
    send_rocket_response('99', 'Transaction ID (txnid) is required');
}
if ($amount <= 0) {
    send_rocket_response('99', 'Invalid Payment Amount');
}

// 2. Parse References
$refInfo = parse_rocket_references($refNo1, $refNo2, $refNo3);
$sccode = (int)$refInfo['sccode'];
$stid = (int)$refInfo['stid'];
$sessionyear = (string)$refInfo['sessionyear'];

if ($stid <= 0 || $sccode <= 0) {
    send_rocket_response('05', 'Invalid Reference / Institution Code');
}

// 3. Security & Authentication Check
validate_rocket_auth($input, $sccode);

// 4. Idempotency Check: Avoid double posting of the same txnid
$checkTxnStmt = $conn->prepare("SELECT id, status FROM rocket_transactions WHERE txnid = ? LIMIT 1");
$checkTxnStmt->bind_param("s", $txnId);
$checkTxnStmt->execute();
$checkTxnRes = $checkTxnStmt->get_result();
if ($existingTxn = $checkTxnRes->fetch_assoc()) {
    $checkTxnStmt->close();
    // Already processed successfully
    send_rocket_response('00', 'Payment Information Updated Successfully');
}
$checkTxnStmt->close();

// 5. Fetch Student Profile & Session Info
$stStmt = $conn->prepare("SELECT stnameeng, guarmobile FROM students WHERE sccode = ? AND stid = ? LIMIT 1");
$stStmt->bind_param("ii", $sccode, $stid);
$stStmt->execute();
$stRes = $stStmt->get_result();
$student = $stRes->fetch_assoc();
$stStmt->close();

if (!$student) {
    send_rocket_response('01', 'Student record not found in EIMBox');
}
$mobile = $student['guarmobile'] ?? '';

// 5. Fetch Session Academic Details from `sessioninfo` table
// Find the latest academic session for the given student (refNo1 = stid) and institution (refNo2 = sccode)
if (!empty($sessionyear)) {
    $syPattern = "%" . $sessionyear . "%";
    $sessStmt = $conn->prepare("SELECT classname, sectionname, rollno, sessionyear FROM sessioninfo WHERE sccode = ? AND stid = ? AND sessionyear LIKE ? ORDER BY id DESC LIMIT 1");
    $sessStmt->bind_param("iis", $sccode, $stid, $syPattern);
} else {
    $sessStmt = $conn->prepare("SELECT classname, sectionname, rollno, sessionyear FROM sessioninfo WHERE sccode = ? AND stid = ? ORDER BY CAST(sessionyear AS UNSIGNED) DESC, id DESC LIMIT 1");
    $sessStmt->bind_param("ii", $sccode, $stid);
}
$sessStmt->execute();
$sessRes = $sessStmt->get_result();
$session = $sessRes->fetch_assoc();
$sessStmt->close();

$className = $session['classname'] ?? '';
$sectionName = $session['sectionname'] ?? '';
$rollNo = $session['rollno'] ?? '';
$actualYear = $session['sessionyear'] ?? date('Y');
$syPattern = "%" . $actualYear . "%";

// 6. Settle Dues in `stfinance`
$currMonth = (int)date('n');
if ($currMonth >= 10) {
    $currMonth = 12;
}

$conn->begin_transaction();
try {
    $dueQuery = "SELECT id, dues, paid, pr1 
                 FROM stfinance 
                 WHERE sccode = ? 
                   AND sessionyear LIKE ? 
                   AND stid = ? 
                   AND month <= ? 
                   AND dues > 0 
                 ORDER BY month ASC, id ASC";
    $dueStmt = $conn->prepare($dueQuery);
    $dueStmt->bind_param("isis", $sccode, $syPattern, $stid, $currMonth);
    $dueStmt->execute();
    $dueRes = $dueStmt->get_result();

    // 6. Generate PRNO for Student in this Session
    // Query the latest receipt (prno) for this student from `stpr`
    $lastPrStmt = $conn->prepare("SELECT prno FROM stpr WHERE sccode = ? AND stid = ? AND sessionyear = ? ORDER BY id DESC LIMIT 1");
    $lastPrStmt->bind_param("iis", $sccode, $stid, $actualYear);
    $lastPrStmt->execute();
    $lastPrRes = $lastPrStmt->get_result();
    $lastPrRow = $lastPrRes->fetch_assoc();
    $lastPrStmt->close();

    $newPrNo = 0;
    if ($lastPrRow && !empty($lastPrRow['prno']) && is_numeric($lastPrRow['prno'])) {
        // Increment last prno by 1
        $newPrNo = (int)$lastPrRow['prno'] + 1;
    } else {
        // Generate new 8-digit receipt number:
        // First 2 digits: Last 2 digits of sessionyear (e.g., '26' from '2026' or '2025-26')
        $cleanYear = preg_replace('/[^0-9]/', '', (string)$actualYear);
        $yearPrefix = strlen($cleanYear) >= 2 ? substr($cleanYear, -2) : date('y');

        // Middle 4 digits: Last 4 digits of student ID (stid)
        $cleanStid = (string)$stid;
        $stidPart = strlen($cleanStid) >= 4 ? substr($cleanStid, -4) : str_pad($cleanStid, 4, '0', STR_PAD_LEFT);

        // Last 2 digits: Sequence starting with '01'
        $newPrNo = (int)($yearPrefix . $stidPart . "01");
    }

    $remainingToPay = $amount;
    $updatedCount = 0;

    // Ensure pure DATE format (YYYY-MM-DD) for stfinance (pr1date, pr2date) and stpr (prdate)
    $payDateFormatted = date('Y-m-d');
    if (!empty($txnDate)) {
        $parsedTime = strtotime($txnDate);
        if ($parsedTime !== false) {
            $payDateFormatted = date('Y-m-d', $parsedTime);
        }
    }

    while ($row = $dueRes->fetch_assoc()) {
        if ($remainingToPay <= 0) {
            break;
        }

        $fId = (int)$row['id'];
        $itemDue = (float)$row['dues'];
        $payForThisItem = min($remainingToPay, $itemDue);

        // Determine PR slot (pr1 or pr2)
        $prField = (float)$row['pr1'] > 0 ? 'pr2' : 'pr1';
        $prNoField = $prField . 'no';
        $prDateField = $prField . 'date';
        $prByField = $prField . 'by';

        $upSql = "UPDATE stfinance 
                  SET paid = paid + ?, 
                      dues = dues - ?, 
                      `$prField` = ?, 
                      `$prNoField` = ?, 
                      `$prDateField` = ?, 
                      `$prByField` = 'Rocket' 
                  WHERE id = ? AND sccode = ?";
        $upStmt = $conn->prepare($upSql);
        $intPrNo = (int)$newPrNo;
        $upStmt->bind_param("dddisii", $payForThisItem, $payForThisItem, $payForThisItem, $intPrNo, $payDateFormatted, $fId, $sccode);
        $upStmt->execute();
        $upStmt->close();

        $remainingToPay -= $payForThisItem;
        $updatedCount++;
    }
    $dueStmt->close();

    // 7. Insert Receipt into `stpr` Table (excluding partid)
    $prInsertSql = "INSERT INTO stpr (
        sessionyear, sccode, classname, sectionname, stid, rollno, 
        prno, prdate, amount, entryby, entrytime, 
        smstxt, smscnt, mobileno, smsstatus, statusvalue
    ) VALUES (
        ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, 'Rocket', NOW(), 
        '', 0, ?, 0, ?
    )";
    $prStmt = $conn->prepare($prInsertSql);
    $statusValue = "TxnID: $txnId | DBBL Rocket";
    $intPrNo = (int)$newPrNo;
    $prStmt->bind_param("sissiiissss", 
        $actualYear, $sccode, $className, $sectionName, $stid, $rollNo,
        $intPrNo, $payDateFormatted, $amount, $mobile, $statusValue
    );
    $prStmt->execute();
    $prStmt->close();

    // Update lastpr in sessioninfo
    $upSess = $conn->prepare("UPDATE sessioninfo SET lastpr = ? WHERE sccode = ? AND stid = ? AND sessionyear LIKE ?");
    $upSess->bind_param("iiis", $intPrNo, $sccode, $stid, $syPattern);
    $upSess->execute();
    $upSess->close();

    // 8. Log into `rocket_transactions`
    $rawPayload = json_encode($input, JSON_UNESCAPED_UNICODE);
    $logTxnSql = "INSERT INTO rocket_transactions (
        sccode, stid, sessionyear, prno, txnid, txndate, amount, 
        refno1, refno2, refno3, status, response_code, response_msg, raw_request
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Success', '00', 'Payment Information Updated Successfully', ?)";
    $logTxnStmt = $conn->prepare($logTxnSql);
    $logTxnStmt->bind_param("iissssdssss", 
        $sccode, $stid, $actualYear, $intPrNo, $txnId, $txnDate_rocket, $amount,
        $refNo1, $refNo2, $refNo3, $rawPayload
    );
    $logTxnStmt->execute();
    $logTxnStmt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    send_rocket_response('99', 'Database error: ' . $e->getMessage());
}

// 9. Send DBBL Success Response (Page 10 of PDF)
send_rocket_response('00', 'Payment Information Updated Successfully');
