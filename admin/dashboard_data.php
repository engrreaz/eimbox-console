<?php
require_once '../core/config.php';
require_once '../core/db.php';


// --- Most Visited Pages ---
$pageQuery = $conn->query("
    SELECT pagename, COUNT(*) as visits, SUM(duration) as total_time
    FROM logbook
    GROUP BY pagename
    ORDER BY visits DESC
    LIMIT 10
");
$pages = [];
$pageVisits = [];
$pageTime = [];
while ($row = $pageQuery->fetch_assoc()) {
    $pages[] = $row['pagename'];
    $pageVisits[] = $row['visits'];
    $pageTime[] = round($row['total_time'] / 60, 1); // মিনিটে
}

// --- Top Active Users ---
$userQuery = $conn->query("
    SELECT email, SUM(duration) as total_time, COUNT(*) as total_visits
    FROM logbook
    GROUP BY email
    ORDER BY total_time DESC
    LIMIT 10
");
$users = [];
$userTime = [];
$userVisits = [];
while ($row = $userQuery->fetch_assoc()) {
    $users[] = $row['email'];
    $userTime[] = round($row['total_time'] / 60, 1);
    $userVisits[] = $row['total_visits'];
}

// --- Daily Active Users ---
$dailyQuery = $conn->query("
    SELECT DATE(entrytime) as day, COUNT(DISTINCT email) as active_users
    FROM logbook
    GROUP BY day
    ORDER BY day DESC
    LIMIT 7
");
$days = [];
$activeUsers = [];
while ($row = $dailyQuery->fetch_assoc()) {
    $days[] = $row['day'];
    $activeUsers[] = $row['active_users'];
}

// JSON আউটপুট
echo json_encode([
    "pages" => $pages,
    "pageVisits" => $pageVisits,
    "pageTime" => $pageTime,
    "users" => $users,
    "userTime" => $userTime,
    "userVisits" => $userVisits,
    "days" => $days,
    "activeUsers" => $activeUsers
]);
?>
