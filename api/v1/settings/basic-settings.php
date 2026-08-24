<?php
/**
 * EIMBox REST API — Basic Primary Settings Endpoint
 * Route: GET /api/v1/settings/basic-settings.php?sccode={sccode}
 * Route: POST /api/v1/settings/basic-settings.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = authenticate_token($conn);

$input = get_api_input();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. Resolve School Code
$sccode = intval($_GET['sccode'] ?? $input['sccode'] ?? $user['sccode'] ?? 0);

if ($sccode <= 0) {
    api_response('error', 'Valid School Code (sccode) is required.', null, 400);
}

/**
 * Upsert into settings table
 */
function save_or_update_setting($conn, $sccode, $title, $value) {
    $chk = $conn->prepare("SELECT id FROM settings WHERE sccode = ? AND setting_title = ? LIMIT 1");
    $chk->bind_param('is', $sccode, $title);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($existing) {
        $up = $conn->prepare("UPDATE settings SET settings_value = ?, modifieddate = NOW() WHERE id = ?");
        $up->bind_param('si', $value, $existing['id']);
        $up->execute();
        $up->close();
    } else {
        $ins = $conn->prepare("INSERT INTO settings (sccode, setting_title, settings_value, modifieddate) VALUES (?, ?, ?, NOW())");
        $ins->bind_param('iss', $sccode, $title, $value);
        $ins->execute();
        $ins->close();
    }
}

// 2. Handle POST / PUT: Save Settings
if ($method === 'POST' || $method === 'PUT') {
    // Weekends
    if (isset($input['weekends'])) {
        $weekendsVal = is_array($input['weekends']) ? implode(',', $input['weekends']) : trim($input['weekends']);
        save_or_update_setting($conn, $sccode, 'Weekends', $weekendsVal);
    }

    // Medium
    if (isset($input['medium'])) {
        $mediumVal = is_array($input['medium']) ? implode(',', $input['medium']) : trim($input['medium']);
        save_or_update_setting($conn, $sccode, 'Medium', $mediumVal);
    }

    // Version
    if (isset($input['version'])) {
        $versionVal = is_array($input['version']) ? implode(',', $input['version']) : trim($input['version']);
        save_or_update_setting($conn, $sccode, 'Version', $versionVal);
    }

    // Classes
    if (isset($input['classes'])) {
        $classesVal = is_array($input['classes']) ? implode(',', $input['classes']) : trim($input['classes']);
        save_or_update_setting($conn, $sccode, 'Classes', $classesVal);
    }

    // Session Years
    if (isset($input['session_years']) && is_array($input['session_years'])) {
        foreach ($input['session_years'] as $sy) {
            $year = trim($sy['year'] ?? $sy['syear'] ?? '');
            $active = intval($sy['active'] ?? 0);
            if (!empty($year)) {
                $chkSy = $conn->prepare("SELECT id FROM sessionyear WHERE sccode = ? AND syear = ? LIMIT 1");
                $chkSy->bind_param('is', $sccode, $year);
                $chkSy->execute();
                $syRow = $chkSy->get_result()->fetch_assoc();
                $chkSy->close();

                if ($syRow) {
                    $upSy = $conn->prepare("UPDATE sessionyear SET active = ? WHERE id = ?");
                    $upSy->bind_param('ii', $active, $syRow['id']);
                    $upSy->execute();
                    $upSy->close();
                } else {
                    $insSy = $conn->prepare("INSERT INTO sessionyear (sccode, syear, active) VALUES (?, ?, ?)");
                    $insSy->bind_param('isi', $sccode, $year, $active);
                    $insSy->execute();
                    $insSy->close();
                }
            }
        }
    }
}

// 3. Fetch Current Saved Settings
$stmt = $conn->prepare("SELECT setting_title, settings_value FROM settings WHERE sccode = ? AND setting_title IN ('Weekends', 'Medium', 'Version', 'Classes')");
$stmt->bind_param('i', $sccode);
$stmt->execute();
$res = $stmt->get_result();

$savedMap = [];
while ($row = $res->fetch_assoc()) {
    $savedMap[$row['setting_title']] = $row['settings_value'] ?? '';
}
$stmt->close();

// Fetch Session Years
$syStmt = $conn->prepare("SELECT id, syear, active FROM sessionyear WHERE sccode = ? ORDER BY syear DESC, id ASC");
$syStmt->bind_param('i', $sccode);
$syStmt->execute();
$syRes = $syStmt->get_result();

$sessionYears = [];
while ($syRow = $syRes->fetch_assoc()) {
    $sessionYears[] = [
        'id' => intval($syRow['id']),
        'year' => $syRow['syear'],
        'active' => intval($syRow['active'])
    ];
}
$syStmt->close();

// If no session years exist in DB, offer default current years
if (empty($sessionYears)) {
    $curY = intval(date('Y'));
    $sessionYears = [
        ['year' => strval($curY + 1), 'active' => 0],
        ['year' => strval($curY), 'active' => 1],
        ['year' => strval($curY - 1), 'active' => 0]
    ];
}

$parseArray = function($val, $default = []) {
    if (empty($val)) return $default;
    return array_values(array_filter(array_map('trim', explode(',', $val))));
};

$allClassesCatalog = [
    'Play', 'Nursery', 'KG', 'Junior One', 'One', 'Two', 'Three',
    'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'SSC',
    'Eleven', 'Twelve'
];

$allDaysCatalog = [
    'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
];

$actionMsg = ($method === 'POST' || $method === 'PUT') 
    ? 'Basic settings updated successfully.' 
    : 'Basic settings retrieved successfully.';

api_response('success', $actionMsg, [
    'sccode' => $sccode,
    'weekends' => $parseArray($savedMap['Weekends'] ?? '', ['Friday', 'Saturday']),
    'medium' => $parseArray($savedMap['Medium'] ?? '', ['Bengali']),
    'version' => $parseArray($savedMap['Version'] ?? '', ['Bengali']),
    'classes' => $parseArray($savedMap['Classes'] ?? '', ['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten']),
    'session_years' => $sessionYears,
    'catalog' => [
        'available_classes' => $allClassesCatalog,
        'available_days' => $allDaysCatalog,
        'available_mediums' => ['Bengali', 'English'],
        'available_versions' => ['Bengali', 'English', 'Arabic']
    ]
]);
