<?php
/**
 * EIMBOX Central Cloud CMS API v1 Gateway & Router
 * Routes clean URLs like /health, /faculty, /pull, /push to corresponding handlers
 */

$uri = $_SERVER['REQUEST_URI'] ?? '';
$path = parse_url($uri, PHP_URL_PATH);
$route = basename($path);

// Check if direct endpoint file exists
if ($route === 'health' || $route === 'health.php') {
    require __DIR__ . '/health.php';
    exit;
}

if ($route === 'faculty' || $route === 'faculty.php') {
    require __DIR__ . '/faculty.php';
    exit;
}

if ($route === 'pull' || $route === 'pull.php') {
    require __DIR__ . '/pull.php';
    exit;
}

if ($route === 'push' || $route === 'push.php') {
    require __DIR__ . '/push.php';
    exit;
}

// Default Gateway Response
require_once __DIR__ . '/bootstrap.php';

cms_api_response('success', 'EIMBOX Central Cloud CMS API v1 Gateway is Online.', [
    'server'      => 'EIMBOX Central Console',
    'version'     => '1.0.0',
    'base_url'    => 'https://console.eimbox.com/api/cms/v1',
    'endpoints'   => [
        'POST/GET /health'  => 'Test connectivity and server health',
        'GET /faculty'      => 'Fetch live teacher/faculty list for an institution (?eiin=XXXXX)',
        'POST /pull'        => 'Pull institutional updates and policies to CMS',
        'POST /push'        => 'Push local records snapshot to central cloud'
    ],
    'timestamp'   => date('c')
], 200);
