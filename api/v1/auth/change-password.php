<?php
/**
 * EIMBox REST API — Change Password Endpoint
 * Route: POST /api/v1/auth/change-password.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate caller
$user = authenticate_token($conn);

$input = get_api_input();
$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';

if (empty($currentPassword) || empty($newPassword)) {
    api_response('error', 'Both current_password and new_password are required.', null, 400);
}

if (strlen($newPassword) < 6) {
    api_response('error', 'New password must be at least 6 characters long.', null, 400);
}

// Verify current password
if (!password_verify($currentPassword, $user['password_hash'])) {
    api_response('error', 'Current password is incorrect.', null, 401);
}

$newHash = password_hash($newPassword, PASSWORD_BCRYPT);
$upStmt = $conn->prepare("UPDATE usersapp SET password_hash = ?, modifieddate = NOW() WHERE id = ?");
$upStmt->bind_param('si', $newHash, $user['id']);
$upStmt->execute();
$upStmt->close();

api_response('success', 'Password changed successfully.');
