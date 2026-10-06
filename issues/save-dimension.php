<?php
/**
 * EIMBox Issue Tracker - Save Dimensions
 * Location: issues/save-dimension.php
 * Updates or creates issues_tracker records per-platform or globally.
 */
require_once __DIR__ . '/../api/v1/bootstrap.php';
require_once __DIR__ . '/init.php';

$input = get_api_input();
$route = trim($input['route'] ?? $input['script'] ?? '');
$featureId = !empty($input['feature_id']) ? (int)$input['feature_id'] : null;
$platform = trim($input['platform'] ?? 'All');
if (empty($platform)) $platform = 'All';

$title = trim($input['title'] ?? '');
$notes = trim($input['notes'] ?? '');

// If route or title is empty but feature_id is passed, get title from features
if ($featureId) {
    $fStmt = $conn->prepare("SELECT feature_name FROM features WHERE id = ? LIMIT 1");
    $fStmt->bind_param("i", $featureId);
    $fStmt->execute();
    $fRes = $fStmt->get_result()->fetch_assoc();
    if ($fRes) {
        if (empty($title) && !empty($fRes['feature_name'])) $title = $fRes['feature_name'];
    }
    $fStmt->close();

    // If still missing title or route, look up from existing issues_tracker records
    if (empty($title) || empty($route)) {
        $tStmt = $conn->prepare("SELECT title, route FROM issues_tracker WHERE feature_id = ? AND (title != '' OR route != '') ORDER BY (title != '' AND title NOT LIKE 'Feature #%') DESC, id ASC LIMIT 1");
        $tStmt->bind_param("i", $featureId);
        $tStmt->execute();
        $tRes = $tStmt->get_result()->fetch_assoc();
        if ($tRes) {
            if (empty($title) && !empty($tRes['title']) && !str_starts_with($tRes['title'], 'Feature #')) {
                $title = $tRes['title'];
            }
            if (empty($route) && !empty($tRes['route'])) {
                $route = $tRes['route'];
            }
        }
        $tStmt->close();
    }
}

// If route is present but title is empty, check if issues_tracker has a matching record with a title
if (!empty($route) && empty($title)) {
    $rStmt = $conn->prepare("SELECT title, feature_id FROM issues_tracker WHERE (route = ? OR route = ?) AND title != '' AND title NOT LIKE 'Feature #%' ORDER BY (platform = 'All') DESC, id ASC LIMIT 1");
    $basename = basename($route);
    $rStmt->bind_param("ss", $route, $basename);
    $rStmt->execute();
    $rRes = $rStmt->get_result()->fetch_assoc();
    if ($rRes) {
        $title = $rRes['title'];
        if (!$featureId && !empty($rRes['feature_id'])) {
            $featureId = (int)$rRes['feature_id'];
        }
    }
    $rStmt->close();
}

// If feature_id is still not found, check if features table has a matching entry
if (!$featureId) {
    $bName = !empty($route) ? basename($route) : '';
    $pattern = "%$bName%";
    $searchTitle = !empty($title) ? $title : $bName;
    $fSearch = $conn->prepare("SELECT id, feature_name FROM features WHERE LOWER(feature_name) = LOWER(?) OR LOWER(feature_name) = LOWER(?) OR description LIKE ? ORDER BY id DESC LIMIT 1");
    $fSearch->bind_param("sss", $searchTitle, $bName, $pattern);
    $fSearch->execute();
    $fFound = $fSearch->get_result()->fetch_assoc();
    $fSearch->close();
    if ($fFound) {
        $featureId = (int)$fFound['id'];
        $title = $fFound['feature_name'];
    }
}

if (empty($route) && empty($featureId) && empty($title)) {
    api_error('Route, Feature ID, or Title parameter is required', 422);
}

if (empty($title)) {
    $title = !empty($route) ? basename($route) : "Feature #$featureId";
}

$allowedDimensions = [
    'ui', 'light', 'dark', 'view', 'insert', 'update', 
    'delete', 'cache', 'push', 'pull', 'dropdown', 
    'modal', 'print', 'pdf', 'permission',
    'documentation', 'faq', 'youtube_video'
];

$validStatuses = ['Not Tested', 'Not applicable', 'On Progress', 'bug', 'error', 'OK', 'ok'];

