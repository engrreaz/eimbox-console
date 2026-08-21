<?php
/**
 * EIMBox REST API — Cashbook Voucher Entry (Income/Expense)
 * Route: POST /api/v1/finance/cashbook-entry.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate caller
$user = authenticate_token($conn);

$input = get_api_input();

$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$date = trim($input['date'] ?? date('Y-m-d'));
$type = trim($input['type'] ?? 'Expense'); // 'Income' or 'Expense'
$accountHeadId = intval($input['account_head_id'] ?? $input['head_id'] ?? 0);
$partid = intval($input['partid'] ?? $input['sub_head_id'] ?? 0);
$particulars = trim($input['particulars'] ?? $input['description'] ?? '');
$amount = floatval($input['amount'] ?? 0);
$memono = intval($input['memono'] ?? $input['voucher_no'] ?? 0);
$sessionyear = intval($input['sessionyear'] ?? date('Y'));
$month = intval(date('n', strtotime($date)));
$year = intval(date('Y', strtotime($date)));
$entryby = $user['profilename'] ?? $user['username'] ?? $user['email'] ?? 'Studio Cashbook';

if ($sccode <= 0 || $amount <= 0 || empty($particulars)) {
    api_response('error', 'Valid sccode, amount > 0, and particulars description are required.', null, 400);
}

if (!in_array($type, ['Income', 'Expense'])) {
    $type = 'Expense';
}

$stmt = $conn->prepare("INSERT INTO cashbook (
    sccode, date, account_head, partid, particulars, amount, type, memono, month, year, entryby, entrytime, sessionyear
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");

$stmt->bind_param('isisdsssiisi', 
    $sccode, $date, $accountHeadId, $partid, $particulars, $amount, $type, $memono, $month, $year, $entryby, $sessionyear
);

if ($stmt->execute()) {
    $insertedId = $stmt->insert_id;
    $stmt->close();

    api_response('success', 'Cashbook voucher entry recorded successfully.', [
        'entry_id' => $insertedId,
        'sccode' => $sccode,
        'date' => $date,
        'type' => $type,
        'amount' => $amount,
        'particulars' => $particulars,
        'memono' => $memono
    ]);
} else {
    $err = $stmt->error;
    $stmt->close();
    api_response('error', 'Failed to save cashbook voucher: ' . $err, null, 500);
}
