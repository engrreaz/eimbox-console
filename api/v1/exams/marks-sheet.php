<?php
/**
 * EIMBox REST API — Marks Sheet Grid Loader Endpoint
 * Route: GET /api/v1/exams/marks-sheet.php
 * Query Params: ?sccode={sccode}&session={session}&exam={exam}&class={class}&section={section}&subcode={subcode}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$session = intval($_GET['session'] ?? date('Y'));
$exam = trim($_GET['exam'] ?? '');
$className = trim($_GET['class'] ?? '');
$sectionName = trim($_GET['section'] ?? '');
$subcode = intval($_GET['subcode'] ?? 0);

if ($sccode <= 0 || empty($exam) || empty($className)) {
    api_response('error', 'sccode, exam, and class are required.', null, 400);
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

$stSql = "SELECT si.stid, si.rollno, si.classname, si.sectionname, s.stnameeng, s.stnameben, s.gender 
FROM sessioninfo si 
LEFT JOIN students s ON s.stid = si.stid AND s.sccode = si.sccode 
WHERE $stWhere 
ORDER BY si.sectionname ASC, si.rollno ASC";

$stStmt = $conn->prepare($stSql);
$stStmt->bind_param($stTypes, ...$stParams);
$stStmt->execute();
$stRes = $stStmt->get_result();

$studentsList = [];
$stids = [];
while ($stRow = $stRes->fetch_assoc()) {
    $stid = (string)$stRow['stid'];
    $stids[] = $stid;
    $studentsList[$stid] = [
        'stid' => $stid,
        'rollno' => intval($stRow['rollno']),
        'name_eng' => $stRow['stnameeng'] ?? '',
        'name_ben' => $stRow['stnameben'] ?? '',
        'classname' => $stRow['classname'],
        'sectionname' => $stRow['sectionname'],
        'marks' => []
    ];
}
$stStmt->close();

// 2. Fetch Stored Marks from stmark
$mWhere = "m.sccode = ? AND m.sessionyear LIKE ? AND m.exam = ? AND m.classname = ?";
$mTypes = "isss";
$mParams = [$sccode, "%$session%", $exam, $className];

if (!empty($sectionName) && strtolower($sectionName) !== 'all') {
    $mWhere .= " AND m.sectionname = ?";
    $mTypes .= "s";
    $mParams[] = $sectionName;
}

if ($subcode > 0) {
    $mWhere .= " AND m.subject = ?";
    $mTypes .= "i";
    $mParams[] = $subcode;
}

$mSql = "SELECT m.id, m.stid, m.subject AS subcode, m.fullmark, m.subj, m.obj, m.pra, m.ca, m.markobt, m.on100, m.gp, m.gl,
s.subject AS subname, s.subshname AS shortname
FROM stmark m
LEFT JOIN subjects s ON (s.subcode = m.subject AND (s.sccode = m.sccode OR s.sccode = 0))
WHERE $mWhere";

$mStmt = $conn->prepare($mSql);
$mStmt->bind_param($mTypes, ...$mParams);
$mStmt->execute();
$mRes = $mStmt->get_result();

while ($mRow = $mRes->fetch_assoc()) {
    $mStid = (string)$mRow['stid'];
    if (isset($studentsList[$mStid])) {
        $studentsList[$mStid]['marks'][$mRow['subcode']] = [
            'mark_id' => intval($mRow['id']),
            'subcode' => intval($mRow['subcode']),
            'subject_name' => $mRow['subname'] ?? '',
            'shortname' => $mRow['shortname'] ?? '',
            'fullmark' => intval($mRow['fullmark']),
            'subj' => floatval($mRow['subj']),
            'obj' => floatval($mRow['obj']),
            'pra' => floatval($mRow['pra']),
            'ca' => floatval($mRow['ca']),
            'markobt' => floatval($mRow['markobt']),
            'on100' => floatval($mRow['on100']),
            'gp' => floatval($mRow['gp']),
            'gl' => $mRow['gl']
        ];
    }
}
$mStmt->close();

api_response('success', 'Marks sheet loaded successfully.', [
    'sccode' => $sccode,
    'session' => $session,
    'exam' => $exam,
    'class' => $className,
    'section' => $sectionName ?: 'All',
    'subcode' => $subcode ?: 'All',
    'total_students' => count($studentsList),
    'sheet_data' => array_values($studentsList)
]);
