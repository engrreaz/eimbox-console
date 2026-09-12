<?php
/**
 * EIMBox Issue Tracker API - Bidirectional Screen Push/Pull Synchronization
 * Route: POST /api/v1/issues/sync-screen.php
 * 
 * Order of Operation:
 * 1. PUSH local mutations/dimension changes to server.
 * 2. PULL latest server state, tombstones & recalculate health metrics.
 */

require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_response('error', 'Method not allowed. Only POST is accepted.', null, 405);
}

$input = get_api_input();
$route = trim($input['route'] ?? '');
$title = trim($input['title'] ?? $route);
$platform = trim($input['platform'] ?? 'Android');
$lastSyncedAt = trim($input['last_synced_at'] ?? '');
$pushData = $input['push_data'] ?? null;

if (empty($route)) {
    api_response('error', 'Parameter route is required', null, 422);
}

$dimensionKeys = [
    'ui', 'light', 'dark', 'view', 'insert', 'update', 
    'delete', 'cache', 'push', 'pull', 'dropdown', 
    'modal', 'print', 'pdf', 'permission',
    'documentation', 'faq', 'youtube_video'
];

function calculate_dim_scores($row, $dimensionKeys) {
    $scoreMap = [
        'not tested' => 100.0,
        'nottest' => 100.0,
        'on progress' => 50.0,
        'ongoing' => 50.0,
        'progress' => 50.0,
        'bug' => 30.0,
        'error' => 70.0,
        'ok' => 0.0,
        'completed' => 0.0,
        'not applicable' => 0.0,
        'n/a' => 0.0,
        'na' => 0.0
    ];
    $totalWeight = 0;
    $totalProblemScore = 0;
    foreach ($dimensionKeys as $key) {
        $val = strtolower(trim((string)($row[$key] ?? 'Not Tested')));
        if ($val === 'not applicable' || $val === 'n/a' || $val === 'na') {
            continue;
        }
        $score = $scoreMap[$val] ?? 100.0;
        $totalProblemScore += $score;
        $totalWeight++;
    }
    $problemPercent = $totalWeight > 0 ? round($totalProblemScore / $totalWeight, 1) : 0.0;
    $healthPercent = round(100.0 - $problemPercent, 1);
    return ['problem_percent' => $problemPercent, 'health_percent' => $healthPercent];
}

// -------------------------------------------------------------
// 1. PUSH PHASE
// -------------------------------------------------------------
$pushed = false;
if (!empty($pushData) && is_array($pushData)) {
    $featureId = isset($pushData['feature_id']) && (int)$pushData['feature_id'] > 0 ? (int)$pushData['feature_id'] : null;
    $notes = isset($pushData['notes']) ? trim((string)$pushData['notes']) : null;
    $dimensions = $pushData['dimensions'] ?? [];

    // Check if record exists
    $checkStmt = $conn->prepare("SELECT id FROM issues_tracker WHERE (route = ? OR title = ?) AND (platform = ? OR platform LIKE 'Android%' OR platform = 'All' OR platform IS NULL OR platform = '') ORDER BY (platform = ?) DESC, id DESC LIMIT 1");
    $checkStmt->bind_param("ssss", $route, $title, $platform, $platform);
    $checkStmt->execute();
    $existRow = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($existRow) {
        // UPDATE
        $updates = [];
        $types = "";
        $params = [];

        if ($featureId !== null) {
            $updates[] = "`feature_id` = ?";
            $types .= "i";
            $params[] = $featureId;
        }
        if ($notes !== null) {
            $updates[] = "`notes` = ?";
            $types .= "s";
            $params[] = $notes;
        }
        foreach ($dimensionKeys as $dim) {
            if (isset($dimensions[$dim])) {
                $updates[] = "`$dim` = ?";
                $types .= "s";
                $params[] = $dimensions[$dim];
            }
        }
        $updates[] = "`modifieddate` = NOW()";

        if (!empty($updates)) {
            $sql = "UPDATE issues_tracker SET " . implode(", ", $updates) . " WHERE id = ?";
            $types .= "i";
            $params[] = (int)$existRow['id'];
            $updStmt = $conn->prepare($sql);
            $updStmt->bind_param($types, ...$params);
            $updStmt->execute();
            $updStmt->close();
            $pushed = true;
        }
    } else {
        // INSERT
        $cols = ["`title`", "`route`", "`platform`", "`created_at`", "`modifieddate`"];
        $vals = ["?", "?", "?", "NOW()", "NOW()"];
        $types = "sss";
        $params = [$title, $route, $platform];

        if ($featureId !== null) {
            $cols[] = "`feature_id`";
            $vals[] = "?";
            $types .= "i";
            $params[] = $featureId;
        }
        if ($notes !== null) {
            $cols[] = "`notes`";
            $vals[] = "?";
            $types .= "s";
            $params[] = $notes;
        }
        foreach ($dimensionKeys as $dim) {
            if (isset($dimensions[$dim])) {
                $cols[] = "`$dim`";
                $vals[] = "?";
                $types .= "s";
                $params[] = $dimensions[$dim];
            }
        }

        $sql = "INSERT INTO issues_tracker (" . implode(", ", $cols) . ") VALUES (" . implode(", ", $vals) . ")";
        $insStmt = $conn->prepare($sql);
        $insStmt->bind_param($types, ...$params);
        $insStmt->execute();
        $insStmt->close();
        $pushed = true;
    }
}

// -------------------------------------------------------------
// 2. PULL PHASE
// -------------------------------------------------------------
$selStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE (route = ? OR title = ?) AND (platform = ? OR platform LIKE 'Android%' OR platform = 'All' OR platform IS NULL OR platform = '') ORDER BY (platform = ?) DESC, id DESC LIMIT 1");
$selStmt->bind_param("ssss", $route, $title, $platform, $platform);
$selStmt->execute();
$serverRecord = $selStmt->get_result()->fetch_assoc();
$selStmt->close();

if (!$serverRecord) {
    // Try without platform condition
    $selStmt2 = $conn->prepare("SELECT * FROM issues_tracker WHERE route = ? OR title = ? ORDER BY id DESC LIMIT 1");
    $selStmt2->bind_param("ss", $route, $title);
    $selStmt2->execute();
    $serverRecord = $selStmt2->get_result()->fetch_assoc();
    $selStmt2->close();
}

$nowRes = $conn->query("SELECT NOW() as `server_time`");
$serverTime = ($nowRes && $r = $nowRes->fetch_assoc()) ? $r['server_time'] : date('Y-m-d H:i:s');

$metrics = $serverRecord ? calculate_dim_scores($serverRecord, $dimensionKeys) : ['problem_percent' => 100.0, 'health_percent' => 0.0];

// Check tombstone deletions for issues_tracker since last_synced_at
$tombstones = [];
if (!empty($lastSyncedAt)) {
    $tombStmt = $conn->prepare("SELECT * FROM sync_tombstones WHERE table_name = 'issues_tracker' AND deleted_at >= ? ORDER BY id ASC");
    $tombStmt->bind_param("s", $lastSyncedAt);
    $tombStmt->execute();
    $tRes = $tombStmt->get_result();
    while ($tRow = $tRes->fetch_assoc()) {
        $tombstones[] = $tRow;
    }
    $tombStmt->close();
}

api_response('success', 'Screen sync processed successfully', [
    'pushed' => $pushed,
    'server_time' => $serverTime,
    'record' => $serverRecord,
    'metrics' => $metrics,
    'tombstones' => $tombstones
]);
