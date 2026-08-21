<?php
/**
 * EIMBox REST API — Detailed Student Profile Endpoint
 * Route: GET /api/v1/academics/student-details.php
 * Query Params: ?sccode={sccode}&stid={stid}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$stid = trim($_GET['stid'] ?? '');

if ($sccode <= 0 || empty($stid)) {
    api_response('error', 'Both sccode and stid are required.', null, 400);
}

// 1. Fetch Student Profile
$stmt = $conn->prepare("SELECT s.*, si.sessionyear, si.classname, si.sectionname, si.rollno, si.groupname, si.lastpr 
FROM students s
LEFT JOIN sessioninfo si ON si.stid = s.stid AND si.sccode = s.sccode
WHERE s.sccode = ? AND s.stid = ?
ORDER BY si.sessionyear DESC LIMIT 1");
$stmt->bind_param('is', $sccode, $stid);
$stmt->execute();
$st = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$st) {
    api_response('error', 'Student not found.', null, 404);
}

// 2. Fetch Outstanding Dues
$fStmt = $conn->prepare("SELECT SUM(dues) AS total_dues, SUM(paid) AS total_paid FROM stfinance WHERE sccode = ? AND stid = ?");
$fStmt->bind_param('is', $sccode, $stid);
$fStmt->execute();
$fin = $fStmt->get_result()->fetch_assoc();
$fStmt->close();

// 3. Fetch Recent Payments
$pStmt = $conn->prepare("SELECT prno, prdate, amount, collection_media FROM stpr WHERE sccode = ? AND stid = ? ORDER BY prno DESC LIMIT 5");
$pStmt->bind_param('is', $sccode, $stid);
$pStmt->execute();
$pRes = $pStmt->get_result();
$recentPayments = [];
while ($p = $pRes->fetch_assoc()) {
    $recentPayments[] = [
        'prno' => (string)$p['prno'],
        'prdate' => $p['prdate'],
        'amount' => floatval($p['amount']),
        'media' => $p['collection_media'] ?: 'Cash'
    ];
}
$pStmt->close();

$photoUrl = '';
if (!empty($st['photo'])) {
    $photoUrl = 'students/' . $st['photo'];
} elseif (file_exists(__DIR__ . '/../../students/' . $stid . '.jpg')) {
    $photoUrl = 'students/' . $stid . '.jpg';
}

api_response('success', 'Student details retrieved successfully.', [
    'student' => [
        'stid' => (string)$st['stid'],
        'name_eng' => $st['stnameeng'] ?? '',
        'name_ben' => $st['stnameben'] ?? '',
        'father_name' => $st['fname'] ?? '',
        'father_name_ben' => $st['fnameben'] ?? '',
        'father_mobile' => $st['fmobile'] ?? '',
        'mother_name' => $st['mname'] ?? '',
        'mother_name_ben' => $st['mnameben'] ?? '',
        'mother_mobile' => $st['mmobile'] ?? '',
        'guardian_name' => $st['guarname'] ?? '',
        'guardian_mobile' => $st['guarmobile'] ?? '',
        'guardian_relation' => $st['guarrelation'] ?? '',
        'dob' => $st['dob'] ?? '',
        'gender' => $st['gender'] ?? '',
        'blood_group' => $st['bgroup'] ?? '',
        'religion' => $st['religion'] ?? '',
        'present_address' => [
            'village' => $st['previll'] ?? '',
            'post_office' => $st['prepo'] ?? '',
            'police_station' => $st['preps'] ?? '',
            'district' => $st['predist'] ?? ''
        ],
        'permanent_address' => [
            'village' => $st['pervill'] ?? '',
            'post_office' => $st['perpo'] ?? '',
            'police_station' => $st['perps'] ?? '',
            'district' => $st['perdist'] ?? ''
        ],
        'academic' => [
            'sccode' => intval($st['sccode']),
            'sessionyear' => intval($st['sessionyear'] ?? date('Y')),
            'classname' => $st['classname'] ?? '',
            'sectionname' => $st['sectionname'] ?? '',
            'rollno' => intval($st['rollno'] ?? 0),
            'group' => $st['groupname'] ?? '',
            'doa' => $st['doa'] ?? '',
            'last_pr_no' => $st['lastpr'] ? (string)$st['lastpr'] : null
        ],
        'finance_summary' => [
            'total_dues' => floatval($fin['total_dues'] ?? 0),
            'total_paid' => floatval($fin['total_paid'] ?? 0),
            'recent_payments' => $recentPayments
        ],
        'photo_url' => $photoUrl
    ]
]);
