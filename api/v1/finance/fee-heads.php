<?php
/**
 * EIMBox REST API — Fee Heads / Particular Items Endpoint
 * Route: GET /api/v1/finance/fee-heads.php
 * Query Params: ?sccode={sccode}&session={session}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$session = intval($_GET['session'] ?? date('Y'));

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// Check financesetup first, fallback to distinct stfinance items
$items = [];
$stmt = $conn->prepare("SELECT id, sccode, slno, itemcode, particulareng, particularben, month, sessionyear 
FROM financesetup 
WHERE sccode = ? OR sccode = 0 
ORDER BY slno ASC, id ASC");
$stmt->bind_param('i', $sccode);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $items[] = [
            'id' => intval($row['id']),
            'slno' => intval($row['slno']),
            'itemcode' => $row['itemcode'] ?? '',
            'title_en' => $row['particulareng'],
            'title_bn' => $row['particularben'],
            'month' => intval($row['month']),
            'sessionyear' => $row['sessionyear']
        ];
    }
} else {
    // Fallback to distinct stfinance items
    $fStmt = $conn->prepare("SELECT DISTINCT partid, itemcode, particulareng, particularben, amount, month 
        FROM stfinance 
        WHERE sccode = ? 
        ORDER BY partid ASC");
    $fStmt->bind_param('i', $sccode);
    $fStmt->execute();
    $fRes = $fStmt->get_result();
    while ($fRow = $fRes->fetch_assoc()) {
        $items[] = [
            'id' => intval($fRow['partid']),
            'slno' => intval($fRow['partid']),
            'itemcode' => $fRow['itemcode'] ?? '',
            'title_en' => $fRow['particulareng'],
            'title_bn' => $fRow['particularben'],
            'amount' => floatval($fRow['amount']),
            'month' => intval($fRow['month']),
            'sessionyear' => $session
        ];
    }
    $fStmt->close();
}
$stmt->close();

api_response('success', 'Fee heads loaded successfully.', [
    'sccode' => $sccode,
    'total_count' => count($items),
    'fee_heads' => $items
]);
