<?php
/**
 * EIMBox REST API — Daily Student Attendance Register Sheet
 * Route: GET /api/v1/attendance/daily-sheet.php
 * Query Params: ?sccode={sccode}&session={session}&date={YYYY-MM-DD}&class={class}&section={section}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$session = intval($_GET['session'] ?? date('Y'));
$date = trim($_GET['date'] ?? date('Y-m-d'));
$className = trim($_GET['class'] ?? '');
$sectionName = trim($_GET['section'] ?? '');

if ($sccode <= 0 || empty($className)) {
    api_response('error', 'sccode and class are required.', null, 400);
}

// 1. Fetch Students in Class/Section
$stWhere = "si.sccode = ? AND si.sessionyear LIKE ? AND si.classname = ?";
$stTypes = "iss";
$stParams = [$sccode, "%$session%", $className];

if (!empty($sectionName) && strtolower($sectionName) !== 'all') {
    $stWhere .= " AND si.sectionname = ?";
    $stTypes .= "s";
    $stParams[] = $sectionName;
}

$stSql = "SELECT si.stid, si.rollno, si.classname, si.sectionname, s.stnameeng, s.stnameben, s.guarmobile, s.gender 
FROM sessioninfo si 
LEFT JOIN students s ON s.stid = si.stid AND s.sccode = si.sccode 
WHERE $stWhere 
ORDER BY si.sectionname ASC, si.rollno ASC";

$stStmt = $conn->prepare($stSql);
$stStmt->bind_param($stTypes, ...$stParams);
$stStmt->execute();
$stRes = $stStmt->get_result();

$sheet = [];
while ($st = $stRes->fetch_assoc()) {
    $stid = (string)$st['stid'];
    $sheet[$stid] = [
        'stid' => $stid,
        'rollno' => intval($st['rollno']),
        'name_eng' => $st['stnameeng'] ?? '',
        'name_ben' => $st['stnameben'] ?? '',
        'classname' => $st['classname'],
        'sectionname' => $st['sectionname'],
        'guardian_mobile' => $st['guarmobile'] ?? '',
        'status' => null, // 1 = Present, 0 = Absent
        'in_time' => null,
        'out_time' => null
    ];
}
$stStmt->close();

// 2. Fetch Stored Attendance from stattnd
$attStmt = $conn->prepare("SELECT stid, yn, intime, outtime FROM stattnd WHERE sccode = ? AND adate = ?");
$attStmt->bind_param('is', $sccode, $date);
$attStmt->execute();
$attRes = $attStmt->get_result();

$presentCount = 0;
$absentCount = 0;

while ($att = $attRes->fetch_assoc()) {
    $aStid = (string)$att['stid'];
    if (isset($sheet[$aStid])) {
        $yn = intval($att['yn']);
        $sheet[$aStid]['status'] = $yn;
        $sheet[$aStid]['in_time'] = $att['intime'];
        $sheet[$aStid]['out_time'] = $att['outtime'];

        if ($yn === 1) $presentCount++;
        else $absentCount++;
    }
}
$attStmt->close();

$totalCount = count($sheet);
$unmarkedCount = $totalCount - ($presentCount + $absentCount);
$presentRate = $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 2) : 0;

api_response('success', 'Daily attendance sheet loaded.', [
    'sccode' => $sccode,
    'session' => $session,
    'date' => $date,
    'class' => $className,
    'section' => $sectionName ?: 'All',
    'summary' => [
        'total_students' => $totalCount,
        'present' => $presentCount,
        'absent' => $absentCount,
        'unmarked' => $unmarkedCount,
        'present_rate' => $presentRate
    ],
    'attendance_records' => array_values($sheet)
]);
