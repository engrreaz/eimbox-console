<?php
require_once __DIR__ . '/../vendor/autoload.php'; // mPDF পাথ নিশ্চিত করুন
require_once '../db_config.php'; // ডাটাবেজ কানেকশন

// ১. প্যারামিটার গ্রহণ
$cls = $_GET['cls'] ?? '';
$sec = $_GET['sec'] ?? '';
$dateFrom = $_GET['dateFrom'] ?? date('Y-m-01');
$dateTo = $_GET['dateTo'] ?? date('Y-m-t');

// ২. mPDF কনফিগারেশন (Bangla Support & Landscape)
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L', // Landscape মোড
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 15,
    'margin_bottom' => 20,
    'autoScriptToLang' => true,
    'autoLangToFont' => true,
]);

// ৩. ডাটা প্রসেসিং (লজিক আগের মতোই)
// এখানে স্টুডেন্ট লিস্ট এবং এটেনডেন্স ম্যাপ তৈরি করে নিতে হবে...

// ৪. HTML কন্টেন্ট জেনারেশন
$html = '
<style>
    body { font-family: "nikosh", sans-serif; }
    .letterhead { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
    .school-name { font-size: 24px; font-weight: bold; margin-bottom: 5px; color: #1a237e; }
    .school-address { font-size: 14px; color: #555; }
    
    .report-title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 15px; background: #f0f0f0; padding: 5px; }
    
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #444; padding: 4px; text-align: center; font-size: 11px; }
    th { background-color: #f5f5f5; font-weight: bold; }
    .student-name { text-align: left; padding-left: 8px; font-weight: bold; width: 180px; }
    
    .present { color: #2e7d32; font-weight: bold; }
    .absent { color: #c62828; font-weight: bold; }
    
    /* সিগনেচার সেকশন */
    .signature-area { margin-top: 50px; width: 100%; }
    .sig-box { width: 30%; text-align: center; border-top: 1px solid #000; padding-top: 5px; }
</style>

<div class="letterhead">
    <div class="school-name">আপনার প্রতিষ্ঠানের নাম এখানে</div>
    <div class="school-address">গ্রাম, ডাকঘর, থানা, জেলা - ১২৩৪ | মোবাইল: ০১৯১৯xxxxxx</div>
</div>

<div class="report-title">Monthly Attendance Register: '.$cls.' (Section: '.$sec.')</div>
<p style="text-align:center; font-size:12px;">Reporting Period: <b>'.$dateFrom.'</b> to <b>'.$dateTo.'</b></p>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:40px;">Roll</th>
            <th rowspan="2">Student Name</th>
            <th colspan="31">Dates</th> </tr>
        <tr>';
            // তারিখের হেডার লুপ (উদাহরন)
            for($d=1; $d<=15; $d++) { $html .= '<th>'.$d.'</th>'; }
$html .= '
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>১০১</td>
            <td class="student-name">মোঃ সাইদুর রহমান (বাংলা নাম)</td>';
            // উপস্থিতির লুপ (উদাহরন)
            for($d=1; $d<=15; $d++) { 
                $status = ($d % 5 == 0) ? "A" : "P"; // ডামি ডাটা
                $class = ($status == "A") ? "absent" : "present";
                $html .= '<td class="'.$class.'">'.$status.'</td>'; 
            }
$html .= '
        </tr>
    </tbody>
</table>

<table class="signature-area" style="border:none;">
    <tr style="border:none;">
        <td class="sig-box" style="border:none; width:33%;">
            <p>Class Teacher Signature</p>
        </td>
        <td style="border:none; width:34%;"></td> <td class="sig-box" style="border:none; width:33%;">
            <p>Principal Signature & Seal</p>
        </td>
    </tr>
</table>

<div style="font-size: 9px; margin-top: 20px; color: #999;">
    Report Generated on: '.date("d-m-Y H:i A").' | System by EIMBox
</div>';

// ৫. আউটপুট
$mpdf->WriteHTML($html);
$mpdf->Output('Attendance_Report_'.$tid.'.pdf', 'I');