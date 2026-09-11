<?php
/**
 * EIMBox Issue Tracker - Get Page Issues & Dimensions
 * Location: issues/get-page-issues.php
 * Calculates Problem % and Health % based on Dimension Health Formula
 */
require_once __DIR__ . '/../api/v1/bootstrap.php';

$input = get_api_input();
$script = trim($input['script'] ?? $_GET['script'] ?? '');
$route = trim($input['route'] ?? $_GET['route'] ?? $script);
$platform = trim($input['platform'] ?? $_GET['platform'] ?? 'Console');

if (empty($route) && empty($script)) {
    api_error('Parameter script or route is required', 422);
}

// Target identifier (filename or route)
$target = !empty($route) ? $route : $script;
$basename = basename($target);

// Standard Dimensions
$dimensionKeys = [
    'ui', 'light', 'dark', 'view', 'insert', 'update', 
    'delete', 'cache', 'push', 'pull', 'dropdown', 
    'modal', 'print', 'pdf', 'permission',
    'documentation', 'faq', 'youtube_video'
];

/**
 * Dimension problem score mapping:
 * Not Tested = 100%, Not applicable = 0%, On Progress = 50%, bug = 30%, error = 70%, ok = 0%
 */
function get_dimension_problem_score($status) {
    $st = strtolower(trim((string)$status));
    switch ($st) {
        case 'not tested':
        case 'nottest':
            return 100.0;
        case 'on progress':
        case 'ongoing':
        case 'progress':
            return 50.0;
        case 'bug':
            return 30.0;
        case 'error':
            return 70.0;
        case 'ok':
        case 'completed':
            return 0.0;
        case 'not applicable':
        case 'n/a':
        case 'na':
            return 0.0;
        default:
            return 100.0; // Default to Not Tested
    }
}

// 1. Fetch or create issues_tracker row for the specific platform
$dimStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE (route = ? OR route = ?) AND platform = ? LIMIT 1");
$dimStmt->bind_param("sss", $target, $basename, $platform);
$dimStmt->execute();
$dimRes = $dimStmt->get_result();
$dimensions = $dimRes->fetch_assoc();
$dimStmt->close();

if (!$dimensions && $platform !== 'All') {
    // Check if a baseline row exists to borrow title/notes, but keep dimensions as Not Tested for this platform
    $bStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE (route = ? OR route = ?) AND platform = 'All' LIMIT 1");
    $bStmt->bind_param("ss", $target, $basename);
    $bStmt->execute();
    $bRow = $bStmt->get_result()->fetch_assoc();
    $bStmt->close();
    if ($bRow) {
        $dimensions = [
            'id' => 0,
            'feature_id' => $bRow['feature_id'] ?? null,
            'title' => $bRow['title'] ?? $basename,
            'route' => $bRow['route'] ?? $target,
            'notes' => $bRow['notes'] ?? '',
            'platform' => $platform
        ];
        foreach ($dimensionKeys as $dk) {
            $dimensions[$dk] = 'Not Tested';
        }
    }
}

if (!$dimensions) {
    // Return default structure
    $dimensions = [
        'id' => 0,
        'feature_id' => null,
        'title' => $basename,
        'route' => $target,
        'ui' => 'Not Tested',
        'light' => 'Not Tested',
        'dark' => 'Not Tested',
        'view' => 'Not Tested',
        'insert' => 'Not Tested',
        'update' => 'Not Tested',
        'delete' => 'Not Tested',
        'cache' => 'Not Tested',
        'push' => 'Not Tested',
        'pull' => 'Not Tested',
        'dropdown' => 'Not Tested',
        'modal' => 'Not Tested',
        'print' => 'Not Tested',
        'pdf' => 'Not Tested',
        'permission' => 'Not Tested',
        'documentation' => 'Not Tested',
        'faq' => 'Not Tested',
        'youtube_video' => 'Not Tested',
        'notes' => '',
        'created_at' => date('Y-m-d H:i:s'),
        'modifieddate' => date('Y-m-d H:i:s')
    ];
}

// Resolve linked feature (from feature_id, other platforms, or matching feature name)
$resolvedFeatureId = !empty($dimensions['feature_id']) ? (int)$dimensions['feature_id'] : 0;
if ($resolvedFeatureId <= 0) {
    $fSearchStmt = $conn->prepare("SELECT feature_id FROM issues_tracker WHERE (route = ? OR route = ?) AND feature_id > 0 ORDER BY id DESC LIMIT 1");
    $fSearchStmt->bind_param("ss", $target, $basename);
    $fSearchStmt->execute();
    $fSearchRow = $fSearchStmt->get_result()->fetch_assoc();
    $fSearchStmt->close();
    if ($fSearchRow && !empty($fSearchRow['feature_id'])) {
        $resolvedFeatureId = (int)$fSearchRow['feature_id'];
    }
}

