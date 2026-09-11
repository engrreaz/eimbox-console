<?php
/**
 * EIMBox Issue Tracker API - Get All Issues
 * Multi-Platform aggregation, KPIs, search and dimension health overview.
 * Relocated to issues/ directory for modular architecture.
 */
require_once __DIR__ . '/../api/v1/bootstrap.php';

if (!function_exists('calc_dim_problem_score')) {
    function calc_dim_problem_score($val) {
        $st = strtolower(trim((string)$val));
        if ($st === 'not tested' || $st === '' || $st === null || $st === 'nottest') return 100.0;
        if ($st === 'error') return 100.0;
        if ($st === 'bug') return 50.0;
        if ($st === 'on progress' || $st === 'ongoing' || $st === 'progress') return 40.0;
        if ($st === 'ok' || $st === 'completed' || $st === 'not applicable' || $st === 'n/a' || $st === 'na') return 0.0;
        return 100.0;
    }
}

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
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $issues[] = $row;
    }
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

// Fetch Dimension Summaries (15 Dimensions)
$dimSql = "SELECT * FROM issues_tracker ORDER BY id DESC";
$dimRes = $conn->query($dimSql);
$dimensionScreens = [];
$totalScreensWithDimensions = 0;
$dimensionProblemSum = 0;

$dimKeys = [
    'ui', 'light', 'dark', 'view', 'insert', 'update', 'delete',
    'cache', 'push', 'pull', 'dropdown', 'modal', 'print', 'pdf', 'permission',
    'documentation', 'faq', 'youtube_video'
];

if ($dimRes) {
    while ($d = $dimRes->fetch_assoc()) {
        $screenProb = 0;
        $testedDimCount = 0;
        foreach ($dimKeys as $dk) {
            $val = $d[$dk] ?? 'Not Tested';
            $st = strtolower(trim((string)$val));
            if ($st !== 'not tested' && $st !== 'nottest') {
                $testedDimCount++;
            }
            $screenProb += calc_dim_problem_score($val);
        }
        $screenProbAvg = round($screenProb / count($dimKeys), 1);
        $d['problem_percent'] = $screenProbAvg;
        $d['health_percent'] = round(100 - $screenProbAvg, 1);
        $d['tested_count'] = $testedDimCount;
        $dimensionScreens[] = $d;

        $dimensionProblemSum += $screenProbAvg;
        $totalScreensWithDimensions++;
    }
}

$globalDimProblemAvg = $totalScreensWithDimensions > 0 ? round($dimensionProblemSum / $totalScreensWithDimensions, 1) : 0;
// Global Health is computed below based on featuresCatalog and unresolved issues penalty

// Fetch all raw issues across all platforms for accurate 5-platform breakdown
$allRawIssuesRes = $conn->query("SELECT * FROM eimbox_features ORDER BY id DESC");
$allRawIssues = [];
if ($allRawIssuesRes) {
    while ($r = $allRawIssuesRes->fetch_assoc()) {
        $allRawIssues[] = $r;
    }
}

// Preload all trackers for per-platform dimension resolution (newest records first)
$allTrackersRes = $conn->query("SELECT * FROM issues_tracker ORDER BY id DESC");
$allTrackers = [];
if ($allTrackersRes) {
    while ($t = $allTrackersRes->fetch_assoc()) {
        $allTrackers[] = $t;
    }
}

// Fetch Features Catalog ordered by modulelist.slno
$featSql = "
    SELECT 
        f.id AS feature_id,
        f.feature_name,
        f.description AS feature_desc,
        f.module_name,
        COALESCE(ml.slno, 999) AS module_slno,
        COALESCE(ml.module_icon, 'box') AS module_icon
    FROM features f
    LEFT JOIN modulelist ml ON LOWER(ml.module_name) = LOWER(f.module_name)
    ORDER BY COALESCE(ml.slno, 999) ASC, f.module_name ASC, f.id ASC
";
$featRes = $conn->query($featSql);
$featuresCatalog = [];
$trackedIds = [];

$platformsList = ['Console', 'Dashboard', 'Android Lite', 'Android Premium', 'Desktop'];

