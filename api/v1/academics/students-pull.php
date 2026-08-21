<?php
/**
 * EIMBox REST API — Students Pull Endpoint
 * Route: GET /api/v1/academics/students-pull.php
 * Query Params: ?sccode={sccode}&session={session}&class={class}&section={section}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate Bearer Token
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$session = intval($_GET['session'] ?? date('Y'));
$className = trim($_GET['class'] ?? '');
$sectionName = trim($_GET['section'] ?? '');

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// Build query
$where = "si.sccode = ? AND si.sessionyear LIKE ?";
$types = "is";
$params = [$sccode, "%$session%"];

if (!empty($className) && strtolower($className) !== 'all') {
    $where .= " AND si.classname = ?";
    $types .= "s";
    $params[] = $className;
}

if (!empty($sectionName) && strtolower($sectionName) !== 'all') {
    $where .= " AND si.sectionname = ?";
    $types .= "s";
    $params[] = $sectionName;
}

$sql = "SELECT 
    si.stid,
    si.sessionyear,
    si.classname,
    si.sectionname,
    si.rollno,
    si.lastpr,
    s.stnameeng,
    s.stnameben,
    s.fname,
    s.mname,
    s.guarmobile,
    s.guaremail,
    s.dob,
    s.gender,
    s.previll,
    s.prepo,
    s.preps,
    s.predist,
    s.photo,
    s.photo_id,
    COALESCE(df.total_due, 0) AS total_dues
FROM sessioninfo si
LEFT JOIN students s ON s.stid = si.stid AND s.sccode = si.sccode
LEFT JOIN (
    SELECT stid, sccode, sessionyear, SUM(dues) AS total_due 
    FROM stfinance 
    WHERE sccode = ? 
    GROUP BY stid, sccode, sessionyear
) df ON df.stid = si.stid AND df.sessionyear LIKE ?
WHERE $where
ORDER BY si.classname ASC, si.sectionname ASC, si.rollno ASC";

// Add subquery params to front
array_unshift($params, "%$session%");
array_unshift($params, $sccode);
$types = "is" . $types;

$stmt = $conn->prepare($sql);
if (!$stmt) {
    api_response('error', 'Query preparation failed: ' . $conn->error, null, 500);
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$students = [];
while ($row = $res->fetch_assoc()) {
    $stid = $row['stid'];
    $photoUrl = '';
    if (!empty($row['photo'])) {
        $photoUrl = 'students/' . $row['photo'];
    } elseif (file_exists(__DIR__ . '/../../students/' . $stid . '.jpg')) {
        $photoUrl = 'students/' . $stid . '.jpg';
    }

    $students[] = [
        'stid' => (string)$row['stid'],
        'sessionyear' => intval($row['sessionyear']),
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'rollno' => intval($row['rollno']),
        'name_eng' => $row['stnameeng'] ?? '',
        'name_ben' => $row['stnameben'] ?? '',
        'father_name' => $row['fname'] ?? '',
        'mother_name' => $row['mname'] ?? '',
        'guardian_mobile' => $row['guarmobile'] ?? '',
        'guardian_email' => $row['guaremail'] ?? '',
        'dob' => $row['dob'] ?? '',
        'gender' => $row['gender'] ?? '',
        'address' => trim(($row['previll'] ?? '') . ', ' . ($row['prepo'] ?? '') . ', ' . ($row['predist'] ?? '')),
        'photo_url' => $photoUrl,
        'total_dues' => floatval($row['total_dues']),
        'last_pr_no' => $row['lastpr'] ? (string)$row['lastpr'] : null
    ];
}
$stmt->close();

api_response('success', 'Students retrieved successfully.', [
    'sccode' => $sccode,
    'session' => $session,
    'class' => $className ?: 'All',
    'section' => $sectionName ?: 'All',
    'total_count' => count($students),
    'students' => $students
]);
