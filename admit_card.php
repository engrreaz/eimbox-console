<?php
session_start();
include('core/config.php');
include('core/db.php');
// include('header-plain.php');

// ✅ mPDF load
require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;


$reg = $_GET['id'] ?? '';
if (!$reg)
    die('Missing reg id');

$stmt = $conn->prepare("SELECT * FROM registrations WHERE id = ? LIMIT 1");
$stmt->bind_param("s", $reg);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$stmt->close();

if (!$data)
    die('Data not found');

// ✅ PDF settings
// mPDF font setup
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




$mpdf->SetTitle('Entry Ticket - ' . $data['id']);

// ✅ HTML content
$html = '
<div style="font-family:solaimanlipi; font-size:14px; line-height:1.6;">
    <h2 style="text-align:center;">ভর্তির প্রবেশপত্র (Entry Ticket)</h2>
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td width="60%">
                <b>রেজিস্ট্রেশন আইডি:</b> ' . htmlspecialchars($data['reg_id']) . '<br>
                <b>নাম:</b> ' . htmlspecialchars($data['stnameeng']) . '<br>
                <b>PIN:</b> ' . htmlspecialchars($data['pin']) . '<br>
                <b>প্রতিষ্ঠান:</b> ' . htmlspecialchars($data['insname']) . '<br>
                <b>সেশন:</b> ' . htmlspecialchars($data['sessionyear']) . '<br>
                <b>স্কুল কোড:</b> ' . htmlspecialchars($data['sccode']) . '<br>
            </td>
            <td width="40%" align="right">
';

// ✅ Add image if available
if (!empty($data['photo']) && file_exists(__DIR__ . '/' . $data['photo'])) {
    $html .= '<img src="' . htmlspecialchars($data['photo']) . '" width="100" height="127" style="border:1px solid #6e6a6aff;">';
} else {
    $html .= '<div style="width:150px;height:190px;border:1px solid #dd4b4bff;text-align:center;line-height:140px;">No Photo</div>';
}


$html .= '
            </td>
        </tr>
    </table>
    <br><hr>
    <p style="text-align:center;">এই প্রবেশপত্রটি পরীক্ষা বা সাক্ষাৎকারে অংশগ্রহণের জন্য প্রযোজ্য।</p>
</div>
';

// ✅ Output PDF
$mpdf->WriteHTML($html);
$mpdf->Output('entry_ticket_' . $data['id'] . '.pdf', 'I');