<?php
// cron-job/cron-heartbeat.php
$log_file = __DIR__ . '/cron_status.json';
$data = [
    'last_run_time' => date('Y-m-d H:i:s'),
    'timestamp' => time(),
    'status' => 'active'
];

file_put_contents($log_file, json_encode($data, JSON_PRETTY_PRINT));
echo "Cron heartbeat recorded at: " . $data['last_run_time'] . "\n";
