<?php
/**
 * EIMBox - bKash Pay Bill API Core Bootstrap
 * Location: /api/payment/bkash/v1/bootstrap.php
 * Documentation Reference: bKash Pay Bill Integration Specification v2.0 (PayBill_API_For_BKash_v2.docx)
 */

if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-KEY, X-SECRET-KEY");
    header("Content-Type: application/json; charset=UTF-8");
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    if (!headers_sent()) {
        http_response_code(200);
    }
    exit;
}

date_default_timezone_set('Asia/Dhaka');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Log directory
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}
ini_set('error_log', $logDir . '/bkash-error-' . date('Y-m-d') . '.log');

// 2. Database Connection
$coreConfig = __DIR__ . '/../../../../core/config.php';
$coreDb = __DIR__ . '/../../../../core/db.php';
$panelDb = __DIR__ . '/../../../../../eimbox-panel/db.php';

if (file_exists($coreConfig) && file_exists($coreDb)) {
    require_once $coreConfig;
    require_once $coreDb;
    if (!isset($conn) || !$conn) {
        $conn = db_connect();
    }
} elseif (file_exists($panelDb)) {
    require_once $panelDb;
} else {
    // Direct Fallback for local environments
    $conn = new mysqli('localhost', 'root', '', 'eimbox');
}

if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'ErrorCode' => '209',
        'ErrorMsg' => 'Internal error: Database connection failed'
    ]);
    exit;
}
$conn->set_charset('utf8mb4');
@$conn->query("SET time_zone = '+06:00'");

