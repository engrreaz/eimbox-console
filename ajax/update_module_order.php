<?php
require_once '../core/config.php';
require_once '../core/db.php';

if(!empty($_POST['order'])){
    $orderData = json_decode($_POST['order'], true);
    foreach($orderData as $row){
        $stmt = $conn->prepare("UPDATE modulelist SET slno=? WHERE id=?");
        $stmt->bind_param('ii', $row['slno'], $row['id']);
        $stmt->execute();
        $stmt->close();
    }
    echo "OK";
}
else{
    echo "No data received";
}
