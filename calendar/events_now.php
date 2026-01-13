<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$user_id = $_SESSION['user_id'];
$now = date('Y-m-d H:i:s');

$sql = "
SELECT *
FROM events
WHERE sccode='$sccode'
AND (
    scope='institution'
    OR (scope='personal' AND user_id='$user_id')
)
AND start <= '$now'
AND (end IS NULL OR end >= '$now')
ORDER BY start
";

$res = mysqli_query($conn, $sql);

$rows = [];
while($row = mysqli_fetch_assoc($res)){
    $rows[] = $row;
}

header('Content-Type: application/json');
echo json_encode($rows);
