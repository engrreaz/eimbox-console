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
    'format' => 'A4',
    'orientation' => 'P'
]);

$html = '
<h3 style="text-align:center;">Progress Report</h3>
<table border="1" width="100%" cellpadding="8">
<tr><th>GPA</th><td>'.$row['gpa'].'</td></tr>
<tr><th>Grade</th><td>'.$row['gla'].'</td></tr>
<tr><th>Total Marks</th><td>'.$row['totalmarks'].'</td></tr>
</table>
';

$mpdf->WriteHTML($html);
$mpdf->Output('marksheet.pdf', 'D');