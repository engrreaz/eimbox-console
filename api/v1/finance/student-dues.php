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

// 1. Fetch Student Profile & Current Session
$stmtSt = $conn->prepare("SELECT s.*, si.sessionyear, si.classname, si.sectionname, si.rollno, si.lastpr 
FROM students s
LEFT JOIN sessioninfo si ON si.stid = s.stid AND si.sccode = s.sccode
WHERE s.sccode = ? AND s.stid = ?
ORDER BY si.sessionyear DESC LIMIT 1");
$stmtSt->bind_param('is', $sccode, $stid);
$stmtSt->execute();
$student = $stmtSt->get_result()->fetch_assoc();
$stmtSt->close();

if (!$student) {
    api_response('error', 'Student not found with the provided ID.', null, 404);
}

$sessionyear = $student['sessionyear'] ?: date('Y');

// 2. Fetch Payable & Pending Items from stfinance (Filtered by month <= current_month or specified upto_month)
$currentMonth = intval(date('n')); // 1 to 12
$uptoMonth = isset($_GET['upto_month']) && intval($_GET['upto_month']) > 0 ? intval($_GET['upto_month']) : $currentMonth;
$allMonthsParam = $_GET['all_months'] ?? null;
$allMonths = ($allMonthsParam !== null && ($allMonthsParam === '1' || $allMonthsParam === 'true' || $allMonthsParam === 1 || $allMonthsParam === true || $allMonthsParam === 'all'));

if ($allMonths) {
    $stmtFin = $conn->prepare("SELECT id, partid, itemcode, particulareng, particularben, amount, payableamt, paid, dues, month, setupdate
    FROM stfinance 
    WHERE sccode = ? AND stid = ? AND dues > 0
    ORDER BY month ASC, id ASC");
    $stmtFin->bind_param('is', $sccode, $stid);
} else {
    $stmtFin = $conn->prepare("SELECT id, partid, itemcode, particulareng, particularben, amount, payableamt, paid, dues, month, setupdate
    FROM stfinance 
    WHERE sccode = ? AND stid = ? AND dues > 0 AND (month <= ? OR month = 0 OR month IS NULL)
    ORDER BY month ASC, id ASC");
    $stmtFin->bind_param('isi', $sccode, $stid, $uptoMonth);
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
        'partid' => intval($item['partid']),
        'itemcode' => $item['itemcode'],
        'title_en' => $item['particulareng'],
        'title_bn' => $item['particularben'],
        'amount' => floatval($item['amount']),
        'payable_amt' => floatval($item['payableamt']),
        'paid_so_far' => floatval($item['paid']),
        'due_amount' => $itemDue,
        'month' => $itemMonth,
        'setupdate' => $item['setupdate']
    ];
}
$stmtFin->close();

// Fetch overall all-time total dues for the student
$stmtAllDues = $conn->prepare("SELECT COALESCE(SUM(dues), 0) AS all_dues FROM stfinance WHERE sccode = ? AND stid = ? AND dues > 0");
$stmtAllDues->bind_param('is', $sccode, $stid);
$stmtAllDues->execute();
$allTimeTotalDues = floatval($stmtAllDues->get_result()->fetch_assoc()['all_dues'] ?? $totalDues);
$stmtAllDues->close();

// 3. Fetch Last Payment Record (stpr)
$stmtPr = $conn->prepare("SELECT prno, prdate, amount, entryby, entrytime, collection_media 
FROM stpr 
WHERE sccode = ? AND stid = ? 
ORDER BY prno DESC LIMIT 1");
$stmtPr->bind_param('is', $sccode, $stid);
$stmtPr->execute();
$lastPr = $stmtPr->get_result()->fetch_assoc();
$stmtPr->close();

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
    'last_payment' => $lastPr ? [
        'prno' => (string)$lastPr['prno'],
        'prdate' => $lastPr['prdate'],
        'amount' => floatval($lastPr['amount']),
        'entryby' => $lastPr['entryby'],
        'collection_media' => $lastPr['collection_media'] ?? 'Cash'
    ] : null,
    'suggested_prno' => $suggestedPrNo
]);
