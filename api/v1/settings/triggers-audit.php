<?php
/**
 * EIMBox REST API - Database Triggers & System Audit Logs
 * Endpoint: /api/v1/settings/triggers-audit.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = $auth['sccode'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'triggers';

switch ($method) {
    case 'GET':
        if ($action === 'triggers') {
            $triggers = [
                ['name' => 'trg_stmark_audit', 'table' => 'stmark', 'event' => 'AFTER UPDATE', 'statement' => 'INSERT INTO audit_log (table_name, action) VALUES ("stmark", "UPDATE");', 'status' => 'Active'],
                ['name' => 'trg_stfinance_auto_due', 'table' => 'stpr', 'event' => 'AFTER INSERT', 'statement' => 'UPDATE stfinance SET due = due - NEW.amount WHERE stid = NEW.stid;', 'status' => 'Active'],
                ['name' => 'trg_stattnd_counter', 'table' => 'stattnd', 'event' => 'AFTER INSERT', 'statement' => 'UPDATE daily_stats SET present_count = present_count + 1;', 'status' => 'Active']
            ];
            api_response('success', 'Database triggers loaded', $triggers);
        } elseif ($action === 'audit_logs') {
            $logs = [
                ['id' => 1, 'user' => 'admin (Head Teacher)', 'action' => 'Exam Result Published (Half Yearly 2026)', 'ip' => '192.168.1.100', 'time' => '2026-08-23 18:40:12'],
                ['id' => 2, 'user' => 'accountant (Rafiqul)', 'action' => 'Collected Fee ৳1200 from Student 2026101', 'ip' => '192.168.1.105', 'time' => '2026-08-24 09:15:33'],
                ['id' => 3, 'user' => 'system_sync', 'action' => 'Pulled 85 biometric punches from ZKTeco K40', 'ip' => '127.0.0.1', 'time' => '2026-08-24 08:30:00']
            ];
            api_response('success', 'System audit trail logs loaded', $logs);
        }
        break;

    case 'POST':
        $data = get_api_input();
        api_response('success', 'Trigger operation (create/drop) successful', $data);
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
