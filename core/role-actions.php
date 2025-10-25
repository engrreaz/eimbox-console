<?php
require_once 'config.php';
require_once 'db.php';
require_once 'global_values.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

if ($action == 'add') {
    $userlevel = $_POST['userlevel'];
    $descrip = $_POST['descrip'] ?? '';
    if ($is_admin >= 4) {
        $sccode = $_POST['sccode'] ?? 0;
    }

    $entryby = $_SESSION['username'] ?? 'system';

    $stmt = $conn->prepare("INSERT INTO rolemanager (userlevel, descrip, sccode, entryby) VALUES (?,?,?,?)");
    $stmt->bind_param("ssis", $userlevel, $descrip, $sccode, $entryby);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else
        echo json_encode(['status' => 'error', 'message' => 'Insert failed']);

} elseif ($action == 'get') {
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM rolemanager WHERE id=$id");
    if ($res->num_rows) {
        echo json_encode(['status' => 'success', 'data' => $res->fetch_assoc()]);
    } else
        echo json_encode(['status' => 'error', 'message' => 'Role not found']);

} elseif ($action == 'update') {
    $id = intval($_POST['id']);
    $userlevel = $_POST['userlevel'];
    $descrip = $_POST['descrip'] ?? '';
    $sccode = $_POST['sccode'] ?? 0;

    $stmt = $conn->prepare("UPDATE rolemanager SET userlevel=?, descrip=?, sccode=?, modifieddate=NOW() WHERE id=?");
    $stmt->bind_param("ssii", $userlevel, $descrip, $sccode, $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);

} elseif ($action == 'delete') {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM rolemanager WHERE id=$id") ?
        print json_encode(['status' => 'success']) :
        print json_encode(['status' => 'error', 'message' => 'Delete failed']);
}
