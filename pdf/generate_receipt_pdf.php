<?php
// generate_receipt_pdf.php
session_start();
require_once dirname(__DIR__) . '/vendor/autoload.php'; // ensure path to composer autoload
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';
require_once dirname(__DIR__) . '/core/functions.php';

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;


// require_once dirname(__DIR__) . '/vendor/autoload.php'; // mpdf autoload
// include_once dirname(__DIR__) . '/core/init.php';



// get POST data and sanitize
$stid = $_POST['stid'] ?? '';
$syear = $_POST['syear'] ?? '';
$stnameeng = $_POST['stnameeng'] ?? '';
$classname = $_POST['classname'] ?? '';
$sectionname = $_POST['sectionname'] ?? '';
$rollno = $_POST['rollno'] ?? '';
$amount = $_POST['amount'] ?? '0.00';
$td = $_POST['td'] ?? date('Y-m-d');
$prno = $_POST['prno'] ?? '';
$trxID = $_POST['trx'] ?? '';
$verify_url = $_POST['verify_url'] ?? '';

// $prno = 25000601;
// $stid = 1031870006;
// $syear = 2025;

$stid = htmlspecialchars($stid, ENT_QUOTES, 'UTF-8');
$stnameeng = htmlspecialchars($stnameeng, ENT_QUOTES, 'UTF-8');
$classname = htmlspecialchars($classname, ENT_QUOTES, 'UTF-8');
$sectionname = htmlspecialchars($sectionname, ENT_QUOTES, 'UTF-8');
$rollno = htmlspecialchars($rollno, ENT_QUOTES, 'UTF-8');
$amount = number_format((float) $amount, 2, '.', ',');
$trxID = htmlspecialchars($trxID, ENT_QUOTES, 'UTF-8');
$prno = htmlspecialchars($prno, ENT_QUOTES, 'UTF-8');
$td = htmlspecialchars($td, ENT_QUOTES, 'UTF-8');
$verify_url = trim($verify_url);

// Prepare QR image as base64 (fetch remote QR and embed)
$qr_base64 = '';
if (!empty($verify_url)) {
    $qr_api = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($verify_url);
    $qr_raw = @file_get_contents($qr_api);
    if ($qr_raw !== false) {
        $qr_base64 = 'data:image/png;base64,' . base64_encode($qr_raw);
    }
}


$prData = null;
$getpr = $conn->query("SELECT * from stpr where sccode = '$sccode' AND stid='$stid' AND prno = '$prno' and sessionyear='$syear' order by id DESC LIMIT 1");
if ($getpr && $getpr->num_rows > 0) {
    $prData = $getpr->fetch_assoc();
}

$items = [];
$getitems = $conn->query("SELECT * from stfinance where sccode = '$sccode' AND stid='$stid' AND pr1no = '$prno'  and sessionyear='$syear'  order by id");
if ($getitems && $getitems->num_rows > 0) {
    while ($row = $getitems->fetch_assoc()) {
        $items[] = $row;
    }
}

$itemlist = '';
foreach ($items as $item) {
    $itemlist .= "<tr>
            <td>" . $item['particulareng'] . "</td>
            <td>" . $item['particularben'] . "</td>
            <td style='text-align:right;'>" . number_format($item['pr1'], 2) . "</td>
        </tr>";
}

$amtwrd = taka($prData['amount']);

// Build HTML (A4 friendly)
$html = '
<html>
<head><meta charset="utf-8"><style>
body{font-family: solaimanlipi, DejaVu Sans, Arial, Helvetica, sans-serif; color:#222;}
.container{padding:24px; width:100%;}
.header {text-align:center; margin-bottom:8px;}
.table {width:100%; border-collapse:collapse; font-size:12pt;}
.table td{padding:6px; vertical-align:top;}
.right{ text-align:right; }
.small{font-size:10pt; color:#666;}
#item-table {border-collapse:collapse;}
#item-table td, th{border:1px solid gray; padding:3px 8px; }
</style></head>
<body>
<div class="container">
';

// --- letter-head-01.php output capture ---
ob_start();
include dirname(__DIR__) . '/templete/letter-head-01.php';
$letterHeadHTML = ob_get_clean();

$html .= $letterHeadHTML;
// ----------------------------------------
$html .= '<div class="header">
        <h2>Payment Receipt</h2>
        <div class="small">Receipt generated: ' . date('Y-m-d H:i:s') . '</div>
    </div>

    <table class="table">
        <tr>
            <td><strong>Student ID:</strong> ' . $stid . '</td>
            <td class="right"><strong>Receipt No:</strong> ' . $prno . '</td>
        </tr>
        <tr>
            <td><strong>Name:</strong> ' . $stnameeng . '</td>
            <td class="right"><strong>Date:</strong> ' . $td . '</td>
        </tr>
        <tr>
            <td><strong>Class:</strong> ' . $classname . '</td>
            <td class="right"><strong>Payment Through :</strong> bKash </td>
        </tr>
        <tr>
            <td><strong>Section:</strong> ' . $sectionname . '</td>
            <td class="right"><strong>Roll:</strong> ' . $rollno . '</td>
        </tr>
        <tr>
            <td><strong>Roll No:</strong> ' . $rollno . '</td>
            <td class="right"><strong>Trnx. No.:</strong> ' . $trxID . '</td>
        </tr>
    </table>

    <hr>

    <table id="item-table" style="width:100%; border:1px solid gray;">
        <tr>
            <th style="text-align:left" colspan="2"> Particulars </th>
            <th style="text-align:right;"> Amount </th>
        </tr>
';
$html .= $itemlist;
$html .= '
       <tr>
    <td colspan="2" style="text-align:right;"><strong>Total Amount :</strong></td>
    <td style="text-align:right;"><strong>' . number_format($prData['amount'], 2) . '</strong></td>
</tr>
    </table>

';
$html .= 'Total amount in word : ' .  $amtwrd . ' TAka only.';

$html .= '<div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
        <div style="max-width:60%;">
            <p style="font-size:11pt; margin:0;">This is a computer generated receipt. Verify at: <br><a href="' . htmlspecialchars($verify_url) . '">' . htmlspecialchars($verify_url) . '</a></p>
        </div>
        <div style="text-align:center;">
';


$html .= '<div style="text-align:left;"><img style="padding: 5px; "  src="https://quickchart.io/qr?text=' .  $verify_url . '&size=120" /></div>';
$html .= '
        </div>
    </div>

</div>
</body>
</html>
';

// generate PDF with mpdf
$defaultConfig = (new ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];


$mpdfConfig = [
    'fontDir' => array_merge($fontDirs, [dirname(__DIR__) . '/fonts']),
    'fontdata' => $fontData + [
        'solaimanlipi' => [
            'R' => 'SolaimanLipi.ttf',
            'useOTL' => 0xFF,
            'useKashida' => 75
        ]
    ],
    'default_font' => 'solaimanlipi',
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 16,
    'margin_bottom' => 16,
];

$mpdf = new Mpdf($mpdfConfig);
$mpdf->SetTitle('Payment Receipt - ' . $stid);
$mpdf->WriteHTML($html);

// Output to browser (inline, user can save as PDF)
$filename = 'receipt_' . $stid . '_' . date('Ymd_His') . '.pdf';
$mpdf->Output($filename, 'I'); // 'I' = inline, 'D' = download, 'F' = file
exit;
