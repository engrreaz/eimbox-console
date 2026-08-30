<?php
/**
 * EIMBox REST API — Feature & Customization Request Form Endpoint
 * Route: /api/v1/support/request.php
 * Supports:
 *   - GET  ?action=modules : Returns all public modules from `modulelist`
 *   - GET  [?sccode=X]     : Returns list of submitted support/customization requests
 *   - POST [action=create] : Submits new feature/customization request
 *   - POST [action=update] / PUT : Updates existing request
 *   - POST [action=delete] / DELETE : Deletes existing request
 */

require_once __DIR__ . '/../bootstrap.php';

$user = null;
$headers = function_exists('getallheaders') ? getallheaders() : [];
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!empty($authHeader) && preg_match('/Bearer\s(\S+)/', $authHeader)) {
    try {
        $user = authenticate_token($conn);
    } catch (Exception $e) {}
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = get_api_input();
$action = $_GET['action'] ?? $input['action'] ?? '';

// Ensure support_requests table exists with full schema
$conn->query("CREATE TABLE IF NOT EXISTS support_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id VARCHAR(64) UNIQUE NOT NULL,
    sccode INT DEFAULT 0,
    request_type VARCHAR(50) DEFAULT 'feature',
    module_name VARCHAR(100) DEFAULT 'general',
    priority VARCHAR(20) DEFAULT 'Normal',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    contact_phone VARCHAR(50),
    contact_email VARCHAR(100),
    status VARCHAR(50) DEFAULT 'submitted',
    admin_notes TEXT,
    submitted_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Ensure modulelist table exists
$conn->query("CREATE TABLE IF NOT EXISTS modulelist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slno INT NOT NULL DEFAULT '99',
    module_name VARCHAR(25) UNIQUE DEFAULT NULL,
    module_icon VARCHAR(20) NOT NULL DEFAULT 'circle-square',
    descrip VARCHAR(250) DEFAULT NULL,
    entryby VARCHAR(120) DEFAULT NULL,
    modifieddate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_public INT NOT NULL DEFAULT '1',
    core INT NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// ----------------------------------------------------
// 1. GET Requests Handler
// ----------------------------------------------------
if ($method === 'GET') {
    // Action A: Return Public Modules Catalog from `modulelist`
    if ($action === 'modules' || ($_GET['type'] ?? '') === 'modules') {
        $sql = "SELECT id, slno, module_name, module_icon, descrip, is_public, core 
                FROM modulelist 
                WHERE is_public = 1 
                ORDER BY slno ASC, module_name ASC";
        $res = $conn->query($sql);
        $modules = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['id'] = intval($row['id']);
                $row['slno'] = intval($row['slno']);
                $row['is_public'] = intval($row['is_public']);
                $row['core'] = intval($row['core']);
                $modules[] = $row;
            }
        }
        api_response('success', 'Public modules loaded successfully.', [
            'count' => count($modules),
            'modules' => $modules
        ]);
    }

    // Action B: Return Feature & Customization Requests
    $sccode = intval($_GET['sccode'] ?? $user['sccode'] ?? 0);
    $reqType = trim($_GET['request_type'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $search = trim($_GET['search'] ?? '');
    $limit = intval($_GET['limit'] ?? 100);

    $whereClauses = [];
    $params = [];
    $types = '';

    if ($sccode > 0) {
        $whereClauses[] = "(sccode = ? OR sccode = 0)";
        $params[] = $sccode;
        $types .= 'i';
    }

    if (!empty($reqType)) {
        $whereClauses[] = "request_type = ?";
        $params[] = $reqType;
        $types .= 's';
    }

    if (!empty($status)) {
        $whereClauses[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }

    if (!empty($search)) {
        $whereClauses[] = "(title LIKE ? OR description LIKE ? OR module_name LIKE ? OR request_id LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= 'ssss';
    }

    $whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";
    $query = "SELECT id, request_id, sccode, request_type, module_name, priority, title, description, 
                     contact_phone, contact_email, status, admin_notes, submitted_by, created_at, updated_at 
              FROM support_requests 
              {$whereSql} 
              ORDER BY created_at DESC, id DESC 
              LIMIT ?";
    $params[] = $limit > 0 ? $limit : 100;
    $types .= 'i';

    $stmt = $conn->prepare($query);
    if ($stmt) {
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $requests = [];
        while ($row = $res->fetch_assoc()) {
            $requests[] = $row;
        }
        $stmt->close();

        api_response('success', 'Requests retrieved successfully.', [
            'count' => count($requests),
            'requests' => $requests
        ]);
    } else {
        api_response('error', 'Failed to prepare query: ' . $conn->error, null, 500);
    }
}

// ----------------------------------------------------
// 2. DELETE Requests Handler
// ----------------------------------------------------
if ($method === 'DELETE' || $action === 'delete') {
    $requestId = trim($input['request_id'] ?? $_GET['request_id'] ?? '');
    $id = intval($input['id'] ?? $_GET['id'] ?? 0);

    if (empty($requestId) && $id <= 0) {
        api_response('error', 'Valid request_id or id is required to delete.', null, 400);
    }

    if (!empty($requestId)) {
        $stmt = $conn->prepare("DELETE FROM support_requests WHERE request_id = ?");
        $stmt->bind_param('s', $requestId);
    } else {
        $stmt = $conn->prepare("DELETE FROM support_requests WHERE id = ?");
        $stmt->bind_param('i', $id);
    }

    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Customization request deleted successfully.', ['request_id' => $requestId, 'id' => $id]);
    } else {
        api_response('error', 'Request not found or already deleted.', null, 404);
    }
}

// ----------------------------------------------------
// 3. PUT / UPDATE Requests Handler
// ----------------------------------------------------
if ($method === 'PUT' || $action === 'update' || (!empty($input['request_id']) && $action === 'update')) {
    $requestId = trim($input['request_id'] ?? '');
    $id = intval($input['id'] ?? 0);

    if (empty($requestId) && $id <= 0) {
        api_response('error', 'Valid request_id or id is required for update.', null, 400);
    }

    $requestType = trim($input['request_type'] ?? 'feature');
    $moduleName = trim($input['module_name'] ?? 'general');
    $priority = trim($input['priority'] ?? 'Normal');
    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $contactPhone = trim($input['contact_phone'] ?? '');
    $contactEmail = trim($input['contact_email'] ?? '');
    $status = trim($input['status'] ?? 'submitted');

    if (empty($title) || empty($description)) {
        api_response('error', 'Title and detailed description are required.', null, 400);
    }

    if (!empty($requestId)) {
        $stmt = $conn->prepare("UPDATE support_requests SET request_type = ?, module_name = ?, priority = ?, title = ?, description = ?, contact_phone = ?, contact_email = ?, status = ?, updated_at = NOW() WHERE request_id = ?");
        $stmt->bind_param('sssssssss', $requestType, $moduleName, $priority, $title, $description, $contactPhone, $contactEmail, $status, $requestId);
    } else {
        $stmt = $conn->prepare("UPDATE support_requests SET request_type = ?, module_name = ?, priority = ?, title = ?, description = ?, contact_phone = ?, contact_email = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('ssssssssi', $requestType, $moduleName, $priority, $title, $description, $contactPhone, $contactEmail, $status, $id);
    }

    $stmt->execute();
    $stmt->close();

    api_response('success', 'Customization request updated successfully.', [
        'request_id' => $requestId,
        'title' => $title,
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}

// ----------------------------------------------------
// 4. POST / CREATE Request Handler (Default)
// ----------------------------------------------------
$sccode = intval($input['sccode'] ?? $user['sccode'] ?? 0);
$requestType = trim($input['request_type'] ?? 'feature'); // feature, customization, bug, improvement
$moduleName = trim($input['module_name'] ?? 'general');
$priority = trim($input['priority'] ?? 'Normal');
$title = trim($input['title'] ?? '');
$description = trim($input['description'] ?? '');
$contactPhone = trim($input['contact_phone'] ?? '');
$contactEmail = trim($input['contact_email'] ?? $user['email'] ?? '');
$submittedBy = $user['email'] ?? $contactEmail ?? 'User';

if (empty($title) || empty($description)) {
    api_response('error', 'Title and detailed description are required.', null, 400);
}

$requestId = 'REQ-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

$stmt = $conn->prepare("INSERT INTO support_requests (request_id, sccode, request_type, module_name, priority, title, description, contact_phone, contact_email, status, submitted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', ?)");
$stmt->bind_param('sissssssss', $requestId, $sccode, $requestType, $moduleName, $priority, $title, $description, $contactPhone, $contactEmail, $submittedBy);
$stmt->execute();
$insertId = $stmt->insert_id;
$stmt->close();

api_response('success', 'Your request has been successfully submitted to EIMBox Engineering.', [
    'id' => $insertId,
    'request_id' => $requestId,
    'sccode' => $sccode,
    'request_type' => $requestType,
    'module_name' => $moduleName,
    'priority' => $priority,
    'title' => $title,
    'status' => 'submitted',
    'submitted_at' => date('Y-m-d H:i:s')
]);
