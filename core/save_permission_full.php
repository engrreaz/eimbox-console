<?php
require_once 'config.php';
require_once 'db.php';
require_once 'global_values.php';

$id = $_POST['id'] ?? 0;
$userlevel = $_POST['role_id'] ?? '';
$get_sccode = intval($_POST['sccode'] ?? '');
$email = $_POST['user_id'] ?? '';
$page_name = $_POST['page_name'] ?? '';
$permission = $_POST['permission'] ?? 0;
$entryby = $_POST['entryby'] ?? 'system';

echo $get_sccode;

// Old permission check
if (!empty($userlevel)) {
    // Role-level permission
    $stmt = $conn->prepare("SELECT permission FROM permission_map WHERE page_name=? AND userlevel=? AND (email IS NULL OR email='') LIMIT 1");
    $stmt->bind_param("ss", $page_name, $userlevel);
} else {
    // Email-level permission
    $stmt = $conn->prepare("SELECT permission FROM permission_map WHERE page_name=? AND email=? LIMIT 1");
    $stmt->bind_param("ss", $page_name, $email);
}

$stmt->execute();
$res = $stmt->get_result();
$old_permission = 0;

if ($res->num_rows && $get_sccode != 0) {
    $old_permission = $res->fetch_assoc()['permission'];

    if (!empty($userlevel)) {
        $stmt = $conn->prepare("UPDATE permission_map SET permission=?, entryby=?, modifiedtime=NOW() WHERE page_name=? AND userlevel=? AND (email IS NULL OR email='')");
        $stmt->bind_param("isss", $permission, $entryby, $page_name, $userlevel);
    } else {
        $stmt = $conn->prepare("UPDATE permission_map SET permission=?, entryby=?, modifiedtime=NOW() WHERE page_name=? AND email=?");
        $stmt->bind_param("isss", $permission, $entryby, $page_name, $email);
    }

    $stmt->execute();
    $action = "Updated";

} else {
    $old_permission = 0;
    $stmt = $conn->prepare("INSERT INTO permission_map (sccode, page_name, userlevel, email, permission, entryby) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $sccode,  $page_name, $userlevel, $email, $permission, $entryby);
    $stmt->execute();
    $action = "Inserted";
}




// Audit table update

$stmt = $conn->prepare("INSERT INTO permission_audit (page_name, userlevel, email, old_permission, new_permission, changed_by , crud_action ) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiiss", $page_name, $userlevel, $email, $old_permission, $permission, $entryby, $action);
$stmt->execute();

echo "$action & Audit Saved!";