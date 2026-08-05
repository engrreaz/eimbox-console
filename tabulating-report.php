<?php
include_once 'header.php';

/* =======================
   Helper Functions
======================= */
function fetchAll($conn, $sql)
{
    $data = [];
    $res = $conn->query($sql);
    if ($res && $res->num_rows) {
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

function fetchRow($conn, $sql)
{
    $res = $conn->query($sql);
    return ($res && $res->num_rows) ? $res->fetch_assoc() : null;
}

function idxBy($array, $key)
{
    $map = [];
    foreach ($array as $row) {
        $map[$row[$key]] = $row;
    }
    return $map;
}

/* =======================
   Input Handling
======================= */
$slot = $_GET['slot'] ?? $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_GET['sy'] ?? $_COOKIE['chain-session'] ?? '';
$cn = $_GET['classname'] ?? $_COOKIE['chain-class'] ?? '';
$secname = $_GET['sectionname'] ?? $_COOKIE['chain-section'] ?? '';
$exam = $_GET['exam'] ?? $_COOKIE['chain-exam'] ?? '';

if (!$sessionyear || !$cn || !$exam) {
    echo "<script>window.location.href='result-report-manager.php';</script>";
    exit;
}

$topsheet = $_GET['top'] ?? 'false';
$paper = '320mm';
$etdt = $cur;

/* =======================
   Student Marks
======================= */
$sqlMarks = "SELECT * FROM tabulatingsheet 
             WHERE exam='$exam' AND classname='$cn' AND sectionname='$secname' 
             AND sessionyear='$sessionyear' AND sccode='$sccode'";

$sqlMarksEx = "SELECT * FROM tabulatingsheetex 
               WHERE exam='$exam' AND classname='$cn' AND sectionname='$secname' 
               AND sessionyear='$sessionyear' AND sccode='$sccode'";

$stmarks = fetchAll($conn, $sqlMarks);
$stmarksex = fetchAll($conn, $sqlMarksEx);

$marksByStid = idxBy($stmarks, 'stid');
$marksExByStid = idxBy($stmarksex, 'stid');

/* =======================
   School Info
======================= */
$sc = fetchRow($conn, "SELECT * FROM scinfo WHERE sccode='$sccode'");
$einame = $sc['scname'] ?? '';
$eiadd = $sc['scaddress'] ?? '';
$eicontact = $sc['scmobile'] ?? '';
$email = $sc['scemail'] ?? '';
$eicontact = $email;

/* =======================
   Exam Event Date
======================= */
$sdate = $sessionyear . '-01-01';
$edate = $sessionyear . '-12-31';

$ev = fetchRow($conn, "SELECT date FROM holiday 
    WHERE sccode='$sccode' 
    AND reason LIKE '%$exam%' 
    AND hdtype='Event'
    AND date BETWEEN '$sdate' AND '$edate'");

$rsn = $ev['date'] ?? '';

/* =======================
   Subject List
======================= */
$rowSub = fetchRow($conn, "SELECT allsubject FROM tabulatingsheet
    WHERE classname='$cn' AND sectionname='$secname'
    AND sessionyear LIKE '%$sy%'
    AND sccode='$sccode' AND exam='$exam'
    ORDER BY rollno ASC LIMIT 1");

$allsub = explode('.', $rowSub['allsubject'] ?? '');
$subcnt = count($allsub);

/* =======================
   Subject Count
======================= */
$cntRow = fetchRow($conn, "SELECT COUNT(*) tts FROM subsetup
    WHERE classname='$cn' AND sectionname='$secname'
    AND sessionyear LIKE '%$sy%' AND sccode='$sccode'");

$tts = $cntRow['tts'] ?? 0;

/* =======================
   Signature
======================= */
$sign_path = "https://eimbox.com/sign/{$sccode}9999.png";
if ($sccode == '134579' && $slot != 'School') {
    $sign_path = "https://eimbox.com/sign/1345799998.png";
}
?>

<style>
    @page {
        margin: 8mm;
        -webkit-print-color-adjust: exact !important;
    }

    body {
        print-color-adjust: exact !important;
    }

    thead {
        display: table-header-group
    }

    tfoot {
        display: table-footer-group
    }

    .chip {
        line-height: 11px;
        font-size: 9px;
        padding: 3px !important
    }

    @media print {
        * {
            print-color-adjust: exact !important
        }

        .noprint {
            display: none
        }
    }
</style>
<style>
    #print-tools {
        position: fixed;
        right: 20px;
        bottom: 20px;
        background: transparent;
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
 
        font-size: 12px;
    }

    #print-settings label {
        display: block;
        margin-bottom: 6px;
    }

    /* ===== Print শুধুই print-box ===== */
    @media print {
        body * {
            visibility: hidden;
        }

        #print-box,
        #print-box * {
            visibility: visible;

        }

        #print-box {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: white;
        }
    }
</style>


<div id="print-box">
    <?php if ($topsheet == 'true'):

        // include 'progress-report/result-top-sheet.php';
        // include 'progress-report/top-50-merit-list.php';
        include 'progress-report/tabulating-cover.php';
        include 'progress-report/tabulating-back.php';
    elseif ($topsheet == 'over'):
        include 'progress-report/report-overview-page-1-cover.php';
        include 'progress-report/report-overview-page-2-acknowledgement.php';
        include 'progress-report/report-overview-page-3-acknowledgement-teacher.php';
        include 'progress-report/report-overview-page-4-top-ten.php';
    else: ?>

        <table class="table bordered border" style="-fs-table-paginate:paginate; margin:0;">

            <tfoot>
                <tr>
                    <td colspan="22">
                        <table width="100%">
                            <tr>
                                <td><b>Result Published on : <?= date('d-m-Y') ?></b></td>
                                <td style="text-align:center;font-weight:bold">Signature (Class Teacher)</td>
                                <td style="text-align:center;font-weight:bold">
                                    <img src="<?= $sign_path ?>" style="height:20px"><br>
                                    Signature (Head Teacher)
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tfoot>

            <thead>
                <tr>
                    <td colspan="22">
                        <table width="100%">
                            <tr>
                                <td style="width:35px;border:0">
                                    <img src="https://eimbox.com/logo/<?= $sccode ?>.png" style="height:30px;width:30px">
                                </td>
                                <td style="border:0">
                                    <span style="font-size:12px;font-weight:700;color:#33a04e"><?= $scname ?></span><br>
                                    <span style="font-size:10px"><?= $scaddress ?><br><?= $mobile ?></span>
                                </td>
                                <td style="text-align:right;border:0;font-size:11px">
                                    Class : <b style="color:blue"><?= $cn ?></b> |
                                    Section : <b style="color:blue"><?= $secname ?></b><br>
                                    <b><?= $exam ?> Examination</b> |
                                    Session : <b><?= $sy ?></b>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td>Roll</td>
                    <td>Student</td>
                    <td></td>

                    <?php
                    $part = 0;
                    foreach ($allsub as $sing):
                        if ($sing > 100):
                            $subs = '';
                            $subRow = fetchRow($conn, "SELECT subshname FROM subjects 
            WHERE subcode='$sing' AND sccategory='$sctype'");
                            if (!$subRow) {
                                $subRow = fetchRow($conn, "SELECT subshname FROM subjects 
                WHERE subcode='$sing' AND sccategory='$sctype'
                AND sup_class LIKE '%$cn%'");
                            }
                            $subs = $subRow['subshname'] ?? $sing;
                            if ($sing > 1000)
                                $subs = $sing;

                            if ($sing != 1000) {
                                $tail = ($part == 1) ? '<b>*</b>' : '';
                                echo "<td style='text-align:center; '>$subs$tail</td>";
                            } else
                                $part = 1;
                        endif;
                    endforeach;
                    ?>

                    <td>STAT</td>
                    <td>Merit</td>
                </tr>
            </thead>

            <tbody>

                <?php
                $students = fetchAll($conn, "SELECT * FROM sessioninfo
    WHERE sccode='$sccode' AND classname='$cn'
    AND sectionname='$secname' AND sessionyear='$sessionyear'
    ORDER BY rollno");

                foreach ($students as $stu):
                    $rollno = $stu['rollno'];
                    $stid = $stu['stid'];

                    $stinfo = fetchRow($conn, "SELECT stnameeng,stnameben FROM students WHERE stid='$stid'");
                    $stnameeng = $stinfo['stnameeng'] ?? '';
                    $stnameben = $stinfo['stnameben'] ?? '';

                    $m = $marksByStid[$stid] ?? [];
                    $mx = $marksExByStid[$stid] ?? [];

                    $totalmarks = $m['totalmarks'] ?? 0;
                    $stl = ($totalmarks == 0) ? 'border:1px solid red;padding:3px;border-radius:3px;' : '';
                    ?>

                    <tr>
                        <td style="text-align:center"><span style="<?= $stl ?>"><?= $rollno ?></span></td>
                        <td style="text-align:center;line-height:20px">
                            <?= $stnameeng ?><br><?= $stnameben ?><br>
                            <span style="font-size:9px;color:#ba4b1b"><?= $stid ?></span>
                        </td>

                        <td class="chip" style="text-align:center">M<br>S<br>O<br>P<br>C<br>T<br>G<br>L</td>

                        <?php
                        $ff = 0;
                        foreach ($allsub as $sing):

                            $tt = 0;
                            $gp = 0;
                            $gl = '';
                            $mt = 0;
                            $ss = 0;
                            $oo = 0;
                            $pp = 0;
                            $cc = 0;

                            if ($ff == 1) {
                                $ss = $mx['fourth_subj'] ?? 0;
                                $oo = $mx['fourth_obj'] ?? 0;
                                $pp = $mx['fourth_pra'] ?? 0;
                                $cc = $mx['fourth_ca'] ?? 0;
                                $tt = $mx['fourth_total'] ?? 0;
                                $gp = $mx['fourth_gp'] ?? 0;
                                $gl = $mx['fourth_gl'] ?? '';
                            } else {
                                if ($sing > 0 && $sing < 1000) {
                                    $k = array_search($sing, $m);
                                    if ($k !== false) {
                                        $mt = $m[$k . '_mt'] ?? 0;
                                        $ss = $m[$k . '_sub'] ?? 0;
                                        $oo = $m[$k . '_obj'] ?? 0;
                                        $pp = $m[$k . '_pra'] ?? 0;
                                        $cc = $m[$k . '_ca'] ?? 0;
                                        $tt = $m[$k . '_total'] ?? 0;
                                        $gp = $m[$k . '_gp'] ?? 0;
                                        $gl = $m[$k . '_gl'] ?? '';
                                    }
                                } elseif ($sing > 1000) {
                                    $k = array_search($sing, $mx);
                                    if ($k !== false) {
                                        $kk = str_replace('_code', '', $k);
                                        $ss = $mx[$kk . '_sub'] ?? 0;
                                        $oo = $mx[$kk . '_obj'] ?? 0;
                                        $pp = $mx[$kk . '_pra'] ?? 0;
                                        $cc = $mx[$kk . '_ca'] ?? 0;
                                        $tt = $mx[$kk . '_total'] ?? 0;
                                        $gp = $mx[$kk . '_gp'] ?? 0;
                                        $gl = $mx[$kk . '_gl'] ?? '';
                                    }
                                } else
                                    $ff = 1;
                            }

                            $cllr = ($tt == 0) ? '#fff' : (($gp == 0) ? 'red' : (($gp == 5) ? '#33a04e' : 'black'));

                            if ($sing != 1000 && $sing != ''):
                                ?>
                                <td class="chip" style="text-align:center;color:<?= $cllr ?>">
                                    <?= $mt ?><br><?= $ss ?><br><?= $oo ?><br><?= $pp ?><br><?= $cc ?><br>
                                    <b><?= $tt ?></b><br><?= $gp ?><br><?= $gl ?>
                                </td>
                            <?php endif; endforeach; ?>

                        <td style="text-align:center;font-size:12px">
                            <b><?= $totalmarks ?></b><br>
                            <b><?= sprintf('%0.2f', $m['avgrate'] ?? 0) ?>%</b><br>
                            <?= sprintf('%0.2f', $m['gpa'] ?? 0) ?><br>
                            <?= ($m['gla'] ?? '') . ' (' . ($m['totalfail'] ?? 0) . ')' ?>
                        </td>

                        <td style="text-align:center">
                            <?= $m['meritplace'] ?? '' ?><br>
                            <span style="font-style:italic;font-size:12px">
                                <?= ($m['meritplacecomb'] ?? '') ?> [Comb]
                            </span>
                        </td>

                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    <?php endif; ?>
</div>


<!-- ===== Floating Print Panel ===== -->
<div id="print-tools" class="d-flex">
    <div class="d-block">
        <button onclick="printBox()" title="Print" class="btn btn-link"><i class="bi bi-printer"></i></button>
        <button onclick="savePDF()" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></button>
        <button onclick="toggleSettings()" title="Settings"><i class="bi bi-tools"></i></button>
    </div>

  
    <div id="print-settings">
        <label>
            Paper:
            <select id="paper-size" class="form-select form-select-sm">
                <option value="A4">A4</option>
                <option value="Letter">Letter</option>
            </select>
        </label>
        <label>
            Orientation:
            <select id="paper-orientation" class="form-select form-select-sm">
                <option value="portrait">Portrait</option>
                <option value="landscape">Landscape</option>
            </select>
        </label>
    </div>
</div>


<?php require_once 'footer.php'; ?>




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
<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->
</body>

</html>