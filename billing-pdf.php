<?php
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'vendor/autoload.php'; // mPDF autoload

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;


$id = intval($_GET['id'] ?? 0);
if (!$id)
    die("Invalid invoice ID");

// ইনভয়েস ডেটা


$invoiceQ = $conn->prepare("
    SELECT bi.*, sc.scname 
    FROM billing_invoices bi
    JOIN scinfo sc ON sc.sccode = bi.sccode
    WHERE bi.id = ?
");
$invoiceQ->bind_param("i", $id);
$invoiceQ->execute();
$invoice = $invoiceQ->get_result()->fetch_assoc();

if (!$invoice)
    die("Invoice not found");

// ইনভয়েস আইটেম
$itemQ = $conn->prepare("SELECT * FROM billing_items WHERE invoice_id=?");
$itemQ->bind_param("i", $id);
$itemQ->execute();
$items = $itemQ->get_result()->fetch_all(MYSQLI_ASSOC);

// ===============================
// 🔹 PDF সেটআপ ----------------
// ===============================

$defaultConfig = (new ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new Mpdf([
    'fontDir' => array_merge($fontDirs, [__DIR__ . '/fonts']),
    'fontdata' => $fontData + [
        'solaimanlipi' => [
            'R' => 'solaimanlipi.ttf',
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

// ফন্ট রেজিস্টার (যদি আগে না থাকে)
$fontPath = 'fonts/solaimanlipi.ttf';
if (file_exists($fontPath)) {
    $mpdf->AddFontDirectory('fonts/');
    $mpdf->fontdata['solaimanlipi'] = [
        'R' => 'SolaimanLipi.ttf',
        'useOTL' => 0xFF,
        'useKashida' => 75,
    ];
}

// ===============================
// 🔹 HTML কনটেন্ট
// ===============================
$html = <<<HTML
<style>
body { font-family: solaimanlipi, sans-serif; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #999; padding: 6px; text-align: center; }
th { background-color: #f2f2f2; }
h2 { text-align: center; margin-bottom: 5px; }
.summary td { text-align: right; }
</style>

<h2>ইনভয়েস #{$invoice['id']}</h2>
<p><strong>প্রতিষ্ঠান:</strong> {$invoice['scname']}<br>
<strong>তারিখ:</strong> {$invoice['invoice_date']}</p>

<table>
<thead>
<tr>
<th>ক্রম</th>
<th>বিবরণ</th>
<th>পরিমাণ</th>
<th>দর</th>
<th>মোট</th>
</tr>
</thead>
<tbody>
HTML;

$i = 1;
foreach ($items as $it) {
    $html .= "<tr>
        <td>{$i}</td>
        <td>{$it['item_name']}</td>
        <td>{$it['quantity']}</td>
        <td>{$it['rate']}</td>
        <td>{$it['total']}</td>
    </tr>";
    $i++;
}

$html .= <<<HTML
</tbody>
</table>

<br>
<table class="summary">
<tr><td><strong>সাবটোটাল:</strong></td><td>{$invoice['total_amount']}</td></tr>
<tr><td><strong>ডিসকাউন্ট:</strong></td><td>{$invoice['discount']}</td></tr>
<tr><td><strong>মোট টোটাল:</strong></td><td>{$invoice['grand_total']}</td></tr>
<tr><td><strong>পরিশোধিত:</strong></td><td>{$invoice['paid_amount']}</td></tr>
<tr><td><strong>বাকি:</strong></td><td>{$invoice['due_amount']}</td></tr>
</table>
<br><br>
<p style="text-align:center;">এই ইনভয়েসটি স্বয়ংক্রিয়ভাবে তৈরি করা হয়েছে - EIMBox Billing System</p>

HTML;



// $html = "<p><b>OK </b></p>Man may come";
// ===============================
// 🔹 Output (প্রিন্ট সহ)
// ===============================
$mpdf->WriteHTML($html);
$mpdf->SetJS('this.print();'); // ব্রাউজারে লোড হবার পর সরাসরি প্রিন্ট
$mpdf->Output("Invoice_{$invoice['id']}.pdf", "I"); // ব্রাউজারে দেখাবে
