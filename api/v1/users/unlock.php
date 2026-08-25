<?php
/**
 * EIMBox REST API — Unlock User Account
 * Route: POST /api/v1/users/unlock.php
 */

require_once __DIR__ . '/../bootstrap.php';

$input = get_api_input();

$email = trim($input['email'] ?? '');
$sccode = intval($input['sccode'] ?? 0);

if (empty($email)) {
    api_response('error', 'Email address is required.', null, 400);
}

if ($sccode > 0) {
    $stmt = $conn->prepare("UPDATE usersapp SET failed_attempts = 0, lock_until = NULL, status = 1, modifieddate = NOW() WHERE email = ? AND sccode = ?");
    $stmt->bind_param("si", $email, $sccode);
} else {
    $stmt = $conn->prepare("UPDATE usersapp SET failed_attempts = 0, lock_until = NULL, status = 1, modifieddate = NOW() WHERE email = ?");
    $stmt->bind_param("s", $email);
}

if ($stmt) {
    $success = $stmt->execute();
    $stmt->close();

    api_response($success ? 'success' : 'error', $success ? "Account {$email} has been successfully unlocked." : 'Failed to unlock user account.', [
        'email' => $email
    ]);
} else {
    api_response('error', 'Database query prepare failed: ' . $conn->error, null, 500);
}
