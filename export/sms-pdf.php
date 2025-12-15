<?php 
require_once "../vendor/autoload.php";
require_once "../core/config.php";
require_once "../core/db.php";
require_once "../core/global_values.php";

$mpdf = new \Mpdf\Mpdf();

$html = "<h3>SMS Log</h3><table border='1' width='100%'>";
$q=mysqli_query($conn,"SELECT * FROM sms WHERE sccode='$sccode'");
while($r=mysqli_fetch_assoc($q)){
 $html.="<tr>
 <td>{$r['date']}</td>
 <td>{$r['mobile_number']}</td>
 <td>{$r['sms_text']}</td>
 </tr>";
}
$html.="</table>";

$mpdf->WriteHTML($html);
$mpdf->Output();
