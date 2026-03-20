<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slots = $_POST['slots'] ?? [];
$sessions = $_POST['sessions'] ?? [];

if (empty($slots) || empty($sessions)) {
    echo json_encode([]);
    exit;
}

$slotIn = "'" . implode("','", $slots) . "'";
$sessionIn = "'" . implode("','", $sessions) . "'";

$sql = "
SELECT DISTINCT
    MAX(idno) as idno,
    slot,
    sessionyear,
    areaname
    FROM areas
    WHERE sccode='$sccode'
    AND slot IN ($slotIn)
    AND sessionyear IN ($sessionIn)
    GROUP BY slot, sessionyear, areaname
    ORDER BY slot, sessionyear, idno
    ";
// echo $sql;

$q = mysqli_query($conn, $sql);

$data = [];
while ($r = mysqli_fetch_assoc($q)) {
    $data[] = $r;
}

echo json_encode($data);
