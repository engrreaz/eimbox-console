<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

// প্যারামিটার গ্রহণ
$slot = $_GET['slot'] ?? '';
$session = $_GET['session'] ?? ''; // আপনার গ্লোবাল সিসি কোড ভেরিয়েবল

$exams = [];

// echo $sccode . '/' . $slot . '/' . $session . '/';

if (!empty($slot) && !empty($session)) {
    // Prepared Statement ব্যবহার করা হয়েছে সিকিউরিটির জন্য
    $stmt = $conn->prepare("SELECT  examtitle FROM examlist 
                           WHERE sccode = ? AND slot = ? AND sessionyear = ? 
                           ORDER BY id ASC");
    
    $stmt->bind_param("iss", $sccode, $slot, $session);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $exams[] = $row['examtitle'];
    }
    
    $stmt->close();
}

// JSON আউটপুট
echo json_encode($exams);