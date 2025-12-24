<?php
session_start();
require_once dirname(dirname(dirname(__FILE__))) . '/core/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/core/db.php';
require_once dirname(dirname(dirname(__FILE__))) . '/core/global_values.php';

require_once dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';


use Mpdf\Mpdf;

$sql = "SELECT * FROM tabulatingsheet WHERE stid='$stid' ORDER BY id DESC LIMIT 1";
$row = $conn->query($sql)->fetch_assoc();

$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P'
]);

ob_start();
include 'print-marksheet.php';
$html = ob_get_clean();

$mpdf->WriteHTML($html);
$mpdf->Output('marksheet.pdf', 'D');