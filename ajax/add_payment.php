<?php
require_once '../core/config.php';
require_once '../core/db.php';

header('Content-Type: application/json; charset=utf-8');

$invoice_id = intval($_POST['invoice_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);
$method = $_POST['method'] ?? 'Cash';
$note = trim($_POST['note'] ?? '');
$payment_date = $_POST['payment_date'] ?? date('Y-m-d');

if(!$invoice_id || $amount <= 0){
    echo json_encode(['success'=>false, 'message'=>'Invalid data']);
    exit;
}

$conn->begin_transaction();

try {
    // ✅ পেমেন্ট ইনসার্ট
    $stmt = $conn->prepare("INSERT INTO billing_payments (invoice_id, payment_date, amount, payment_method, remarks, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isdss", $invoice_id, $payment_date, $amount, $method, $note);
    $stmt->execute();

    // ✅ ইনভয়েস আপডেট
    $conn->query("
        UPDATE billing_invoices 
        SET paid_amount = paid_amount + $amount,
            due_amount = grand_total - (paid_amount + $amount),
            payment_status = CASE 
                        WHEN (grand_total - (paid_amount + $amount)) <= 0 THEN 'Paid'
                        WHEN (paid_amount + $amount) > 0 THEN 'Partial'
                        ELSE 'Pending'
                     END,
            updated_at = NOW()
        WHERE id = $invoice_id
    ");

    $conn->commit();
    echo json_encode(['success'=>true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
