<?php
/**
 * EIMBox REST API — Delete User from usersapp & clean permissions
 * Route: POST /api/v1/users/delete.php
 */

require_once __DIR__ . '/../bootstrap.php';

$input = get_api_input();
$email = trim($input['email'] ?? $_GET['email'] ?? '');
$sccode = intval($input['sccode'] ?? $_GET['sccode'] ?? 0);

if (empty($email)) {
    api_response('error', 'Email address is required.', null, 400);
}

// 1. Delete user from usersapp
if ($sccode > 0) {
    $stmt = $conn->prepare("DELETE FROM usersapp WHERE email = ? AND sccode = ?");
    $stmt->bind_param("si", $email, $sccode);
} else {
    $stmt = $conn->prepare("DELETE FROM usersapp WHERE email = ?");
    $stmt->bind_param("s", $email);
}

$success = $stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

// 2. Clean custom permissions if permissions table exists
try {
    if ($sccode > 0) {
        $pStmt = $conn->prepare("DELETE FROM user_custom_permissions WHERE email = ? AND sccode = ?");
        $pStmt->bind_param("si", $email, $sccode);
    } else {
        $pStmt = $conn->prepare("DELETE FROM user_custom_permissions WHERE email = ?");
        $pStmt->bind_param("s", $email);
    }
    $pStmt->execute();
    $pStmt->close();
} catch (Exception $e) { }

api_response($success ? 'success' : 'error', $success ? "User {$email} deleted successfully ({$affected} records removed)." : 'Failed to delete user.', [
    'email' => $email,
    'affected' => $affected
]);
