<?php
/**
 * EIMBox REST API — Complete Academic Structure Tree Endpoint
 * Route: GET /api/v1/academics/structure.php?sccode={sccode}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode']);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 1. Fetch Slots
$slots = [];
$slotStmt = $conn->prepare("SELECT id, slotname FROM slots WHERE sccode = ? OR sccode = 0 ORDER BY id ASC");
$slotStmt->bind_param('i', $sccode);
$slotStmt->execute();
$slotRes = $slotStmt->get_result();
while ($row = $slotRes->fetch_assoc()) {
    $slots[] = $row;
}
$slotStmt->close();

// 2. Fetch Sessions & Areas (Classes & Sections)
$classesTree = [];
$sessionsList = [];

$areaStmt = $conn->prepare("SELECT id, slot, sessionyear, areaname AS classname, subarea AS sectionname, classteacher 
FROM areas 
WHERE sccode = ? 
ORDER BY sessionyear DESC, id ASC");
$areaStmt->bind_param('i', $sccode);
$areaStmt->execute();
$areaRes = $areaStmt->get_result();
while ($row = $areaRes->fetch_assoc()) {
    $session = $row['sessionyear'];
    $slot = $row['slot'] ?: 'General';
    $className = $row['classname'];
    $sectionName = $row['sectionname'];

    if (!in_array($session, $sessionsList)) {
        $sessionsList[] = $session;
    }

    if (!isset($classesTree[$session])) {
        $classesTree[$session] = [];
    }
    if (!isset($classesTree[$session][$slot])) {
        $classesTree[$session][$slot] = [];
    }
    if (!isset($classesTree[$session][$slot][$className])) {
        $classesTree[$session][$slot][$className] = [
            'classname' => $className,
            'sections' => []
        ];
    }

    if (!empty($sectionName)) {
        $classesTree[$session][$slot][$className]['sections'][] = [
            'id' => $row['id'],
            'sectionname' => $sectionName,
            'classteacher_id' => $row['classteacher']
        ];
    }
}
$areaStmt->close();

// 2b. Fetch School Category from scinfo
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

// 3. Fetch Subjects with sccode priority and sccategory filter (eliminating duplicate subcodes)
$subjects = [];
$subjStmt = $conn->prepare("SELECT s.id, s.sccode, s.sccategory, s.subcode, s.subject AS subname, s.subben AS subname_bn, s.subshname AS shortname, s.fourth, s.sup_class 
FROM subjects s
WHERE (s.sccode = ? OR s.sccode = 0) 
  AND (s.sccategory = ? OR s.sccategory = '' OR s.sccategory IS NULL)
ORDER BY s.subcode ASC, (s.sccode = ?) DESC");
$subjStmt->bind_param('isi', $sccode, $sccategory, $sccode);
$subjStmt->execute();
$subjRes = $subjStmt->get_result();

$seenSubcodes = [];
while ($row = $subjRes->fetch_assoc()) {
    $code = intval($row['subcode']);
    if (isset($seenSubcodes[$code])) continue; // Keep highest priority (sccode = $sccode)
    $seenSubcodes[$code] = true;

    $supClasses = !empty($row['sup_class']) ? explode('.', trim($row['sup_class'], '.')) : ['All'];
    
    $subjItem = [
        'id' => intval($row['id']),
        'subcode' => $code,
        'subname_en' => $row['subname'],
        'subname_bn' => $row['subname_bn'],
        'shortname' => $row['shortname'],
        'is_fourth_subject' => intval($row['fourth']),
        'supported_classes' => $supClasses
    ];
    
    $subjects[] = $subjItem;
}
$subjStmt->close();

// 3b. Fetch Subject Setup (subsetup) with Prioritized Subject Name & Mark Breakdown
$subsetupList = [];
$subSetupStmt = $conn->prepare("SELECT ss.id, ss.slno, ss.sccode, ss.sessionyear, ss.slot, ss.classname, ss.sectionname, 
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
WHERE ss.sccode = ?
ORDER BY ss.sessionyear DESC, ss.classname ASC, ss.slno ASC, ss.subject ASC");
if ($subSetupStmt) {
    $subSetupStmt->bind_param('sssi', $sccategory, $sccategory, $sccategory, $sccode);
    $subSetupStmt->execute();
    $subSetupRes = $subSetupStmt->get_result();
    while ($row = $subSetupRes->fetch_assoc()) {
        $subsetupList[] = [
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
    $subSetupStmt->close();
}

// 4. Fetch Exams (examlist)
$exams = [];
$examStmt = $conn->prepare("SELECT id, sccode, sessionyear, examtitle, examcode, slot, datestart, result_publish 
FROM examlist 
WHERE sccode = ? OR sccode = 0 
ORDER BY sessionyear DESC, datestart ASC, id ASC");
if ($examStmt) {
    $examStmt->bind_param('i', $sccode);
    $examStmt->execute();
    $examRes = $examStmt->get_result();
    while ($row = $examRes->fetch_assoc()) {
        $exams[] = [
            'id' => intval($row['id']),
            'sessionyear' => intval($row['sessionyear']),
            'examtitle' => $row['examtitle'],
            'examcode' => $row['examcode'],
            'slot' => $row['slot'],
            'datestart' => $row['datestart'],
            'result_publish' => $row['result_publish']
        ];
    }
    $examStmt->close();
}

// 4b. Fetch GPA Grading Scale (gpa)
$gpaScale = [];
$gpaStmt = $conn->prepare("SELECT id, sccode, slot, minvalues, maxvalues, gp, gl, remark, colorcode 
FROM gpa 
WHERE sccode = ? OR sccode = 0 
ORDER BY minvalues DESC, id ASC");
if ($gpaStmt) {
    $gpaStmt->bind_param('i', $sccode);
    $gpaStmt->execute();
    $gpaRes = $gpaStmt->get_result();
    while ($row = $gpaRes->fetch_assoc()) {
        $gpaScale[] = [
            'id' => intval($row['id']),
            'sccode' => intval($row['sccode']),
            'slot' => $row['slot'] ?: 'School',
            'minvalues' => intval($row['minvalues']),
            'maxvalues' => intval($row['maxvalues']),
            'gp' => floatval($row['gp']),
            'gl' => $row['gl'],
            'remark' => $row['remark'],
            'colorcode' => $row['colorcode']
        ];
    }
    $gpaStmt->close();
}

// 5. Build Flat Classes, Sections, and Class-Section Map for the Requested Session
$reqSession = strval($_GET['session'] ?? $sessionsList[0] ?? date('Y'));
$flatClasses = [];
$flatSections = [];
$classSectionMap = [];

$targetTree = $classesTree[$reqSession] ?? (count($classesTree) > 0 ? reset($classesTree) : []);
if (is_array($targetTree)) {
    foreach ($targetTree as $slot => $classes) {
        if (is_array($classes)) {
            foreach ($classes as $cName => $cData) {
                if (!in_array($cName, $flatClasses)) {
                    $flatClasses[] = $cName;
                }
                if (!isset($classSectionMap[$cName])) {
                    $classSectionMap[$cName] = [];
                }
                if (!empty($cData['sections']) && is_array($cData['sections'])) {
                    foreach ($cData['sections'] as $sec) {
                        $sName = $sec['sectionname'] ?? '';
                        if (!empty($sName)) {
                            if (!in_array($sName, $flatSections)) {
                                $flatSections[] = $sName;
                            }
                            if (!in_array($sName, $classSectionMap[$cName])) {
                                $classSectionMap[$cName][] = $sName;
                            }
                        }
                    }
                }
            }
        }
    }
}

api_response('success', 'Academic structure retrieved successfully.', [
    'sccode' => $sccode,
    'sessionyear' => $reqSession,
    'slots' => $slots,
    'sessions' => $sessionsList,
    'classes_tree' => $classesTree,
    'classes' => $flatClasses,
    'sections' => $flatSections,
    'class_section_map' => $classSectionMap,
    'subjects' => $subjects,
    'subjects_by_class' => $subjects,
    'subsetup' => $subsetupList,
    'exams' => $exams,
    'gpa_scale' => $gpaScale
]);

