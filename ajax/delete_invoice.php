<?php
require_once '../core/config.php';
require_once '../core/db.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_POST['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false]);
    exit;
}

$conn->begin_transaction();
try {
    $conn->query("DELETE FROM billing_items WHERE invoice_id=$id");
    $conn->query("DELETE FROM billing_invoices WHERE id=$id");

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false]);
}
