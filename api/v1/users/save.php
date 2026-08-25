<?php
/**
 * EIMBox REST API — Create / Update User in usersapp
 * Route: POST /api/v1/users/save.php
 */

require_once __DIR__ . '/../bootstrap.php';

$input = get_api_input();

$email = trim($input['email'] ?? '');
$sccode = intval($input['sccode'] ?? 0);

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_response('error', 'Valid email address is required.', null, 400);
}

$profilename = trim($input['profilename'] ?? $input['dispname'] ?? '');
$username = trim($input['username'] ?? strtolower(explode('@', $email)[0]));
$userlevel = trim($input['userlevel'] ?? 'Teacher');
$mobile = trim($input['mobile'] ?? $input['cell'] ?? '');
$userid = trim($input['userid'] ?? '');
$photourl = trim($input['photourl'] ?? '');
$fixedpin = trim($input['fixedpin'] ?? '123456');

// Role & Admin Level calculation
$admin = isset($input['admin']) ? intval($input['admin']) : 0;
if ($userlevel === 'Super Administrator' || $userlevel === 'SuperAdmin') {
    $admin = 5;
} elseif ($userlevel === 'Administrator' || $userlevel === 'Admin') {
    $admin = 3;
}

$is_chief = isset($input['is_chief']) ? intval($input['is_chief']) : ($userlevel === 'Head Teacher' || $userlevel === 'Principal' ? 1 : 0);
$status = isset($input['status']) ? intval($input['status']) : 1;
$active = isset($input['active']) ? intval($input['active']) : 1;
$mfa_enabled = isset($input['mfa_enabled']) ? intval($input['mfa_enabled']) : 0;
$two_factor = isset($input['two_factor']) ? intval($input['two_factor']) : $mfa_enabled;
$mfa_type = trim($input['mfa_type'] ?? 'totp');
$login_gmail = isset($input['login_gmail']) ? intval($input['login_gmail']) : 1;
$login_pass = isset($input['login_pass']) ? intval($input['login_pass']) : 1;
$login_token = isset($input['login_token']) ? intval($input['login_token']) : 1;
$login_qrcode = isset($input['login_qrcode']) ? intval($input['login_qrcode']) : 1;

// Check if user already exists
$checkStmt = $conn->prepare("SELECT id, password_hash, fixedpin FROM usersapp WHERE email = ? AND sccode = ?");
$checkStmt->bind_param("si", $email, $sccode);
$checkStmt->execute();
$existing = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if ($existing) {
    // Update existing user
    $password_hash = !empty($input['password_hash']) ? $input['password_hash'] : $existing['password_hash'];
    $pin = !empty($input['fixedpin']) ? $input['fixedpin'] : $existing['fixedpin'];

    $updateStmt = $conn->prepare("UPDATE usersapp SET 
        username = ?, password_hash = ?, fixedpin = ?, profilename = ?, mobile = ?,
        userlevel = ?, admin = ?, is_chief = ?, status = ?, active = ?,
        two_factor = ?, mfa_enabled = ?, mfa_type = ?, login_gmail = ?, login_pass = ?,
        login_token = ?, login_qrcode = ?, photourl = ?, userid = ?, modifieddate = NOW()
        WHERE email = ? AND sccode = ?");
    
    $updateStmt->bind_param("ssssssiiiiiiiissssisi",
        $username, $password_hash, $pin, $profilename, $mobile,
        $userlevel, $admin, $is_chief, $status, $active,
        $two_factor, $mfa_enabled, $mfa_type, $login_gmail, $login_pass,
        $login_token, $login_qrcode, $photourl, $userid, $email, $sccode
    );

    $success = $updateStmt->execute();
    $updateStmt->close();

    api_response($success ? 'success' : 'error', $success ? 'User profile updated successfully.' : 'Failed to update user profile.', [
        'email' => $email,
        'sccode' => $sccode,
        'action' => 'updated'
    ]);
} else {
    // Insert new user
    $password_hash = !empty($input['password_hash']) ? $input['password_hash'] : '$argon2id$v=19$m=65536,t=4,p=1$defSalt$defHash';

    $insertStmt = $conn->prepare("INSERT INTO usersapp (
        email, username, password_hash, fixedpin, sccode, profilename, mobile,
        userlevel, admin, is_chief, status, active, two_factor, mfa_enabled,
        mfa_type, login_gmail, login_pass, login_token, login_qrcode,
        photourl, userid, created_at, modifieddate
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

    $insertStmt->bind_param("ssssisssiiiiiiiisssss",
        $email, $username, $password_hash, $fixedpin, $sccode, $profilename, $mobile,
        $userlevel, $admin, $is_chief, $status, $active, $two_factor, $mfa_enabled,
        $mfa_type, $login_gmail, $login_pass, $login_token, $login_qrcode,
        $photourl, $userid
    );

    $success = $insertStmt->execute();
    $insertId = $conn->insert_id;
    $insertStmt->close();

    api_response($success ? 'success' : 'error', $success ? 'New user registered successfully.' : 'Failed to register user.', [
        'id' => $insertId,
        'email' => $email,
        'sccode' => $sccode,
        'action' => 'created'
    ]);
}
