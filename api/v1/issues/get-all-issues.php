<?php
/**
 * EIMBox Issue Tracker API - Get All Issues
 * Multi-Platform aggregation, KPIs, search and dimension health overview
 */
require_once __DIR__ . '/../bootstrap.php';

$input = get_api_input();
$platform = trim($input['platform'] ?? $_GET['platform'] ?? 'All');
$module = trim($input['module'] ?? $_GET['module'] ?? 'All');
$status = trim($input['status'] ?? $_GET['status'] ?? 'All');
$priority = trim($input['priority'] ?? $_GET['priority'] ?? 'All');
$search = trim($input['search'] ?? $_GET['search'] ?? '');

$where = ["1=1"];
$params = [];
$types = "";

if (!empty($platform) && $platform !== 'All') {
    $where[] = "(`platform` = ? OR `platform` = 'General')";
    $params[] = $platform;
    $types .= "s";
}

if (!empty($module) && $module !== 'All') {
    $where[] = "`module` = ?";
    $params[] = $module;
    $types .= "s";
}

if (!empty($status) && $status !== 'All') {
    $where[] = "`status` = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($priority) && $priority !== 'All') {
    $where[] = "`priority` = ?";
    $params[] = $priority;
    $types .= "s";
}

if (!empty($search)) {
    $where[] = "(`feature` LIKE ? OR `topic` LIKE ? OR `issues` LIKE ? OR `script` LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "ssss";
}

$whereSql = implode(" AND ", $where);
$sql = "SELECT * FROM eimbox_features WHERE $whereSql ORDER BY id DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}

$issues = [];
while ($row = $res->fetch_assoc()) {
    $issues[] = $row;
}
if (isset($stmt)) $stmt->close();

// Compute KPIs
$totalIssues = count($issues);
$openCount = 0;
$ongoingCount = 0;
$testingCount = 0;
$completedCount = 0;
$criticalCount = 0;
$highCount = 0;
$sumProgress = 0;

$platformStats = [
    'Dashboard' => 0,
    'Console' => 0,
    'Android Lite' => 0,
    'Android Premium' => 0,
    'Desktop' => 0
];

foreach ($issues as $iss) {
    $st = strtolower($iss['status'] ?? '');
    $pr = strtolower($iss['priority'] ?? '');
    $pl = $iss['platform'] ?? 'Dashboard';

    if (isset($platformStats[$pl])) {
        $platformStats[$pl]++;
    }

    if ($st === 'open' || $st === 'pending') $openCount++;
    elseif ($st === 'ongoing') $ongoingCount++;
    elseif ($st === 'testing') $testingCount++;
    elseif ($st === 'completed' || $st === 'closed') $completedCount++;

    if ($pr === 'critical') $criticalCount++;
    elseif ($pr === 'high') $highCount++;

    $sumProgress += (int)($iss['progress_percent'] ?? 0);
}

$avgProgress = $totalIssues > 0 ? round($sumProgress / $totalIssues, 1) : 100.0;

// Fetch Dimension Summaries
$dimSql = "SELECT * FROM issues_tracker ORDER BY id DESC";
$dimRes = $conn->query($dimSql);
$dimensionScreens = [];
$totalScreensWithDimensions = 0;
$dimensionProblemSum = 0;

$dimKeys = ['ui', 'light', 'dark', 'view', 'insert', 'update', 'delete', 'cache', 'push', 'pull', 'dropdown', 'modal', 'print', 'pdf', 'permission'];

while ($d = $dimRes->fetch_assoc()) {
    $screenProb = 0;
    foreach ($dimKeys as $dk) {
        $st = strtolower(trim((string)($d[$dk] ?? '')));
        if ($st === 'not tested' || $st === '') $screenProb += 100;
        elseif ($st === 'on progress') $screenProb += 50;
        elseif ($st === 'bug') $screenProb += 30;
        elseif ($st === 'error') $screenProb += 70;
    }
    $screenProbAvg = round($screenProb / count($dimKeys), 1);
    $d['problem_percent'] = $screenProbAvg;
    $d['health_percent'] = round(100 - $screenProbAvg, 1);
    $dimensionScreens[] = $d;

    $dimensionProblemSum += $screenProbAvg;
    $totalScreensWithDimensions++;
}

$globalDimProblemAvg = $totalScreensWithDimensions > 0 ? round($dimensionProblemSum / $totalScreensWithDimensions, 1) : 0;
$globalHealth = round(100 - ($totalIssues > 0 ? ((100 - $avgProgress) + $globalDimProblemAvg) / 2 : $globalDimProblemAvg), 1);

// Modules List
$modules = [];
$mRes = $conn->query("SELECT id, module_name FROM modulelist ORDER BY slno ASC, module_name ASC");
if ($mRes) {
    while ($m = $mRes->fetch_assoc()) {
        $modules[] = $m;
    }
}

api_response('success', 'All issues data retrieved successfully', [
    'platform' => $platform,
    'total_issues' => $totalIssues,
    'kpis' => [
        'total' => $totalIssues,
        'open' => $openCount,
        'ongoing' => $ongoingCount,
        'testing' => $testingCount,
        'completed' => $completedCount,
        'critical' => $criticalCount,
        'high' => $highCount,
        'avg_progress' => $avgProgress,
        'global_health' => $globalHealth,
        'platforms' => $platformStats
    ],
    'issues' => $issues,
    'dimension_screens' => $dimensionScreens,
    'modules' => $modules
], 200);
