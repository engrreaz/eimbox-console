<?php
// EIMBox SMS Background Dispatcher Worker
// Can be run via Server Cron (* * * * *) or Background Detached Worker

if (php_sapi_name() !== 'cli' && !isset($_GET['manual_key'])) {
    // Optional web trigger protection
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/functions.php';

// Concurrency Lock Check
$lock_file = sys_get_temp_dir() . '/eimbox_sms_dispatcher.lock';
if (file_exists($lock_file) && (time() - filemtime($lock_file) < 90)) {
    exit("Dispatcher already running. Skipping duplicate execution.\n");
}
touch($lock_file);

// 1. Select pending queued messages (Batch chunk of 50 for rate control)
$sql = "SELECT s.*, sci.sms_gateway 
        FROM sms s
        LEFT JOIN scinfo sci ON s.sccode = sci.sccode
        WHERE s.status = 'queued'
        ORDER BY s.id ASC 
        LIMIT 50";

$res = $conn->query($sql);
$processed = 0;

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $sms_id = $row['id'];
        $sccode = $row['sccode'];
        $mobile = $row['mobile_number'];
        $message = $row['sms_text'];
        $parts = intval($row['sms_parts'] ?? 1);

        // Mark as 'sending' to prevent race condition
        $conn->query("UPDATE sms SET status='sending' WHERE id='$sms_id'");

        // Parse Gateway Settings
        $gateway_conf = get_sms_setting($row['sms_gateway'] ?? '', 'gateway');

        // Dispatch to gateway / sandbox
        $api_res = dispatch_gateway_curl($gateway_conf, $mobile, $message);

        $status = ($api_res['status'] === 'success') ? 'sent' : 'failed';
        $res_code = mysqli_real_escape_string($conn, (string)($api_res['response_code'] ?? ''));
        $msg_id = mysqli_real_escape_string($conn, (string)($api_res['message_id'] ?? ''));
        $succ_msg = mysqli_real_escape_string($conn, (string)($api_res['success_message'] ?? ''));
        $err_msg = mysqli_real_escape_string($conn, (string)($api_res['error_message'] ?? ''));
        $provider = mysqli_real_escape_string($conn, (string)($gateway_conf['provider'] ?? 'bulksmsbd'));

        $price = floatval($gateway_conf['price'] ?? 0.35);
        $cost = (($gateway_conf['provider'] ?? '') != 'self') ? ($price * $parts) : 0;

        // Update database log
        $upd_sql = "UPDATE sms SET 
            status = '$status',
            response_code = '$res_code',
            message_id = '$msg_id',
            success_message = '$succ_msg',
            error_message = '$err_msg',
            cost = '$cost',
            gateway_provider = '$provider',
            delivered_time = NOW(),
            modifieddate = NOW()
            WHERE id = '$sms_id'";

        $conn->query($upd_sql);
        $processed++;
    }
}

// Release lock
@unlink($lock_file);

// Update Heartbeat Status
$heartbeat_file = __DIR__ . '/cron_status.json';
file_put_contents($heartbeat_file, json_encode([
    'last_run_time' => date('Y-m-d H:i:s'),
    'timestamp' => time(),
    'processed_count' => $processed,
    'status' => 'active'
], JSON_PRETTY_PRINT));

echo "Processed {$processed} queued messages at " . date('Y-m-d H:i:s') . "\n";
