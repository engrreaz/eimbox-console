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
$session = intval($_GET['session'] ?? $_GET['sessionyear'] ?? date('Y'));
$exam = trim($_GET['exam'] ?? '');
$className = trim($_GET['class'] ?? $_GET['classname'] ?? '');
$sectionName = trim($_GET['section'] ?? $_GET['sectionname'] ?? '');
$subcode = intval($_GET['subcode'] ?? $_GET['subject'] ?? 0);

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
        'name' => $stRow['stnameeng'] ?: ($stRow['stnameben'] ?: "Student {$stRow['rollno']}"),
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

// Fetch School Category from scinfo
$sccategory = 'School';
$scStmt = $conn->prepare("SELECT sccategory FROM scinfo WHERE sccode = ? LIMIT 1");
if ($scStmt) {
    $scStmt->bind_param("i", $sccode);
    $scStmt->execute();
    $scRes = $scStmt->get_result();
    if ($scRow = $scRes->fetch_assoc()) {
        $sccategory = trim($scRow['sccategory'] ?? 'School');
    }
    $scStmt->close();
}

$mSql = "SELECT m.id, m.stid, m.subject AS subcode, m.fullmark, m.ctest, m.mtest, m.subj, m.obj, m.pra, m.ca, m.markobt, m.on100, m.gp, m.gl,
COALESCE(
  (SELECT s.subject FROM subjects s WHERE s.subcode = m.subject AND (s.sccode = m.sccode OR s.sccode = 0) AND (s.sccategory = ? OR s.sccategory = '' OR s.sccategory IS NULL) ORDER BY (s.sccode = m.sccode) DESC, s.sccode DESC LIMIT 1),
  (SELECT s.subject FROM subjects s WHERE s.subcode = m.subject AND (s.sccode = m.sccode OR s.sccode = 0) ORDER BY (s.sccode = m.sccode) DESC, s.sccode DESC LIMIT 1),
  CONCAT('Subject ', m.subject)
) AS subname,
(SELECT s.subshname FROM subjects s WHERE s.subcode = m.subject AND (s.sccode = m.sccode OR s.sccode = 0) AND (s.sccategory = ? OR s.sccategory = '' OR s.sccategory IS NULL) ORDER BY (s.sccode = m.sccode) DESC, s.sccode DESC LIMIT 1) AS shortname
FROM stmark m
WHERE $mWhere";

array_unshift($mParams, $sccategory, $sccategory);
$mTypes = "ss" . $mTypes;

$mStmt = $conn->prepare($mSql);
$mStmt->bind_param($mTypes, ...$mParams);
$mStmt->execute();
$mRes = $mStmt->get_result();

$flatMarks = [];
while ($mRow = $mRes->fetch_assoc()) {
    $mStid = (string)$mRow['stid'];
    $markObj = [
        'id' => intval($mRow['id']),
        'stid' => $mStid,
        'subcode' => intval($mRow['subcode']),
        'subject_name' => $mRow['subname'] ?? '',
        'shortname' => $mRow['shortname'] ?? '',
        'fullmark' => intval($mRow['fullmark']),
        'ctest' => floatval($mRow['ctest'] ?? 0),
        'ct' => floatval($mRow['ctest'] ?? 0),
        'mtest' => floatval($mRow['mtest'] ?? 0),
        'mt' => floatval($mRow['mtest'] ?? 0),
        'subj' => floatval($mRow['subj']),
        'obj' => floatval($mRow['obj']),
        'pra' => floatval($mRow['pra']),
        'ca' => floatval($mRow['ca']),
        'markobt' => floatval($mRow['markobt']),
        'total' => floatval($mRow['markobt']),
        'on100' => floatval($mRow['on100']),
        'gp' => floatval($mRow['gp']),
        'gl' => $mRow['gl']
    ];

    $flatMarks[] = $markObj;

    if (isset($studentsList[$mStid])) {
        $studentsList[$mStid]['marks'][$mRow['subcode']] = $markObj;
    }
}
$mStmt->close();

api_response('success', 'Marks sheet loaded successfully.', [
    'sccode' => $sccode,
    'session' => $session,
    'sessionyear' => $sessionyear ?? $session,
    'exam' => $exam,
    'class' => $className,
    'classname' => $className,
    'section' => $sectionName ?: 'All',
    'sectionname' => $sectionName ?: 'All',
    'subcode' => $subcode ?: 'All',
    'total_students' => count($studentsList),
    'marks' => $flatMarks,
    'sheet_data' => array_values($studentsList)
]);
