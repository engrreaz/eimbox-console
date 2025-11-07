<?php
require_once '../core/config.php';
require_once '../core/db.php';

header('Content-Type: application/json; charset=utf-8');

$data = [];

// সব ইনভয়েস + প্রতিষ্ঠান নাম
$sql = "
    SELECT bi.id, bi.invoice_no, bi.invoice_date, bi.grand_total AS grand_total, IFNULL(bi.paid_amount, 0) AS paid_amount, IFNULL(bi.due_amount, bi.grand_total) AS due_amount, bi.payment_status, sc.scname FROM billing_invoices bi JOIN scinfo sc ON sc.sccode = bi.sccode ORDER BY bi.invoice_date DESC;
";

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);