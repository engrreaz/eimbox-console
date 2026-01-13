<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

// ---------- Today Events ----------
$sqlToday = "
SELECT *
FROM events
WHERE sccode='$sccode'
AND (
    scope='institution'
    OR (scope='personal' AND user_id='$user_id')
)
AND DATE(start) = '$today'
ORDER BY start
";
$resToday = mysqli_query($conn, $sqlToday);

$todayEvents = [];
while($row=mysqli_fetch_assoc($resToday)){
    $todayEvents[] = $row;
}

// ---------- Next Event (any day) ----------
$sqlNext = "
SELECT *
FROM events
WHERE sccode='$sccode'
AND (
    scope='institution'
    OR (scope='personal' AND user_id='$user_id')
)
AND start > '$now'
ORDER BY start
LIMIT 1
";
$resNext = mysqli_query($conn, $sqlNext);
$nextEvent = mysqli_fetch_assoc($resNext);

echo json_encode([
    'today' => $todayEvents,
    'next' => $nextEvent
]);
