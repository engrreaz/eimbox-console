<?php
/**
 * EIMBox Modern Web Device Registration API
 * Endpoint: /api/register_device.php
 * Handles FCM Web Token registration and logout deactivation
 */
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data)) {
    echo json_encode(['success' => false, 'message' => 'Empty request payload.']);
    exit;
}

$action = trim($data['action'] ?? 'register');
$fcmToken = trim($data['fcm_token'] ?? '');
$deviceUuid = trim($data['device_id'] ?? $data['device_uuid'] ?? '');

if (empty($fcmToken) && empty($deviceUuid)) {
    echo json_encode(['success' => false, 'message' => 'FCM Token or Device UUID is required.']);
    exit;
}

// 1. Handle Logout / Deactivation
if ($action === 'logout') {
    if (!empty($fcmToken)) {
        $stmt = $conn->prepare("UPDATE user_devices SET is_active = 0, modifieddate = NOW() WHERE fcm_token = ?");
        $stmt->bind_param("s", $fcmToken);
        $stmt->execute();
        $stmt->close();
    } elseif (!empty($deviceUuid)) {
        $stmt = $conn->prepare("UPDATE user_devices SET is_active = 0, modifieddate = NOW() WHERE device_uuid = ?");
        $stmt->bind_param("s", $deviceUuid);
        $stmt->execute();
        $stmt->close();
    }
    echo json_encode(['success' => true, 'message' => 'Device successfully deactivated on logout.']);
    exit;
}

// 2. Handle Register / Update
$userId = (int)($data['user_id'] ?? $_SESSION['user_id'] ?? $_SESSION['userid'] ?? 0);
$sccode = (int)($data['sccode'] ?? $_SESSION['sccode'] ?? 0);
$userRole = trim($data['user_role'] ?? $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'general');
$platform = trim($data['platform'] ?? 'web_modern');
$browserName = substr(trim($data['browser_name'] ?? 'Browser'), 0, 50);
$browserVersion = substr(trim($data['browser_version'] ?? ''), 0, 50);
$osName = substr(trim($data['os_name'] ?? 'Unknown'), 0, 50);
$deviceType = in_array($data['device_type'] ?? '', ['mobile', 'tablet', 'desktop']) ? $data['device_type'] : 'desktop';
$endpointOrigin = substr(trim($data['endpoint_origin'] ?? ($_SERVER['HTTP_ORIGIN'] ?? '')), 0, 255);

if (empty($deviceUuid)) {
    $deviceUuid = 'dev_' . md5($fcmToken);
}

// Upsert into user_devices
$query = "INSERT INTO user_devices 
    (user_id, sccode, user_role, device_uuid, fcm_token, platform, browser_name, browser_version, os_name, device_type, endpoint_origin, is_active, created_at, modifieddate)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
    ON DUPLICATE KEY UPDATE 
        user_id = VALUES(user_id),
        sccode = VALUES(sccode),
        user_role = VALUES(user_role),
        fcm_token = VALUES(fcm_token),
        platform = VALUES(platform),
        browser_name = VALUES(browser_name),
        browser_version = VALUES(browser_version),
        os_name = VALUES(os_name),
        device_type = VALUES(device_type),
        endpoint_origin = VALUES(endpoint_origin),
        is_active = 1,
        last_active = NOW(),
        modifieddate = NOW()";

$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param(
        "iisssssssss",
        $userId,
        $sccode,
        $userRole,
        $deviceUuid,
        $fcmToken,
        $platform,
        $browserName,
        $browserVersion,
        $osName,
        $deviceType,
        $endpointOrigin
    );
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode([
            'success'     => true,
            'message'     => 'Modern web device registered successfully.',
            'device_uuid' => $deviceUuid,
            'platform'    => $platform
        ]);
    } else {
        $err = $stmt->error;
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Failed to save device: ' . $err]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
}
