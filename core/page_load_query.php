<?php
$status_name = 0;

$stmt = $conn->prepare("SELECT status_name FROM modulemanager WHERE related_pages = ? ORDER BY id DESC LIMIT 1");
if ($stmt) {
    $stmt->bind_param("s", $currentFile);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $page_status = $row['status_name'];
    }
}



$stmt->close();