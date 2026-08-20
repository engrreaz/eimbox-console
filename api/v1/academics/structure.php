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

// 3. Fetch Subjects
$subjects = [];
$subjStmt = $conn->prepare("SELECT id, sccode, sccategory, subcode, subject AS subname, subben AS subname_bn, subshname AS shortname, fourth, sup_class 
FROM subjects 
WHERE sccode = ? OR sccode = 0 
ORDER BY subcode ASC");
$subjStmt->bind_param('i', $sccode);
$subjStmt->execute();
$subjRes = $subjStmt->get_result();
while ($row = $subjRes->fetch_assoc()) {
    $supClasses = !empty($row['sup_class']) ? explode('.', trim($row['sup_class'], '.')) : ['All'];
    
    $subjItem = [
        'id' => intval($row['id']),
        'subcode' => intval($row['subcode']),
        'subname_en' => $row['subname'],
        'subname_bn' => $row['subname_bn'],
        'shortname' => $row['shortname'],
        'is_fourth_subject' => intval($row['fourth']),
        'supported_classes' => $supClasses
    ];
    
    $subjects[] = $subjItem;
}
$subjStmt->close();

// 4. Fetch Exams
$exams = [];
$examStmt = $conn->prepare("SELECT id, sessionyear, examtitle, slot, datestart, dateend, result_publish 
FROM examlist 
WHERE sccode = ? 
ORDER BY sessionyear DESC, datestart DESC");
$examStmt->bind_param('i', $sccode);
$examStmt->execute();
$examRes = $examStmt->get_result();
while ($row = $examRes->fetch_assoc()) {
    $exams[] = [
        'id' => $row['id'],
        'sessionyear' => intval($row['sessionyear']),
        'examtitle' => $row['examtitle'],
        'slot' => $row['slot'],
        'datestart' => $row['datestart'],
        'dateend' => $row['dateend'],
        'result_publish' => $row['result_publish']
    ];
}
$examStmt->close();

api_response('success', 'Academic structure retrieved successfully.', [
    'sccode' => $sccode,
    'slots' => $slots,
    'sessions' => $sessionsList,
    'classes_tree' => $classesTree,
    'subjects_by_class' => $subjects,
    'exams' => $exams
]);
