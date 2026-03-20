<?php
require_once 'config.php';
require_once 'db.php';

if (isset($_POST['module_name'], $_POST['col'], $_POST['val'])) {
    $module = $conn->real_escape_string($_POST['module_name']);
    $col = $conn->real_escape_string($_POST['col']);
    $val = intval($_POST['val']);
    $id = intval($_POST['id']);


    $allowedCols = ['crud', 'ui', 'image', 'perm', 'error', 'feature', 'doc', 'youtube'];
    if (in_array($col, $allowedCols)) {
        $sql = "UPDATE modulemanager SET $col='$val' WHERE id='$id'";
        $conn->query($sql);
        echo 'Updated';
    } else {
        echo 'Invalid column';
    }
} else {
    echo 'Invalid request';
}
?>