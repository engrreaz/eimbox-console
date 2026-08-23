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

// Build query
$sql = "SELECT ss.id, ss.slno, ss.sccode, ss.sessionyear, ss.slot, ss.classname, ss.sectionname, 
ss.subject AS subcode, ss.fullmarks, ss.ctest, ss.mtest, ss.subj, ss.obj, ss.pra, ss.ca, ss.camanual, ss.ctmt, 
ss.pass_algorithm, ss.fourth, ss.combind_1, ss.combind_2, ss.combind_3, ss.combind_4,
s.subject AS subname_en, s.subben AS subname_bn, s.subshname AS shortname
FROM subsetup ss
LEFT JOIN subjects s ON s.subcode = ss.subject AND (s.sccode = ss.sccode OR s.sccode = 0)
WHERE ss.sccode = ? AND ss.sessionyear = ?";

$params = [$sccode, $sessionyear];
$types = "is";

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
