<?php
/**
 * EIMBox REST API — User Login Endpoint
 * Route: POST /api/v1/auth/login.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

$input = get_api_input();

$email = trim($input['email'] ?? $input['username'] ?? '');
$password = $input['password'] ?? '';
$hw_uuid = trim($input['hw_uuid'] ?? '');
$mac_addr = trim($input['mac_addr'] ?? '');

if (empty($email) || empty($password)) {
    api_response('error', 'Email/Username and Password are required.', null, 400);
}

// 1. Check in usersapp
$stmt = $conn->prepare("SELECT * FROM usersapp WHERE email = ? OR username = ? LIMIT 1");
$stmt->bind_param('ss', $email, $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    // Check if it's a student login (stid)
    $stmtSt = $conn->prepare("SELECT * FROM students WHERE stid = ? AND (guarmobile = ? OR dob = ?) LIMIT 1");
    $stmtSt->bind_param('sss', $email, $password, $password);
    $stmtSt->execute();
    $resSt = $stmtSt->get_result();
    $student = $resSt->fetch_assoc();
    $stmtSt->close();

    if ($student) {
        // Fetch School Info
        $stmtSc = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
        $stmtSc->bind_param('i', $student['sccode']);
        $stmtSc->execute();
        $school = $stmtSc->get_result()->fetch_assoc();
        $stmtSc->close();

        $token = generate_token($student['stid'], $student['sccode']);
        api_response('success', 'Student authentication successful.', [
            'token' => $token,
            'user_type' => 'student',
            'mfa_required' => false,
            'user' => [
                'id' => $student['stid'],
                'stid' => $student['stid'],
                'name_eng' => $student['stnameeng'],
                'name_ben' => $student['stnameben'],
                'sccode' => $student['sccode'],
                'classname' => $student['classname'],
                'sectionname' => $student['sectionname'],
                'rollno' => $student['rollno'],
                'role' => 'Student',
                'userlevel' => 'Student'
            ],
            'school' => [
                'sccode' => $school['sccode'] ?? $student['sccode'],
                'scname' => $school['scname'] ?? '',
                'scaddress' => trim(($school['scadd1'] ?? '') . ', ' . ($school['ps'] ?? '') . ', ' . ($school['dist'] ?? '')),
                'mobile' => $school['mobile'] ?? ''
            ]
        ]);
    }

    api_response('error', 'Invalid email, username or password.', null, 401);
}

// 2. Lockout Check
$failedAttempts = intval($user['failed_attempts'] ?? 0);
$lockUntil = $user['lock_until'] ?? null;

if ($lockUntil && strtotime($lockUntil) > time()) {
    api_response('error', 'Account is temporarily locked due to multiple failed login attempts. Try again later.', null, 403);
}

// 3. Password Verification
$passwordHash = $user['password_hash'] ?? '';
if (!password_verify($password, $passwordHash)) {
    $failedAttempts++;
    $newLock = null;
    if ($failedAttempts >= 5) {
        $newLock = date('Y-m-d H:i:s', time() + 300);
    }
    
    $upStmt = $conn->prepare("UPDATE usersapp SET failed_attempts = ?, lock_until = ? WHERE id = ?");
    $upStmt->bind_param('isi', $failedAttempts, $newLock, $user['id']);
    $upStmt->execute();
    $upStmt->close();

    api_response('error', 'Invalid email or password.', null, 401);
}

// Reset failed attempts upon successful password match
$rstStmt = $conn->prepare("UPDATE usersapp SET failed_attempts = 0, lock_until = NULL WHERE id = ?");
$rstStmt->bind_param('i', $user['id']);
$rstStmt->execute();
$rstStmt->close();

// 4. MFA Check
if (!empty($user['mfa_enabled'])) {
    $otp = sprintf("%06d", random_int(100000, 999999));
    $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
    $expires = date('Y-m-d H:i:s', time() + 300); // 5 mins

    $mfaStmt = $conn->prepare("UPDATE usersapp SET mfa_secret = ?, mfa_temp_token = ?, mfa_temp_expires = ? WHERE id = ?");
    $mfaStmt->bind_param('sssi', $otp, $hashedOtp, $expires, $user['id']);
    $mfaStmt->execute();
    $mfaStmt->close();

    // Send OTP via mail / sms (placeholder handler)
    // require_once __DIR__ . '/../../mailer/send-mail.php';

    api_response('success', 'MFA required. 6-digit verification code has been sent.', [
        'mfa_required' => true,
        'temp_token' => base64_encode(json_encode(['uid' => $user['id'], 'time' => time()])),
        'expires_in' => 300,
        'email_hint' => substr($user['email'], 0, 3) . '***@' . substr(strrchr($user['email'], "@"), 1)
    ]);
}

// 5. Fetch School Metadata
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

api_response('success', 'Login successful.', [
    'token' => $token,
    'mfa_required' => false,
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'username' => $user['username'],
        'profilename' => $user['profilename'] ?? $user['email'],
        'mobile' => $user['mobile'],
        'sccode' => $user['sccode'],
        'userlevel' => $user['userlevel'],
        'is_admin' => intval($user['is_admin'] ?? $user['admin'] ?? 0),
        'admin' => intval($user['is_admin'] ?? $user['admin'] ?? 0),
        'admin_level' => intval($user['is_admin'] ?? $user['admin'] ?? 0),
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
        'tier' => $school['tier'] ?? 'A',
        'headname' => $school['headname'] ?? '',
        'headtitle' => $school['headtitle'] ?? 'Headmaster'
    ] : null
]);
