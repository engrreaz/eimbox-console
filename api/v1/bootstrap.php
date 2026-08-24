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

// 2. Comprehensive API Error, Request & Response Logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$_api_start_time = microtime(true);

$apiLogDir = __DIR__ . '/../../core/logs';
if (!is_dir($apiLogDir)) {
    @mkdir($apiLogDir, 0777, true);
}
$apiErrorLogFile = $apiLogDir . '/api-error-' . date('Y-m-d') . '.log';
$apiResponseLogFile = $apiLogDir . '/api-response-' . date('Y-m-d') . '.log';
ini_set('error_log', $apiErrorLogFile);

/**
 * Sanitize sensitive keys before logging
 */
function api_sanitize_log_data($data) {
    if (!is_array($data)) return $data;
    $sensitiveKeys = ['password', 'password_hash', 'token', 'mfa_secret', 'secret_key', 'api_key', 'otp'];
    $clean = [];
    foreach ($data as $k => $v) {
        if (in_array(strtolower($k), $sensitiveKeys)) {
            $clean[$k] = '***REDACTED***';
        } elseif (is_array($v)) {
            $clean[$k] = api_sanitize_log_data($v);
        } else {
            $clean[$k] = $v;
        }
    }
    return $clean;
}

/**
 * Write structured entry to API Log files
 */
function api_log_error($type, $message, $file = '', $line = 0, $trace = '') {
    global $apiErrorLogFile, $apiResponseLogFile;
    $timestamp = date('Y-m-d H:i:s');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
    $uri = $_SERVER['REQUEST_URI'] ?? 'Unknown URI';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $logEntry = "[$timestamp] [$type] [$method $uri] [IP: $ip]\n";
    $logEntry .= "Message: $message\n";
    if ($file) {
        $logEntry .= "Location: $file (Line $line)\n";
    }
    if (!empty($trace)) {
        $logEntry .= "Stack Trace:\n$trace\n";
    }
    $logEntry .= str_repeat('-', 70) . "\n";

    @file_put_contents($apiErrorLogFile, $logEntry, FILE_APPEND);
    @file_put_contents($apiResponseLogFile, $logEntry, FILE_APPEND);
    error_log("[$type] $method $uri - $message");
}

/**
 * Log outgoing API response
 */
function api_log_response($httpCode, $status, $message, $data = null) {
    global $apiResponseLogFile, $apiErrorLogFile, $_api_start_time;
    $timestamp = date('Y-m-d H:i:s');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
    $uri = $_SERVER['REQUEST_URI'] ?? 'Unknown URI';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $durationMs = isset($_api_start_time) ? round((microtime(true) - $_api_start_time) * 1000, 2) : 0;

    $input = get_api_input();
    $cleanInput = api_sanitize_log_data(is_array($input) ? $input : []);
    $inputJson = !empty($cleanInput) ? json_encode($cleanInput, JSON_UNESCAPED_UNICODE) : '{}';

    $logType = ($httpCode >= 400 || $status === 'error') ? 'API_ERROR_RESPONSE' : 'API_SUCCESS_RESPONSE';

    $logEntry = "[$timestamp] [$logType] [$method $uri] [IP: $ip] [Time: {$durationMs}ms]\n";
    $logEntry .= "HTTP Code: $httpCode | Status: $status | Message: $message\n";
    $logEntry .= "Input: $inputJson\n";
    
    if ($data !== null) {
        $cleanData = api_sanitize_log_data(is_array($data) ? $data : ['data' => $data]);
        $preview = json_encode($cleanData, JSON_UNESCAPED_UNICODE);
        if (mb_strlen($preview) > 500) {
            $preview = mb_substr($preview, 0, 500) . '... [truncated]';
        }
        $logEntry .= "Data Preview: $preview\n";
    }
    $logEntry .= str_repeat('-', 70) . "\n";

    // Always log to api-response log
    @file_put_contents($apiResponseLogFile, $logEntry, FILE_APPEND);

    // If error/failure, also write to api-error log and trigger system error_log
    if ($httpCode >= 400 || $status === 'error') {
        @file_put_contents($apiErrorLogFile, $logEntry, FILE_APPEND);
        error_log("[$logType] $method $uri (HTTP $httpCode) - $message");
    } else {
        error_log("[$logType] $method $uri (HTTP $httpCode) - $message ({$durationMs}ms)");
    }
}

// Convert PHP errors/warnings into logs
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    api_log_error('PHP_NOTICE_WARNING', $message, $file, $line);
    return true;
});

// Handle uncaught exceptions
set_exception_handler(function (Throwable $e) {
    api_log_error('UNCAUGHT_EXCEPTION', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'API Server Exception: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
});

// Handle fatal shutdown errors (parse errors, fatal crashes)
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        api_log_error('FATAL_ERROR', $error['message'], $error['file'], $error['line']);
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
        }
        echo json_encode([
            'status' => 'error',
            'message' => 'Fatal Server Error: ' . $error['message'],
            'file' => basename($error['file']),
            'line' => $error['line'],
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
});

// 3. Include Core Config & DB
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/core-val.php';

// Ensure DB connection exists
if (!isset($conn) || !$conn) {
    try {
        $conn = db_connect();
    } catch (Exception $e) {
        api_log_error('DB_CONNECT_ERROR', $e->getMessage(), $e->getFile(), $e->getLine());
        api_response('error', 'Database connection error: ' . $e->getMessage(), null, 500);
    }
}

/**
 * Standard JSON Response Sender with Logging
 */
function api_response($status = 'success', $message = '', $data = null, $httpCode = 200) {
    api_log_response($httpCode, $status, $message, $data);
    
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

/**
 * Backward compatibility alias for authenticate_token
 */
function api_authenticate_request($connection = null) {
    global $conn;
    return authenticate_token($connection ?: $conn);
}

/**
 * Backward compatibility alias for db connection
 */
function api_get_db_connection() {
    global $conn;
    return $conn;
}

/**
 * Backward compatibility alias for response sender
 */
function api_send_response($httpCode, $success, $message, $data = null) {
    api_response($success ? 'success' : 'error', $message, $data, $httpCode);
}



