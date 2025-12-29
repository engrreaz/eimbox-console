<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

// Check POST
if(!isset($_POST['id'])){
    echo json_encode(['status'=>'error','msg'=>'ID missing']);
    exit;
}

$id = intval($_POST['id']);

// Optional: security check, ensure this item belongs to current sccode
$q = $conn->query("SELECT id FROM financesetup WHERE id='$id' AND sccode='$sccode'");
if(!$q->num_rows){
    echo json_encode(['status'=>'error','msg'=>'Item not found']);
    exit;
}

// Delete item
if($conn->query("DELETE FROM financesetup WHERE id='$id' AND sccode = '$sccode'")){
    echo json_encode(['status'=>'success','msg'=>'Item deleted successfully']);
} else {
    echo json_encode(['status'=>'error','msg'=>'Failed to delete item']);
}
