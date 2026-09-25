<?php
include_once 'header.php';

$preview = $_GET['preview'] ?? '';

if($preview != ''){
    $tail = ' LIMIT 1';
}


$q = mysqli_query($conn, "SELECT admin_data FROM scinfo WHERE sccode='$sccode' LIMIT 1");
$row = mysqli_fetch_assoc($q);

$settings = [];
if (!empty($row['admin_data'])) {
    $settings = json_decode($row['admin_data'], true);
    if (!is_array($settings))
        $settings = [];
}


$progress_report_head = $settings['report_header'];
$progress_report_background = $settings['report_background'];
$progress_report_text = $settings['report_text_image'];
$progress_report_wartermark = $settings['watermark'];
$progress_report_header_scale = $settings['header_scale'];


$progress_report_head            = $settings['report_header']        ?? '';
$progress_report_background      = $settings['report_background']    ?? '';
$progress_report_text            = $settings['report_text_image']    ?? '';
$progress_report_wartermark      = $settings['watermark']            ?? '0';
$progress_report_header_scale    = $settings['header_scale']         ?? '1.00';

$progress_report_grading_system  = $settings['grading_system']       ?? '0';
$progress_report_student_photo   = $settings['student_photo']        ?? '0';
$progress_report_student_name_en = $settings['student_name_en']      ?? '0';
$progress_report_student_name_bn = $settings['student_name_bn']      ?? '0';
$progress_report_parents_info    = $settings['parents_info']         ?? '0';
$progress_report_attendance      = $settings['attendance_info']      ?? '0';
$progress_report_highest_mark    = $settings['highest_mark']         ?? '0';
$progress_report_annotate        = $settings['annotate']             ?? '0';
$progress_report_publish_date    = $settings['publish_date']         ?? '0';
$progress_report_guardian_sign   = $settings['guardian_signature']   ?? '0';
$progress_report_qr_code         = $settings['qr_code']              ?? '0';
$progress_report_class_teacher   = $settings['class_teacher']        ?? '0';
$progress_report_head_signature  = $settings['head_signature']       ?? '0';

$progress_report_parents_style   = $settings['parents_text_style']   ?? 'son_daughter';
$progress_report_result_position = $settings['result_position']      ?? 'right';


/* =========================
   INPUTS
========================= */
function gv($k, $d = '')
{
    return $_GET[$k] ?? $_COOKIE[$k] ?? $d;
}

$slot = gv('slot', $_COOKIE['chain-slot'] ?? 'School');
$sessionyear = $_GET['session'] ?? gv('sy', $_COOKIE['chain-session'] ?? '');
$cn = $_GET['classname'] ?? gv('cls', $_COOKIE['chain-class'] ?? '');
$secname = $_GET['sectionname'] ?? gv('sec', $_COOKIE['chain-section'] ?? '');
$exam = $_GET['exam'] ?? gv('exam', $_COOKIE['chain-exam'] ?? '');
$stid = $_GET['stid'] ?? '';
$color = $_GET['clr'] ?? 0;

if (!$sessionyear || !$cn || !$exam) {
    echo "<script>location.href='result-report-manager.php'</script>";
    exit;
}

$bg = 'bg-01.jpg';
$etdt = date('Y-m-d H:i:s');

$ordinal = ['1st', '2nd', '3rd', '4th', 'Pre'];
$ordinal_sup = ['1<sup>st</sup>', '2<sup>nd</sup>', '3<sup>rd</sup>', '4<sup>th</sup>', 'Pre'];

/* =========================
   QUICK FETCH HELPERS
========================= */
function fetch_all($conn, $sql)
{
    $res = $conn->query($sql);
    $out = [];
    if ($res && $res->num_rows) {
        while ($r = $res->fetch_assoc())
            $out[] = $r;
    }
    return $out;
}
function fetch_row($conn, $sql)
{
    $res = $conn->query($sql);
    return ($res && $res->num_rows) ? $res->fetch_assoc() : [];
}
function map_by($rows, $key)
{
    $m = [];
    foreach ($rows as $r)
        $m[$r[$key]] = $r;
    return $m;
}

