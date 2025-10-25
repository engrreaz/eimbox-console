<?php
include '../core/config.php';
include '../core/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add_shortcut') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    if ($id == 0) {
        $user_email = mysqli_real_escape_string($conn, $_POST['user_email']);
        $sccode = mysqli_real_escape_string($conn, $_POST['sccode']);
        $page_name = mysqli_real_escape_string($conn, $_POST['page_name']);
        $page_title = mysqli_real_escape_string($conn, $_POST['page_title']);
        $page_icon = mysqli_real_escape_string($conn, $_POST['page_icon']);
        $module = mysqli_real_escape_string($conn, $_POST['module']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        $sql = "INSERT INTO user_shortcuts (user_email, sccode, page_name, page_title, page_icon, module, status)
            VALUES ('$user_email', '$sccode', '$page_name', '$page_title', '$page_icon', '$module', '$status')";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'message' => 'Shortcut Added Successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }

    } else {

        $sql = "DELETE FROM user_shortcuts where id='$id'";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true, 'message' => 'Shortcut Removed Successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }

    }






} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>