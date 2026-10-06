<?php
/**
 * EIMBox REST API — Collect Fee & Issue Money Receipt
 * Route: POST /api/v1/finance/collect-fee.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate Bearer Token
$user = authenticate_token($conn);

$input = get_api_input();

$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$stid = trim($input['stid'] ?? '');
$sessionyear = intval($input['sessionyear'] ?? date('Y'));
$items = $input['items'] ?? [];
$prdate = trim($input['prdate'] ?? date('Y-m-d'));
$collectionMedia = trim($input['collection_media'] ?? 'Cash');
$customPrNo = isset($input['prno']) && intval($input['prno']) > 0 ? intval($input['prno']) : null;
$entryby = $user['profilename'] ?? $user['username'] ?? $user['email'] ?? 'Studio POS';

if ($sccode <= 0 || empty($stid)) {
    api_response('error', 'Both sccode and stid are required.', null, 400);
}

if (!is_array($items) || empty($items)) {
    api_response('error', 'Items array cannot be empty.', null, 400);
}

// 1. Fetch Student & Session Details
$stmtSt = $conn->prepare("SELECT s.stnameeng, s.stnameben, s.guarmobile, s.previll, s.prepo, s.predist,
si.classname, si.sectionname, si.rollno, si.sessionyear
FROM students s
LEFT JOIN sessioninfo si ON si.stid = s.stid AND si.sccode = s.sccode
WHERE s.sccode = ? AND s.stid = ?
ORDER BY si.sessionyear DESC LIMIT 1");
$stmtSt->bind_param('is', $sccode, $stid);
$stmtSt->execute();
$student = $stmtSt->get_result()->fetch_assoc();
$stmtSt->close();

if (!$student) {
    api_response('error', 'Student not found.', null, 404);
}

$cls = $student['classname'] ?? '';
$sec = $student['sectionname'] ?? '';
$rollno = intval($student['rollno'] ?? 0);
$mobile = $student['guarmobile'] ?? '';
$stname = $student['stnameeng'] ?: ($student['stnameben'] ?? 'Student');

// 2. Fetch School Details for Print Receipt
$stmtSc = $conn->prepare("SELECT scname, scadd1, scadd2, ps, dist, mobile FROM scinfo WHERE sccode = ? LIMIT 1");
$stmtSc->bind_param('i', $sccode);
$stmtSc->execute();
$school = $stmtSc->get_result()->fetch_assoc();
$stmtSc->close();

$scname = $school['scname'] ?? 'EIMBox Institute';
$scaddress = trim(($school['scadd1'] ?? '') . ', ' . ($school['ps'] ?? '') . ', ' . ($school['dist'] ?? ''));
$scmobile = $school['mobile'] ?? '';

// Start Transaction
$conn->begin_transaction();

try {
    // 3. Determine PR Number
    $prno = $customPrNo;
    if (!$prno) {
        $prStmt = $conn->prepare("SELECT MAX(prno) AS max_pr FROM stpr WHERE sccode = ?");
        $prStmt->bind_param('i', $sccode);
        $prStmt->execute();
        $prRow = $prStmt->get_result()->fetch_assoc();
        $prStmt->close();

        if ($prRow && $prRow['max_pr'] > 0) {
            $prno = intval($prRow['max_pr']) + 1;
        } else {
            $shortYear = date('y');
            $numericSuffix = intval(substr($stid, -4)) ?: 1;
            $prno = intval($shortYear . sprintf("%04d", $numericSuffix) . '01');
        }
    }

    $totalPaid = 0;
    $receiptItems = [];
    $partNamesEng = [];
    $partNamesBen = [];

    // 4. Update each stfinance item
    foreach ($items as $itm) {
        $fid = intval($itm['id'] ?? $itm['itemid'] ?? 0);
        $partid = intval($itm['partid'] ?? 0);
        $paidAmt = floatval($itm['paid'] ?? $itm['paid_amount'] ?? 0);

        if ($paidAmt <= 0) {
            continue;
        }

        $fRow = null;
        $itmMonth = intval($itm['month'] ?? 0);
        $itmPeng = trim($itm['particulareng'] ?? $itm['title_en'] ?? $itm['name'] ?? '');

        // Fetch current item (by id if provided, else fallback to partid+month, partid, or particulareng)
        if ($fid > 0) {
            $fStmt = $conn->prepare("SELECT id, partid, particulareng, particularben, amount, payableamt, paid, dues, pr1, pr1no FROM stfinance WHERE id = ? AND sccode = ? AND (CAST(stid AS CHAR) = ? OR stid = ?) LIMIT 1");
            $fStmt->bind_param('iiss', $fid, $sccode, $stid, $stid);
            $fStmt->execute();
            $fRow = $fStmt->get_result()->fetch_assoc();
            $fStmt->close();
        }

        if (!$fRow && $partid > 0 && $itmMonth > 0) {
            $fStmt = $conn->prepare("SELECT id, partid, particulareng, particularben, amount, payableamt, paid, dues, pr1, pr1no FROM stfinance WHERE partid = ? AND month = ? AND sccode = ? AND (CAST(stid AS CHAR) = ? OR stid = ?) AND dues > 0 ORDER BY id ASC LIMIT 1");
            $fStmt->bind_param('iiiss', $partid, $itmMonth, $sccode, $stid, $stid);
            $fStmt->execute();
            $fRow = $fStmt->get_result()->fetch_assoc();
            $fStmt->close();
        }

        if (!$fRow && $partid > 0) {
            $fStmt = $conn->prepare("SELECT id, partid, particulareng, particularben, amount, payableamt, paid, dues, pr1, pr1no FROM stfinance WHERE partid = ? AND sccode = ? AND (CAST(stid AS CHAR) = ? OR stid = ?) AND dues > 0 ORDER BY id ASC LIMIT 1");
            $fStmt->bind_param('iiss', $partid, $sccode, $stid, $stid);
            $fStmt->execute();
            $fRow = $fStmt->get_result()->fetch_assoc();
            $fStmt->close();
        }

        if (!$fRow && !empty($itmPeng)) {
            $fStmt = $conn->prepare("SELECT id, partid, particulareng, particularben, amount, payableamt, paid, dues, pr1, pr1no FROM stfinance WHERE particulareng = ? AND sccode = ? AND (CAST(stid AS CHAR) = ? OR stid = ?) AND dues > 0 ORDER BY id ASC LIMIT 1");
            $fStmt->bind_param('siss', $itmPeng, $sccode, $stid, $stid);
            $fStmt->execute();
            $fRow = $fStmt->get_result()->fetch_assoc();
            $fStmt->close();
        }

        if (!$fRow) {
            continue;
        }

        $rowId = intval($fRow['id']);
        $isPr1Used = intval($fRow['pr1']) > 0 || !empty($fRow['pr1no']);
        $fld = $isPr1Used ? 'pr2' : 'pr1';
        $flddt = $isPr1Used ? 'pr2date' : 'pr1date';
        $fldby = $isPr1Used ? 'pr2by' : 'pr1by';
        $fldno = $isPr1Used ? 'pr2no' : 'pr1no';

        $currDue = floatval($fRow['dues']);
        $newDue = max(0, $currDue - $paidAmt);

        $upStmt = $conn->prepare("UPDATE stfinance 
            SET $fld = ?, $fldno = ?, $flddt = ?, $fldby = ?, paid = paid + ?, dues = ?, modifieddate = NOW() 
            WHERE id = ?");
        $upStmt->bind_param('dissddi', $paidAmt, $prno, $prdate, $entryby, $paidAmt, $newDue, $rowId);
        $upStmt->execute();
        $upStmt->close();

        $totalPaid += $paidAmt;
        $receiptItems[] = [
            'item_id' => $rowId,
            'title' => $fRow['particulareng'] ?: ($fRow['particularben'] ?: 'Fee Item'),
            'title_bn' => $fRow['particularben'],
            'paid' => $paidAmt
        ];

        if (!empty($fRow['particulareng'])) {
            $partNamesEng[] = $fRow['particulareng'];
        }
        if (!empty($fRow['particularben'])) {
            $partNamesBen[] = $fRow['particularben'];
        }
    }

    if ($totalPaid <= 0) {
        throw new Exception('No valid payable items with positive amounts were provided.');
    }

    // 5. Insert into stpr
    $pengStr = implode(', ', array_slice($partNamesEng, 0, 3));
    $pbenStr = implode(', ', array_slice($partNamesBen, 0, 3));
    $emptyTxt = '';
    $zeroVal = 0;

    $insPr = $conn->prepare("INSERT INTO stpr (
        sessionyear, sccode, classname, sectionname, stid, rollno, prno, prdate, partid, peng, pben, amount, entryby, entrytime, smstxt, smscnt, mobileno, smsstatus, statusvalue, collection_media, modifieddate
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, NOW())");
    
    $insPr->bind_param('iisssisissdsisssiss', 
        $sessionyear, $sccode, $cls, $sec, $stid, $rollno, $prno, $prdate, 
        $zeroVal, $pengStr, $pbenStr, $totalPaid, $entryby, 
        $emptyTxt, $zeroVal, $mobile, $zeroVal, $emptyTxt, $collectionMedia
    );
    $insPr->execute();
    $insPr->close();

    // 6. Update sessioninfo.lastpr
    $upSess = $conn->prepare("UPDATE sessioninfo SET lastpr = ? WHERE sccode = ? AND stid = ? AND sessionyear LIKE ?");
    $sessLike = "%$sessionyear%";
    $upSess->bind_param('siss', $prno, $sccode, $stid, $sessLike);
    $upSess->execute();
    $upSess->close();

    // 7. Calculate new overall remaining dues for the student
    $totDueStmt = $conn->prepare("SELECT COALESCE(SUM(dues), 0) AS remaining_due FROM stfinance WHERE sccode = ? AND stid = ? AND dues > 0");
    $totDueStmt->bind_param('is', $sccode, $stid);
    $totDueStmt->execute();
    $newOverallDue = floatval($totDueStmt->get_result()->fetch_assoc()['remaining_due'] ?? 0);
    $totDueStmt->close();

    // Commit Transaction
    $conn->commit();

    // 8. Generate Print Payload for ESC/POS Thermal Printing
    $verifyUrl = "https://eimbox.com/verify-receipt.php?sccode={$sccode}&prno={$prno}";

    $printPayload = [
        'school_name' => $scname,
        'school_address' => $scaddress,
        'school_mobile' => $scmobile,
        'prno' => (string)$prno,
        'prdate' => $prdate,
        'stname' => $stname,
        'stid' => $stid,
        'classname' => $cls,
        'sectionname' => $sec,
        'rollno' => $rollno,
        'collection_media' => $collectionMedia,
        'items' => $receiptItems,
        'total_paid' => $totalPaid,
        'entryby' => $entryby,
        'verify_url' => $verifyUrl
    ];

    api_response('success', 'Fee collection recorded successfully.', [
        'receipt_id' => (string)$prno,
        'prno' => $prno,
        'prdate' => $prdate,
        'stid' => $stid,
        'total_paid' => $totalPaid,
        'new_due' => $newOverallDue,
        'print_payload' => $printPayload
    ]);

} catch (Exception $e) {
    $conn->rollback();
    api_response('error', 'Fee collection failed: ' . $e->getMessage(), null, 500);
}
