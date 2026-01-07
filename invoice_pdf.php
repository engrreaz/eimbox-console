<?php
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'vendor/autoload.php';

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

if (!isset($_GET['id']))
    die("Invoice ID missing");
$invoice_id = intval($_GET['id']);
$printMode = isset($_GET['print']) && $_GET['print'] == 1;

// Fetch invoice
$stmt = $conn->prepare("SELECT * FROM billing_invoices WHERE id = ?");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invoice)
    die("Invoice not found");

// Fetch items
$stmt2 = $conn->prepare("SELECT * FROM billing_items WHERE invoice_id = ?");
$stmt2->bind_param("i", $invoice_id);
$stmt2->execute();
$itemsResult = $stmt2->get_result();
$items = [];
while ($row = $itemsResult->fetch_assoc())
    $items[] = $row;
$stmt2->close();

// mPDF font setup
$defaultConfig = (new ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new Mpdf([
    'fontDir' => array_merge($fontDirs, [__DIR__ . '/fonts']),
    'fontdata' => $fontData + [
        'solaimanlipi' => [
            'R' => 'SolaimanLipi.ttf',
            'useOTL' => 0xFF,
            'useKashida' => 75
        ]
    ],
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_top' => 15,
    'margin_bottom' => 15,
    'margin_left' => 15,
    'margin_right' => 15,
    'default_font' => 'solaimanlipi'
]);

// HTML content
$html = '<h2 style="text-align:center;">ইনভয়েস</h2>
<p><strong>Invoice No:</strong> ' . $invoice['invoice_no'] . '<br>
<strong>Date:</strong> ' . $invoice['invoice_date'] . '<br>
<strong>গ্রাহক:</strong> ' . $invoice['customer_name'] . '<br>
<strong>ঠিকানা:</strong> ' . $invoice['customer_address'] . '</p>

<table style="width:100%; border-collapse: collapse; border: 1px solid #000;">
    <thead>
        <tr style="background:#f2f2f2;">
            <th>#</th>
            <th>বিবরণ</th>
            <th>পরিমাণ</th>
            <th>দর</th>
            <th>মোট</th>
        </tr>
    </thead>
    <tbody>';

$i = 1;
foreach ($items as $item) {
    $html .= '<tr>
        <td>' . $i . '</td>
        <td>' . $item['description'] . '</td>
        <td>' . $item['qty'] . '</td>
        <td>' . $item['rate'] . '</td>
        <td>' . $item['amount'] . '</td>
    </tr>';
    $i++;
}

$html .= '</tbody></table>

<table style="width:100%; margin-top:15px;">
<tr>
    <td style="border:none;"></td>
    <td style="border:none; text-align:right;">Subtotal: ' . $invoice['subtotal'] . '</td>
</tr>
<tr>
    <td style="border:none;"></td>
    <td style="border:none; text-align:right;">Discount: ' . $invoice['discount'] . '</td>
</tr>
<tr>
    <td style="border:none;"></td>
    <td style="border:none; text-align:right;">VAT (' . $invoice['vat_percent'] . '%): ' . $invoice['vat_amount'] . '</td>
</tr>
<tr>
    <td style="border:none;"></td>
    <td style="border:none; text-align:right;"><strong>Grand Total: ' . $invoice['grand_total'] . '</strong></td>
</tr>
<tr>
    <td style="border:none;"></td>
    <td style="border:none; text-align:right;">Paid: ' . $invoice['paid_amount'] . '</td>
</tr>
<tr>
    <td style="border:none;"></td>
    <td style="border:none; text-align:right;">Due: ' . $invoice['due_amount'] . '</td>
</tr>
</table>';

// Generate PDF
$mpdf->WriteHTML($html);

if ($printMode) {
    $mpdf->SetJS('this.print();');
    $mpdf->Output($invoice['invoice_no'] . '.pdf', 'I');
} else {
    $mpdf->Output($invoice['invoice_no'] . '.pdf', 'D');
}
