<?php
$_GET['filter_basis'] = 'month_year';
$_GET['month_param'] = '2026-09';
$_GET['date_from'] = '2026-09-01';
$_GET['date_to'] = '2026-09-30';
$_GET['slot'] = '';
$_GET['session'] = '';
$_GET['status_filter'] = '1';

chdir('pdf');
ob_start();
include 'financial-statement-pdf.php';
$pdf = ob_get_clean();

echo "PDF OUTPUT LENGTH: " . strlen($pdf) . "\n";
echo "IS PDF: " . (substr($pdf, 0, 4) === '%PDF' ? 'YES' : 'NO') . "\n";
