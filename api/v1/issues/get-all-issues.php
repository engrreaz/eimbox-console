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
    'Android Native' => 0,
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

        // Attach issues for this feature
        $fId = (int)$fRow['feature_id'];
        $fName = $fRow['feature_name'];
        $fRoute = $fRow['route'] ?? '';
        $fIssues = [];
        $fPlatformBreakdown = ['Console' => 0, 'Dashboard' => 0, 'Android Lite' => 0, 'Android Native' => 0, 'Desktop' => 0, 'General' => 0];

        foreach ($issues as $iss) {
            $match = false;
            if ($fId > 0 && isset($iss['feature_id']) && (int)$iss['feature_id'] === $fId) $match = true;
            elseif (!empty($iss['feature']) && strtolower($iss['feature']) === strtolower($fName)) $match = true;
            elseif (!empty($iss['script']) && !empty($fRoute) && strtolower(basename($iss['script'])) === strtolower(basename($fRoute))) $match = true;

            if ($match) {
                $fIssues[] = $iss;
                $plat = $iss['platform'] ?? 'General';
                if (isset($fPlatformBreakdown[$plat])) {
                    $fPlatformBreakdown[$plat]++;
                } else {
                    $fPlatformBreakdown['General']++;
                }
            }
        }

        $fRow['issues'] = $fIssues;
        $fRow['issue_count'] = count($fIssues);
        $fRow['platform_breakdown'] = $fPlatformBreakdown;
        $featuresCatalog[] = $fRow;
    }
}

// Append any unlinked tracked screens from issues_tracker
$unlinkedSql = "SELECT * FROM issues_tracker";
if (!empty($trackedIds)) {
    $unlinkedSql .= " WHERE id NOT IN (" . implode(',', $trackedIds) . ")";
}
$unlinkedRes = $conn->query($unlinkedSql);
if ($unlinkedRes) {
    while ($uRow = $unlinkedRes->fetch_assoc()) {
        $dimProbSum = 0;
        $dims = [];
        foreach ($dimKeys as $dk) {
            $val = $uRow[$dk] ?? 'Not Tested';
            if (empty($val)) $val = 'Not Tested';
            $dims[$dk] = $val;
            $dimProbSum += calc_dim_problem_score($val);
        }
        $probAvg = round($dimProbSum / count($dimKeys), 1);
        
        $uRoute = $uRow['route'] ?? '';
        $uTitle = $uRow['title'] ?: $uRoute;
        $uIssues = [];
        $uPlatformBreakdown = ['Console' => 0, 'Dashboard' => 0, 'Android Lite' => 0, 'Android Native' => 0, 'Desktop' => 0, 'General' => 0];

        foreach ($issues as $iss) {
            $match = false;
            if (!empty($iss['script']) && !empty($uRoute) && strtolower(basename($iss['script'])) === strtolower(basename($uRoute))) $match = true;
            elseif (!empty($iss['screen_title']) && strtolower($iss['screen_title']) === strtolower($uTitle)) $match = true;
            elseif (!empty($iss['feature']) && strtolower($iss['feature']) === strtolower($uTitle)) $match = true;

            if ($match) {
                $uIssues[] = $iss;
                $plat = $iss['platform'] ?? 'General';
                if (isset($uPlatformBreakdown[$plat])) {
                    $uPlatformBreakdown[$plat]++;
                } else {
                    $uPlatformBreakdown['General']++;
                }
            }
        }

        $featuresCatalog[] = [
            'feature_id' => $uRow['feature_id'] ?: 0,
            'feature_name' => $uTitle,
            'feature_desc' => 'Tracked screen route',
            'module_name' => 'General',
            'module_slno' => 999,
            'module_icon' => 'display',
            'tracker_id' => $uRow['id'],
            'screen_title' => $uTitle,
            'route' => $uRoute,
            'dimensions' => $dims,
            'problem_percent' => $probAvg,
            'health_percent' => round(100 - $probAvg, 1),
            'notes' => $uRow['notes'] ?? '',
            'issues' => $uIssues,
            'issue_count' => count($uIssues),
            'platform_breakdown' => $uPlatformBreakdown
        ];
    }
}

// Fetch all modules from modulelist ordered by slno
$modules = [];
$modQuery = $conn->query("SELECT id, module_name, slno, module_icon, descrip FROM modulelist WHERE module_name IS NOT NULL AND module_name != '' ORDER BY slno ASC, module_name ASC");
if ($modQuery) {
    while ($mRow = $modQuery->fetch_assoc()) {
        $modules[] = $mRow;
    }
}

// If modulelist is empty or missing, collect distinct module names from features
if (empty($modules)) {
    $fallbackModQuery = $conn->query("SELECT DISTINCT module_name FROM features WHERE module_name IS NOT NULL AND module_name != '' ORDER BY module_name ASC");
    if ($fallbackModQuery) {
        $sl = 1;
        while ($fm = $fallbackModQuery->fetch_assoc()) {
            $modules[] = [
                'id' => $sl,
                'module_name' => $fm['module_name'],
                'slno' => $sl,
                'module_icon' => 'folder2',
                'descrip' => ''
            ];
            $sl++;
        }
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
    'features_catalog' => $featuresCatalog,
    'modules' => $modules
], 200);
