<?php
session_start();
require_once '../core/config.php'; 
require_once '../core/db.php'; 
require_once '../core/global_values.php'; 
require_once dirname(__DIR__) . '/vendor/autoload.php';


$month = $_GET['month'];
$year = $_GET['year'];
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);

// ডাটা ফেচিং... (একই লজিক)
// ১. শিক্ষক ডাটা
$teachers = [];
$t_res = $conn->query("SELECT tid, tname, position FROM teacher WHERE sccode = '$sccode' ORDER BY sl ASC");
while($row = $t_res->fetch_assoc()) { $teachers[$row['tid']] = $row; }

// ২. হাজিরা ডাটা (In/Out Time সহ)
$attnd_data = [];
$a_res = $conn->query("SELECT tid, adate, realin, realout, statusin FROM teacherattnd 
                      WHERE sccode = '$sccode' AND MONTH(adate) = '$month' AND YEAR(adate) = '$year'");
while($row = $a_res->fetch_assoc()) {
    $attnd_data[$row['tid']][$row['adate']] = $row;
}


$html = '
<style>
    body { font-family: "nikosh", sans-serif; }
    .letterhead { text-align: center; border-bottom: 2px solid #333; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #444; text-align: center; padding: 2px; }
    .t-info { text-align: left; font-size: 9px; width: 120px; }
    .time-box { font-size: 7px; }
    .in { color: green; border-bottom: 0.1pt solid #ccc; }
    .out { color: blue; }
    .absent { color: red; font-weight: bold; }
    .weekend { background-color: #f0f0f0; }
</style>

<div class="letterhead">
    <h2>EIMBox School & College</h2>
    <p>Monthly Teacher Attendance & Time Log: '.date('F, Y', mktime(0,0,0,$month,1)).'</p>
</div>

<table>
    <thead>
        <tr style="background:#eee;">
            <th>Teacher Name</th>';
            for($d=1; $d<=$days_in_month; $d++) { $html .= '<th>'.$d.'</th>'; }
$html .= '</tr>
    </thead>
    <tbody>';

foreach($teachers as $tid => $t) {
    $html .= '<tr>
                <td class="t-info"><b>'.$t['tname'].'</b><br>'.$t['position'].'</td>';
    for($d=1; $d<=$days_in_month; $d++) {
        $date_str = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
        $is_weekend = (date('N', strtotime($date_str)) == 5);
        $data = $attnd_data[$tid][$date_str] ?? null;
        
        $cell = '';
        $style = $is_weekend ? 'class="weekend"' : '';
        
        if($is_weekend) { $cell = "W"; }
        elseif($data) {
            $in = date('h:i', strtotime($data['realin']));
            $out = $data['realout'] ? date('h:i', strtotime($data['realout'])) : '--';
            $cell = '<div class="time-box"><div class="in">'.$in.'</div><div class="out">'.$out.'</div></div>';
        } elseif($date_str <= date('Y-m-d')) {
            $cell = '<span class="absent">A</span>';
        }
        
        $html .= '<td '.$style.'>'.$cell.'</td>';
    }
    $html .= '</tr>';
}

$html .= '</tbody></table>';

$mpdf->WriteHTML($html);
$mpdf->Output('Teacher_Time_Log.pdf', 'I');