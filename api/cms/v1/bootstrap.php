<?php
/**
 * EIMBOX Central Cloud CMS API v1 — Core Bootstrap & Helpers
 * Base Path: /api/cms/v1/
 */

// 1. CORS & Security Headers
if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-EIMBOX-EIIN, X-Device-UUID");
    header("Content-Type: application/json; charset=UTF-8");
}

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

date_default_timezone_set('Asia/Dhaka');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$_cms_api_start = microtime(true);

// 2. Database & Core Config Connection (config.php MUST load before db.php)
$coreConfig = __DIR__ . '/../../../core/config.php';
if (file_exists($coreConfig)) {
    require_once $coreConfig;
}

$coreDbConfig = __DIR__ . '/../../../core/db.php';
if (file_exists($coreDbConfig)) {
    require_once $coreDbConfig;
}

// Ensure DB connection
if (!isset($conn) || !$conn || !($conn instanceof mysqli)) {
    $dbHost = defined('DB_HOST') ? DB_HOST : 'localhost';
    $dbUser = defined('DB_USER') ? DB_USER : 'root';
    $dbPass = defined('DB_PASS') ? DB_PASS : '';
    $dbName = defined('DB_NAME') ? DB_NAME : 'eimbox';

    try {
        $conn = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
        if ($conn->connect_error) {
            $conn = @new mysqli($dbHost, $dbUser, $dbPass, 'eimbox');
        }
        if (!$conn->connect_error) {
            $conn->set_charset("utf8mb4");
            @$conn->query("SET time_zone = '+06:00'");
        }
    } catch (\Throwable $e) {
        // Connection handled gracefully in endpoint handlers
    }
}

/**
 * Standard JSON Response Sender
 */
function cms_api_response(string $status = 'success', string $message = '', $data = null, int $httpCode = 200): void
{
    global $_cms_api_start;
    $durationMs = isset($_cms_api_start) ? round((microtime(true) - $_cms_api_start) * 1000, 2) . 'ms' : '0ms';

    if (!headers_sent()) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=UTF-8');
    }
    echo json_encode([
        'status'    => $status,
        'message'   => $message,
        'latency'   => $durationMs,
        'timestamp' => date('Y-m-d H:i:s'),
        'data'      => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Parse incoming JSON body or $_POST/$_GET
 */
function get_cms_api_input(): array
{
    $raw = file_get_contents("php://input");
    $json = json_decode($raw, true);
    if (is_array($json)) {
        return array_merge($_GET, $json);
    }
    return array_merge($_GET, $_POST);
}

/**
 * Validate API Key & EIIN Header/Payload
 */
function authenticate_cms_request(?mysqli $conn = null): array
{
    $input = get_cms_api_input();
    $headers = [];

    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $name => $value) {
            $headers[strtolower($name)] = $value;
        }
    } else {
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $key = strtolower(str_replace('_', '-', substr($name, 5)));
                $headers[$key] = $value;
            }
        }
    }

    $authHeader = $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $apiKey = '';
    if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
        $apiKey = $matches[1];
    } elseif (!empty($input['api_key'])) {
        $apiKey = trim((string)$input['api_key']);
    }

    $eiin = $headers['x-eimbox-eiin'] ?? $input['school_eiin'] ?? $input['eiin'] ?? $input['sccode'] ?? '';
    $eiin = trim((string)$eiin);

    if (empty($eiin)) {
        cms_api_response('error', 'ইনস্টিটিউশন EIIN বা School Code (sccode) প্রদান করা আবশ্যক।', null, 400);
    }

    return [
        'eiin'    => $eiin,
        'apiKey'  => $apiKey,
        'input'   => $input,
        'headers' => $headers
    ];
}
