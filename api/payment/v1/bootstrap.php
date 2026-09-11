<?php
/**
 * EIMBox - DBBL Rocket Bill Payment API Core Bootstrap
 * Location: /api/payment/v1/bootstrap.php
 * Documentation Reference: Dutch-Bangla Bank Limited (DBBL) Bill Payment Reconciliation API Document V1.1
 */

// 1. Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-KEY, X-SECRET-KEY");
header("Content-Type: application/json; charset=UTF-8");

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
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
ini_set('error_log', $logDir . '/rocket-error-' . date('Y-m-d') . '.log');

// 2. Database Connection
// Check multiple possible paths to core config / db
$coreConfig = __DIR__ . '/../../../core/config.php';
$coreDb = __DIR__ . '/../../../core/db.php';
$panelDb = __DIR__ . '/../../../../eimbox-panel/db.php';

if (file_exists($coreConfig) && file_exists($coreDb)) {
    require_once $coreConfig;
    require_once $coreDb;
    if (!isset($conn) || !$conn) {
        $conn = db_connect();
    }
} elseif (file_exists($panelDb)) {
    require_once $panelDb;
} else {
    // Direct Fallback
    $conn = new mysqli('localhost', 'root', '', 'eimbox');
}

if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'errCode' => '99',
        'errMsg' => 'Database connection failed'
    ]);
    exit;
}
$conn->set_charset('utf8mb4');
@$conn->query("SET time_zone = '+06:00'");

