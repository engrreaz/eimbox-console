<?php
/**
 * EIMBox - bKash Pay Bill: Bill Payment API
 * Endpoint: /api/payment/bkash/v1/BillPayment
 * Methods: POST, GET
 * Documentation: bKash Pay Bill Integration Specification v2.0 (Bill Payment)
 */

require_once __DIR__ . '/bootstrap.php';

// 1. Get and Log Request
$input = get_bkash_input();
log_bkash_transaction('BILL_PAYMENT_REQUEST', $input, null);

$trxId = trim((string)($input['trxid'] ?? $input['Trxid'] ?? $input['txnid'] ?? ''));
$payTimeInput = trim((string)($input['paytime'] ?? $input['Paytime'] ?? $input['txndate'] ?? date('Ymd')));
$refId = trim((string)($input['ref_id'] ?? $input['refId'] ?? $input['refno1'] ?? ''));
$billMonth = trim((string)($input['bill_month'] ?? $input['billMonth'] ?? 'na'));
$amount = (float)($input['amount'] ?? $input['Total_amount'] ?? 0.0);
$userMobile = trim((string)($input['user_mobile_number'] ?? $input['User_Mobile_Number'] ?? $input['mobileno'] ?? ''));
$sccodeInput = trim((string)($input['sccode'] ?? $input['refno2'] ?? ''));

// Validate Mandatory Fields
if (empty($trxId)) {
    send_bkash_response('202', 'Mandatory Field missing: trxid is required');
}
if (empty($refId)) {
    send_bkash_response('202', 'Mandatory Field missing: ref_id is required');
}
if ($amount <= 0) {
    send_bkash_response('202', 'Mandatory Field missing: amount must be greater than zero');
}

// 2. Parse Student ID and Institution Code
$refInfo = parse_bkash_reference($refId, $sccodeInput);
$sccode = (int)$refInfo['sccode'];
$stid = (int)$refInfo['stid'];

if ($stid <= 0) {
    send_bkash_response('205', 'Data not found: Invalid student reference');
}
if ($sccode <= 0) {
    send_bkash_response('210', 'School not found: Institution code cannot be resolved');
}

// 3. Security & Authentication Check
validate_bkash_auth($input, $sccode);

// 4. Idempotency Check: Avoid double posting of the same trxid
$checkTxnStmt = $conn->prepare("SELECT id, sccode, stid, prno, amount, status, error_code, error_msg FROM bkash_transactions WHERE trxid = ? LIMIT 1");
$checkTxnStmt->bind_param("s", $trxId);
$checkTxnStmt->execute();
$checkTxnRes = $checkTxnStmt->get_result();
if ($existingTxn = $checkTxnRes->fetch_assoc()) {
    $checkTxnStmt->close();
    // Already processed successfully - return 200 Success immediately
    send_bkash_response('200', 'Success', [
        'Consumer_Name' => 'Student #' . $stid,
        'Total_amount' => (string)round($existingTxn['amount']),
        'Trxid' => $trxId,
        'Paytime' => $payTimeInput,
        'Amount_Breakdown' => 'Already Processed | PR: ' . ($existingTxn['prno'] ?? 'N/A')
    ]);
}
$checkTxnStmt->close();

// 5. Fetch Student Profile from `students` table
$stStmt = $conn->prepare("SELECT stnameeng, stnameben, guarmobile FROM students WHERE sccode = ? AND stid = ? LIMIT 1");
$stStmt->bind_param("ii", $sccode, $stid);
$stStmt->execute();
$stRes = $stStmt->get_result();
$student = $stRes->fetch_assoc();
$stStmt->close();

if (!$student) {
    send_bkash_response('205', 'Data not found: Student record not found in EIMBox');
}

$consumerName = trim($student['stnameeng'] ?? '');
if (empty($consumerName)) {
    $consumerName = trim($student['stnameben'] ?? 'Student #' . $stid);
}
if (empty($userMobile)) {
    $userMobile = $student['guarmobile'] ?? '';
}

// 6. Fetch Session Academic Details from `sessioninfo` table
$sessStmt = $conn->prepare("SELECT classname, sectionname, rollno, sessionyear FROM sessioninfo WHERE sccode = ? AND stid = ? ORDER BY CAST(sessionyear AS UNSIGNED) DESC, id DESC LIMIT 1");
$sessStmt->bind_param("ii", $sccode, $stid);
$sessStmt->execute();
$sessRes = $sessStmt->get_result();
$session = $sessRes->fetch_assoc();
$sessStmt->close();