/* =========================
   HIGHEST MARKS
========================= */
$hmark = fetch_all($conn, "
    SELECT subject, MAX(markobt) kkk
    FROM stmark
    WHERE sccode='$sccode'
      AND sessionyear='$sessionyear'
      AND exam='$exam'
      AND classname='$cn'
    GROUP BY subject
");
$hmark_map = map_by($hmark, 'subject');

$hmarkex = fetch_all($conn, "
    SELECT sub_code_1, sub_code_2,
           MAX(sub_1_total) comb1,
           MAX(sub_2_total) comb2
    FROM tabulatingsheetex
    WHERE sccode='$sccode'
      AND sessionyear='$sessionyear'
      AND exam='$exam'
      AND classname='$cn'
    GROUP BY sub_code_1, sub_code_2
");

$hmarktot = fetch_row($conn, "
    SELECT MAX(totalmarks) m
    FROM tabulatingsheet
    WHERE exam='$exam'
      AND classname='$cn'
      AND sessionyear='$sessionyear'
      AND sccode='$sccode'
")['m'] ?? 0;

/* =========================
   SLOT INFO
========================= */
$slotinfo = fetch_row($conn, "
    SELECT * FROM slots
    WHERE sccode='$sccode' AND slotname='$slot'
");
$cus_report = $slotinfo['cus_report'] ?? '';
$engname = $slotinfo['trans_name_eng'] ?? 1;
$benname = $slotinfo['trans_name_ben'] ?? 1;
$parents = $slotinfo['parents'] ?? '';

// if ($cus_report) {
//     $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//     $url = str_replace('progress-preport', 'custom-preport/progress-preport-' . $cus_report, $url);
//     header("Location:$url");
//     exit;
// }

/* =========================
   SCHOOL INFO
========================= */
$einame = $scname;
$eiaddress = ($sccode == 105671) ? 'Homna, Cumilla' : $scadd1 . ', ' . $ps . ', ' . $dist;
$eicontact = $mobile;

/* =========================
   TEACHER INFO
========================= */
$area = fetch_row($conn, "
    SELECT classteacher
    FROM areas
    WHERE areaname='$cn'
      AND subarea='$secname'
      AND sessionyear LIKE '%$sessionyear%'
      AND user='$rootuser'
");
$ctid = $area['classteacher'] ?? 0;

$cteacher = fetch_row($conn, "
    SELECT tname FROM teacher
    WHERE sccode='$sccode' AND tid='$ctid'
")['tname'] ?? '';

$head = fetch_row($conn, "
    SELECT tname,position,tid
    FROM teacher
    WHERE sccode='$sccode'
      AND (position='Head Teacher' OR position='Principal')
");
$headname = $head['tname'] ?? '';
$headtitle = $head['position'] ?? '';
$htid = $head['tid'] ?? $sccode;

/* =========================
   USERS
========================= */

/* =========================
   TABULATION DATA
========================= */
$tsheet = fetch_all($conn, "
    SELECT * FROM tabulatingsheet
    WHERE exam='$exam'
      AND classname='$cn'
      AND sectionname='$secname'
      AND sessionyear='$sessionyear'
      AND slot='$slot'
      AND sccode='$sccode'
");
$tsheet_map = map_by($tsheet, 'stid');

$tsheetex = fetch_all($conn, "
    SELECT * FROM tabulatingsheetex
    WHERE exam='$exam'
      AND classname='$cn'
      AND sectionname='$secname'
      AND sessionyear='$sessionyear'
      AND slot='$slot'
      AND sccode='$sccode'
");
$tsheetex_map = map_by($tsheetex, 'stid');

/* =========================
   SUBJECT DATA
========================= */
$subjects = fetch_all($conn, "SELECT * FROM subjects WHERE (sccode='$sccode' OR sccode=0)");
$subjects_map = [];
foreach ($subjects as $sub) {
    if (!isset($subjects_map[$sub['subcode']]) || $sub['sccode'] == $sccode) {
        $subjects_map[$sub['subcode']] = $sub;
    }
}

$subsetup = fetch_all($conn, "
    SELECT * FROM subsetup
    WHERE sccode='$sccode'
      AND classname='$cn'
      AND sectionname='$secname'
      AND slot='$slot'
      AND sessionyear='$sessionyear'
");
$subsetup_map = map_by($subsetup, 'subject');

/* =========================
   RESULT DATE
========================= */
$rpubdt = fetch_row($conn, "
    SELECT result_publish
    FROM examlist
    WHERE examtitle='$exam'
      AND sccode='$sccode'
      AND sessionyear='$sessionyear'
      AND slot='$slot'
")['result_publish'] ?? $td;

/* =========================
   STUDENTS
========================= */
$tail = ($preview != '') ? ' LIMIT 1' : '';
$stCond = ($stid != '') ? " AND stid='$stid'" : '';

$students = fetch_all($conn, "
    SELECT * FROM sessioninfo
    WHERE sccode='$sccode'
      AND classname='$cn'
      AND sectionname='$secname'
      AND sessionyear='$sessionyear'
      AND slot='$slot'
      $stCond
    ORDER BY rollno $tail
");

if (!$students) {
    echo '<div style="padding:20px; text-align:center; font-size:16px;">No Student Found for the selected criteria.</div>';
    exit;
}

?>

<style>
    @page {
        size: A4 portrait;
        margin: 8mm 10mm;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    body {
        margin: 0;
        padding: 0;
        background: #f4f5f7;
        font-family: 'Inter', 'Noto Sans Bengali', sans-serif;
    }

    /* Screen Display: A4 Sheet Card */
    .paper {
        width: 210mm;
        min-height: 297mm;
        box-sizing: border-box;
        margin: 25px auto;
        padding: 8mm 10mm;
        border: 1px solid #d0d7de;
        border-radius: 6px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        page-break-after: always;
        page-break-inside: avoid;
        break-after: page;
        break-inside: avoid;
        background: #ffffff;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .logo-watermark {
        position: absolute;
        width: 70mm;
        height: 70mm;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        z-index: 1;
        opacity: 0.07;
        pointer-events: none;
    }

    /* Print View: Exactly 1 A4 Page Per Student */
    @media print {
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body * {
            visibility: hidden;
        }

        #print-box, #print-box * {
            visibility: visible;
        }

        #print-box {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .paper {
            width: 100% !important;
            height: 100vh !important;
            min-height: 100vh !important;
            max-height: 100vh !important;
            margin: 0 !important;
            padding: 6mm 10mm !important;
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            page-break-after: always !important;
            page-break-inside: avoid !important;
            break-after: page !important;
            break-inside: avoid !important;
            overflow: hidden !important;
        }

        .noprint, #print-tools, .layout-navbar, .layout-menu, .content-footer, header, footer {
            display: none !important;
        }
    }

    #print-tools {
        position: fixed;
        right: 20px;
        bottom: 20px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .2);
        padding: 10px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    #print-tools button {
        border: none;
        background: #2b7cff;
        color: #fff;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 18px;
    }

    #print-tools button:hover {
        background: #1a5edb;
    }

    #print-settings {
        display: none;
        margin-top: 6px;
        padding-top: 6px;
        border-top: 1px solid #ddd;
        font-size: 12px;
    }

    #print-settings label {
        display: block;
        margin-bottom: 6px;
    }
</style>

<?php

echo '<div id="print-box">';
foreach ($students as $i => $stu) {

    $stid = $stu['stid'];
    $rollno = $stu['rollno'];

    $stmark = $tsheet_map[$stid] ?? [];
    $stmarkex = $tsheetex_map[$stid] ?? [];

    if (!$stmark)
        continue;

    /* ===== student info ===== */


    $std = fetch_row($conn, "SELECT * FROM students WHERE stid='$stid'");
    $stnameeng = $std['stnameeng'] ?? '';
    $stnameben = $std['stnameben'] ?? '';
    $fname = $std['fname'] ?? '';
    $mname = $std['mname'] ?? '';
    $gender = $std['gender'] ?? '';
    $religion = $std['religion'] ?? '';

    $parent_line = ($parents == 'DOSO')
        ? (($gender == 'Boy' ? 'S/O' : 'D/O') . " $fname & $mname")
        : "F : $fname & M : $mname";

    /* ===== totals ===== */
    $totalmarks = $stmark['totalmarks'];
    $fullmarks = $stmark['full_marks'];
    $gpa = $stmark['gpa'];
    $gla = $stmark['gla'];
    $meritplace = $stmark['meritplace'];
    $mcomb = $stmark['meritplacecomb'];
    $totalfail = $stmark['totalfail'];
    $failsub = $stmark['failsub'];

    /* ===== GPA remark ===== */
    $gpaRow = fetch_row($conn, "SELECT remark,colorcode FROM gpa WHERE gl='$gla'");
    $remark = $gpaRow['remark'] ?? '';
    $clc = '#' . ($gpaRow['colorcode'] ?? '000');

    include 'progress-report/report-body-01.php';
}
echo '</div>';
?>

<!-- ===== Floating Print Panel ===== -->
<div id="print-tools">
    <button onclick="printBox()" title="Print">🖨️</button>
    <button onclick="savePDF()" title="Download PDF">📄</button>
    <button onclick="toggleSettings()" title="Settings">⚙️</button>

    <div id="print-settings">
        <label>
            Paper:
            <select id="paper-size">
                <option value="A4">A4</option>
                <option value="Letter">Letter</option>
            </select>
        </label>
        <label>
            Orientation:
            <select id="paper-orientation">
                <option value="portrait">Portrait</option>
                <option value="landscape">Landscape</option>
            </select>
        </label>
    </div>
</div>



<?php
include_once 'footer.php';

$q = mysqli_query($conn, "SELECT admin_data FROM scinfo WHERE sccode='$sccode' LIMIT 1");
$row = mysqli_fetch_assoc($q);

$settings = [];
if (!empty($row['admin_data'])) {
    $settings = json_decode($row['admin_data'], true);
    if (!is_array($settings))
        $settings = [];
}

?>


<script>
    function printBox() {
        applyPrintSettings();
        window.print();
    }

    function savePDF() {
        applyPrintSettings();
        window.print(); // Browser Save as PDF
    }

    function toggleSettings() {
        const el = document.getElementById('print-settings');
        el.style.display = (el.style.display === 'block') ? 'none' : 'block';
    }

    function applyPrintSettings() {
        const size = document.getElementById('paper-size').value;
        const orient = document.getElementById('paper-orientation').value;

        let style = document.getElementById('dynamic-print-style');
        if (!style) {
            style = document.createElement('style');
            style.id = 'dynamic-print-style';
            document.head.appendChild(style);
        }

        style.innerHTML = `
        @page {
            size: ${size} ${orient};
            margin: 10mm;
        }
    `;
    }

</script>
</body>

</html>