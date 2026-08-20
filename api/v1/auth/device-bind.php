<?php
/**
 * EIMBox REST API — Device Binding Endpoint
 * Route: POST /api/v1/auth/device-bind.php
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

// Authenticate caller (Admin token required)
$currentUser = authenticate_token($conn);

$input = get_api_input();
$sccode = intval($input['sccode'] ?? $currentUser['sccode']);
$hw_uuid = trim($input['hw_uuid'] ?? '');
$mac_addr = trim($input['mac_addr'] ?? '');
$deviceName = trim($input['device_name'] ?? 'EIMBox Desktop Terminal');
$osVersion = trim($input['os_version'] ?? 'Windows');

if (empty($hw_uuid)) {
    api_response('error', 'Hardware UUID is required for terminal binding.', null, 400);
}

// Create authorized_devices table if not exists
$createTableSQL = "CREATE TABLE IF NOT EXISTS authorized_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sccode INT NOT NULL,
    hw_uuid VARCHAR(100) NOT NULL,
    mac_addr VARCHAR(50),
    device_name VARCHAR(150),
    os_version VARCHAR(100),
    bound_by_uid INT,
    status ENUM('active', 'revoked', 'pending') DEFAULT 'active',
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_device (sccode, hw_uuid),
    INDEX idx_sccode (sccode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$conn->query($createTableSQL);

// Insert or Update Device Registration
$stmt = $conn->prepare("INSERT INTO authorized_devices (sccode, hw_uuid, mac_addr, device_name, os_version, bound_by_uid, status)
VALUES (?, ?, ?, ?, ?, ?, 'active')
ON DUPLICATE KEY UPDATE 
    mac_addr = VALUES(mac_addr),
    device_name = VALUES(device_name),
    os_version = VALUES(os_version),
    last_seen = NOW()");

$stmt->bind_param('issssi', $sccode, $hw_uuid, $mac_addr, $deviceName, $osVersion, $currentUser['id']);
$success = $stmt->execute();
$stmt->close();

if (!$success) {
    api_response('error', 'Failed to register terminal device.', null, 500);
}

api_response('success', 'Terminal device successfully bound and authorized for institute.', [
    'sccode' => $sccode,
    'hw_uuid' => $hw_uuid,
    'device_name' => $deviceName,
    'status' => 'active',
    'authorized_at' => date('Y-m-d H:i:s')
]);
