<?php
/**
 * EIMBox REST API — Feature & Customization Request Form Endpoint
 * Route: POST /api/v1/support/request.php
 */

require_once __DIR__ . '/../bootstrap.php';

$user = null;
try {
    $user = authenticate_token($conn);
} catch (Exception $e) {}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$requestType = trim($input['request_type'] ?? 'feature'); // feature, customization, bug, improvement
$moduleName = trim($input['module_name'] ?? 'general');
$priority = trim($input['priority'] ?? 'Normal');
$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$contactPhone = trim($input['contact_phone'] ?? '');
$contactEmail = trim($input['contact_email'] ?? $user['email'] ?? '');

if (empty($title) || empty($description)) {
    api_response('error', 'Title and detailed description are required.', null, 400);
}

// Auto-create support_requests table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS support_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id VARCHAR(64) UNIQUE,
    sccode INT DEFAULT 0,
    request_type VARCHAR(50) DEFAULT 'feature',
    module_name VARCHAR(100) DEFAULT 'general',
    priority VARCHAR(20) DEFAULT 'Normal',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    contact_phone VARCHAR(50),
    contact_email VARCHAR(100),
    status VARCHAR(50) DEFAULT 'submitted',
    submitted_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$requestId = 'REQ-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
$submittedBy = $user['email'] ?? $contactEmail ?? 'User';

$stmt = $conn->prepare("INSERT INTO support_requests (request_id, sccode, request_type, module_name, priority, title, description, contact_phone, contact_email, status, submitted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', ?)");
$stmt->bind_param('sissssssss', $requestId, $sccode, $requestType, $moduleName, $priority, $title, $description, $contactPhone, $contactEmail, $submittedBy);
$stmt->execute();
$insertId = $stmt->insert_id;
$stmt->close();

api_response('success', 'Your request has been successfully submitted to EIMBox Engineering.', [
    'request_id' => $requestId,
    'title' => $title,
    'status' => 'submitted',
    'submitted_at' => date('Y-m-d H:i:s')
]);
