<?php
/**
 * EIMBox REST API — Remote App Update Policy Check Endpoint
 * Route: GET /api/v1/updates/check.php?current_version={v}&platform={win|mac|linux}
 */

require_once __DIR__ . '/../bootstrap.php';

$currentVersion = trim($_GET['current_version'] ?? '1.0.0');
$platform = trim($_GET['platform'] ?? 'win32');

// In production, this can be fetched from a database table or release configuration
$updatePolicy = [
    'latest_version'        => '2.0.0',
    'min_required_version'  => '1.0.0', // Versions below this MUST mandatorily update
    'is_mandatory'          => false,
    'release_date'          => '2026-09-04',
    'title'                 => 'EIMBox Desktop Studio 2.0.0 Release',
    'release_notes'         => 'Complete institutional workstation with offline-first SQLite synchronization, dual biometric bridge, custom grade scale matrix, student fee waivers, and Windows 11 Fluent interface.',
    'download_url'          => 'https://console.eimbox.com/downloads/eimbox-desktop-setup.exe',
    'checksum_sha256'       => 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855'
];

// Helper version compare
function isVersionOlder($current, $target) {
    return version_compare($current, $target, '<');
}

$hasUpdate = isVersionOlder($currentVersion, $updatePolicy['latest_version']);
$isMandatory = isVersionOlder($currentVersion, $updatePolicy['min_required_version']) || ($hasUpdate && $updatePolicy['is_mandatory']);

api_response('success', 'Update policy evaluated.', [
    'current_version'      => $currentVersion,
    'has_update'           => $hasUpdate,
    'is_mandatory'         => $isMandatory,
    'latest_version'       => $updatePolicy['latest_version'],
    'min_required_version' => $updatePolicy['min_required_version'],
    'title'                => $updatePolicy['title'],
    'release_notes'        => $updatePolicy['release_notes'],
    'download_url'         => $updatePolicy['download_url']
]);
