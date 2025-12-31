<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');



if(isset($_POST['rowid']) && intval($_POST['rowid']) > 0) {
    $rowid = intval($_POST['rowid']);

    $sql = "DELETE FROM financesetupind WHERE id='$rowid' LIMIT 1";
    if($conn->query($sql)) {
        echo "Row Deleted Successfully.";
    } else {
        echo "Failed to delete row.";
    }
} else {
    echo "Invalid row id.";
}
