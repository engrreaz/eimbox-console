<?php
/**
 * EIMBox REST API — Daily Fee Collection Summary & Ledger
 * Route: GET /api/v1/finance/daily-collection.php
 * Query Params: ?sccode={sccode}&date={YYYY-MM-DD}&from_date={...}&to_date={...}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$date = trim($_GET['date'] ?? date('Y-m-d'));
$fromDate = trim($_GET['from_date'] ?? $date);
$toDate = trim($_GET['to_date'] ?? $date);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

$stmt = $conn->prepare("SELECT id, sessionyear, classname, sectionname, stid, rollno, prno, prdate, peng, pben, amount, entryby, entrytime, collection_media 
FROM stpr 
WHERE sccode = ? AND prdate BETWEEN ? AND ? 
ORDER BY prdate DESC, prno DESC");
$stmt->bind_param('iss', $sccode, $fromDate, $toDate);
$stmt->execute();
$res = $stmt->get_result();

$collections = [];
$totalAmount = 0;
$byMedia = [];
$byCollector = [];

while ($row = $res->fetch_assoc()) {
    $amt = floatval($row['amount']);
    $totalAmount += $amt;
    $media = $row['collection_media'] ?: 'Cash';
    $collector = $row['entryby'] ?: 'Admin';

    if (!isset($byMedia[$media])) $byMedia[$media] = 0;
    $byMedia[$media] += $amt;

    if (!isset($byCollector[$collector])) $byCollector[$collector] = 0;
    $byCollector[$collector] += $amt;

    $collections[] = [
        'id' => intval($row['id']),
        'prno' => (string)$row['prno'],
        'prdate' => $row['prdate'],
        'stid' => (string)$row['stid'],
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'rollno' => intval($row['rollno']),
        'particulars' => $row['peng'] ?: $row['pben'],
        'amount' => $amt,
        'entryby' => $collector,
        'media' => $media,
        'entrytime' => $row['entrytime']
    ];
}
$stmt->close();

api_response('success', 'Daily collection report generated.', [
    'sccode' => $sccode,
    'from_date' => $fromDate,
    'to_date' => $toDate,
    'total_receipts' => count($collections),
    'total_amount' => $totalAmount,
    'breakdown_by_media' => $byMedia,
    'breakdown_by_collector' => $byCollector,
    'receipts' => $collections
]);
