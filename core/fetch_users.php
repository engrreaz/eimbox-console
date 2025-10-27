<?php
require_once 'config.php';
require_once 'db.php';
require_once 'global_values.php';

$page_name = $_GET['page'] ?? '';
if (!$page_name) {
        echo json_encode([]);
        exit;
}

// Exclude usersappapp who already have custom permission for this page
$sql = "SELECT email, profilename 
        FROM usersapp 
        WHERE sccode=? AND active=1 AND email NOT IN (SELECT email FROM permission_map WHERE page_name=? AND email != '')
        ORDER BY profilename";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $sccode, $page_name);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc())
        $users[] = $row;

header('Content-Type: application/json');
echo json_encode($users);
