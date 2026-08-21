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

$stmt = $conn->prepare("SELECT id, sccode, sccategory, subcode, subject AS subname, subben AS subname_bn, subshname AS shortname, ncode, fourth, sup_class 
FROM subjects 
WHERE sccode = ? OR sccode = 0 
ORDER BY subcode ASC");
$stmt->bind_param('i', $sccode);
$stmt->execute();
$res = $stmt->get_result();

$subjects = [];
while ($row = $res->fetch_assoc()) {
    $supClasses = !empty($row['sup_class']) ? explode('.', trim($row['sup_class'], '.')) : ['All'];

    // Filter by class if specified
    if (!empty($className) && strtolower($className) !== 'all') {
        $match = in_array('All', $supClasses) || in_array($className, $supClasses);
        if (!$match) continue;
    }

    $subjects[] = [
        'id' => intval($row['id']),
        'subcode' => intval($row['subcode']),
        'name_eng' => $row['subname'],
        'name_ben' => $row['subname_bn'] ?? '',
        'shortname' => $row['shortname'] ?? '',
        'is_fourth_subject' => intval($row['fourth']) === 1,
        'noipunno_code' => intval($row['ncode'] ?? 0),
        'supported_classes' => $supClasses
    ];
}
$stmt->close();

api_response('success', 'Subjects loaded successfully.', [
    'sccode' => $sccode,
    'class' => $className ?: 'All',
    'total_count' => count($subjects),
    'subjects' => $subjects
]);
