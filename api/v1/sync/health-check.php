<?php
/**
 * EIMBox REST API — System Health & Online Status Check
 * Route: GET /api/v1/sync/health-check.php
 */

require_once __DIR__ . '/../bootstrap.php';

$startTime = microtime(true);

// Test Database Ping
$dbStatus = 'healthy';
$dbLatencyMs = 0;
try {
    $dbStart = microtime(true);
    $q = $conn->query("SELECT 1");
    $dbLatencyMs = round((microtime(true) - $dbStart) * 1000, 2);
} catch (Exception $e) {
    $dbStatus = 'unhealthy: ' . $e->getMessage();
}

$serverLatencyMs = round((microtime(true) - $startTime) * 1000, 2);

api_response('success', 'EIMBox REST API Gateway is operational.', [
    'status' => 'online',
    'api_version' => '1.0.0',
    'server_time' => date('Y-m-d H:i:s'),
    'timezone' => date_default_timezone_get(),
    'database' => [
        'status' => $dbStatus,
        'latency_ms' => $dbLatencyMs
    ],
    'gateway_latency_ms' => $serverLatencyMs,
    'supported_modules' => [
        'auth', 'academics', 'finance', 'exams', 'attendance', 'sms', 'notices', 'settings', 'sync'
    ]
]);
