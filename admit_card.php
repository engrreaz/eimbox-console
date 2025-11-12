<?php
session_start();
include('core/config.php');
include('core/db.php');
include('core/core-val.php');
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

$sccode = $data['sccode'];
include_once 'actions/get-sc-data.php';

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


$html = '<table class="mt-4 mb-6 " style="margin:auto;">
                    <tr>
                      
                          <td><img src="' . dirname(__DIR__) . '/logo/' . $sccode . '.png"
                                style="max-width:50px; max-height:50px;" />
                        </td>
                        <td style="width:15px; border-right:5px solid gray;"></td>
                        <td style="width:15px;"></td>
                        <td>
                            <h3 class="m-0 p-0 fw-bold">' . $scname . '</h3>
                            <div class="m-0 p-0">' . $address . '</div>

                        </td>

                    </tr>
                </table>';

// ✅ HTML content -------------------------------------------------------------------------------------------------------
// ✅ HTML content -------------------------------------------------------------------------------------------------------
// ✅ HTML content -------------------------------------------------------------------------------------------------------
// ✅ HTML content -------------------------------------------------------------------------------------------------------
// ✅ HTML content -------------------------------------------------------------------------------------------------------
// ✅ HTML content -------------------------------------------------------------------------------------------------------
// ✅ HTML content -------------------------------------------------------------------------------------------------------
// ✅ HTML content -------------------------------------------------------------------------------------------------------
$html .= '
<div style="font-family:solaimanlipi; font-size:14px; line-height:1.6;">
    <h2 style="text-align:center;">ভর্তির আবেদনপত্র (Application Form)</h2>
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td  colspan="7">
               <div style="font-size: 16px; font-weight: bold; padding-bottom: 10px;"> Information of Applicant\'s / আবেদনকারীর তথ্যাবলী </div>
               <hr>
            </td>
</tr>
<tr>
<td width="75%" colspan="4"></td>
<td  align="center" rowspan="16" style="width:5px; border-right:1px dotted gray;"></td>
<td  align="center" rowspan="16" style="width:5px;"></td>

            <td  align="center" rowspan="16" style="padding:10px;">
';

// ✅ Add image if available
if (!empty($data['photo']) && file_exists(__DIR__ . '/uploads/photos/' . $data['photo'])) {
    $html .= '<img src="' . htmlspecialchars('uploads/photos/' . $data['photo']) . '" width="100" height="127" style="border:1px solid #6e6a6aff;">';
} else {
    $html .= '<div style="width:150px;height:190px;border:1px solid #dd4b4bff;text-align:center;line-height:140px;">No Photo</div>';
}


$html .= '


<br><br><br><br><br><br>
<div style="font-size: 11px;">
 Signature
<hr style="margin:0 5px;">
শিক্ষার্থীর স্বাক্ষর
</div>


<br><br><br><br>
<div style="font-size: 11px;">
 Guardian\'s Signature
<hr style="margin:0 5px;">
অভিভাবকের স্বাক্ষর
</div>


            </td>
        </tr>


        <tr>
            <td> Registration No. / রেজিস্ট্রেশন নম্বর :</td>
            <td style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['reg_id']) . '</td>
            <td> Roll No. / ক্রমিক নম্বর :</td>
            <td style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['roll_no']) . '</td>
        </tr>

        <tr>
            <td> Session. / শিক্ষাবর্ষ :</td>
            <td style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['sessionyear']) . '</td>
            <td> Class / ভর্তিচ্ছু শ্রেণি :</td>
            <td style="border-bottom:1px dotted gray; padding-left:10px;">' . htmlspecialchars($data['admit_class']) . '</td>
        </tr>
        <tr><td style="height:35px;" colspan="4" ></td></tr>




<tr>
<td>Name of Student :</td><td> (In English)  </td>
<td colspan="2" style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['stnameeng']) . '</td>
</tr>

<tr>
    <td>শিক্ষার্থীর নাম</td><td> (বাংলায়)</td>
    <td colspan="2"  style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['stnameben']) . '</td>
</tr>

<tr>
    <td colspan="2">Father\'s Name / পিতার নাম</td>
    <td colspan="2"  style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['fname']) . '</td>
</tr>
<tr>
    <td colspan="2">Mother\'s Name / মাতার নাম</td>
    <td colspan="2"  style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['mname']) . '</td>
</tr>

<tr>
    <td colspan="4" ><h4>Address / ঠিকানা </h4></td>
    
</tr>


        <tr>
            <td>Village / গ্রাম :</td>
            <td style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['village']) . '</td>
            <td>Post Office / ডাকঘর :</td>
            <td style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['po']) . '</td>
        </tr>
        <tr>
            <td>PS / উপজেলা :</td>
            <td style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['ps']) . '</td>
            <td>District / জেলা :</td>
            <td style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['dist']) . '</td>
        </tr>
   

<tr>
    <td colspan="2" >Mobile Number / মোবাইল নম্বর : </td>
                <td colspan="2" style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['mnumber']) . '</td>

</tr>

<tr>
    <td colspan="4" ><h4>Previous Institution / পূর্ববর্তী প্রতিষ্ঠানের তথ্য : </h4></td>
                

</tr>

