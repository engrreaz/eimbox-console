<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';



$dtf = $_GET['dfrom'] ?? date('Y-m-d');
$dtt = $_GET['dto'] ?? date('Y-m-d');
$y_v2 = $_GET['sy'] ?? $y_v2;

// ðŸ”¥ SAME DATA LOAD (copy from main page)
include_once dirname(__DIR__) . '/daily-collection-data.php'; 

$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '/../fonts',
    ]),
    'fontdata' => $fontData + [
        'solaimanlipi' => [
            'R' => 'SolaimanLipi.ttf',
        ]
    ],
    'default_font' => 'solaimanlipi'
]);
ob_start();

?>

<style>
    body { font-family: solaimanlipi, Arial; }
    table { border-collapse: collapse; width: 100%; }
    td { border: 1px solid #000; padding: 5px; }
</style>

<?php include dirname(__DIR__) . '/templete/letter-head-01.php'; ?>

<h3 style="text-align:center;">Daily Collection Report</h3>
<p>Date: <?= $dtf ?> to <?= $dtt ?></p>

<?php include dirname(__DIR__) . '/daily-collection-template.php'; ?>

<?php include dirname(__DIR__) . '/templete/letter-tail-01.php'; ?>

<?php
$fname = "Daily_Collection_Summary_" .$dtf . "_" . $dtt . ".pdf";
$html = ob_get_clean();

$mpdf->WriteHTML($html);
$mpdf->Output($fname, "D");