if ($featRes) {
    while ($fRow = $featRes->fetch_assoc()) {
        $fId = (int)$fRow['feature_id'];
        $fName = $fRow['feature_name'];

        // Find baseline tracker row for this feature (strictly platform='All' or empty)
        $baselineTracker = null;
        foreach ($allTrackers as $t) {
            $plat = strtolower(trim((string)($t['platform'] ?? 'all')));
            if ($plat === 'all' || $plat === '') {
                $match = false;
                if ($fId > 0 && isset($t['feature_id']) && (int)$t['feature_id'] === $fId) $match = true;
                elseif (!empty($t['title']) && strtolower($t['title']) === strtolower($fName)) $match = true;

                if ($match) {
                    $baselineTracker = $t;
                    $trackedIds[] = (int)$t['id'];
                    break;
                }
            }
        }

        $fRoute = $baselineTracker ? ($baselineTracker['route'] ?? '') : '';
        $fRow['tracker_id'] = $baselineTracker ? (int)$baselineTracker['id'] : null;
        $fRow['screen_title'] = $baselineTracker ? $baselineTracker['title'] : $fName;
        $fRow['route'] = $fRoute;
        $fRow['notes'] = $baselineTracker ? ($baselineTracker['notes'] ?? '') : '';

        // Feature general/baseline dimensions
        $dimProbSum = 0;
        $testedCount = 0;
        $dims = [];
        foreach ($dimKeys as $dk) {
            $val = $baselineTracker[$dk] ?? ($fRow[$dk] ?? 'Not Tested');
            if (empty($val)) $val = 'Not Tested';
            $dims[$dk] = $val;
            $st = strtolower(trim((string)$val));
            if ($st !== 'not tested' && $st !== 'nottest') {
                $testedCount++;
            }
            $dimProbSum += calc_dim_problem_score($val);
        }
        $dimErrorPercent = round($dimProbSum / count($dimKeys), 1);
        $fRow['dimensions'] = $dims;
        $fRow['tested_dimensions_count'] = $testedCount;

        // Build distinct per-platform dimension data
        $platformsData = [];
        foreach ($platformsList as $plName) {
            $platTracker = null;
            // 1. First priority: Exact feature_id match
            if ($fId > 0) {
                foreach ($allTrackers as $t) {
                    $plat = strtolower(trim((string)($t['platform'] ?? '')));
                    if ($plat === strtolower($plName)) {
                        if (isset($t['feature_id']) && (int)$t['feature_id'] === $fId) {
                            $platTracker = $t;
                            $trackedIds[] = (int)$t['id'];
                            break;
                        }
                    }
                }
            }

            // 2. Second priority: Route match (if not found by feature_id)
            if (!$platTracker && !empty($fRoute)) {
                foreach ($allTrackers as $t) {
                    $plat = strtolower(trim((string)($t['platform'] ?? '')));
                    if ($plat === strtolower($plName)) {
                        if (!empty($t['route']) && strtolower(basename($t['route'])) === strtolower(basename($fRoute))) {
                            $platTracker = $t;
                            $trackedIds[] = (int)$t['id'];
                            break;
                        }
                    }
                }
            }

            // 3. Third priority: Title fallback match
            if (!$platTracker && !empty($fName)) {
                foreach ($allTrackers as $t) {
                    $plat = strtolower(trim((string)($t['platform'] ?? '')));
                    if ($plat === strtolower($plName)) {
                        if (!empty($t['title']) && strtolower($t['title']) === strtolower($fName)) {
                            $platTracker = $t;
                            $trackedIds[] = (int)$t['id'];
                            break;
                        }
                    }
                }
            }

            if ($platTracker) {
                $platDimProbSum = 0;
                $platTestedCount = 0;
                $platDims = [];
                foreach ($dimKeys as $dk) {
                    $val = $platTracker[$dk] ?? 'Not Tested';
                    if (empty($val)) $val = 'Not Tested';
                    $platDims[$dk] = $val;
                    $st = strtolower(trim((string)$val));
                    if ($st !== 'not tested' && $st !== 'nottest') {
                        $platTestedCount++;
                    }
                    $platDimProbSum += calc_dim_problem_score($val);
                }
                $platDimErrorPercent = round($platDimProbSum / count($dimKeys), 1);
                $platStatus = ($platTestedCount > 0 ? ($platDimErrorPercent > 50 ? 'Critical' : ($platDimErrorPercent > 20 ? 'Warning' : ($platDimErrorPercent > 0 ? 'Attention' : 'OK'))) : 'Untested');

                $platformsData[$plName] = [
                    'platform' => $plName,
                    'tracker_id' => (int)$platTracker['id'],
                    'is_custom' => true,
                    'issue_count' => 0,
                    'error_percent' => $platDimErrorPercent,
                    'tested_count' => $platTestedCount,
                    'status' => $platStatus,
                    'dimensions' => $platDims,
                    'issues' => []
                ];
            } else {
                $platDims = [];
                foreach ($dimKeys as $dk) {
                    $platDims[$dk] = 'Not Tested';
                }
                $platformsData[$plName] = [
                    'platform' => $plName,
                    'tracker_id' => null,
                    'is_custom' => false,
                    'issue_count' => 0,
                    'error_percent' => 100.0,
                    'tested_count' => 0,
                    'status' => 'Untested',
                    'dimensions' => $platDims,
                    'issues' => []
                ];
            }
        }

        // Attach issues for this feature
        $fIssues = [];
        $fPlatformBreakdown = ['Console' => 0, 'Dashboard' => 0, 'Android Lite' => 0, 'Android Premium' => 0, 'Desktop' => 0, 'General' => 0];

        foreach ($allRawIssues as $iss) {
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

                $st = strtolower($iss['status'] ?? 'open');
                $pr = strtolower($iss['priority'] ?? 'medium');
                $isResolved = ($st === 'completed' || $st === 'closed');

                $penalty = 0;
                if (!$isResolved) {
                    if ($pr === 'critical') $penalty = 50;
                    elseif ($pr === 'high') $penalty = 30;
                    elseif ($pr === 'medium') $penalty = 15;
                    else $penalty = 10;

                    $prog = (int)($iss['progress_percent'] ?? 0);
                    if ($prog > 0) {
                        $penalty = round($penalty * ((100 - $prog) / 100));
                    }
                }

                $targetPlatforms = ($plat === 'General' || empty($plat)) ? $platformsList : [$plat];
                foreach ($targetPlatforms as $tpl) {
                    if (isset($platformsData[$tpl])) {
                        $platformsData[$tpl]['issue_count']++;
                        $platformsData[$tpl]['issues'][] = $iss;
                        if (!$isResolved) {
                            $platformsData[$tpl]['error_percent'] = min(100.0, $platformsData[$tpl]['error_percent'] + $penalty);
                            if ($pr === 'critical') $platformsData[$tpl]['status'] = 'Critical';
                            elseif ($platformsData[$tpl]['status'] !== 'Critical' && $pr === 'high') $platformsData[$tpl]['status'] = 'Warning';
                            elseif (!in_array($platformsData[$tpl]['status'], ['Critical', 'Warning'])) $platformsData[$tpl]['status'] = 'Attention';
                        }
                    }
                }
            }
        }

        // Compute 5 platforms average error
        $sum5PlatError = 0;
        foreach ($platformsList as $plName) {
            $sum5PlatError += $platformsData[$plName]['error_percent'];
        }
        $avg5PlatformError = round($sum5PlatError / count($platformsList), 1);

        $fRow['issues'] = $fIssues;
        $fRow['issue_count'] = count($fIssues);
        $fRow['platform_breakdown'] = $fPlatformBreakdown;
        $fRow['platforms_data'] = $platformsData;
        $fRow['avg_5_platform_error'] = $avg5PlatformError;

        // Displayed problem_percent for the feature:
        if ($platform === 'All' || empty($platform)) {
            $fRow['problem_percent'] = $avg5PlatformError;
        } else {
            $fRow['problem_percent'] = $platformsData[$platform]['error_percent'] ?? $avg5PlatformError;
        }
        $fRow['health_percent'] = round(100 - $fRow['problem_percent'], 1);

        $featuresCatalog[] = $fRow;
    }
}

