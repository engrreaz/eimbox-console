<?php
require_once 'config.php';
require_once 'db.php';

$role_id = $_POST['role_id'] ?? 0;
$user_id = $_POST['user_id'] ?? 0;
$page_name = $_POST['page_name'] ?? '';
$permission = $_POST['permission'] ?? 0;
$entryby = 'admin'; // Demo user

// Check if record exists
$stmt = $conn->prepare("SELECT id FROM permission_map WHERE (role_id=? OR user_id=?) AND page_name=? LIMIT 1");
$stmt->bind_param('iis', $role_id, $user_id, $page_name);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows){
    $stmt = $conn->prepare("UPDATE permission_map SET permission=?, entryby=?, modifieddate=NOW() WHERE (role_id=? OR user_id=?) AND page_name=?");
    $stmt->bind_param('issis', $permission, $entryby, $role_id, $user_id, $page_name);
    $stmt->execute();
    echo "Updated!";
} else {
    $stmt = $conn->prepare("INSERT INTO permission_map (role_id, user_id, page_name, permission, entryby) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iisis', $role_id, $user_id, $page_name, $permission, $entryby);
    $stmt->execute();
    echo "Inserted!";
}
?>
