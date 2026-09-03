<?php
/**
 * Route: /api/cms/v1/health or /api/cms/v1/health.php
 * Method: GET, POST
 * Purpose: Connection & Health Check for EIMBOX Central Cloud Sync
 */

require_once __DIR__ . '/bootstrap.php';

$auth = authenticate_cms_request($conn ?? null);
$eiin = $auth['eiin'];

$dbStatus = 'disconnected';
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    if (@$conn->ping()) {
        $dbStatus = 'connected';
    }
}

cms_api_response('success', 'EIMBOX Central Cloud API (CMS v1) সার্ভারের সাথে সংযোগ সফল ও সক্রিয় রয়েছে।', [
    'eiin'             => $eiin,
    'server_name'      => 'EIMBOX Central Console Cloud',
    'api_version'      => 'v1.0.0-cms',
    'db_status'        => $dbStatus,
    'timestamp'        => date('c'),
    'supported_routes' => ['/health', '/faculty', '/pull', '/push']
], 200);
