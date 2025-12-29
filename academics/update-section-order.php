<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$push = '';

$order = json_decode($_POST['order'], true);

$stmt = $conn->prepare(
    "UPDATE areas 
     SET idno = ? 
     WHERE areaname = ? AND slot = ? AND sessionyear = ? AND id = ? AND sccode=?"
);

foreach ($order as $row) {
    $stmt->bind_param(
        "isssii",
        $row['position'],
        $row['classname'],
        $row['slot'],
        $row['session'],
        $row['section_id'],
        $sccode

    );

    $stmt->execute();
    $push .= $row['section_id'];
}

$stmt->close();

echo json_encode(['status' => 'success-Sectuib' . $push]);