if (!$session) {
    send_bkash_response('205', 'Data not found: Academic session record not found in sessioninfo');
}

$className = $session['classname'] ?? '';
$sectionName = $session['sectionname'] ?? '';
$rollNo = $session['rollno'] ?? '';
$actualYear = $session['sessionyear'] ?? date('Y');
$syPattern = "%" . $actualYear . "%";

// 7. Validate Outstanding Dues from `stfinance` table
$currMonth = (int)date('n');
if ($currMonth >= 10) {
    $currMonth = 12;
}

if (!empty($billMonth) && strtolower($billMonth) !== 'na' && strlen($billMonth) === 6 && is_numeric($billMonth)) {
    $targetMonth = (int)substr($billMonth, 0, 2);
    $dueStmt = $conn->prepare("SELECT COALESCE(SUM(dues), 0) AS total_dues, COUNT(*) AS due_items 
                               FROM stfinance 
                               WHERE sccode = ? 
                                 AND sessionyear LIKE ? 
                                 AND stid = ? 
                                 AND month = ? 
                                 AND dues > 0");
    $dueStmt->bind_param("isis", $sccode, $syPattern, $stid, $targetMonth);
} else {
    $dueStmt = $conn->prepare("SELECT COALESCE(SUM(dues), 0) AS total_dues, COUNT(*) AS due_items 
                               FROM stfinance 
                               WHERE sccode = ? 
                                 AND sessionyear LIKE ? 
                                 AND stid = ? 
                                 AND month <= ? 
                                 AND dues > 0");
    $dueStmt->bind_param("isis", $sccode, $syPattern, $stid, $currMonth);
}
$dueStmt->execute();
$dueRes = $dueStmt->get_result();
$dueRow = $dueRes->fetch_assoc();
$dueStmt->close();

$totalDues = (float)($dueRow['total_dues'] ?? 0.00);
$expectedAmount = round($totalDues);

if ($totalDues <= 0) {
    send_bkash_response('204', 'Already paid: Student has no outstanding dues');
}

// Verify that the payment amount matches total dues
if (abs($amount - $totalDues) > 0.01 && abs($amount - $expectedAmount) > 0.01) {
    send_bkash_response('208', "Pay amount and biller amount not match: Paid ({$amount}) does not match outstanding dues ({$expectedAmount})");
}

// 8. Settle Dues in `stfinance` & Issue Receipt Voucher
$conn->begin_transaction();
try {
    if (!empty($billMonth) && strtolower($billMonth) !== 'na' && strlen($billMonth) === 6 && is_numeric($billMonth)) {
        $targetMonth = (int)substr($billMonth, 0, 2);
        $settleQuery = "SELECT id, dues, paid, pr1 
                        FROM stfinance 
                        WHERE sccode = ? 
                          AND sessionyear LIKE ? 
                          AND stid = ? 
                          AND month = ? 
                          AND dues > 0 
                        ORDER BY month ASC, id ASC";
        $settleStmt = $conn->prepare($settleQuery);
        $settleStmt->bind_param("isis", $sccode, $syPattern, $stid, $targetMonth);
    } else {
        $settleQuery = "SELECT id, dues, paid, pr1 
                        FROM stfinance 
                        WHERE sccode = ? 
                          AND sessionyear LIKE ? 
                          AND stid = ? 
                          AND month <= ? 
                          AND dues > 0 
                        ORDER BY month ASC, id ASC";
        $settleStmt = $conn->prepare($settleQuery);
        $settleStmt->bind_param("isis", $sccode, $syPattern, $stid, $currMonth);
    }
    $settleStmt->execute();
    $settleRes = $settleStmt->get_result();

    // 8.1 Generate PRNO for Student in this Session
    $lastPrStmt = $conn->prepare("SELECT prno FROM stpr WHERE sccode = ? AND stid = ? AND sessionyear = ? ORDER BY id DESC LIMIT 1");
    $lastPrStmt->bind_param("iis", $sccode, $stid, $actualYear);
    $lastPrStmt->execute();
    $lastPrRes = $lastPrStmt->get_result();
    $lastPrRow = $lastPrRes->fetch_assoc();
    $lastPrStmt->close();

    $newPrNo = 0;
    if ($lastPrRow && !empty($lastPrRow['prno']) && is_numeric($lastPrRow['prno'])) {
        $newPrNo = (int)$lastPrRow['prno'] + 1;
    } else {
        $cleanYear = preg_replace('/[^0-9]/', '', (string)$actualYear);
        $yearPrefix = strlen($cleanYear) >= 2 ? substr($cleanYear, -2) : date('y');
        $cleanStid = (string)$stid;
        $stidPart = strlen($cleanStid) >= 4 ? substr($cleanStid, -4) : str_pad($cleanStid, 4, '0', STR_PAD_LEFT);
        $newPrNo = (int)($yearPrefix . $stidPart . "01");
    }

    $remainingToPay = $amount;
    $updatedCount = 0;

    // Standard Pay Date (YYYY-MM-DD)
    $payDateFormatted = date('Y-m-d');
    if (!empty($payTimeInput)) {
        $parsedTime = strtotime($payTimeInput);
        if ($parsedTime !== false) {
            $payDateFormatted = date('Y-m-d', $parsedTime);
        }
    }

    while ($row = $settleRes->fetch_assoc()) {
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
                      `$prByField` = 'bKash' 
                  WHERE id = ? AND sccode = ?";
        $upStmt = $conn->prepare($upSql);
        $intPrNo = (int)$newPrNo;
        $upStmt->bind_param("dddisii", $payForThisItem, $payForThisItem, $payForThisItem, $intPrNo, $payDateFormatted, $fId, $sccode);
        $upStmt->execute();
        $upStmt->close();

        $remainingToPay -= $payForThisItem;
        $updatedCount++;
    }
    $settleStmt->close();

    // 8.2 Insert Receipt into `stpr` Table
    $prInsertSql = "INSERT INTO stpr (
        sessionyear, sccode, classname, sectionname, stid, rollno, 
        prno, prdate, amount, entryby, entrytime, 
        smstxt, smscnt, mobileno, smsstatus, statusvalue
    ) VALUES (
        ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, 'bKash', NOW(), 
        '', 0, ?, 0, ?
    )";
    $prStmt = $conn->prepare($prInsertSql);
    $statusValue = "TxnID: $trxId | bKash PayBill";
    $intPrNo = (int)$newPrNo;
    $prStmt->bind_param("sissiiissss", 
        $actualYear, $sccode, $className, $sectionName, $stid, $rollNo,
        $intPrNo, $payDateFormatted, $amount, $userMobile, $statusValue
    );
    $prStmt->execute();
    $prStmt->close();

    // 8.3 Update lastpr in sessioninfo
    $upSess = $conn->prepare("UPDATE sessioninfo SET lastpr = ? WHERE sccode = ? AND stid = ? AND sessionyear LIKE ?");
    $upSess->bind_param("iiis", $intPrNo, $sccode, $stid, $syPattern);
    $upSess->execute();
    $upSess->close();

    // 8.4 Log into `bkash_transactions`
    $rawPayload = json_encode($input, JSON_UNESCAPED_UNICODE);
    $logTxnSql = "INSERT INTO bkash_transactions (
        sccode, stid, sessionyear, prno, trxid, paytime, amount, 
        ref_id, bill_month, user_mobile, status, error_code, error_msg, raw_request
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Success', '200', 'Success', ?)";
    $logTxnStmt = $conn->prepare($logTxnSql);
    $logTxnStmt->bind_param("iissssdssss", 
        $sccode, $stid, $actualYear, $intPrNo, $trxId, $payTimeInput, $amount,
        $refId, $billMonth, $userMobile, $rawPayload
    );
    $logTxnStmt->execute();
    $logTxnStmt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    send_bkash_response('209', 'Internal error: ' . $e->getMessage());
}

// 9. Send bKash Success Response
$formattedPaytime = !empty($payTimeInput) ? preg_replace('/[^0-9]/', '', $payTimeInput) : date('Ymd');
$breakdown = (!empty($className) ? "Class: $className" : "") . 
             (!empty($rollNo) ? " | Roll: $rollNo" : "") . 
             " | PR: $newPrNo";

send_bkash_response('200', 'Success', [
    'Consumer_Name' => $consumerName,
    'Total_amount' => (string)round($amount),
    'Trxid' => $trxId,
    'Paytime' => $formattedPaytime,
    'Amount_Breakdown' => $breakdown
]);