// 3. Ensure rocket_transactions table exists for audit & idempotency
$initSql = "CREATE TABLE IF NOT EXISTS `rocket_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sccode` INT NOT NULL,
    `stid` INT NOT NULL,
    `sessionyear` VARCHAR(20) NOT NULL,
    `txnid` VARCHAR(60) NOT NULL UNIQUE,
    `txndate` VARCHAR(40) NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `refno1` VARCHAR(50) NULL,
    `refno2` VARCHAR(50) NULL,
    `refno3` VARCHAR(50) NULL,
    `status` VARCHAR(20) DEFAULT 'Success',
    `response_code` VARCHAR(20) DEFAULT '00',
    `response_msg` VARCHAR(100) DEFAULT 'Payment Information Updated Successfully',
    `raw_request` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_txn` (`txnid`),
    INDEX `idx_student` (`sccode`, `stid`, `sessionyear`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
@$conn->query($initSql);

// Ensure payment_gateway_keys table exists for per-sccode API key & Secret Key
$initKeysSql = "CREATE TABLE IF NOT EXISTS `payment_gateway_keys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sccode` INT NOT NULL UNIQUE,
    `gateway_name` VARCHAR(50) NOT NULL DEFAULT 'Rocket',
    `biller_id` VARCHAR(50) NOT NULL,
    `api_key` VARCHAR(100) NOT NULL,
    `secret_key` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sccode` (`sccode`),
    INDEX `idx_api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
@$conn->query($initKeysSql);

// 4. Default Biller Credentials (Global DBBL Rocket Partnership)
define('ROCKET_DEFAULT_USER', 'Rocket');
define('ROCKET_DEFAULT_PASS', 'Rocket123');

/**
 * Log Rocket Gateway Activity
 */
function log_rocket_transaction($type, $request, $response) {
    global $logDir;
    $logFile = $logDir . '/rocket-' . date('Y-m-d') . '.log';
    $entry = "[" . date('Y-m-d H:i:s') . "] [$type] REQ: " . json_encode($request, JSON_UNESCAPED_UNICODE) . " | RES: " . json_encode($response, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    @file_put_contents($logFile, $entry, FILE_APPEND);
}

/**
 * Standard DBBL Rocket Response Sender
 */
function send_rocket_response($errCode, $errMsg, $additionalData = []) {
    $response = array_merge([
        'errCode' => (string)$errCode,
        'errMsg' => (string)$errMsg
    ], $additionalData);

    log_rocket_transaction('RESPONSE', debug_backtrace()[1]['function'] ?? 'api', $response);

    http_response_code(200); // DBBL expects HTTP 200 with errCode inside payload
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Parse Input JSON Payload
 */
function get_rocket_input() {
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return $_POST;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        send_rocket_response('11', 'Not a valid JSON in request body');
    }
    return $decoded;
}

/**
 * Validate Rocket Authentication (Basic Auth / Payload credentials / per-sccode keys)
 */
function validate_rocket_auth($input, $sccode = null) {
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

    // 2. Fallback to payload credentials
    $reqUser = $input['usrid'] ?? $input['userid'] ?? $input['user_id'] ?? $basicUser;
    $reqPass = $input['pswrd'] ?? $input['password'] ?? $basicPass;

    if (empty($reqUser)) {
        send_rocket_response('06', 'User ID Missing');
    }
    if (empty($reqPass)) {
        send_rocket_response('07', 'Password Missing');
    }

    // 3. Match against Default Rocket Credentials
    if ($reqUser === ROCKET_DEFAULT_USER && $reqPass === ROCKET_DEFAULT_PASS) {
        return true;
    }

    // 4. Match against School-Specific API Key / Secret Key (if sccode provided)
    if ($sccode && $sccode > 0) {
        $stmt = $conn->prepare("SELECT api_key, secret_key, biller_id FROM payment_gateway_keys WHERE sccode = ? AND is_active = 1 LIMIT 1");
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

    // 5. Match against any school in payment_gateway_keys
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

    // Authentication Failed
    send_rocket_response('01', 'Invalid Authentication');
}

/**
 * Parse Student ID and School Code from Rocket Reference Parameters
 * Priority Rules:
 * - refNo1: stid (e.g. 10-digit student ID)
 * - refNo2: eiin / sccode (e.g. 6-digit institute code)
 * - refNo3: sessionyear (optional, empty defaults to latest session lookup)
 * Backward compatible with combined formats (e.g. "1001-10502")
 */
function parse_rocket_references($ref1, $ref2 = null, $ref3 = null) {
    global $conn;

    $ref1 = trim((string)$ref1);
    $ref2 = trim((string)$ref2);
    $ref3 = trim((string)$ref3);

    $sccode = 0;
    $stid = 0;
    $sessionyear = !empty($ref3) ? $ref3 : '';

    // Standard Rule: refNo1 = stid and refNo2 = sccode/eiin
    if (!empty($ref1) && !empty($ref2)) {
        if (strpos($ref1, '-') !== false || strpos($ref1, '_') !== false) {
            $delimiter = strpos($ref1, '-') !== false ? '-' : '_';
            $parts = explode($delimiter, $ref1);
            $sccode = (int)($parts[0] ?? 0);
            $stid = (int)($parts[1] ?? 0);
        } else {
            // refNo1 is stid (10-digit student ID), refNo2 is eiin/sccode (6-digit institute code)
            $stid = (int)$ref1;
            $sccode = (int)$ref2;
        }
    }
    // Combined delimiter in refNo1 only (e.g. "1001-10502" or "1001_10502")
    elseif (!empty($ref1) && (strpos($ref1, '-') !== false || strpos($ref1, '_') !== false)) {
        $delimiter = strpos($ref1, '-') !== false ? '-' : '_';
        $parts = explode($delimiter, $ref1);
        $sccode = (int)($parts[0] ?? 0);
        $stid = (int)($parts[1] ?? 0);
    }
    // Pure stid in refNo1 with refNo2 missing (fallback lookup across students table)
    elseif (!empty($ref1) && is_numeric($ref1)) {
        $stid = (int)$ref1;
        $stQuery = $conn->prepare("SELECT sccode FROM students WHERE stid = ? ORDER BY id DESC LIMIT 1");
        $stQuery->bind_param("i", $stid);
        $stQuery->execute();
        $stRes = $stQuery->get_result();
        if ($stRow = $stRes->fetch_assoc()) {
            $sccode = (int)$stRow['sccode'];
        }
        $stQuery->close();
    }

    return [
        'sccode' => $sccode,
        'stid' => $stid,
        'sessionyear' => $sessionyear
    ];
}
