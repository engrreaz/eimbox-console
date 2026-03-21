<?php
require_once '../core/config.php';
require_once '../core/db.php'; 

// Get current open connections
$q1 = $conn->query("SHOW STATUS LIKE 'Threads_connected'");
$row1 = $q1->fetch_assoc();
$threads_connected = (int)$row1['Value'];

// Get peak used connections since restart
$q2 = $conn->query("SHOW STATUS LIKE 'Max_used_connections'");
$row2 = $q2->fetch_assoc();
$max_used = (int)$row2['Value'];

// Get server max limit
$q3 = $conn->query("SHOW VARIABLES LIKE 'max_connections'");
$row3 = $q3->fetch_assoc();
$max_limit = (int)$row3['Value'];

// Insert লগ
$stmt = $conn->prepare("
    INSERT INTO connection_log
    (threads_connected, max_used_connections, max_connections)
    VALUES (?, ?, ?)
");
$stmt->bind_param("iii", $threads_connected, $max_used, $max_limit);
$stmt->execute();
$stmt->close();

$conn->close();