// Extract dimension changes
$dimUpdates = [];
if (isset($input['dimensions']) && is_array($input['dimensions'])) {
    foreach ($input['dimensions'] as $dim => $status) {
        if (in_array(strtolower($dim), $allowedDimensions)) {
            $dimUpdates[strtolower($dim)] = $status;
        }
    }
} elseif (isset($input['dimension']) && isset($input['status'])) {
    $d = strtolower(trim($input['dimension']));
    if (in_array($d, $allowedDimensions)) {
        $dimUpdates[$d] = $input['status'];
    }
}

// Ensure audit logging table exists
$conn->query("CREATE TABLE IF NOT EXISTS `issues_dimension_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tracker_id` INT(11) DEFAULT NULL,
  `feature_id` INT(11) DEFAULT NULL,
  `route` VARCHAR(255) NOT NULL,
  `platform` VARCHAR(50) NOT NULL DEFAULT 'All',
  `dimension` VARCHAR(50) NOT NULL COMMENT 'e.g. ui, light, dark, insert, delete',
  `old_status` VARCHAR(50) DEFAULT 'Not Tested',
  `new_status` VARCHAR(50) NOT NULL,
  `updated_by` VARCHAR(100) NOT NULL COMMENT 'User Email',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_route_plat` (`route`, `platform`),
  INDEX `idx_feature` (`feature_id`),
  INDEX `idx_dimension` (`dimension`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Ensure issues_tracker has created_by and modified_by columns
$chkCols = $conn->query("SHOW COLUMNS FROM `issues_tracker` LIKE 'created_by'");
if ($chkCols && $chkCols->num_rows === 0) {
    $conn->query("ALTER TABLE `issues_tracker` ADD COLUMN `created_by` VARCHAR(100) DEFAULT NULL AFTER `notes`");
}
$chkCols2 = $conn->query("SHOW COLUMNS FROM `issues_tracker` LIKE 'modified_by'");
if ($chkCols2 && $chkCols2->num_rows === 0) {
    $conn->query("ALTER TABLE `issues_tracker` ADD COLUMN `modified_by` VARCHAR(100) DEFAULT NULL AFTER `created_by`");
}

// Robust User Email Resolution for Audit Logging
$userEmail = '';
if (!empty($_SESSION['user_email'])) {
    $userEmail = trim($_SESSION['user_email']);
} elseif (!empty($_SESSION['email'])) {
    $userEmail = trim($_SESSION['email']);
} elseif (!empty($_SESSION['useremail'])) {
    $userEmail = trim($_SESSION['useremail']);
} elseif (!empty($input['updated_by']) && filter_var($input['updated_by'], FILTER_VALIDATE_EMAIL)) {
    $userEmail = trim($input['updated_by']);
} elseif (!empty($input['modified_by']) && filter_var($input['modified_by'], FILTER_VALIDATE_EMAIL)) {
    $userEmail = trim($input['modified_by']);
} elseif (!empty($input['created_by']) && filter_var($input['created_by'], FILTER_VALIDATE_EMAIL)) {
    $userEmail = trim($input['created_by']);
} elseif (!empty($_SESSION['usr']) && filter_var($_SESSION['usr'], FILTER_VALIDATE_EMAIL)) {
    $userEmail = trim($_SESSION['usr']);
} elseif (!empty($usr) && filter_var($usr, FILTER_VALIDATE_EMAIL)) {
    $userEmail = trim($usr);
} elseif (!empty($_SESSION['username']) && filter_var($_SESSION['username'], FILTER_VALIDATE_EMAIL)) {
    $userEmail = trim($_SESSION['username']);
} else {
    // Lookup user email in users table by session user_id or username
    $lookupId = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
    $lookupName = $_SESSION['usr'] ?? $_SESSION['username'] ?? $usr ?? '';
    if ($lookupId > 0) {
        $uStmt = $conn->prepare("SELECT email FROM users WHERE id = ? AND email IS NOT NULL AND email != '' LIMIT 1");
        if ($uStmt) {
            $uStmt->bind_param("i", $lookupId);
            $uStmt->execute();
            $uRow = $uStmt->get_result()->fetch_assoc();
            if ($uRow && !empty($uRow['email'])) {
                $userEmail = trim($uRow['email']);
            }
            $uStmt->close();
        }
    }
    if (empty($userEmail) && !empty($lookupName)) {
        $uStmt = $conn->prepare("SELECT email FROM users WHERE (username = ? OR email = ?) AND email IS NOT NULL AND email != '' LIMIT 1");
        if ($uStmt) {
            $uStmt->bind_param("ss", $lookupName, $lookupName);
            $uStmt->execute();
            $uRow = $uStmt->get_result()->fetch_assoc();
            if ($uRow && !empty($uRow['email'])) {
                $userEmail = trim($uRow['email']);
            }
            $uStmt->close();
        }
    }
    if (empty($userEmail)) {
        $userEmail = trim($input['updated_by'] ?? $input['modified_by'] ?? $input['created_by'] ?? $_SESSION['usr'] ?? $_SESSION['username'] ?? $usr ?? 'admin@eimbox.com');
    }
}

// Check if record exists for this feature/route AND platform
$existing = null;
if ($featureId !== null && $featureId > 0) {
    $checkStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE feature_id = ? AND platform = ? LIMIT 1");
    $checkStmt->bind_param("is", $featureId, $platform);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();
}

if (!$existing && !empty($route)) {
    $checkStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE (route = ? OR route = ?) AND platform = ? ORDER BY id DESC LIMIT 1");
    $basename = basename($route);
    $checkStmt->bind_param("sss", $route, $basename, $platform);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();
}

if (!$existing && !empty($title)) {
    $checkStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE title = ? AND platform = ? ORDER BY id DESC LIMIT 1");
    $checkStmt->bind_param("ss", $title, $platform);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();
}

if ($existing) {
    // Update existing record
    $recordId = (int)$existing['id'];
    $setParts = [];
    $params = [];
    $types = "";

    if (!empty($title)) {
        $setParts[] = "`title` = ?";
        $params[] = $title;
        $types .= "s";
    }
    if ($featureId !== null) {
        $setParts[] = "`feature_id` = ?";
        $params[] = $featureId;
        $types .= "i";
    }
    if (!empty($route)) {
        $setParts[] = "`route` = ?";
        $params[] = $route;
        $types .= "s";
    }
    if (isset($input['notes'])) {
        $setParts[] = "`notes` = ?";
        $params[] = $notes;
        $types .= "s";
    }

    // Prepare audit logger
    $logStmt = $conn->prepare("INSERT INTO issues_dimension_logs (`tracker_id`, `feature_id`, `route`, `platform`, `dimension`, `old_status`, `new_status`, `updated_by`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    foreach ($dimUpdates as $dim => $status) {
        $setParts[] = "`$dim` = ?";
        $params[] = $status;
        $types .= "s";

        // Audit log if status actually changed
        $oldStatus = $existing[$dim] ?? 'Not Tested';
        if (strcasecmp(trim((string)$oldStatus), trim((string)$status)) !== 0) {
            $fIdParam = $featureId ? (int)$featureId : ((int)($existing['feature_id'] ?? 0) ?: null);
            $rRouteParam = !empty($route) ? $route : ($existing['route'] ?? '');
            if ($logStmt) {
                $logStmt->bind_param("iissssss", $recordId, $fIdParam, $rRouteParam, $platform, $dim, $oldStatus, $status, $userEmail);
                $logStmt->execute();
            }
        }
    }
    if ($logStmt) $logStmt->close();

    // Always update modified_by and modifieddate
    $setParts[] = "`modified_by` = ?";
    $params[] = $userEmail;
    $types .= "s";

    if (!empty($setParts)) {
        $sql = "UPDATE issues_tracker SET " . implode(", ", $setParts) . ", modifieddate = NOW() WHERE id = ?";
        $params[] = $recordId;
        $types .= "i";

        $upStmt = $conn->prepare($sql);
        $upStmt->bind_param($types, ...$params);
        $upStmt->execute();
        $upStmt->close();
    }
} else {
    // Inherit from baseline row if exists (e.g. platform = 'All')
    $baseline = null;
    if ($featureId !== null && $featureId > 0) {
        $bStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE feature_id = ? ORDER BY (platform = 'All') DESC, id ASC LIMIT 1");
        $bStmt->bind_param("i", $featureId);
        $bStmt->execute();
        $baseline = $bStmt->get_result()->fetch_assoc();
        $bStmt->close();
    } elseif (!empty($route)) {
        $bStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE (route = ? OR route = ?) ORDER BY (platform = 'All') DESC, id ASC LIMIT 1");
        $basename = basename($route);
        $bStmt->bind_param("ss", $route, $basename);
        $bStmt->execute();
        $baseline = $bStmt->get_result()->fetch_assoc();
        $bStmt->close();
    }

    if ($baseline) {
        if ((empty($title) || str_starts_with($title, 'Feature #')) && !empty($baseline['title']) && !str_starts_with($baseline['title'], 'Feature #')) {
            $title = $baseline['title'];
        }
        if (empty($route) && !empty($baseline['route'])) {
            $route = $baseline['route'];
        }
    }

    $cols = ['`title`', '`route`', '`platform`'];
    $placeholders = ['?', '?', '?'];
    $params = [$title, $route, $platform];
    $types = "sss";

    if ($featureId !== null) {
        $cols[] = '`feature_id`';
        $placeholders[] = '?';
        $params[] = $featureId;
        $types .= "i";
    }
    if (!empty($notes)) {
        $cols[] = '`notes`';
        $placeholders[] = '?';
        $params[] = $notes;
        $types .= "s";
    }

    $cols[] = '`created_by`';
    $placeholders[] = '?';
    $params[] = $userEmail;
    $types .= "s";

    $cols[] = '`modified_by`';
    $placeholders[] = '?';
    $params[] = $userEmail;
    $types .= "s";

    foreach ($allowedDimensions as $dim) {
        $cols[] = "`$dim`";
        $placeholders[] = '?';
        $val = $dimUpdates[$dim] ?? ($baseline[$dim] ?? 'Not Tested');
        $params[] = $val;
        $types .= "s";
    }

    $sql = "INSERT INTO issues_tracker (" . implode(", ", $cols) . ", `created_at`, `modifieddate`) VALUES (" . implode(", ", $placeholders) . ", NOW(), NOW())";
    $insStmt = $conn->prepare($sql);
    $insStmt->bind_param($types, ...$params);
    $insStmt->execute();
    $recordId = $conn->insert_id;
    $insStmt->close();

    // Log newly set non-default dimensions
    if (!empty($dimUpdates)) {
        $logStmt = $conn->prepare("INSERT INTO issues_dimension_logs (`tracker_id`, `feature_id`, `route`, `platform`, `dimension`, `old_status`, `new_status`, `updated_by`, `created_at`) VALUES (?, ?, ?, ?, ?, 'Not Tested', ?, ?, NOW())");
        if ($logStmt) {
            foreach ($dimUpdates as $dim => $status) {
                $logStmt->bind_param("iisssss", $recordId, $featureId, $route, $platform, $dim, $status, $userEmail);
                $logStmt->execute();
            }
            $logStmt->close();
        }
    }
}

// Fetch fresh row
$getStmt = $conn->prepare("SELECT * FROM issues_tracker WHERE id = ?");
$getStmt->bind_param("i", $recordId);
$getStmt->execute();
$updatedData = $getStmt->get_result()->fetch_assoc();
$getStmt->close();

// Compute problem score & error percent
$dimProbSum = 0;
$testedCount = 0;
$dimsClean = [];
foreach ($allowedDimensions as $dk) {
    $val = $updatedData[$dk] ?? 'Not Tested';
    $dimsClean[$dk] = $val;
    $st = strtolower(trim((string)$val));
    if ($st !== 'not tested' && $st !== 'nottest') {
        $testedCount++;
    }
    $dimProbSum += calc_dim_problem_score($val);
}
$dimErrorPercent = round($dimProbSum / count($allowedDimensions), 1);

// Fetch recent dimension change audit logs
$recentLogs = [];
$auditStmt = $conn->prepare("SELECT `id`, `dimension`, `old_status`, `new_status`, `updated_by`, `created_at` 
                            FROM `issues_dimension_logs` 
                            WHERE (`route` = ? OR `tracker_id` = ? " . ($featureId ? "OR `feature_id` = ?" : "") . ") 
                              AND (`platform` = ? OR `platform` = 'All') 
                            ORDER BY `id` DESC LIMIT 25");
if ($featureId) {
    $auditStmt->bind_param("siis", $route, $recordId, $featureId, $platform);
} else {
    $auditStmt->bind_param("sis", $route, $recordId, $platform);
}
$auditStmt->execute();
$auditRes = $auditStmt->get_result();
if ($auditRes) {
    while ($ar = $auditRes->fetch_assoc()) {
        $recentLogs[] = $ar;
    }
}
$auditStmt->close();

api_response('success', 'Dimension saved and audited successfully', [
    'id' => $recordId,
    'feature_id' => $featureId,
    'platform' => $platform,
    'route' => $route,
    'error_percent' => $dimErrorPercent,
    'tested_count' => $testedCount,
    'status' => ($testedCount > 0 ? ($dimErrorPercent > 50 ? 'Critical' : ($dimErrorPercent > 20 ? 'Warning' : ($dimErrorPercent > 0 ? 'Attention' : 'OK'))) : 'Untested'),
    'dimensions' => $dimsClean,
    'modified_by' => $userEmail,
    'audit_logs' => $recentLogs
], 200);