// 3. Ensure bkash_transactions table exists for audit & idempotency
$initSql = "CREATE TABLE IF NOT EXISTS `bkash_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sccode` INT NOT NULL,
    `stid` INT NOT NULL,
    `sessionyear` VARCHAR(20) NOT NULL,
    `prno` BIGINT NULL DEFAULT NULL,
    `trxid` VARCHAR(60) NOT NULL UNIQUE,
    `paytime` VARCHAR(40) NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `ref_id` VARCHAR(50) NULL,
    `bill_month` VARCHAR(20) NULL,
    `user_mobile` VARCHAR(30) NULL,
    `status` VARCHAR(20) DEFAULT 'Success',
    `error_code` VARCHAR(20) DEFAULT '200',
    `error_msg` VARCHAR(100) DEFAULT 'Success',
    `raw_request` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_trx` (`trxid`),
    INDEX `idx_prno` (`prno`),
    INDEX `idx_student` (`sccode`, `stid`, `sessionyear`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
@$conn->query($initSql);

// Ensure payment_gateway_keys table exists for per-sccode API key & Secret Key
$initKeysSql = "CREATE TABLE IF NOT EXISTS `payment_gateway_keys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sccode` INT NOT NULL,
    `gateway_name` VARCHAR(50) NOT NULL DEFAULT 'bKash',
    `biller_id` VARCHAR(50) NOT NULL,
    `api_key` VARCHAR(100) NOT NULL,
    `secret_key` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sccode` (`sccode`),
    INDEX `idx_gateway` (`gateway_name`),
    INDEX `idx_api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
@$conn->query($initKeysSql);

// 4. Default Biller Credentials (Global bKash Pay Bill Partnership)
define('BKASH_DEFAULT_USER', 'bKash00');
define('BKASH_DEFAULT_PASS', 'bKash12321');
define('BKASH_DEMO_USER', 'demo3');
define('BKASH_DEMO_PASS', 'aaa');

/**
 * Log bKash Gateway Activity
 */
function log_bkash_transaction($type, $request, $response) {
    global $logDir;
    $logFile = $logDir . '/bkash-' . date('Y-m-d') . '.log';
    $entry = "[" . date('Y-m-d H:i:s') . "] [$type] REQ: " . json_encode($request, JSON_UNESCAPED_UNICODE) . " | RES: " . json_encode($response, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    @file_put_contents($logFile, $entry, FILE_APPEND);
}

/**
 * Standard bKash Pay Bill Response Sender
 */
function send_bkash_response($errorCode, $errorMsg, $additionalData = []) {
    $response = array_merge([
        'ErrorCode' => (string)$errorCode,
        'ErrorMsg' => (string)$errorMsg
    ], $additionalData);

    log_bkash_transaction('RESPONSE', debug_backtrace()[1]['function'] ?? 'api', $response);

    if (!headers_sent()) {
        http_response_code(200); // bKash expects HTTP 200 with ErrorCode inside payload
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Parse Input (Supports JSON payload, POST Form Data, and GET Query String)
 */
function get_bkash_input() {
    $input = [];

    // 1. Parse GET query params
    if (!empty($_GET)) {
        $input = array_merge($input, $_GET);
    }

    // 2. Parse POST form data
    if (!empty($_POST)) {
        $input = array_merge($input, $_POST);
    }

    // 3. Parse JSON Body
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $input = array_merge($input, $decoded);
        }
    }

    return $input;
}

/**
 * Validate bKash Authentication (Basic Auth / Request parameters / per-sccode keys)
 */
function validate_bkash_auth($input, $sccode = null) {
    global $conn;

    // 1. Check HTTP Basic Authentication Header
    $basicUser = $_SERVER['PHP_AUTH_USER'] ?? null;
    $basicPass = $_SERVER['PHP_AUTH_PW'] ?? null;

    if (!$basicUser && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $decodedAuth = base64_decode($matches[1]);
            if ($decodedAuth && strpos($decodedAuth, ':') !== false) {
                list($basicUser, $basicPass) = explode(':', $decodedAuth, 2);
            }
        }
    }

    // 2. Fallback to payload or query parameters
    $reqUser = $input['username'] ?? $input['userName'] ?? $input['usrid'] ?? $input['userid'] ?? $basicUser;
    $reqPass = $input['password'] ?? $input['pswrd'] ?? $basicPass;

    if (empty($reqUser) || empty($reqPass)) {
        send_bkash_response('202', 'Mandatory Field missing: username or password is required');
    }

    // 3. Match against Default bKash Partner / Demo Credentials
    if (($reqUser === BKASH_DEFAULT_USER && $reqPass === BKASH_DEFAULT_PASS) ||
        ($reqUser === BKASH_DEMO_USER && $reqPass === BKASH_DEMO_PASS)) {
        return true;
    }

    // 4. Match against School-Specific API Key / Secret Key in payment_gateway_keys (if sccode provided)
    if ($sccode && $sccode > 0) {
        $stmt = $conn->prepare("SELECT api_key, secret_key, biller_id FROM payment_gateway_keys WHERE sccode = ? AND (gateway_name = 'bKash' OR gateway_name = 'All' OR gateway_name = '') AND is_active = 1 LIMIT 1");
        $stmt->bind_param("i", $sccode);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (($reqUser === $row['biller_id'] || $reqUser === $row['api_key']) && ($reqPass === $row['secret_key'])) {
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
    }

    // 5. Match against any school in payment_gateway_keys with matching username
    $keyStmt = $conn->prepare("SELECT sccode, biller_id, secret_key FROM payment_gateway_keys WHERE (biller_id = ? OR api_key = ?) AND is_active = 1 LIMIT 1");
    $keyStmt->bind_param("ss", $reqUser, $reqUser);
    $keyStmt->execute();
    $keyRes = $keyStmt->get_result();
    if ($keyRow = $keyRes->fetch_assoc()) {
        if ($reqPass === $keyRow['secret_key']) {
            $keyStmt->close();
            return true;
        }
    }
    $keyStmt->close();

    // Authentication Failed (bKash Error Code 201)
    send_bkash_response('201', 'Authentication failed');
}

/**
 * Parse Student ID and School Code from bKash ref_id parameter
 * Supported formats:
 * 1. Combined format with delimiter: "sccode-stid" (e.g. "103187-1031871631", "103187_1031871631")
 * 2. 10-digit Student ID (e.g. "1031871631" - first 6 digits resolve sccode or lookup in students table)
 * 3. General stid with database lookup
 */
function parse_bkash_reference($refId, $sccodeParam = null) {
    global $conn;

    $refId = trim((string)$refId);
    $sccode = $sccodeParam ? (int)$sccodeParam : 0;
    $stid = 0;

    if (empty($refId)) {
        return ['sccode' => 0, 'stid' => 0];
    }

    // Combined delimiter in ref_id (e.g. "103187-1031871631" or "103187_1031871631")
    if (strpos($refId, '-') !== false || strpos($refId, '_') !== false) {
        $delimiter = strpos($refId, '-') !== false ? '-' : '_';
        $parts = explode($delimiter, $refId);
        $sccode = (int)($parts[0] ?? 0);
        $stid = (int)($parts[1] ?? 0);
    }
    // Numeric Reference
    elseif (is_numeric($refId)) {
        $stid = (int)$refId;

        // If sccode not passed, check if 10-digit standard EIMBox student ID (e.g. 1031871631 -> sccode: 103187)
        if ($sccode <= 0 && strlen($refId) === 10) {
            $potentialSc = (int)substr($refId, 0, 6);
            $checkSc = $conn->prepare("SELECT sccode FROM scinfo WHERE sccode = ? LIMIT 1");
            $checkSc->bind_param("i", $potentialSc);
            $checkSc->execute();
            $scRes = $checkSc->get_result();
            if ($scRes && $scRes->num_rows > 0) {
                $sccode = $potentialSc;
            }
            $checkSc->close();
        }

        // Direct lookup from students table
        if ($sccode <= 0) {
            $stQuery = $conn->prepare("SELECT sccode FROM students WHERE stid = ? ORDER BY id DESC LIMIT 1");
            $stQuery->bind_param("i", $stid);
            $stQuery->execute();
            $stRes = $stQuery->get_result();
            if ($stRow = $stRes->fetch_assoc()) {
                $sccode = (int)$stRow['sccode'];
            }
            $stQuery->close();
        }
    }

    return [
        'sccode' => $sccode,
        'stid' => $stid
    ];
}
