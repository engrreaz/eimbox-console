<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Item ID is missing']);
    exit;
}

$id = intval($_POST['id']);

// 1. Fetch item details
$q = $conn->query("SELECT id, itemcode, particulareng, particularben, sessionyear FROM financesetup WHERE id='$id' AND sccode='$sccode'");
if (!$q || $q->num_rows == 0) {
    echo json_encode(['status' => 'error', 'msg' => 'Payment item not found.']);
    exit;
}

$row = $q->fetch_assoc();
$itemcode = $row['itemcode'];
$particulareng = $row['particulareng'];
$session = $row['sessionyear'];

// 2. Check if students have already made payments for this item in stfinance
$chkPaid = $conn->query("SELECT COUNT(*) AS total_paid_students, COALESCE(SUM(paid), 0) AS total_collected
                         FROM stfinance 
                         WHERE itemcode='$itemcode' AND sccode='$sccode' AND paid > 0");

$totalPaidStudents = 0;
$totalCollected = 0;

if ($chkPaid && $paidRow = $chkPaid->fetch_assoc()) {
    $totalPaidStudents = intval($paidRow['total_paid_students']);
    $totalCollected = floatval($paidRow['total_collected']);
}

// 3. If payments exist, BLOCK hard delete to maintain accounting integrity
if ($totalCollected > 0 || $totalPaidStudents > 0) {
    echo json_encode([
        'status' => 'blocked',
        'code' => 'PAYMENT_EXISTS',
        'item_id' => $id,
        'item_name' => $particulareng,
        'paid_students' => $totalPaidStudents,
        'total_amount' => number_format($totalCollected, 2),
        'msg' => "এই আইটেমে ইতোমধ্যে {$totalPaidStudents} জন শিক্ষার্থী মোট ৳ " . number_format($totalCollected, 2) . " পরিশোধ করেছে। অডিট ও হিসাবের নিরাপত্তার স্বার্থে এটি সম্পূর্ণ ডিলিট করা যাবে না। আপনি চাইলে আইটেমটি নিষ্ক্রিয় (Deactivate / Archive) করে রাখতে পারেন।"
    ]);
    exit;
}

// 4. If NO payments have been made, perform safe deletion
$delSetup = $conn->query("DELETE FROM financesetup WHERE id='$id' AND sccode='$sccode'");

if ($delSetup) {
    if (!empty($itemcode)) {
        // Remove configured rates
        $conn->query("DELETE FROM financesetupvalue WHERE itemcode='$itemcode' AND sccode='$sccode'");
        
        // Remove only unpaid dues records from student ledgers
        $conn->query("DELETE FROM stfinance WHERE itemcode='$itemcode' AND sccode='$sccode' AND paid = 0");
    }

    echo json_encode([
        'status' => 'success',
        'msg' => "Payment item '{$particulareng}' and its unpaid dues have been deleted safely."
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'msg' => 'Failed to delete item: ' . $conn->error
    ]);
}