if ($resolvedFeatureId <= 0) {
    // Try matching with features table by title, feature_name, or description
    $titleCandidate = $dimensions['title'] ?? $basename;
    $descPattern = "%$basename%";
    $fMatchStmt = $conn->prepare("SELECT id FROM features WHERE LOWER(feature_name) = LOWER(?) OR LOWER(feature_name) = LOWER(?) OR description LIKE ? ORDER BY id DESC LIMIT 1");
    $fMatchStmt->bind_param("sss", $titleCandidate, $basename, $descPattern);
    $fMatchStmt->execute();
    $fMatchRow = $fMatchStmt->get_result()->fetch_assoc();
    $fMatchStmt->close();
    if ($fMatchRow && !empty($fMatchRow['id'])) {
        $resolvedFeatureId = (int)$fMatchRow['id'];
        // Auto-link existing tracker rows for this route so they don't remain unlinked
        $autoLink = $conn->prepare("UPDATE issues_tracker SET feature_id = ? WHERE (route = ? OR route = ?) AND (feature_id IS NULL OR feature_id = 0)");
        $autoLink->bind_param("iss", $resolvedFeatureId, $target, $basename);
        $autoLink->execute();
        $autoLink->close();
    }
}

$currentFeature = null;
if ($resolvedFeatureId > 0) {
    $cfStmt = $conn->prepare("SELECT id, feature_name, module_name, description FROM features WHERE id = ? LIMIT 1");
    $cfStmt->bind_param("i", $resolvedFeatureId);
    $cfStmt->execute();
    $currentFeature = $cfStmt->get_result()->fetch_assoc();
    $cfStmt->close();

    if ($currentFeature) {
        $dimensions['feature_id'] = $resolvedFeatureId;
        if (empty($dimensions['title']) || $dimensions['title'] === $basename) {
            $dimensions['title'] = $currentFeature['feature_name'];
        }
    }
}

// 2. Fetch issues from eimbox_features for this script/route
$queryIssues = "SELECT * FROM eimbox_features 
                WHERE (script = ? OR script = ? OR script LIKE ?) ";
$params = [$target, $basename, "%$basename%"];
$types = "sss";

if (!empty($platform) && $platform !== 'All') {
    $queryIssues .= " AND (platform = ? OR platform = 'General') ";
    $params[] = $platform;
    $types .= "s";
}
$queryIssues .= " ORDER BY id DESC";

$issueStmt = $conn->prepare($queryIssues);
$issueStmt->bind_param($types, ...$params);
$issueStmt->execute();
$issueRes = $issueStmt->get_result();
$issuesList = [];
if ($issueRes) {
    while ($row = $issueRes->fetch_assoc()) {
        $issuesList[] = $row;
    }
}
$issueStmt->close();

// 3. Compute Problem % and Health %
$dimScores = [];
foreach ($dimensionKeys as $key) {
    $val = $dimensions[$key] ?? 'Not Tested';
    $dimScores[$key] = [
        'status' => $val,
        'problem_score' => get_dimension_problem_score($val)
    ];
}

// Total dimensions count & total dimension problem sum
$totalDimProblem = 0.0;
$dimCount = count($dimensionKeys);
foreach ($dimScores as $d) {
    $totalDimProblem += $d['problem_score'];
}
$dimProblemAvg = $dimCount > 0 ? ($totalDimProblem / $dimCount) : 0.0;

// Issues remaining problem calculation: 100 - progress_percent
$totalIssueProblem = 0.0;
$issueCount = count($issuesList);
foreach ($issuesList as $iss) {
    $progress = (int)($iss['progress_percent'] ?? 0);
    $status = strtolower(trim((string)($iss['status'] ?? '')));
    if ($status === 'completed' || $status === 'closed') {
        $remaining = 0.0;
    } else {
        $remaining = (float)max(0, 100 - $progress);
    }
    $totalIssueProblem += $remaining;
}
$issueProblemAvg = $issueCount > 0 ? ($totalIssueProblem / $issueCount) : 0.0;

// Combined Problem Percentage
if ($issueCount > 0) {
    // Weighted combination of dimensions & issues
    $overallProblem = round(($totalDimProblem + $totalIssueProblem) / ($dimCount + $issueCount), 1);
} else {
    $overallProblem = round($dimProblemAvg, 1);
}

$healthPercent = round(max(0.0, 100.0 - $overallProblem), 1);

// 4. Fetch Modules & Features for select options
$modules = [];
$mRes = $conn->query("SELECT id, module_name, module_icon FROM modulelist ORDER BY slno ASC, module_name ASC");
if ($mRes) {
    while ($m = $mRes->fetch_assoc()) {
        $modules[] = $m;
    }
}

$features = [];
$fRes = $conn->query("SELECT id, feature_name, module_name FROM features ORDER BY feature_name ASC");
if ($fRes) {
    while ($f = $fRes->fetch_assoc()) {
        $features[] = $f;
    }
}

api_response('success', 'Page issue data retrieved successfully', [
    'script' => $basename,
    'route' => $target,
    'platform' => $platform,
    'dimensions' => $dimensions,
    'dimension_scores' => $dimScores,
    'dim_problem_avg' => round($dimProblemAvg, 1),
    'issues' => $issuesList,
    'issue_count' => $issueCount,
    'issue_problem_avg' => round($issueProblemAvg, 1),
    'problem_percent' => $overallProblem,
    'health_percent' => $healthPercent,
    'current_feature' => $currentFeature,
    'modules' => $modules,
    'features' => $features
], 200);
