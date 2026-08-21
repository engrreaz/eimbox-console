<?php
/**
 * EIMBox REST API — Class Routine & Schedule Endpoint
 * Route: GET /api/v1/academics/class-routine.php
 * Query Params: ?sccode={sccode}&session={session}&class={class}&section={section}&tid={tid}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$session = intval($_GET['session'] ?? date('Y'));
$className = trim($_GET['class'] ?? '');
$sectionName = trim($_GET['section'] ?? '');
$tid = trim($_GET['tid'] ?? '');

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 1. Fetch Periods from classschedule
$periods = [];
$pStmt = $conn->prepare("SELECT period, timestart, timeend, slots FROM classschedule WHERE sccode = ? AND sessionyear = ? ORDER BY period ASC");
$pStmt->bind_param('is', $sccode, $session);
$pStmt->execute();
$pRes = $pStmt->get_result();
while ($p = $pRes->fetch_assoc()) {
    $periods[] = [
        'period' => intval($p['period']),
        'start_time' => $p['timestart'],
        'end_time' => $p['timeend'],
        'slot' => $p['slots']
    ];
}
$pStmt->close();

// 2. Fetch Routine entries from clsroutine
$where = "r.sccode = ? AND r.sessionyear = ?";
$types = "is";
$params = [$sccode, $session];

if (!empty($className)) {
    $where .= " AND r.classname = ?";
    $types .= "s";
    $params[] = $className;
}
if (!empty($sectionName)) {
    $where .= " AND r.sectionname = ?";
    $types .= "s";
    $params[] = $sectionName;
}
if (!empty($tid)) {
    $where .= " AND r.tid = ?";
    $types .= "s";
    $params[] = $tid;
}

$sql = "SELECT r.id, r.classname, r.sectionname, r.period, r.wday, r.subcode, r.tid,
s.subject AS subname, s.subben AS subname_bn, s.subshname AS shortname,
t.tname AS teacher_name, t.position AS teacher_designation
FROM clsroutine r
LEFT JOIN subjects s ON (s.subcode = r.subcode AND (s.sccode = r.sccode OR s.sccode = 0))
LEFT JOIN teacher t ON (t.tid = r.tid AND t.sccode = r.sccode)
WHERE $where
ORDER BY r.wday ASC, r.period ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$wdayNames = [
    1 => 'Saturday',
    2 => 'Sunday',
    3 => 'Monday',
    4 => 'Tuesday',
    5 => 'Wednesday',
    6 => 'Thursday',
    7 => 'Friday'
];

$routine = [];
while ($row = $res->fetch_assoc()) {
    $wday = intval($row['wday']);
    $routine[] = [
        'id' => intval($row['id']),
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'wday' => $wday,
        'day_name' => $wdayNames[$wday] ?? 'Day ' . $wday,
        'period' => intval($row['period']),
        'subject' => [
            'subcode' => intval($row['subcode']),
            'name_eng' => $row['subname'] ?? '',
            'name_ben' => $row['subname_bn'] ?? '',
            'shortname' => $row['shortname'] ?? ''
        ],
        'teacher' => [
            'tid' => (string)$row['tid'],
            'name' => $row['teacher_name'] ?? '',
            'designation' => $row['teacher_designation'] ?? ''
        ]
    ];
}
$stmt->close();

api_response('success', 'Class routine retrieved successfully.', [
    'sccode' => $sccode,
    'session' => $session,
    'class' => $className ?: 'All',
    'section' => $sectionName ?: 'All',
    'periods' => $periods,
    'routine' => $routine
]);
