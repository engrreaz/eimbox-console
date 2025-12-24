<?php
require 'marksheet-data.php';

require dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';
use Mpdf\Mpdf;

$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'default_font' => 'dejavusans'
]);

ob_start();
include 'marksheet-view.php';
$html = ob_get_clean();

$mpdf->WriteHTML($html);
$mpdf->Output('marksheet.pdf', 'D');
$mpdf->Output('marksheet.pdf', 'I');
