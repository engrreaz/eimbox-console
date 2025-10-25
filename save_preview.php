<?php
require_once 'header.php';

$id = intval($_POST['id'] ?? 0);
$content = $_POST['full_documentation'] ?? '';

if (!$id) { http_response_code(400); exit; }

$stmt = $conn->prepare("UPDATE project_documentation SET full_documentation=? WHERE id=?");
$stmt->bind_param("si", $content, $id);
$stmt->execute();
$stmt->close();

echo "Saved";
?>
