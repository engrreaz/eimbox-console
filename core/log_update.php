<?php
require_once 'config.php';
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];
$duration = $data['duration'] ?? 0;
$modifieddate = date('Y-m-d H:i:s');

$stmt = $conn->prepare("UPDATE logbook SET duration=?, modifieddate=? WHERE id=?");
$stmt->bind_param("isi", $duration, $modifieddate, $id);
$stmt->execute();
?>
