<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sy = trim($_POST['session'] ?? $_POST['sessionyear'] ?? $_COOKIE['chain-session'] ?? date('Y'));
$slot = trim($_POST['slot'] ?? $_COOKIE['chain-slot'] ?? '');

echo '<option value="">Select Class</option>';

if (empty($sccode)) {
    exit;
}

$query = "SELECT MAX(idno) as idno, areaname FROM areas WHERE sccode = ? AND sessionyear LIKE ? AND areaname IS NOT NULL AND areaname != ''";
$syParam = "%$sy%";
$params = [$sccode, $syParam];
$types = "is";

if (!empty($slot)) {
    $query .= " AND (slot = ? OR slot = '' OR slot IS NULL)";
    $params[] = $slot;
    $types .= "s";
}

$query .= " GROUP BY areaname ORDER BY idno ASC, areaname ASC";

$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $r = $stmt->get_result();
    while ($row = $r->fetch_assoc()) {
        $cName = htmlspecialchars($row['areaname']);
        echo "<option value='{$cName}'>{$cName}</option>";
    }
    $stmt->close();
}