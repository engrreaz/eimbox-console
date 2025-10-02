<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');
/*
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = $conn->prepare("SELECT * FROM dev_timeline WHERE id=?");
    $res->bind_param("i", $id);
    $res->execute();
    $result = $res->get_result()->fetch_assoc();
    $res->close();

    if ($result) {
        $chk = $conn->prepare("SELECT COUNT(*) as cnt FROM dev_timeline WHERE feature_name=?");
        $chk->bind_param("s", $result['feature_name']);
        $chk->execute();
        $cnt = $chk->get_result()->fetch_assoc()['cnt'];
        $chk->close();

        $result['feature_in_list'] = $cnt > 1;
        echo json_encode($result);
        exit;
    }
}
*/
if (isset($_POST['delete'])) {
    $id = intval($_POST['delete']);
    $stmt = $conn->prepare("DELETE FROM dev_timeline WHERE id=?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
    exit;
}

if ($_POST) {
    $id = intval($_POST['id'] ?? 0);
    $page_name = $_POST['page_name'];
    $feature_name = ($_POST['feature_select'] == 'new') ? $_POST['feature_new'] : $_POST['feature_select'];
    $action_type = $_POST['action_type'];
    $status = $_POST['status'];
    $description = $_POST['notes'] ?? '';
    $logged_by = 'Admin'; // এখানে ইউজারের নাম/আইডি রাখো

    if ($id) {
        $stmt = $conn->prepare("UPDATE dev_timeline SET feature_name=?, action_type=?, status=?, description=?, logged_by=? WHERE id=?");
        $stmt->bind_param("sssssi", $feature_name, $action_type, $status, $description, $logged_by, $id);
        $ok = $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO dev_timeline (page_name, feature_name, action_type, status, description, logged_by, created_at) VALUES (?,?,?,?,?,?,NOW())");
        $stmt->bind_param("ssssss", $page_name, $feature_name, $action_type, $status, $description, $logged_by);
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
    }

    $statusColors = [
        'draft'=>'secondary','planning'=>'warning','in_progress'=>'info','testing'=>'info',
        'alpha'=>'primary','beta'=>'primary','rc'=>'primary','staging'=>'info',
        'stable'=>'success','lts'=>'success','deprecated'=>'dark','archived'=>'secondary'
    ];

    echo json_encode([
        'success' => $ok,
        'id' => $id,
        'created_at' => date('Y-m-d H:i'),
        'feature_name' => $feature_name,
        'action_type' => $action_type,
        'status' => ucfirst(str_replace('_',' ',$status)),
        'status_color' => $statusColors[$status] ?? 'secondary',
        'description' => $description,
        'logged_by' => $logged_by
    ]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid request']);
