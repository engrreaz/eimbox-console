<?php
require_once '../core/db.php';

$id = intval($_GET['id'] ?? 0);
if(!$id) exit;

$q1 = $conn->query("SELECT * FROM billing_invoices WHERE id=$id LIMIT 1");
$invoice = $q1->fetch_assoc();

if(!$invoice) exit;

$q2 = $conn->query("SELECT * FROM billing_items WHERE invoice_id=$id");
$items = [];
while($r = $q2->fetch_assoc()) $items[] = $r;

$colorMap = [
    'Pending' => 'warning',
    'Partial' => 'info',
    'Paid' => 'success',
    'Cancelled' => 'secondary'
];
$invoice['status_color'] = $colorMap[$invoice['status']] ?? 'light';

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['invoice'=>$invoice, 'items'=>$items], JSON_UNESCAPED_UNICODE);
