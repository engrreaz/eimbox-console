<?php
session_start();
require_once '../core/config.php'; 
require_once '../core/db.php'; 
require_once '../core/global_values.php'; 


header('Content-Type: application/json');

// JSON ডাটা গ্রহণ করা
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['ids']) && is_array($data['ids'])) {
    $ids = $data['ids'];
    $success = true;
    $error = '';

    // ট্রানজ্যাকশন শুরু করা (নিরাপত্তার জন্য)
    $conn->begin_transaction();

    try {
        $sl = 1;
        foreach ($ids as $id) {
            $id = intval($id);
            $stmt = $conn->prepare("UPDATE teacher SET sl = ? WHERE id = ? AND sccode = ?");
            $stmt->bind_param("iii", $sl, $id, $sccode); // $sccode আপনার গ্লোবাল ভ্যারিয়েবল
            $stmt->execute();
            $sl++;
        }
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $success = false;
        $error = $e->getMessage();
    }

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
}
exit();
?>