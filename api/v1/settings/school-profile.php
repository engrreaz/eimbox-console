<?php
/**
 * EIMBox REST API — Institute Profile & Configuration Endpoint
 * Route: GET /api/v1/settings/school-profile.php
 * Query Params: ?sccode={sccode}
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$input = get_api_input();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. Resolve School Code from Query, Body, or Token
$sccode = intval($_GET['sccode'] ?? $input['sccode'] ?? $user['sccode'] ?? 0);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

// 2. Handle POST / PUT: Update Institute Profile
if ($method === 'POST' || $method === 'PUT') {
    $scname = trim($input['name'] ?? $input['scname'] ?? '');
    $scadd1 = trim($input['scadd1'] ?? $input['address_line1'] ?? '');
    $scadd2 = trim($input['scadd2'] ?? $input['address_line2'] ?? '');
    $ps = trim($input['ps'] ?? $input['upazila'] ?? $input['thana'] ?? '');
    $dist = trim($input['dist'] ?? $input['district'] ?? '');
    $postal_code = intval($input['postal_code'] ?? $input['zip'] ?? 0);
    $mobile = trim($input['mobile'] ?? $input['phone'] ?? '');
    $scmail = trim($input['scmail'] ?? $input['email'] ?? '');
    $scweb = trim($input['scweb'] ?? $input['website'] ?? '');
    $headname = trim($input['head_name'] ?? $input['headname'] ?? '');
    $headtitle = trim($input['head_title'] ?? $input['headtitle'] ?? 'Headmaster');
    $geolat = trim($input['geolat'] ?? $input['lat'] ?? '');
    $geolon = trim($input['geolon'] ?? $input['lon'] ?? '');

    $updateSql = "UPDATE scinfo SET 
        scname = COALESCE(NULLIF(?, ''), scname),
        scadd1 = COALESCE(NULLIF(?, ''), scadd1),
        scadd2 = COALESCE(NULLIF(?, ''), scadd2),
        ps = COALESCE(NULLIF(?, ''), ps),
        dist = COALESCE(NULLIF(?, ''), dist),
        postal_code = CASE WHEN ? > 0 THEN ? ELSE postal_code END,
        mobile = COALESCE(NULLIF(?, ''), mobile),
        scmail = COALESCE(NULLIF(?, ''), scmail),
        scweb = COALESCE(NULLIF(?, ''), scweb),
        headname = COALESCE(NULLIF(?, ''), headname),
        headtitle = COALESCE(NULLIF(?, ''), headtitle),
        geolat = COALESCE(NULLIF(?, ''), geolat),
        geolon = COALESCE(NULLIF(?, ''), geolon),
        modifieddate = NOW()
    WHERE sccode = ?";

    $upStmt = $conn->prepare($updateSql);
    $upStmt->bind_param(
        'sssssiisssssssi',
        $scname, $scadd1, $scadd2, $ps, $dist,
        $postal_code, $postal_code,
        $mobile, $scmail, $scweb,
        $headname, $headtitle,
        $geolat, $geolon,
        $sccode
    );

    if (!$upStmt->execute()) {
        api_response('error', 'Failed to update institute profile: ' . $conn->error, null, 500);
    }
    $upStmt->close();
}

// 3. Fetch latest institute profile from scinfo
$stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
$stmt->bind_param('i', $sccode);
$stmt->execute();
$sc = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sc) {
    api_response('error', 'Institute profile not found for sccode: ' . $sccode, null, 404);
}

// 4. Fetch active counts
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

$actionMsg = ($method === 'POST' || $method === 'PUT') 
    ? 'Institute profile updated successfully.' 
    : 'Institute profile retrieved successfully.';

api_response('success', $actionMsg, [
    'institute' => [
        'sccode' => intval($sc['sccode']),
        'name' => $sc['scname'],
        'category' => $sc['sccategory'] ?? 'School',
        'address' => trim(($sc['scadd1'] ?? '') . ', ' . ($sc['ps'] ?? '') . ', ' . ($sc['dist'] ?? '')),
        'scadd1' => $sc['scadd1'] ?? '',
        'scadd2' => $sc['scadd2'] ?? '',
        'ps' => $sc['ps'] ?? '',
        'dist' => $sc['dist'] ?? '',
        'postal_code' => intval($sc['postal_code'] ?? 0),
        'mobile' => $sc['mobile'] ?? '',
        'scmail' => $sc['scmail'] ?? '',
        'scweb' => $sc['scweb'] ?? '',
        'root_user' => $sc['rootuser'] ?? '',
        'head_name' => $sc['headname'] ?? '',
        'head_title' => $sc['headtitle'] ?? 'Head Teacher',
        'geolat' => $sc['geolat'] ?? '',
        'geolon' => $sc['geolon'] ?? '',
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
