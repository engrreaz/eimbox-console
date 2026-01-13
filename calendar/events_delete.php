<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id = intval($_POST['id'] ?? 0);
// $institution_id = $_SESSION['institution_id'];

$sql = "DELETE FROM events WHERE id=? AND sccode=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $id, $sccode);
mysqli_stmt_execute($stmt);

echo json_encode(['status'=>'success']);
