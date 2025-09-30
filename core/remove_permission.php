<?php
require_once 'config.php';
require_once 'db.php';
require_once 'global_values.php';

$id = $_POST['id'] ?? 0;
$entryby = $_POST['entryby'] ?? 'system';

if (!$id) {
    echo "Invalid ID";
    exit;
}

// প্রথমে পুরনো permission ধরে রাখি audit-এর জন্য
$stmt = $conn->prepare("SELECT page_name, userlevel, email, permission FROM permission_map WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) {
    echo "Permission not found";
    exit;
}
$old = $res->fetch_assoc();

// Permission মুছে ফেলা


$stmt = $conn->prepare("DELETE FROM permission_map WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Audit table update
$permission = 0;
$action = "Removed";
$stmt = $conn->prepare("INSERT INTO permission_audit (page_name, email, old_permission, new_permission, changed_by , crud_action ) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiiss", $old['page_name'], $old['email'], $old['permission'], $permission, $entryby, $action);
$stmt->execute();


echo "Permission removed & audit saved!";
