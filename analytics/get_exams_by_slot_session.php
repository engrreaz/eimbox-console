<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/core-val.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

$sccode = $_SESSION['sccode'] ?? null;
$slot = $_POST['slot'] ?? null;
$sessionyear = $_POST['sessionyear'] ?? null;

if (!$sccode || !$slot || !$sessionyear) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, examtitle 
    FROM examlist 
    WHERE sccode = ? AND slot = ? AND sessionyear = ?
    ORDER BY datestart DESC
");
$stmt->bind_param("iss", $sccode, $slot, $sessionyear);
$stmt->execute();
$result = $stmt->get_result();

$exams = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'exams' => $exams
]);
?>