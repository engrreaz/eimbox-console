<?php
/**
 * EIMBox - DBBL Rocket Bill Payment: Validation API
 * Endpoint: /api/payment/v1/paymentValidation
 * Method: POST
 * Documentation: DBBL Rocket Bill Payment Reconciliation API Document V1.1 (Section 4.1.a & 4.2.b)
 */

require_once __DIR__ . '/bootstrap.php';

// 1. Get and Log Request
$input = get_rocket_input();
log_rocket_transaction('VALIDATION_REQUEST', $input, null);

$refNo1 = trim((string)($input['refNo1'] ?? $input['refno1'] ?? ''));
$refNo2 = trim((string)($input['refNo2'] ?? $input['refno2'] ?? ''));
$refNo3 = trim((string)($input['refNo3'] ?? $input['refno3'] ?? ''));

if (empty($refNo1)) {
    send_rocket_response('05', 'Payment Reference Number (refNo1) Missing');
}

// 2. Parse Institution Code & Student ID
$refInfo = parse_rocket_references($refNo1, $refNo2, $refNo3);
$sccode = (int)$refInfo['sccode'];
$stid = (int)$refInfo['stid'];
$sessionyear = (string)$refInfo['sessionyear'];

if ($stid <= 0) {
    send_rocket_response('99', 'Invalid Student Reference');
}
if ($sccode <= 0) {
    send_rocket_response('05', 'Institution Code (sccode) Missing in Reference');
}

// 3. Security & Authentication Check
validate_rocket_auth($input, $sccode);

// 4. Fetch Student Name & Profile from `students` table
$stStmt = $conn->prepare("SELECT stnameeng, stnameben, guarmobile FROM students WHERE sccode = ? AND stid = ? LIMIT 1");
$stStmt->bind_param("ii", $sccode, $stid);
$stStmt->execute();
$stRes = $stStmt->get_result();
$student = $stRes->fetch_assoc();
$stStmt->close();

if (!$student) {
    send_rocket_response('01', 'Student Not Found for given ID and Institution');
}

$customerName = trim($student['stnameeng'] ?? '');
if (empty($customerName)) {
    $customerName = trim($student['stnameben'] ?? 'Student #' . $stid);
}

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

if (!$session) {
    send_rocket_response('01', 'Academic Session record not found in sessioninfo for given Student and Institution');
}

$className = $session['classname'] ?? '';
$sectionName = $session['sectionname'] ?? '';
$rollNo = $session['rollno'] ?? '';
$actualYear = $session['sessionyear'] ?? date('Y');
$syPattern = "%" . $actualYear . "%";

// 6. Calculate Current Dues from `stfinance` table
// EIMBox Business Logic: Current month <= current_month, if month >= 10, include through 12
$currMonth = (int)date('n');
if ($currMonth >= 10) {
    $currMonth = 12;
}

$dueStmt = $conn->prepare("SELECT COALESCE(SUM(dues), 0) AS total_dues, COUNT(*) AS due_items 
                           FROM stfinance 
                           WHERE sccode = ? 
                             AND sessionyear LIKE ? 
                             AND stid = ? 
                             AND month <= ? 
                             AND dues > 0");
$dueStmt->bind_param("isis", $sccode, $syPattern, $stid, $currMonth);
$dueStmt->execute();
$dueRes = $dueStmt->get_result();
$dueRow = $dueRes->fetch_assoc();
$dueStmt->close();

$totalDues = (float)($dueRow['total_dues'] ?? 0.00);
$amountFormatted = number_format($totalDues, 2, '.', '');

// 7. Format Rocket Response (DBBL Page 9: Amount Provided From Partner API)
$optionalInfo1 = !empty($className) ? "Class: $className" . (!empty($sectionName) ? " ($sectionName)" : "") : "EIMBox Student ID: $stid";
$optionalInfo2 = (!empty($rollNo) ? "Roll: $rollNo | " : "") . "Year: $actualYear";
$optionalInfo3 = "Institute SC: $sccode";

send_rocket_response('00', 'Successful', [
    'customerName' => $customerName,
    'optionalInfo1' => $optionalInfo1,
    'optionalInfo2' => $optionalInfo2,
    'optionalInfo3' => $optionalInfo3,
    'amount' => (string)round($totalDues) // Rocket accepts numeric/string amount
]);
