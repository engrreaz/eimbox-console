<?php
/**
 * EIMBox REST API Core Bootstrap & Helper Module
 * Version: 1.0 (v1)
 */

// 1. CORS & Response Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Device-UUID");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 3. Include Core Config & DB
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/core-val.php';

// Ensure DB connection exists
if (!isset($conn) || !$conn) {
    $conn = db_connect();
}

/**
 * Standard JSON Response Sender
 */
function api_response($status = 'success', $message = '', $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Parse incoming JSON body or $_POST
 */
function get_api_input() {
    $rawInput = file_get_contents("php://input");
    $jsonData = json_decode($rawInput, true);
    if (is_array($jsonData)) {
        return $jsonData;
    }
    return $_POST;
}

/**
 * Generate Secure Token
 */
function generate_token($userId, $sccode) {
    $payload = [
        'uid' => $userId,
        'sccode' => $sccode,
        'time' => time(),
        'rand' => bin2hex(random_bytes(16))
    ];
    return base64_encode(json_encode($payload)) . '.' . hash_hmac('sha256', json_encode($payload), 'EIMBox_Secret_Key_2026_Studio');
}

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

/**
 * Validate and Extract Token from Authorization Header
 */
function authenticate_token($conn) {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    
    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        api_response('error', 'Authorization Bearer Token is missing or invalid.', null, 401);
    }
    
    $token = $matches[1];
    $parts = explode('.', $token);
    if (count($parts) !== 2) {
        api_response('error', 'Malformed token format.', null, 401);
    }
    
    $payloadJson = base64_decode($parts[0]);
    $signature = $parts[1];
    
    $expectedSig = hash_hmac('sha256', $payloadJson, 'EIMBox_Secret_Key_2026_Studio');
    if (!hash_equals($expectedSig, $signature)) {
        api_response('error', 'Token signature verification failed.', null, 401);
    }
    
    $payload = json_decode($payloadJson, true);
    if (!$payload || !isset($payload['uid'])) {
        api_response('error', 'Invalid token payload data.', null, 401);
    }
    
    // Fetch User from Database
    $stmt = $conn->prepare("SELECT * FROM usersapp WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $payload['uid']);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        api_response('error', 'User associated with token no longer exists.', null, 401);
    }
    
    return $user;
}
