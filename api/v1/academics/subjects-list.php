<?php
/**
 * EIMBox REST API — Subject List & Evaluation Rules Endpoint
 * Route: GET /api/v1/academics/subjects-list.php
 * Query Params: ?sccode={sccode}&class={class}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
$className = trim($_GET['class'] ?? '');

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// Fetch School Category from scinfo
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

$stmt = $conn->prepare("SELECT id, sccode, sccategory, subcode, subject AS subname, subben AS subname_bn, subshname AS shortname, ncode, fourth, sup_class 
FROM subjects 
WHERE (sccode = ? OR sccode = 0)
  AND sccategory = ?
ORDER BY subcode ASC, (sccode = ?) DESC");
$stmt->bind_param('isi', $sccode, $sccategory, $sccode);
$stmt->execute();
$res = $stmt->get_result();


$subjects = [];
$seenSubcodes = [];
while ($row = $res->fetch_assoc()) {
    $code = intval($row['subcode']);
    if (isset($seenSubcodes[$code])) continue;
    $seenSubcodes[$code] = true;

    $supClasses = !empty($row['sup_class']) ? explode('.', trim($row['sup_class'], '.')) : ['All'];

    // Filter by class if specified
    if (!empty($className) && strtolower($className) !== 'all') {
        $match = in_array('All', $supClasses) || in_array($className, $supClasses);
        if (!$match) continue;
    }

    $subjects[] = [
        'id' => intval($row['id']),
        'subcode' => $code,
        'name_eng' => $row['subname'],
        'name_ben' => $row['subname_bn'] ?? '',
        'shortname' => $row['shortname'] ?? '',
        'is_fourth_subject' => intval($row['fourth']) === 1,
        'noipunno_code' => intval($row['ncode'] ?? 0),
        'supported_classes' => $supClasses
    ];
}
$stmt->close();
error_log("subjects-list.php" . print_r($subjects, true));
api_response('success', 'Subjects loaded successfully.', [
    'sccode' => $sccode,
    'class' => $className ?: 'All',
    'total_count' => count($subjects),
    'subjects' => $subjects
]);
