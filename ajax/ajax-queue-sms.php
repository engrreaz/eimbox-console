<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $payload = null;

    // 1. Try reading from POST payload field
    if (!empty($_POST['payload'])) {
        $payload = json_decode($_POST['payload'], true);
    }
    // 2. Try reading from raw php://input
    else {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $payload = json_decode($raw, true);
        }
    }

    // 3. Fallback to direct POST fields
    if (!is_array($payload)) {
        $payload = [
            'campaign' => $_POST['campaign'] ?? 'Bulk Campaign',
            'sms_type' => $_POST['sms_type'] ?? 'notice',
            'recipients' => $_POST['recipients'] ?? []
        ];
    }

    $campaign_raw = $payload['campaign'] ?? 'Bulk Campaign';
    $sms_type_raw = $payload['sms_type'] ?? 'notice';
    $recipients = $payload['recipients'] ?? [];

    if (empty($sccode)) {
        $sccode = $_SESSION['sccode'] ?? '';
    }

    if (empty($sccode)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Institution code (sccode) session missing. Please log in again.'
        ]);
        exit;
    }

    if (empty($recipients) || !is_array($recipients)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No recipients selected to queue!'
        ]);
        exit;
    }

    $campaign = mysqli_real_escape_string($conn, $campaign_raw);
    $sms_type = mysqli_real_escape_string($conn, $sms_type_raw);
    $batch_id = 'BATCH_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(4)), 0, 6);
    $sessionyear = $y_v2 ?? date('Y');
    $td = date('Y-m-d');
    $usr_esc = mysqli_real_escape_string($conn, $usr ?? 'admin');

    $insert_rows = [];
    $total_parts = 0;

    foreach ($recipients as $r) {
        $mobile = trim($r['mobile'] ?? '');
        $text = trim($r['text'] ?? '');
        if (empty($mobile) || empty($text)) continue;

        $mobile_esc = mysqli_real_escape_string($conn, $mobile);
        $text_esc = mysqli_real_escape_string($conn, $text);
        $name_esc = mysqli_real_escape_string($conn, $r['name'] ?? '');
        $rec_id_esc = mysqli_real_escape_string($conn, $r['id'] ?? '');
        $cls_esc = mysqli_real_escape_string($conn, $r['classname'] ?? '');
        $sec_esc = mysqli_real_escape_string($conn, $r['sectionname'] ?? '');
        $roll = intval($r['rollno'] ?? 0);
        $rec_type_esc = mysqli_real_escape_string($conn, $r['recipient_type'] ?? 'guardian');
        $s_year = !empty($r['sessionyear']) ? mysqli_real_escape_string($conn, $r['sessionyear']) : $sessionyear;

        $len = mb_strlen($text);
        $is_unicode = preg_match('/\p{Bengali}/u', $text) ? 1 : 0;
        $parts = $is_unicode ? ceil($len / 70) : ceil($len / 160);
        $total_parts += $parts;

        $insert_rows[] = "('$sccode', '$s_year', '$rec_type_esc', '$rec_id_esc', '$name_esc', '$cls_esc', '$sec_esc', $roll, '$td', '$mobile_esc', '$sms_type', '$campaign', '$text_esc', $len, $parts, $parts, $is_unicode, 'queued', '$batch_id', '$usr_esc', NOW())";
    }

    if (empty($insert_rows)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No valid recipient phone numbers found in the list.'
        ]);
        exit;
    }

    // Bulk insert in chunks of 500
    $chunk_size = 500;
    $chunks = array_chunk($insert_rows, $chunk_size);
    foreach ($chunks as $chunk) {
        $sql = "INSERT INTO sms 
            (sccode, sessionyear, recipient_type, recipient_id, recipient_name, classname, sectionname, rollno, date, mobile_number, sms_type, campaign, sms_text, sms_len, sms_parts, count, is_unicode, status, batch_id, send_by, send_time) 
            VALUES " . implode(',', $chunk);
        $conn->query($sql);
    }

    // Trigger background worker asynchronously
    try {
        $dispatcher_path = dirname(__DIR__) . '/cron-job/sms-dispatcher.php';
        $php_bin = defined('PHP_BINARY') && file_exists(PHP_BINARY) ? PHP_BINARY : 'php';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = 'start /B "" "' . $php_bin . '" "' . $dispatcher_path . '" > NUL 2>&1';
            $h = @popen($cmd, "r");
            if ($h) @pclose($h);
        } else {
            @exec('"' . $php_bin . '" "' . $dispatcher_path . '" > /dev/null 2>&1 &');
        }
    } catch (Exception $e) {
        // Log background trigger failure without failing the user response
        error_log("Background trigger error: " . $e->getMessage());
    }

    echo json_encode([
        'status' => 'success',
        'batch_id' => $batch_id,
        'total_recipients' => count($insert_rows),
        'total_sms_parts' => $total_parts,
        'message' => count($insert_rows) . ' টি মেসেজ সফলভাবে কিউতে যুক্ত হয়েছে। ব্যাকগ্রাউন্ডে স্বয়ংক্রিয়ভাবে প্রেরিত হচ্ছে।'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Queueing failed: ' . $e->getMessage()
    ]);
}
