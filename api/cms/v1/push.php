<?php
/**
 * Route: /api/cms/v1/push or /api/cms/v1/push.php
 * Method: POST
 * Purpose: Central Cloud Push Endpoint — Receives CMS Local Data Snapshot into EIMBOX Console
 */

require_once __DIR__ . '/bootstrap.php';

$auth = authenticate_cms_request($conn ?? null);
$eiin = $auth['eiin'];
$input = $auth['input'];
$incomingData = $input['data'] ?? [];

$staffCount = count($incomingData['staff'] ?? []);
$noticesCount = count($incomingData['notices'] ?? []);
$servicesCount = count($incomingData['digital_services'] ?? []);
$totalReceived = $staffCount + $noticesCount + $servicesCount;

// Log the backup snapshot into log folder
$backupDir = __DIR__ . '/logs/backups';
if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0777, true);
}
$backupFile = $backupDir . '/sync_' . preg_replace('/[^0-9a-zA-Z_-]/', '', $eiin) . '_' . date('Y-m-d_His') . '.json';
@file_put_contents($backupFile, json_encode([
    'eiin'      => $eiin,
    'timestamp' => date('c'),
    'data'      => $incomingData
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

cms_api_response('success', "সেন্ট্রাল ক্লাউড সার্ভারে সফলভাবে {$totalReceived}টি রেকর্ড গ্রহণ ও ব্যাকআপ সংরক্ষিত হয়েছে।", [
    'eiin'             => $eiin,
    'records_received' => $totalReceived,
    'backup_file'      => basename($backupFile),
    'synced_at'        => date('Y-m-d H:i:s')
], 200);