<tr>
    <td colspan="2" > Name of Institution / প্রতিষ্ঠানের নাম : </td>
                <td colspan="2" style="border-bottom:1px dotted gray;"><b>' . htmlspecialchars($data['insname']) . '</b></td>
</tr>
<tr>
    <td colspan="2" > Address / ঠিকানা : </td>
                <td colspan="2" style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['inspo']) . ', ' . htmlspecialchars($data['insps']) . ', ' . htmlspecialchars($data['insdist']) . '</td>
</tr>
<tr>
    <td colspan="2" > Date of Application / আবেদনের তারিখ : </td>
                <td colspan="2" style="border-bottom:1px dotted gray;">' . date('d F, Y', strtotime(htmlspecialchars($data['created_at']))) . '</td>
</tr>







    </table>
    <br><hr>
</div>
';





// ✅ HTML content


$html .= '<table class="mt-4 mb-6 " style="margin:auto;">
                    <tr>
                      
                          <td><img src="' . dirname(__DIR__) . '/logo/' . $sccode . '.png"
                                style="max-width:50px; max-height:50px;" />
                        </td>
                        <td style="width:15px; border-right:5px solid gray;"></td>
                        <td style="width:15px;"></td>
                        <td>
                            <h3 class="m-0 p-0 fw-bold">' . $scname . '</h3>
                            <div class="m-0 p-0">' . $address . '</div>

                        </td>

                    </tr>
                </table>

                
                ';


$html .= '
<div style="font-family:solaimanlipi; font-size:14px; line-height:1;">
    <h2 style="text-align:center;">ভর্তির প্রবেশপত্র (Admit Card for Admission Test)</h2>
                        <p style="text-align:center; margin:0; padding:0;">এই প্রবেশপত্রটি পরীক্ষা বা সাক্ষাৎকারে অংশগ্রহণের জন্য প্রযোজ্য।</p>

    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td  colspan="7">
          
            </td>
</tr>
<tr>
<td width="75%" colspan="4"></td>
<td  align="center" rowspan="11" style="width:5px; border-right:1px dotted gray;"></td>
<td  align="center" rowspan="11" style="width:5px;"></td>

            <td  align="center" rowspan="11" style="padding:10px;">
';

// ✅ Add image if available
if (!empty($data['photo']) && file_exists(__DIR__ . '/uploads/photos/' . $data['photo'])) {
    $html .= '<img src="' . htmlspecialchars('uploads/photos/' . $data['photo']) . '" width="100" height="127" style="border:1px solid #6e6a6aff;">';
} else {
    $html .= '<div style="width:150px;height:190px;border:1px solid #dd4b4bff;text-align:center;line-height:140px;">No Photo</div>';
}


$html .= '


<br><br><br><br>
<img src="'. dirname(__DIR__) . '/sign/' . $sccode . '.png' .'" style="width:100px;"/>
<div style="font-size: 11px;">
প্রতিষ্ঠান প্রধান
</div>




            </td>
        </tr>


        <tr>
            <td style="width:100px;"> Registration No. / রেজিস্ট্রেশন নম্বর :</td>
            <td style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['reg_id']) . '</td>
            <td> Roll No. / ক্রমিক নম্বর :</td>
            <td style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['roll_no']) . '</td>
        </tr>

        <tr>
            <td> Session. / শিক্ষাবর্ষ :</td>
            <td style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['sessionyear']) . '</td>
            <td> Class / ভর্তিচ্ছু শ্রেণি :</td>
            <td style="border-bottom:1px dotted gray; padding-left:10px;">' . htmlspecialchars($data['admit_class']) . '</td>
        </tr>
        <tr><td style="height:35px;" colspan="4" ></td></tr>




<tr>
<td>Name of Student :</td><td> (In English)  </td>
<td colspan="2" style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['stnameeng']) . '</td>
</tr>

<tr>
    <td>শিক্ষার্থীর নাম</td><td> (বাংলায়)</td>
    <td colspan="2"  style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['stnameben']) . '</td>
</tr>

<tr>
    <td colspan="2">Father\'s Name / পিতার নাম</td>
    <td colspan="2"  style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['fname']) . '</td>
</tr>
<tr>
    <td colspan="2">Mother\'s Name / মাতার নাম</td>
    <td colspan="2"  style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['mname']) . '</td>
</tr>

<tr>
    <td colspan="4" >Address / ঠিকানা </td>
            

    
</tr>
<tr>
    <td colspan="4"  style="border-bottom:1px dotted gray;" >' . 
            htmlspecialchars($data['village']) . ', ' .
            htmlspecialchars($data['po']) . ', ' .
            htmlspecialchars($data['ps']) . ', ' .
            htmlspecialchars($data['dist']) . '. ' .
            
            
            '</td>

    
</tr>


       
   

<tr>
    <td colspan="2" >Mobile Number / মোবাইল নম্বর : </td>
                <td colspan="2" style="border-bottom:1px dotted gray;">' . htmlspecialchars($data['mnumber']) . '</td>

</tr>







    </table>

</div>
';



// ✅ Output PDF
$mpdf->WriteHTML($html);
$mpdf->Output('entry_ticket_' . $data['id'] . '.pdf', 'I');