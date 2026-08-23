<?php
/**
 * EIMBox REST API — Subject Setup (subsetup) Query Endpoint
 * Route: GET /api/v1/exams/subsetup.php?sccode={sccode}&sessionyear={session}&classname={class}&sectionname={section}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode']);
$sessionyear = strval($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));
$classname = trim(strval($_GET['classname'] ?? $_GET['class'] ?? ''));
$sectionname = trim(strval($_GET['sectionname'] ?? $_GET['section'] ?? ''));

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 1. Fetch School Category (sccategory / $sctype) from scinfo if not passed
$sccategory = trim(strval($_GET['sccategory'] ?? $_GET['sctype'] ?? ''));
if (empty($sccategory)) {
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
}
if (empty($sccategory)) {
    $sccategory = 'School';
}

// Build query with prioritized sccode and sccategory matching to eliminate duplicate rows
$sql = "SELECT ss.id, ss.slno, ss.sccode, ss.sessionyear, ss.slot, ss.classname, ss.sectionname, 
ss.subject AS subcode, ss.fullmarks, ss.ctest, ss.mtest, ss.subj, ss.obj, ss.pra, ss.ca, ss.camanual, ss.ctmt, 
ss.pass_algorithm, ss.fourth, ss.combind_1, ss.combind_2, ss.combind_3, ss.combind_4,
COALESCE(
  (SELECT s.subject FROM subjects s WHERE s.subcode = ss.subject AND (s.sccode = ss.sccode OR s.sccode = 0) AND (s.sccategory = ? OR s.sccategory = '' OR s.sccategory IS NULL) ORDER BY (s.sccode = ss.sccode) DESC, s.sccode DESC LIMIT 1),
  (SELECT s.subject FROM subjects s WHERE s.subcode = ss.subject AND (s.sccode = ss.sccode OR s.sccode = 0) ORDER BY (s.sccode = ss.sccode) DESC, s.sccode DESC LIMIT 1),
  CONCAT('Subject ', ss.subject)
) AS subname_en,
(SELECT s.subben FROM subjects s WHERE s.subcode = ss.subject AND (s.sccode = ss.sccode OR s.sccode = 0) AND (s.sccategory = ? OR s.sccategory = '' OR s.sccategory IS NULL) ORDER BY (s.sccode = ss.sccode) DESC, s.sccode DESC LIMIT 1) AS subname_bn,
(SELECT s.subshname FROM subjects s WHERE s.subcode = ss.subject AND (s.sccode = ss.sccode OR s.sccode = 0) AND (s.sccategory = ? OR s.sccategory = '' OR s.sccategory IS NULL) ORDER BY (s.sccode = ss.sccode) DESC, s.sccode DESC LIMIT 1) AS shortname
FROM subsetup ss
WHERE ss.sccode = ? AND ss.sessionyear = ? AND ss.classname = ? AND ss.sectionname = ?";

$params = [$sccategory, $sccategory, $sccategory, $sccode, $sessionyear, $classname, $sectionname];
$types = "sssisss";

if (!empty($classname)) {
    $sql .= " AND ss.classname = ?";
    $params[] = $classname;
    $types .= "s";
}

if (!empty($sectionname) && strtolower($sectionname) !== 'all' && strtolower($sectionname) !== 'all sections') {
    $sql .= " AND (ss.sectionname = ? OR ss.sectionname = '' OR ss.sectionname IS NULL OR ss.sectionname = 'All')";
    $params[] = $sectionname;
    $types .= "s";
}

$sql .= " ORDER BY ss.classname ASC, ss.slno ASC, ss.subject ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$subjects = [];
while ($row = $res->fetch_assoc()) {
    $subjects[] = [
        'id' => intval($row['id']),
        'slno' => intval($row['slno']),
        'sessionyear' => strval($row['sessionyear']),
        'slot' => $row['slot'] ?: 'School',
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'subcode' => intval($row['subcode']),
        'subname_en' => $row['subname_en'] ?: ('Subject ' . $row['subcode']),
        'subname_bn' => $row['subname_bn'],
        'shortname' => $row['shortname'],
        'fullmarks' => intval($row['fullmarks'] ?? 100),
        'subj' => intval($row['subj'] ?? 0),
        'obj' => intval($row['obj'] ?? 0),
        'pra' => intval($row['pra'] ?? 0),
        'ca' => intval($row['ca'] ?? 0),
        'camanual' => intval($row['camanual'] ?? 0),
        'ctest' => intval($row['ctest'] ?? 0),
        'mtest' => intval($row['mtest'] ?? 0),
        'ctmt' => intval($row['ctmt'] ?? 0),
        'pass_algorithm' => intval($row['pass_algorithm'] ?? 0),
        'fourth' => intval($row['fourth'] ?? 0),
        'combind_1' => intval($row['combind_1'] ?? 0),
        'combind_2' => intval($row['combind_2'] ?? 0),
        'combind_3' => intval($row['combind_3'] ?? 0),
        'combind_4' => intval($row['combind_4'] ?? 0)
    ];
}
$stmt->close();

api_response('success', 'Subject setup retrieved successfully.', [
    'sccode' => $sccode,
    'sessionyear' => $sessionyear,
    'classname' => $classname,
    'sectionname' => $sectionname,
    'count' => count($subjects),
    'subjects' => $subjects
]);
