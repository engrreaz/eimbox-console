<?php
/**
 * EIMBox REST API — MFA OTP Verification Endpoint
 * Route: POST /api/v1/auth/mfa-verify.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

$input = get_api_input();
$tempToken = trim($input['temp_token'] ?? '');
$otpCode = trim($input['otp_code'] ?? $input['otp'] ?? '');

if (empty($tempToken) || empty($otpCode)) {
    api_response('error', 'Temporary token and 6-digit OTP code are required.', null, 400);
}

$tokenData = json_decode(base64_decode($tempToken), true);
if (!$tokenData || !isset($tokenData['uid'])) {
    api_response('error', 'Invalid or expired temporary session token.', null, 400);
}

$userId = intval($tokenData['uid']);

$stmt = $conn->prepare("SELECT * FROM usersapp WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    api_response('error', 'User not found.', null, 404);
}

if (empty($user['mfa_temp_expires']) || strtotime($user['mfa_temp_expires']) < time()) {
    api_response('error', 'MFA verification code has expired. Please login again.', null, 400);
}

// Verify against hash or direct token
$isValid = false;
if (!empty($user['mfa_temp_token']) && password_verify($otpCode, $user['mfa_temp_token'])) {
    $isValid = true;
} elseif (!empty($user['mfa_secret']) && $user['mfa_secret'] === $otpCode) {
    $isValid = true;
}

if (!$isValid) {
    api_response('error', 'Incorrect verification code. Please check and try again.', null, 401);
}

// Clear temporary tokens
$clrStmt = $conn->prepare("UPDATE usersapp SET mfa_temp_token = NULL, mfa_temp_expires = NULL WHERE id = ?");
$clrStmt->bind_param('i', $userId);
$clrStmt->execute();
$clrStmt->close();

// Fetch School Metadata
$sccode = intval($user['sccode']);
$school = null;
if ($sccode > 0) {
    $scStmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
    $scStmt->bind_param('i', $sccode);
    $scStmt->execute();
    $school = $scStmt->get_result()->fetch_assoc();
    $scStmt->close();
}

$token = generate_token($user['id'], $user['sccode']);

api_response('success', 'MFA verified successfully. Login granted.', [
    'token' => $token,
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
    ] : null
]);
