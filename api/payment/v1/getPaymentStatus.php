<?php
/**
 * EIMBox - DBBL Rocket Bill Payment: Inquiry API
 * Endpoint: /api/payment/v1/getPaymentStatus
 * Method: POST
 * Documentation: DBBL Rocket Bill Payment Reconciliation API Document V1.1 (Section 4.1.c & 4.2.d)
 */

require_once __DIR__ . '/bootstrap.php';

// 1. Get and Log Request
$input = get_rocket_input();
log_rocket_transaction('INQUIRY_REQUEST', $input, null);

$txnId = trim((string)($input['txnid'] ?? $input['txnId'] ?? ''));
$refNo1 = trim((string)($input['refno1'] ?? $input['refNo1'] ?? ''));
$refNo2 = trim((string)($input['refno2'] ?? $input['refNo2'] ?? ''));
$refNo3 = trim((string)($input['refno3'] ?? $input['refNo3'] ?? ''));

if (empty($txnId)) {
    send_rocket_response('99', 'Transaction ID (txnid) is required for Inquiry');
}

// 2. Parse References if provided
$refInfo = parse_rocket_references($refNo1, $refNo2, $refNo3);
$sccode = (int)$refInfo['sccode'];

// 3. Security & Authentication Check
validate_rocket_auth($input, $sccode);

// 4. Query `rocket_transactions` or `stpr`
$inqStmt = $conn->prepare("SELECT id, txnid, amount, status, response_code, response_msg 
                          FROM rocket_transactions 
                          WHERE txnid = ? 
                          LIMIT 1");
$inqStmt->bind_param("s", $txnId);
$inqStmt->execute();
$inqRes = $inqStmt->get_result();

if ($txnRow = $inqRes->fetch_assoc()) {
    $inqStmt->close();
    if ($txnRow['status'] === 'Success') {
        send_rocket_response('00', 'Payment Information Updated Successfully');
    } else {
        send_rocket_response('01', 'Payment Status: ' . $txnRow['status']);
    }
}
$inqStmt->close();

// Check in stpr as fallback
$prStmt = $conn->prepare("SELECT id, prno, amount FROM stpr WHERE prno = ? LIMIT 1");
$prStmt->bind_param("s", $txnId);
$prStmt->execute();
$prRes = $prStmt->get_result();

if ($prRow = $prRes->fetch_assoc()) {
    $prStmt->close();
    send_rocket_response('00', 'Payment Information Updated Successfully');
}
$prStmt->close();

// If not found in records
send_rocket_response('01', 'Transaction Not Found');
