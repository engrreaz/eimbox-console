<?php
/**
 * EIMBox - bKash Pay Bill: Recheck Bill Payment API
 * Endpoint: /api/payment/bkash/v1/RecheckPayment
 * Methods: GET, POST
 * Documentation: bKash Pay Bill Integration Specification v2.0 (Recheck Bill Payment)
 */

require_once __DIR__ . '/bootstrap.php';

// 1. Get and Log Request
$input = get_bkash_input();
log_bkash_transaction('RECHECK_PAYMENT_REQUEST', $input, null);

$trxId = trim((string)($input['trxid'] ?? $input['Trxid'] ?? $input['txnid'] ?? ''));
$sccodeInput = trim((string)($input['sccode'] ?? ''));

if (empty($trxId)) {
    send_bkash_response('202', 'Mandatory Field missing: trxid is required');
}

// 2. Authentication Check
validate_bkash_auth($input, $sccodeInput ? (int)$sccodeInput : null);

// 3. Query `bkash_transactions`
$inqStmt = $conn->prepare("SELECT id, sccode, stid, sessionyear, prno, trxid, paytime, amount, status, error_code, error_msg, created_at 
                           FROM bkash_transactions 
                           WHERE trxid = ? 
                           LIMIT 1");
$inqStmt->bind_param("s", $trxId);
$inqStmt->execute();
$inqRes = $inqStmt->get_result();

if ($txnRow = $inqRes->fetch_assoc()) {
    $inqStmt->close();
    $prNo = $txnRow['prno'] ?? 'N/A';
    $amount = round((float)$txnRow['amount']);
    $prevMsg = "Payment Information Updated Successfully. PR: {$prNo} | Amount: {$amount} BDT";
    
    send_bkash_response('200', 'Success', [
        'PreviousMessage' => $prevMsg
    ]);
}
$inqStmt->close();

// Fallback search in `stpr` for statusvalue containing the transaction ID
$searchPattern = "%" . $trxId . "%";
$prStmt = $conn->prepare("SELECT id, sccode, stid, sessionyear, prno, amount, prdate FROM stpr WHERE statusvalue LIKE ? LIMIT 1");
$prStmt->bind_param("s", $searchPattern);
$prStmt->execute();
$prRes = $prStmt->get_result();

if ($prRow = $prRes->fetch_assoc()) {
    $prStmt->close();
    $prNo = $prRow['prno'];
    $amount = round((float)$prRow['amount']);
    $prevMsg = "Payment Information Updated Successfully. PR: {$prNo} | Amount: {$amount} BDT";
    
    send_bkash_response('200', 'Success', [
        'PreviousMessage' => $prevMsg
    ]);
}
$prStmt->close();

// If not found in records
send_bkash_response('205', 'Data not found: Transaction not found in EIMBox records', [
    'PreviousMessage' => 'Transaction not found'
]);
