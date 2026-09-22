<?php
/**
 * EIMBox - bKash Pay Bill: Check Bill API
 * Endpoint: /api/payment/bkash/v1/CheckBill
 * Methods: GET, POST
 * Documentation: bKash Pay Bill Integration Specification v2.0 (Check Bill)
 */

require_once __DIR__ . '/bootstrap.php';

// 1. Get and Log Request
$input = get_bkash_input();
log_bkash_transaction('CHECK_BILL_REQUEST', $input, null);

$refId = trim((string)($input['ref_id'] ?? $input['refId'] ?? $input['refno1'] ?? ''));
$billMonth = trim((string)($input['bill_month'] ?? $input['billMonth'] ?? 'na'));
$sccodeInput = trim((string)($input['sccode'] ?? $input['refno2'] ?? ''));

if (empty($refId)) {
    send_bkash_response('202', 'Mandatory Field missing: ref_id is required');
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

// 4. Fetch Student Name & Profile from `students` table
$stStmt = $conn->prepare("SELECT stnameeng, stnameben, guarmobile FROM students WHERE sccode = ? AND stid = ? LIMIT 1");
$stStmt->bind_param("ii", $sccode, $stid);
$stStmt->execute();
$stRes = $stStmt->get_result();
$student = $stRes->fetch_assoc();
$stStmt->close();

if (!$student) {
    send_bkash_response('205', 'Data not found: Student record not found for given ID and Institution');
}

$consumerName = trim($student['stnameeng'] ?? '');
if (empty($consumerName)) {
    $consumerName = trim($student['stnameben'] ?? 'Student #' . $stid);
}

// 5. Fetch Session Academic Details from `sessioninfo` table
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

// 6. Calculate Current Dues from `stfinance` table
// EIMBox Business Logic: If bill_month specified as MMYYYY, filter by month; otherwise till current month
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

// If already paid / zero dues
if ($totalDues <= 0) {
    send_bkash_response('204', 'Already paid: Student has no outstanding dues', [
        'Consumer_Name' => $consumerName,
        'Bill_month' => $billMonth,
        'Bill_amount' => '0',
        'Bill_due_date' => date('Y-m-t'),
        'Trxid' => 'CHK' . date('YmdHis') . rand(100, 999),
        'Querytime' => date('YmdHis'),
        'Amount_Breakdown' => (!empty($className) ? "Class: $className" : "") . (!empty($rollNo) ? " | Roll: $rollNo" : "")
    ]);
}

// 7. Format bKash Response (as per bKash PayBill API v2 doc)
$queryTime = date('YmdHis');
$billDueDate = date('Y-m-t'); // Last day of current month
$trxTrackingId = 'CHK' . date('YmdHis') . rand(100, 999);
$breakdown = (!empty($className) ? "Class: $className" : "") . 
             (!empty($sectionName) ? " ($sectionName)" : "") . 
             (!empty($rollNo) ? " | Roll: $rollNo" : "") . 
             " | SC: $sccode";

send_bkash_response('200', 'Success', [
    'Consumer_Name' => $consumerName,
    'Bill_month' => $billMonth,
    'Bill_amount' => (string)round($totalDues),
    'Bill_due_date' => $billDueDate,
    'Trxid' => $trxTrackingId,
    'Querytime' => $queryTime,
    'Amount_Breakdown' => $breakdown
]);
