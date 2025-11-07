<?php
require_once("../header.php");

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$trx = $data['trxID'] ?? '';
$invoice = $data['merchantInvoiceNumber'] ?? '';
$status = $data['transactionStatus'] ?? 'Failed';

// ডাটাবেজে আপডেট
mysqli_query($conn, "UPDATE payments SET status='$status', trx_id='$trx' WHERE invoice_no='$invoice'");

http_response_code(200);
echo json_encode(['status' => 'ok']);
