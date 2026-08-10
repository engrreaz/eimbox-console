<?php
require_once '../core/init.php';

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
    ORDER BY examtitle
");
$stmt->bind_param("sss", $sccode, $slot, $sessionyear);
$stmt->execute();
$result = $stmt->get_result();

$exams = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($exams);
?>