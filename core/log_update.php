<?php
require_once 'config.php';
require_once 'db.php';

// -------------------------
// Get POST data safely
// -------------------------
$data = [];

// Check JSON POST
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) $data = [];
}

// Fallback to normal POST
$data = array_merge($data, $_POST);

// Extract variables
$id = isset($data['id']) ? intval($data['id']) : 0;
$duration = isset($data['duration']) ? intval($data['duration']) : 0;
$modifieddate = date('Y-m-d H:i:s');

$stmt = $conn->prepare("UPDATE logbook SET duration=?, modifieddate=? WHERE id=?");

$stmt->bind_param("isi", $duration, $modifieddate, $id);
$stmt->execute();

$stmt->close();