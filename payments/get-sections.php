<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sy = trim($_POST['session'] ?? $_POST['sessionyear'] ?? date('Y'));
$cls = trim($_POST['cls'] ?? $_POST['classname'] ?? '');
$selectedSec = trim($_POST['sec'] ?? $_POST['selected_sec'] ?? '');

echo '<option value="">Select Section</option>';

if ($cls === '' || empty($sccode)) {
    exit;
}

$stmt = $conn->prepare("SELECT DISTINCT subarea FROM areas WHERE sccode = ? AND (sessionyear LIKE ? OR sessionyear = '' OR sessionyear IS NULL) AND areaname = ? AND subarea IS NOT NULL AND subarea != '' ORDER BY subarea ASC");
if ($stmt) {
    $syParam = "%$sy%";
    $stmt->bind_param("iss", $sccode, $syParam, $cls);
    $stmt->execute();
    $res = $stmt->get_result();
    $foundSelected = false;
    while ($row = $res->fetch_assoc()) {
        $sub = htmlspecialchars($row['subarea']);
        $sel = '';
        if (!empty($selectedSec) && strcasecmp($row['subarea'], $selectedSec) === 0) {
            $sel = 'selected';
            $foundSelected = true;
        }
        echo "<option value='{$sub}' {$sel}>{$sub}</option>";
    }
    $stmt->close();

    if (!empty($selectedSec) && !$foundSelected) {
        $secSafe = htmlspecialchars($selectedSec);
        echo "<option value='{$secSafe}' selected>{$secSafe}</option>";
    }
}
