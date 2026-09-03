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
 * Validate 2-Key Pair (API Key + Secret Key) bound to sccode
 */
function authenticate_cms_request(?mysqli $conn = null): array
{
    global $conn;
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

    // 1. Extract School EIIN / sccode
    $eiin = $headers['x-eimbox-eiin'] ?? $headers['x-eimbox-sccode'] ?? $input['school_eiin'] ?? $input['eiin'] ?? $input['sccode'] ?? '';
    $eiin = trim((string)$eiin);

    if (empty($eiin)) {
        cms_api_response('error', 'ইনস্টিটিউশন EIIN বা School Code (sccode) প্রদান করা আবশ্যক।', null, 400);
    }

    // 2. Extract API Key (Public Client Key)
    $apiKey = $headers['x-eimbox-api-key'] ?? $input['api_key'] ?? $input['apiKey'] ?? '';
    $apiKey = trim((string)$apiKey);

    // 3. Extract Secret Key (Private Token)
    $authHeader = $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $secretKey = '';
    if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
        $secretKey = $matches[1];
    } elseif (!empty($headers['x-eimbox-secret-key'])) {
        $secretKey = trim((string)$headers['x-eimbox-secret-key']);
    } elseif (!empty($input['secret_key'])) {
        $secretKey = trim((string)$input['secret_key']);
    }

    // 4. Server-Side Key Verification against Database
    if ($conn && $conn instanceof mysqli && !$conn->connect_error) {
        // Auto-create table if not exists for fail-safe setup
        @$conn->query("CREATE TABLE IF NOT EXISTS `school_api_keys` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `sccode` VARCHAR(30) NOT NULL UNIQUE,
            `school_name` VARCHAR(150) NULL,
            `api_key` VARCHAR(80) NOT NULL UNIQUE,
            `secret_key` VARCHAR(120) NOT NULL,
            `status` ENUM('active', 'suspended', 'revoked') DEFAULT 'active',
            `allowed_domains` VARCHAR(255) NULL,
            `last_used_at` DATETIME NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_sccode_keys` (`sccode`, `api_key`, `secret_key`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $stmt = $conn->prepare("SELECT * FROM `school_api_keys` WHERE `sccode` = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $eiin);
            $stmt->execute();
            $keyRecord = $stmt->get_result()->fetch_assoc();

            if (!$keyRecord) {
                cms_api_response('error', "প্রতিষ্ঠান কোড [{$eiin}] সেন্ট্রাল ক্লাউড সার্ভারে নিবন্ধিত নয়। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।", null, 403);
            }

            if (($keyRecord['status'] ?? '') !== 'active') {
                cms_api_response('error', "প্রতিষ্ঠান কোড [{$eiin}] এর ক্লাউড সিঙ্ক সেবা সাময়িকভাবে স্থগিত (Suspended) রয়েছে।", null, 403);
            }

            // Verify Key Matching
            $validApi = !empty($apiKey) && hash_equals($keyRecord['api_key'], $apiKey);
            $validSec = !empty($secretKey) && hash_equals($keyRecord['secret_key'], $secretKey);

            if (!$validApi || !$validSec) {
                cms_api_response('error', "অবৈধ API Key অথবা Secret Key! প্রতিষ্ঠান কোড [{$eiin}] এর সাথে প্রদত্ত কী-জোড়া মেলেনি।", [
                    'provided_sccode'  => $eiin,
                    'api_key_valid'    => $validApi,
                    'secret_key_valid' => $validSec
                ], 401);
            }

            // Update last used timestamp
            @$conn->query("UPDATE `school_api_keys` SET `last_used_at` = NOW() WHERE `id` = " . (int)$keyRecord['id']);
        }
    }

    return [
        'eiin'      => $eiin,
        'apiKey'    => $apiKey,
        'secretKey' => $secretKey,
        'input'     => $input,
        'headers'   => $headers
    ];
}
