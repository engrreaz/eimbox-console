<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';



$order = json_decode($_POST['order'], true);

$stmt = $conn->prepare(
    "UPDATE areas 
     SET idno = ? 
     WHERE areaname = ? AND slot = ? AND sessionyear = ?"
);

foreach ($order as $row) {
    $stmt->bind_param(
        "isss",
        $row['position'],
        $row['classname'],
        $row['slot'],
        $row['session']
    );
    $stmt->execute();
}

$stmt->close();

echo json_encode(['status' => 'success']);
