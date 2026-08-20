<?php
/**
 * EIMBox REST API — Current User Profile & Token Verification
 * Route: GET /api/v1/auth/me.php
 */

require_once __DIR__ . '/../bootstrap.php';

$user = authenticate_token($conn);

$sccode = intval($user['sccode']);
$school = null;
if ($sccode > 0) {
    $scStmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
    $scStmt->bind_param('i', $sccode);
    $scStmt->execute();
    $school = $scStmt->get_result()->fetch_assoc();
    $scStmt->close();
}

// Fetch user's permissions
$permissions = [];
$permStmt = $conn->prepare("SELECT page_name, permission FROM permission_map WHERE (sccode = ? OR sccode = 0) AND (userlevel = ? OR email = ?)");
$permStmt->bind_param('iss', $sccode, $user['userlevel'], $user['email']);
$permStmt->execute();
$permRes = $permStmt->get_result();
while ($row = $permRes->fetch_assoc()) {
    $permissions[$row['page_name']] = intval($row['permission']);
}
$permStmt->close();

api_response('success', 'Token is active and valid.', [
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'username' => $user['username'],
        'profilename' => $user['profilename'] ?? $user['email'],
        'mobile' => $user['mobile'],
        'sccode' => $user['sccode'],
        'userlevel' => $user['userlevel'],
        'admin_level' => intval($user['admin']),
        'is_chief' => intval($user['is_chief'] ?? 0),
        'photourl' => $user['photourl'] ?? ''
    ],
    'school' => $school ? [
        'sccode' => $school['sccode'],
        'scname' => $school['scname'],
        'sccategory' => $school['sccategory'],
        'scaddress' => trim($school['scadd1'] . ', ' . $school['ps'] . ', ' . $school['dist']),
        'mobile' => $school['mobile'],
        'package_id' => $school['package_id'] ?? 2,
        'package_name' => $school['package_name'] ?? '',
        'tier' => $school['tier'] ?? 'A'
    ] : null,
    'permissions' => $permissions
]);
