<?php
/**
 * EIMBox REST API — Student Dues & Payable Items Endpoint
 * Route: GET /api/v1/finance/student-dues.php
 * Query Params: ?sccode={sccode}&stid={stid}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate Bearer Token
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$stid = trim($_GET['stid'] ?? '');

if ($sccode <= 0 || empty($stid)) {
    api_response('error', 'Both sccode and stid are required.', null, 400);
}

$targetSession = trim($_GET['session'] ?? $_GET['sessionyear'] ?? '');

// 1. Fetch Student Profile & Active Session Information
if (!empty($targetSession)) {
    $sessLike = "%$targetSession%";
    $stmtSt = $conn->prepare("SELECT s.*, si.sessionyear, si.classname, si.sectionname, si.rollno, si.lastpr 
    FROM students s
    LEFT JOIN sessioninfo si ON si.stid = s.stid AND si.sccode = s.sccode AND (si.sessionyear = ? OR si.sessionyear LIKE ?)
    WHERE s.sccode = ? AND s.stid = ?
    ORDER BY (si.sessionyear = ?) DESC, si.id DESC LIMIT 1");
    $stmtSt->bind_param('ssiss', $targetSession, $sessLike, $sccode, $stid, $targetSession);
} else {
    $stmtSt = $conn->prepare("SELECT s.*, si.sessionyear, si.classname, si.sectionname, si.rollno, si.lastpr 
    FROM students s
    LEFT JOIN sessioninfo si ON si.stid = s.stid AND si.sccode = s.sccode
    WHERE s.sccode = ? AND s.stid = ?
    ORDER BY si.sessionyear DESC LIMIT 1");
    $stmtSt->bind_param('is', $sccode, $stid);
}
$stmtSt->execute();
$student = $stmtSt->get_result()->fetch_assoc();
$stmtSt->close();

if (!$student) {
    api_response('error', 'Student not found with the provided ID.', null, 404);
}

$sessionyear = !empty($targetSession) ? $targetSession : ($student['sessionyear'] ?: date('Y'));
$sessLike = "%$sessionyear%";

// 2. Fetch Payable & Pending Items from stfinance (Filtered by active session and month)
$currentMonth = intval(date('n')); // 1 to 12
$uptoMonth = isset($_GET['upto_month']) && intval($_GET['upto_month']) > 0 ? intval($_GET['upto_month']) : $currentMonth;
$allMonthsParam = $_GET['all_months'] ?? null;
$allMonths = ($allMonthsParam !== null && ($allMonthsParam === '1' || $allMonthsParam === 'true' || $allMonthsParam === 1 || $allMonthsParam === true || $allMonthsParam === 'all'));

if ($allMonths) {
    $stmtFin = $conn->prepare("SELECT * 
    FROM stfinance 
    WHERE sccode = ? AND stid = ? AND dues > 0 AND (sessionyear = ? OR sessionyear LIKE ?)
    ORDER BY month ASC, id ASC");
    $stmtFin->bind_param('isis', $sccode, $stid, $sessionyear, $sessLike);
} else {
    $stmtFin = $conn->prepare("SELECT * 
    FROM stfinance 
    WHERE sccode = ? AND stid = ? AND dues > 0 AND (sessionyear = ? OR sessionyear LIKE ?) AND (month <= ? OR month = 0 OR month IS NULL)
    ORDER BY month ASC, id ASC");
    $stmtFin->bind_param('isisis', $sccode, $stid, $sessionyear, $sessLike, $uptoMonth);
}

$stmtFin->execute();
$finRes = $stmtFin->get_result();

$payableItems = [];
$totalDues = 0;

// 12 Months dynamic keywords map (English & Bengali)
$allMonthKeywords = [
    1  => ['january', 'jan', 'জানুয়ারি', 'জানুয়ারি'],
    2  => ['february', 'feb', 'ফেব্রুয়ারি', 'ফেব্রুয়ারি'],
    3  => ['march', 'mar', 'মার্চ'],
    4  => ['april', 'apr', 'এপ্রিল'],
    5  => ['may', 'মে'],
    6  => ['june', 'jun', 'জুন'],
    7  => ['july', 'jul', 'জুলাই'],
    8  => ['august', 'aug', 'আগস্ট'],
    9  => ['september', 'sep', 'সেপ্টেম্বর'],
    10 => ['october', 'oct', 'অক্টোবর'],
    11 => ['november', 'nov', 'নভেম্বর'],
    12 => ['december', 'dec', 'ডিসেম্বর']
];

while ($item = $finRes->fetch_assoc()) {
    $itemDue = floatval($item['dues']);
    $itemMonth = intval($item['month'] ?? 0);

    if (!$allMonths) {
        // 1. If explicit numeric month is greater than current/upto month, filter out
        if ($itemMonth > 0 && $itemMonth > $uptoMonth) {
            continue;
        }

        // 2. If month is 0/null but title explicitly mentions any future month relative to current month, filter out
        $titleLower = strtolower(($item['particulareng'] ?? '') . ' ' . ($item['particularben'] ?? ''));
        $isFutureItem = false;
        foreach ($allMonthKeywords as $mNum => $kws) {
            if ($mNum > $uptoMonth) {
                foreach ($kws as $kw) {
                    if (strpos($titleLower, $kw) !== false) {
                        $isFutureItem = true;
                        break 2;
                    }
                }
            }
        }
        if ($isFutureItem) {
            continue;
        }
    }

    $totalDues += $itemDue;

    $payableItems[] = [
        'id' => intval($item['id']),
        'sccode' => intval($item['sccode'] ?? $sccode),
        'sessionyear' => $item['sessionyear'] ?: $sessionyear,
        'classname' => $item['classname'] ?: ($student['classname'] ?? ''),
        'sectionname' => $item['sectionname'] ?: ($student['sectionname'] ?? ''),
        'stid' => (string)$item['stid'],
        'rollno' => intval($item['rollno'] ?: ($student['rollno'] ?? 1)),
        'partid' => intval($item['partid'] ?? 0),
        'itemcode' => $item['itemcode'] ?? '',
        'sub_head' => intval($item['sub_head'] ?? 0),
        'title_en' => $item['particulareng'] ?? 'Fee Item',
        'particulareng' => $item['particulareng'] ?? 'Fee Item',
        'title_bn' => $item['particularben'] ?? '',
        'particularben' => $item['particularben'] ?? '',
        'amount' => floatval($item['amount'] ?? 0),
        'payable_amt' => floatval($item['payableamt'] ?? 0),
        'payableamt' => floatval($item['payableamt'] ?? 0),
        'paid_so_far' => floatval($item['paid'] ?? 0),
        'paid' => floatval($item['paid'] ?? 0),
        'paidx' => floatval($item['paidx'] ?? 0),
        'due_amount' => $itemDue,
        'dues' => $itemDue,
        'month' => $itemMonth,
        'idmon' => $item['idmon'] ?? '',
        'setupdate' => $item['setupdate'] ?? null,
        'setupby' => $item['setupby'] ?? null,
        'modifieddate' => $item['modifieddate'] ?? null,
        'modifiedby' => $item['modifiedby'] ?? null,
        'pr1' => intval($item['pr1'] ?? 0),
        'pr1no' => $item['pr1no'] ?? null,
        'pr1date' => $item['pr1date'] ?? null,
        'pr1by' => $item['pr1by'] ?? null,
        'cashbook1' => intval($item['cashbook1'] ?? 0),
        'pr2' => intval($item['pr2'] ?? 0),
        'pr2no' => $item['pr2no'] ?? null,
        'pr2date' => $item['pr2date'] ?? null,
        'pr2by' => $item['pr2by'] ?? null,
        'cashbook2' => intval($item['cashbook2'] ?? 0),
        'remark' => $item['remark'] ?? null,
        'extra' => intval($item['extra'] ?? 0),
        'last_update' => $item['last_update'] ?? null,
        'validate' => intval($item['validate'] ?? 0),
        'validationtime' => $item['validationtime'] ?? '2024-01-01 00:00:00',
        'deleteby' => $item['deleteby'] ?? null,
        'deletetime' => $item['deletetime'] ?? null,
        'splitid' => $item['splitid'] ?? null,
        'scan_status' => intval($item['scan_status'] ?? 3),
        'splitid2' => $item['splitid2'] !== null ? intval($item['splitid2']) : null
    ];
}
$stmtFin->close();

// Fetch overall all-time total dues for the student in the active session
$stmtAllDues = $conn->prepare("SELECT COALESCE(SUM(dues), 0) AS all_dues FROM stfinance WHERE sccode = ? AND stid = ? AND dues > 0 AND (sessionyear = ? OR sessionyear LIKE ?)");
$stmtAllDues->bind_param('isis', $sccode, $stid, $sessionyear, $sessLike);
$stmtAllDues->execute();
$allTimeTotalDues = floatval($stmtAllDues->get_result()->fetch_assoc()['all_dues'] ?? $totalDues);
$stmtAllDues->close();

// 3. Fetch Recent Payment History (stpr)
$stmtPr = $conn->prepare("SELECT 
    prno, 
    prdate, 
    SUM(amount) AS amount, 
    COALESCE(GROUP_CONCAT(DISTINCT peng SEPARATOR ', '), 'Tuition/Fees') AS particulars,
    MAX(entryby) AS entryby, 
    MAX(entrytime) AS entrytime, 
    MAX(collection_media) AS collection_media 
FROM stpr 
WHERE sccode = ? AND stid = ? AND (sessionyear = ? OR sessionyear IS NULL OR sessionyear = '')
GROUP BY prno, prdate
ORDER BY prno DESC LIMIT 20");
$stmtPr->bind_param('iss', $sccode, $stid, $sessionyear);
$stmtPr->execute();
$resPr = $stmtPr->get_result();
$paymentHistory = [];
while ($pRow = $resPr->fetch_assoc()) {
    $paymentHistory[] = [
        'prno' => (string)$pRow['prno'],
        'prdate' => $pRow['prdate'],
        'amount' => floatval($pRow['amount']),
        'particulars' => $pRow['particulars'] ?? '',
        'entryby' => $pRow['entryby'] ?? 'Counter Cashier',
        'collection_media' => $pRow['collection_media'] ?? 'Cash'
    ];
}
$stmtPr->close();

$lastPr = count($paymentHistory) > 0 ? $paymentHistory[0] : null;

// Compute Next Receipt Number suggestion
$suggestedPrNo = null;
if ($lastPr && !empty($lastPr['prno'])) {
    $suggestedPrNo = intval($lastPr['prno']) + 1;
} else {
    // Generate base PR from year + stid suffix
    $shortYear = date('y');
    $numericSuffix = intval(substr($stid, -4)) ?: 1;
    $suggestedPrNo = intval($shortYear . sprintf("%04d", $numericSuffix) . '01');
}

api_response('success', 'Student dues loaded successfully.', [
    'student' => [
        'stid' => (string)$student['stid'],
        'name_eng' => $student['stnameeng'] ?? '',
        'name_ben' => $student['stnameben'] ?? '',
        'sccode' => intval($student['sccode']),
        'sessionyear' => intval($sessionyear),
        'classname' => $student['classname'] ?? '',
        'sectionname' => $student['sectionname'] ?? '',
        'rollno' => intval($student['rollno'] ?? 0),
        'guardian_mobile' => $student['guarmobile'] ?? '',
        'guardian_name' => $student['guarname'] ?? '',
        'photo_url' => file_exists(__DIR__ . '/../../students/' . $stid . '.jpg') ? 'students/' . $stid . '.jpg' : null
    ],
    'total_dues' => $totalDues,
    'all_time_total_dues' => $allTimeTotalDues,
    'current_month' => $currentMonth,
    'upto_month' => $uptoMonth,
    'payable_items' => $payableItems,
    'last_payment' => $lastPr,
    'payment_history' => $paymentHistory,
    'suggested_prno' => $suggestedPrNo
]);
