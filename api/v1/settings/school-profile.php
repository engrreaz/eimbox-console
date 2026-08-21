<?php
/**
 * EIMBox REST API — Institute Profile & Configuration Endpoint
 * Route: GET /api/v1/settings/school-profile.php
 * Query Params: ?sccode={sccode}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

$stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
$stmt->bind_param('i', $sccode);
$stmt->execute();
$sc = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sc) {
    api_response('error', 'Institute profile not found.', null, 404);
}

// Fetch active counts
$stCountStmt = $conn->prepare("SELECT COUNT(*) AS total_students FROM sessioninfo WHERE sccode = ? AND sessionyear LIKE ?");
$curYear = '%' . date('Y') . '%';
$stCountStmt->bind_param('is', $sccode, $curYear);
$stCountStmt->execute();
$stCount = $stCountStmt->get_result()->fetch_assoc()['total_students'] ?? 0;
$stCountStmt->close();

$teaCountStmt = $conn->prepare("SELECT COUNT(*) AS total_teachers FROM teacher WHERE sccode = ?");
$teaCountStmt->bind_param('i', $sccode);
$teaCountStmt->execute();
$teaCount = $teaCountStmt->get_result()->fetch_assoc()['total_teachers'] ?? 0;
$teaCountStmt->close();

api_response('success', 'Institute profile retrieved successfully.', [
    'institute' => [
        'sccode' => intval($sc['sccode']),
        'name' => $sc['scname'],
        'category' => $sc['sccategory'],
        'address' => trim(($sc['scadd1'] ?? '') . ', ' . ($sc['ps'] ?? '') . ', ' . ($sc['dist'] ?? '')),
        'mobile' => $sc['mobile'],
        'root_user' => $sc['rootuser'] ?? '',
        'head_name' => $sc['headname'] ?? '',
        'head_title' => $sc['headtitle'] ?? 'Head Teacher',
        'package_id' => intval($sc['package_id'] ?? 1),
        'package_name' => $sc['package_name'] ?? 'Standard',
        'tier' => $sc['tier'] ?? 'A',
        'valid_module' => $sc['valid_module'] ?? '',
        'active_module' => $sc['active_module'] ?? '',
        'stats' => [
            'active_students' => intval($stCount),
            'teachers_count' => intval($teaCount)
        ]
    ]
]);
