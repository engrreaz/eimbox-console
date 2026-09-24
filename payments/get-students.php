<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

if (empty($sccode)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or session expired']);
    exit;
}

$sy = trim($_POST['session'] ?? $_POST['sessionyear'] ?? date('Y'));
$cls = trim($_POST['cls'] ?? $_POST['classname'] ?? '');
$sec = trim($_POST['sec'] ?? $_POST['sectionname'] ?? '');
$slot = trim($_POST['slot'] ?? '');

if (empty($cls)) {
    echo json_encode(['status' => 'success', 'students' => []]);
    exit;
}

$query = "
    SELECT 
        si.id AS sessioninfo_id,
        si.stid, 
        si.rollno, 
        si.rate, 
        si.slot,
        si.classname,
        si.sectionname,
        COALESCE(NULLIF(s.stnameeng, ''), 'Student') AS stnameeng,
        COALESCE(NULLIF(s.stnameben, ''), '') AS stnameben,
        s.fmobile,
        s.photo
    FROM sessioninfo si 
    LEFT JOIN students s ON si.stid = s.stid AND (s.sccode = si.sccode OR s.sccode = 0 OR s.sccode IS NULL)
    WHERE si.sccode = ? 
      AND (si.sessionyear LIKE ? OR si.sessionyear = '' OR si.sessionyear IS NULL)
      AND si.classname = ?
";

$syParam = "%$sy%";
$params = [$sccode, $syParam, $cls];
$types = "iss";

if (!empty($sec) && $sec !== 'Select Section' && $sec !== 'All' && $sec !== 'All Sections') {
    $query .= " AND (TRIM(si.sectionname) = TRIM(?) OR si.sectionname = ?)";
    $params[] = $sec;
    $params[] = $sec;
    $types .= "ss";
}

if (!empty($slot) && $slot !== 'All Slots' && $slot !== 'All' && $slot !== 'Select Slot') {
    $query .= " AND (si.slot = ? OR si.slot = '' OR si.slot IS NULL)";
    $params[] = $slot;
    $types .= "s";
}

$query .= " ORDER BY CAST(si.rollno AS UNSIGNED) ASC, si.rollno ASC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Database prepare error: ' . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$students = [];
while ($row = $res->fetch_assoc()) {
    $students[] = [
        'sessioninfo_id' => (int)$row['sessioninfo_id'],
        'stid' => (string)$row['stid'],
        'rollno' => (int)$row['rollno'],
        'rate' => (int)($row['rate'] ?? 100),
        'slot' => $row['slot'],
        'classname' => $row['classname'],
        'sectionname' => $row['sectionname'],
        'stnameeng' => $row['stnameeng'],
        'stnameben' => $row['stnameben'],
        'fmobile' => $row['fmobile'] ?? '',
        'photo' => $row['photo'] ?? ''
    ];
}
$stmt->close();

echo json_encode(['status' => 'success', 'students' => $students]);
