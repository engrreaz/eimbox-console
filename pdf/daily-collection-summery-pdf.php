<?php
session_start();
require_once dirname(__DIR__) . '/vendor/autoload.php'; // mpdf autoload
include_once dirname(__DIR__) . '/core/init.php';
include_once dirname(__DIR__) . '/format/letter-head.php'; // Letter Head include

$dfrom = $_GET['dfrom'] ?? date('Y-m-d');
$dto = $_GET['dto'] ?? date('Y-m-d');

// Bangla Font (SutonnyOMJ)
$mpdf = new \Mpdf\Mpdf([
    'default_font' => 'SutonnyOMJ',
    'mode' => 'utf-8'
]);

// ক্যাপচার HTML
ob_start();
include "daily-collection-summery-print-view.php";  // শুধুমাত্র print-block অংশ
$html = ob_get_clean();

$mpdf->WriteHTML($html);
$mpdf->Output("Daily_Collection_Summary.pdf", "I");
