<?php
/**
 * EIMBox REST API Test Worker
 * Invoked by test-all-apis.php
 */

$file = $argv[1] ?? '';
$method = $argv[2] ?? 'GET';
$query = $argv[3] ?? '';
$token = $argv[4] ?? '';

if (!$file || !file_exists(__DIR__ . '/' . $file)) {
    echo json_encode(['status' => 'error', 'message' => 'File not found']);
    exit(1);
}

$_SERVER['REQUEST_METHOD'] = $method;
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
$_SERVER['REQUEST_URI'] = '/api/v1/' . $file . ($query ? '?' . $query : '');
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

if ($query) {
    parse_str($query, $_GET);
}

require __DIR__ . '/' . $file;