// Append any unlinked tracked screens from issues_tracker
$unlinkedTrackers = [];
foreach ($allTrackers as $t) {
    if (!in_array((int)$t['id'], $trackedIds)) {
        if (!empty($t['feature_id'])) {
            $uKey = 'fid_' . (int)$t['feature_id'];
        } else {
            $uKey = 'title_' . strtolower(trim($t['title'])) . '|' . strtolower(trim($t['route'] ?? ''));
        }
        if (!isset($unlinkedTrackers[$uKey])) {
            $unlinkedTrackers[$uKey] = [
                'baseline' => null,
                'platforms' => [],
                'all_ids' => []
            ];
        }
        $unlinkedTrackers[$uKey]['all_ids'][] = (int)$t['id'];
        $plat = strtolower(trim((string)($t['platform'] ?? 'all')));
        if ($plat === 'all' || $plat === '' || $unlinkedTrackers[$uKey]['baseline'] === null) {
            $unlinkedTrackers[$uKey]['baseline'] = $t;
        }
        foreach ($platformsList as $plName) {
            if ($plat === strtolower($plName)) {
                $unlinkedTrackers[$uKey]['platforms'][$plName] = $t;
            }
        }
    }
}

foreach ($unlinkedTrackers as $uItem) {
    $uRow = $uItem['baseline'];
    if (!$uRow) continue;
    foreach ($uItem['all_ids'] as $tid) {
        $trackedIds[] = $tid;
    }

    $uRoute = $uRow['route'] ?? '';
    $uTitle = $uRow['title'] ?: $uRoute;
    $fId = !empty($uRow['feature_id']) ? (int)$uRow['feature_id'] : 0;

    // Feature general/baseline dimensions
    $dimProbSum = 0;
    $testedCount = 0;
    $dims = [];
    foreach ($dimKeys as $dk) {
        $val = $uRow[$dk] ?? 'Not Tested';
        if (empty($val)) $val = 'Not Tested';
        $dims[$dk] = $val;
        $st = strtolower(trim((string)$val));
        if ($st !== 'not tested' && $st !== 'nottest') {
            $testedCount++;
        }
        $dimProbSum += calc_dim_problem_score($val);
    }
    $dimErrorPercent = round($dimProbSum / count($dimKeys), 1);
    $uRow['dimensions'] = $dims;
    $uRow['tested_dimensions_count'] = $testedCount;

    // Distinct per-platform dimension data
    $platformsData = [];
    foreach ($platformsList as $plName) {
        $platTracker = $uItem['platforms'][$plName] ?? null;
        if ($platTracker) {
            $platDimProbSum = 0;
            $platTestedCount = 0;
            $platDims = [];
            foreach ($dimKeys as $dk) {
                $val = $platTracker[$dk] ?? 'Not Tested';
                if (empty($val)) $val = 'Not Tested';
                $platDims[$dk] = $val;
                $st = strtolower(trim((string)$val));
                if ($st !== 'not tested' && $st !== 'nottest') {
                    $platTestedCount++;
                }
                $platDimProbSum += calc_dim_problem_score($val);
            }
            $platDimErrorPercent = round($platDimProbSum / count($dimKeys), 1);
            $platStatus = ($platTestedCount > 0 ? ($platDimErrorPercent > 50 ? 'Critical' : ($platDimErrorPercent > 20 ? 'Warning' : ($platDimErrorPercent > 0 ? 'Attention' : 'OK'))) : 'Untested');

            $platformsData[$plName] = [
                'platform' => $plName,
                'tracker_id' => (int)$platTracker['id'],
                'is_custom' => true,
                'issue_count' => 0,
                'error_percent' => $platDimErrorPercent,
                'tested_count' => $platTestedCount,
                'status' => $platStatus,
                'dimensions' => $platDims,
                'issues' => []
            ];
        } else {
            $platDims = [];
            foreach ($dimKeys as $dk) {
                $platDims[$dk] = 'Not Tested';
            }
            $platformsData[$plName] = [
                'platform' => $plName,
                'tracker_id' => null,
                'is_custom' => false,
                'issue_count' => 0,
                'error_percent' => 100.0,
                'tested_count' => 0,
                'status' => 'Untested',
                'dimensions' => $platDims,
                'issues' => []
            ];
        }
    }

    $uIssues = [];
    $uPlatformBreakdown = ['Console' => 0, 'Dashboard' => 0, 'Android Lite' => 0, 'Android Premium' => 0, 'Desktop' => 0, 'General' => 0];

    foreach ($allRawIssues as $iss) {
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

            $st = strtolower($iss['status'] ?? 'open');
            $pr = strtolower($iss['priority'] ?? 'medium');
            $isResolved = ($st === 'completed' || $st === 'closed');

            $penalty = 0;
            if (!$isResolved) {
                if ($pr === 'critical') $penalty = 50;
                elseif ($pr === 'high') $penalty = 30;
                elseif ($pr === 'medium') $penalty = 15;
                else $penalty = 10;

                $prog = (int)($iss['progress_percent'] ?? 0);
                if ($prog > 0) {
                    $penalty = round($penalty * ((100 - $prog) / 100));
                }
            }

            $targetPlatforms = ($plat === 'General' || empty($plat)) ? $platformsList : [$plat];
            foreach ($targetPlatforms as $tpl) {
                if (isset($platformsData[$tpl])) {
                    $platformsData[$tpl]['issue_count']++;
                    $platformsData[$tpl]['issues'][] = $iss;
                    if (!$isResolved) {
                        $platformsData[$tpl]['error_percent'] = min(100.0, $platformsData[$tpl]['error_percent'] + $penalty);
                        if ($pr === 'critical') $platformsData[$tpl]['status'] = 'Critical';
                        elseif ($platformsData[$tpl]['status'] !== 'Critical' && $pr === 'high') $platformsData[$tpl]['status'] = 'Warning';
                        elseif (!in_array($platformsData[$tpl]['status'], ['Critical', 'Warning'])) $platformsData[$tpl]['status'] = 'Attention';
                    }
                }
            }
        }
    }

    $sum5PlatError = 0;
    foreach ($platformsList as $plName) {
        $sum5PlatError += $platformsData[$plName]['error_percent'];
    }
    $avg5PlatformError = round($sum5PlatError / count($platformsList), 1);

    $uItemClean = [
        'feature_id' => $fId ?: $uRow['id'],
        'feature_name' => $uTitle,
        'feature_desc' => !empty($uRoute) ? "Tracked screen: $uRoute" : "Tracked screen item",
        'module_name' => 'General',
        'module_slno' => 999,
        'module_icon' => 'display',
        'tracker_id' => (int)$uRow['id'],
        'screen_title' => $uTitle,
        'route' => $uRoute,
        'dimensions' => $dims,
        'tested_dimensions_count' => $testedCount,
        'problem_percent' => ($platform === 'All' || empty($platform)) ? $avg5PlatformError : ($platformsData[$platform]['error_percent'] ?? $avg5PlatformError),
        'health_percent' => round(100 - (($platform === 'All' || empty($platform)) ? $avg5PlatformError : ($platformsData[$platform]['error_percent'] ?? $avg5PlatformError)), 1),
        'notes' => $uRow['notes'] ?? '',
        'issues' => $uIssues,
        'issue_count' => count($uIssues),
        'platform_breakdown' => $uPlatformBreakdown,
        'platforms_data' => $platformsData,
        'avg_5_platform_error' => $avg5PlatformError
    ];

    $featuresCatalog[] = $uItemClean;
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

