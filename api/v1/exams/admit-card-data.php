<?php
/**
 * EIMBox REST API — Admit Card & Exam Roll Slip Data Endpoint
 * Route: GET /api/v1/exams/admit-card-data.php
 * Query Params: ?sccode={sccode}&session={session}&exam={exam}&class={class}&section={section}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$session = intval($_GET['session'] ?? date('Y'));
$exam = trim($_GET['exam'] ?? '');
$className = trim($_GET['class'] ?? '');
$sectionName = trim($_GET['section'] ?? '');

if ($sccode <= 0 || empty($exam) || empty($className)) {
    api_response('error', 'sccode, exam, and class are required.', null, 400);
}

// 1. Fetch School Branding
$scStmt = $conn->prepare("SELECT scname, scadd1, scadd2, ps, dist, mobile, logo, headname, headtitle FROM scinfo WHERE sccode = ? LIMIT 1");
$scStmt->bind_param('i', $sccode);
$scStmt->execute();
$school = $scStmt->get_result()->fetch_assoc();
$scStmt->close();

// 2. Fetch Exam Dates & Meta from examlist
$exStmt = $conn->prepare("SELECT id, examtitle, slot, datestart, result_publish FROM examlist WHERE sccode = ? AND sessionyear LIKE ? AND examtitle = ? LIMIT 1");
$exStmt->bind_param('iss', $sccode, $session, $exam);
$exStmt->execute();
$examInfo = $exStmt->get_result()->fetch_assoc();
$exStmt->close();

// 3. Fetch Students List
$where = "si.sccode = ? AND si.sessionyear LIKE ? AND si.classname = ?";
$types = "iss";
$params = [$sccode, "%$session%", $className];

if (!empty($sectionName) && strtolower($sectionName) !== 'all') {
    $where .= " AND si.sectionname = ?";
    $types .= "s";
    $params[] = $sectionName;
}

$sql = "SELECT si.stid, si.rollno, si.classname, si.sectionname, si.groupname,
s.stnameeng, s.stnameben, s.fname, s.mname, s.guarmobile, s.photo 
FROM sessioninfo si 
LEFT JOIN students s ON s.stid = si.stid AND s.sccode = si.sccode 
WHERE $where 
ORDER BY si.sectionname ASC, si.rollno ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$students = [];
while ($row = $res->fetch_assoc()) {
    $stid = (string)$row['stid'];
    $photoUrl = '';
    if (!empty($row['photo'])) {
        $photoUrl = 'students/' . $row['photo'];
    } elseif (file_exists(__DIR__ . '/../../students/' . $stid . '.jpg')) {
        $photoUrl = 'students/' . $stid . '.jpg';
    }

    $students[] = [
        'stid' => $stid,
        'rollno' => intval($row['rollno']),
        'name_eng' => $row['stnameeng'] ?? '',
        'name_ben' => $row['stnameben'] ?? '',
        'father_name' => $row['fname'] ?? '',
        'mother_name' => $row['mname'] ?? '',
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'group' => $row['groupname'] ?? '',
        'photo_url' => $photoUrl
    ];
}
$stmt->close();

api_response('success', 'Admit card data generated.', [
    'school' => [
        'sccode' => $sccode,
        'scname' => $school['scname'] ?? '',
        'address' => trim(($school['scadd1'] ?? '') . ', ' . ($school['ps'] ?? '') . ', ' . ($school['dist'] ?? '')),
        'mobile' => $school['mobile'] ?? '',
        'head_name' => $school['headname'] ?? '',
        'head_title' => $school['headtitle'] ?? 'Head Teacher'
    ],
    'exam' => [
        'title' => $exam,
        'sessionyear' => $session,
        'start_date' => $examInfo['datestart'] ?? ''
    ],
    'total_candidates' => count($students),
    'candidates' => $students
]);
