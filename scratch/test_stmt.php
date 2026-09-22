<?php
$_POST['filter_basis'] = 'month_year';
$_POST['month_param'] = '2026-09';
$_POST['date_from'] = '2026-09-01';
$_POST['date_to'] = '2026-09-30';
$_POST['slot'] = '';
$_POST['session'] = '';
$_POST['status_filter'] = '1';

chdir('finance');
ob_start();
include 'get-financial-statement.php';
$output = ob_get_clean();

echo "OUTPUT LENGTH: " . strlen($output) . "\n";
echo "CONTAINS COA: " . (strpos($output, 'Comprehensive Financial Statement') !== false ? 'YES' : 'NO') . "\n";
if (strpos($output, 'Fatal error') !== false || strpos($output, 'Warning') !== false || strpos($output, 'Notice') !== false) {
    echo "ERRORS FOUND IN OUTPUT:\n";
    echo $output;
} else {
    // Print snippet
    echo "SNIPPET:\n" . substr(strip_tags($output), 0, 1000);
}