// Calculate Global Health based on Features Catalog and active unresolved issues
$totalCatalogFeatures = count($featuresCatalog);
if ($totalCatalogFeatures > 0) {
    $sumFeatureHealth = 0;
    foreach ($featuresCatalog as $fc) {
        $sumFeatureHealth += (float)($fc['health_percent'] ?? 100.0);
    }
    $avgFeatureHealth = round($sumFeatureHealth / $totalCatalogFeatures, 1);
} else {
    $avgFeatureHealth = 100.0;
}

// Issue penalty calculation: unresolved issues deduct points based on priority and progress
$issuePenalty = 0.0;
foreach ($issues as $iss) {
    $st = strtolower($iss['status'] ?? '');
    if ($st === 'completed' || $st === 'closed') continue;

    $pr = strtolower($iss['priority'] ?? '');
    $baseDeduct = 1.0;
    if ($pr === 'critical') $baseDeduct = 3.0;
    elseif ($pr === 'high') $baseDeduct = 2.0;
    elseif ($pr === 'medium') $baseDeduct = 1.0;
    else $baseDeduct = 0.5;

    $prog = (int)($iss['progress_percent'] ?? 0);
    $effectiveDeduct = $baseDeduct * ((100 - max(0, min(100, $prog))) / 100.0);
    $issuePenalty += $effectiveDeduct;
}

// Cap max penalty from issues to 15% so a few bugs don't completely zero out the system
$issuePenalty = min(15.0, round($issuePenalty, 1));
$globalHealth = max(0.0, min(100.0, round($avgFeatureHealth - $issuePenalty, 1)));
$globalProb = round(100.0 - $globalHealth, 1);

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
        'global_problem' => $globalProb,
        'avg_feature_health' => $avgFeatureHealth,
        'issue_penalty' => $issuePenalty,
        'platforms' => $platformStats
    ],
    'issues' => $issues,
    'dimension_screens' => $dimensionScreens,
    'features_catalog' => $featuresCatalog,
    'modules' => $modules
], 200);
