<?php
/**
 * EIMBox Issue Tracker API - Get All Issues
 * Moved to issues/ directory for better organization.
 */
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/global_values.php';

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
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "ssss";
}

$whereSql = implode(' AND ', $where);
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
$openCount = $ongoingCount = $testingCount = $completedCount = $criticalCount = $highCount = 0;
$sumProgress = 0;
$platformStats = [
    'Dashboard' => 0,
    'Console' => 0,
    'Android Lite' => 0,
    'Android Premium' => 0,
    'Desktop' => 0,
];
foreach ($issues as $iss) {
    $st = strtolower($iss['status'] ?? '');
    $pr = strtolower($iss['priority'] ?? '');
    $pl = $iss['platform'] ?? 'Dashboard';
    if (isset($platformStats[$pl])) $platformStats[$pl]++;
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
$dimSql = "SELECT * FROM issues_tracker WHERE (`platform` = ? OR `platform` = 'General') ORDER BY id DESC";
    $dimStmt = $conn->prepare($dimSql);
    $dimStmt->bind_param('s', $platform);
    $dimStmt->execute();
    $dimRes = $dimStmt->get_result();
$dimRes = $conn->query($dimSql);
$dimensionScreens = [];
$totalScreensWithDimensions = 0;
$dimensionProblemSum = 0;
$dimKeys = ['ui','light','dark','view','insert','update','delete','cache','push','pull','dropdown','modal','print','pdf','permission'];
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

// Fetch Features Catalog ordered by modulelist.slno
$featSql = "
    SELECT 
        f.id AS feature_id,
        f.feature_name,
        f.description AS feature_desc,
        f.module_name,
        COALESCE(ml.slno, 999) AS module_slno,
        COALESCE(ml.module_icon, 'box') AS module_icon,
        it.id AS tracker_id,
        it.title AS screen_title,
        it.route,
        it.ui, it.light, it.dark, it.view, it.insert, it.update, it.delete,
        it.cache, it.push, it.pull, it.dropdown, it.modal, it.print, it.pdf, it.permission,
        it.notes
    FROM features f
    LEFT JOIN modulelist ml ON LOWER(ml.module_name) = LOWER(f.module_name)
    LEFT JOIN issues_tracker it ON (it.feature_id = f.id OR LOWER(it.title) = LOWER(f.feature_name) OR (it.route IS NOT NULL AND LOWER(it.route) = LOWER(f.feature_name)))
    ORDER BY COALESCE(ml.slno, 999) ASC, f.module_name ASC, f.id ASC
";
$featRes = $conn->query($featSql);
$featuresCatalog = [];
$trackedIds = [];
function calc_dim_problem_score($val) {
    $st = strtolower(trim((string)$val));
    if ($st === 'not tested' || $st === '' || $st === null) return 100.0;
    if ($st === 'error') return 70.0;
    if ($st === 'on progress' || $st === 'ongoing') return 50.0;
    if ($st === 'bug') return 30.0;
    if ($st === 'ok' || $st === 'completed' || $st === 'not applicable' || $st === 'n/a') return 0.0;
    return 100.0;
}
if ($featRes) {
    while ($fRow = $featRes->fetch_assoc()) {
        if (!empty($fRow['tracker_id'])) {
            $trackedIds[] = (int)$fRow['tracker_id'];
        }
        $dimProbSum = 0;
        $dims = [];
        foreach ($dimKeys as $dk) {
            $val = $fRow[$dk] ?? 'Not Tested';
            if (empty($val)) $val = 'Not Tested';
            $dims[$dk] = $val;
            $dimProbSum += calc_dim_problem_score($val);
        }
        $probAvg = round($dimProbSum / count($dimKeys), 1);
        $fRow['dimensions'] = $dims;
        $fRow['problem_percent'] = $probAvg;
        $fRow['health_percent'] = round(100 - $probAvg, 1);
        $featuresCatalog[] = $fRow;
    }
    $featRes->free();
}

// Attach issues to each feature
foreach ($featuresCatalog as &$feat) {
    $fid = (int)$feat['feature_id'];
    $feat['issues'] = [];
    foreach ($issues as $iss) {
        if ((int)($iss['feature_id'] ?? 0) === $fid) {
            $feat['issues'][] = $iss;
        }
    }
}

$response = [
    'status' => 'success',
    'platformStats' => $platformStats,
    'kpis' => [
        'total_issues' => $totalIssues,
        'open' => $openCount,
        'ongoing' => $ongoingCount,
        'testing' => $testingCount,
        'completed' => $completedCount,
        'critical' => $criticalCount,
        'high' => $highCount,
        'avg_progress' => $avgProgress,
        'global_health' => $globalHealth,
    ],
    'dimensions' => $dimensionScreens,
    'features' => $featuresCatalog,
    'issues' => $issues,
];
header('Content-Type: application/json');
echo json_encode($response);
?>
