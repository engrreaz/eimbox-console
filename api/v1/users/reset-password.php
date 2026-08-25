<?php
/**
 * EIMBox REST API — Reset User Password & Fixed PIN
 * Route: POST /api/v1/users/reset-password.php
 */

require_once __DIR__ . '/../bootstrap.php';

$input = get_api_input();

$email = trim($input['email'] ?? '');
$sccode = intval($input['sccode'] ?? 0);
$password_hash = trim($input['password_hash'] ?? '');
$fixedpin = trim($input['fixedpin'] ?? '');

if (empty($email)) {
    api_response('error', 'Email address is required.', null, 400);
}

if (empty($password_hash) && empty($fixedpin)) {
    api_response('error', 'Either password_hash or fixedpin is required to reset credentials.', null, 400);
}

$updates = [];
$params = [];
$types = '';

if (!empty($password_hash)) {
    $updates[] = "password_hash = ?";
    $params[] = $password_hash;
    $types .= 's';
}

if (!empty($fixedpin)) {
    $updates[] = "fixedpin = ?";
    $params[] = $fixedpin;
    $types .= 's';
}

$updates[] = "failed_attempts = 0";
$updates[] = "lock_until = NULL";
$updates[] = "modifieddate = NOW()";

$updateStr = implode(', ', $updates);

if ($sccode > 0) {
    $sql = "UPDATE usersapp SET {$updateStr} WHERE email = ? AND sccode = ?";
    $params[] = $email;
    $params[] = $sccode;
    $types .= 'si';
} else {
    $sql = "UPDATE usersapp SET {$updateStr} WHERE email = ?";
    $params[] = $email;
    $types .= 's';
}

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $success = $stmt->execute();
    $stmt->close();

    api_response($success ? 'success' : 'error', $success ? "Credentials reset successfully for {$email}." : 'Failed to update credentials in database.', [
        'email' => $email
    ]);
} else {
    api_response('error', 'Database query prepare failed: ' . $conn->error, null, 500);
}
