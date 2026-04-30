<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'] ?? '';
$sessionyear = $_POST['sessionyear'] ?? '';
$examname = $_POST['exam'] ?? '';
$clsname = $_POST['classname'] ?? '';
$secname = $_POST['sectionname'] ?? '';
$action = $_POST['action'] ?? '';
$mode = $_POST['mode'] ?? 'preview';

echo $_COOKIE['chain-exam'] . '................';

$subjectList = [];

$sqlSub = "SELECT subcode, subject 
           FROM subjects
           WHERE sccategory='$sctype'
           AND (sccode='0' OR sccode='$sccode')
           ORDER BY subcode";

$resSub = mysqli_query($conn, $sqlSub);

while ($rowSub = mysqli_fetch_assoc($resSub)) {
    $subjectList[] = $rowSub;
}


$sql = "SELECT r.id, r.date, r.time, r.subcode, s.subject
            FROM examroutine r
            LEFT JOIN subjects s 
                ON r.subcode = s.subcode 
            WHERE s.sccategory = '$sctype'
            AND r.sccode='$sccode'
            AND r.sessionyear='$sessionyear'
            AND r.examname='$examname'
            AND r.clsname='$clsname'
            AND r.secname='$secname'
            ORDER BY r.date, r.time";

$res = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}


if ($mode == 'preview') {
    $sqlStudent = "SELECT 
                    si.rollno,
                    si.stid,
                    st.stnameeng,
                    st.stnameben
               FROM sessioninfo si
               INNER JOIN students st 
                    ON si.stid = st.stid
               WHERE si.classname = '$clsname'
               AND si.sectionname = '$secname'
               AND si.sessionyear = '$sessionyear'
               AND si.sccode = '$sccode'
               AND si.slot = '$slot'
               LIMIT 1";
} else {
    $sqlStudent = "SELECT 
                    si.rollno,
                    si.stid,
                    st.stnameeng,
                    st.stnameben
               FROM sessioninfo si
               INNER JOIN students st 
                    ON si.stid = st.stid
               WHERE si.classname = '$clsname'
               AND si.sectionname = '$secname'
               AND si.sessionyear = '$sessionyear'
               AND si.sccode = '$sccode'
               AND si.slot = '$slot' LIMIT 4";
}

$resStudent = mysqli_query($conn, $sqlStudent);

$students = [];

while ($row = mysqli_fetch_assoc($resStudent)) {
    $students[] = $row;
}





?>

<div class="card-header d-flex justify-content-between">
    <div>
        <b>Exam Routine</b>
        <div id="routine-info" class="d-flex mt-2">
            <div>
                <div class="small">Slot</div>
                <div class="fs-6 fw-bold"><?= $slot ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Session</div>
                <div class="fs-6 fw-bold"><?= $sessionyear ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Examination</div>
                <div class="fs-6 fw-bold"><?= $examname ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Class</div>
                <div class="fs-6 fw-bold"><?= $clsname ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Section</div>
                <div class="fs-6 fw-bold"><?= $secname ?? '' ?></div>
            </div>

        </div>
    </div>

    <div class="d-flex gap-3 fs-4">

        <!-- PRINT -->
        <i class="bi bi-printer text-primary" style="cursor:pointer;" onclick="printAdmit()"></i>

        <!-- PDF -->
        <i class="bi bi-file-earmark-pdf text-danger" style="cursor:pointer;" onclick="downloadPDF()"></i>

        <!-- SETTINGS -->
        <i class="bi bi-gear text-info" style="cursor:pointer;" onclick="openSettings()"></i>

    </div>
</div>

<div class="card-body">

    <!-- Empty State -->
    <div id="emptyState" style="display:none; text-align:center;">
        <h4>No Routine Found</h4>
        <button id="btnStart" class="btn btn-success">
            Let's Get Started
        </button>
    </div>

    <!-- Table -->


</div>

<div id="routineTable" class="table table-responsive">
    <?php
    foreach ($students as $st) {
        ?>
        <div class="admit-card mb-4 p-3 border"
            style="background:url('assets/admit/sample_02.png'); background-size:cover;">

            <!-- HEADER -->

            <table>
                <tr>
                    <td>
                        <img src="https://eimbox.com/logo/<?php echo $sccode; ?>.png" height="60">
                    </td>
                    <td>
<h5 class="mb-0"><?php echo $scname; ?></h5>
                    <small><?php echo $scaddress; ?></small><br>
                    <small>Contact: <?php echo $scmobile; ?></small><br>
                    <small>Email : <?= $scmail ?>, Web : <?= $scweb ?></small>
                    </td>
                </tr>
            </table>
           

            <!-- TITLE -->
            <div class="text-center mb-2">
                <img src="assets/admit/admit.png" height="35px"><br>
                <b><?php echo $examname . ' Examination - ' . $sessionyear; ?></b>
            </div>

            <!-- STUDENT INFO -->
            <table style="width:100%;">
                <tr>
                    <td>
                        <b><?php echo $st['stnameeng']; ?></b><br>
                        Class: <b><?php echo $st['classname']; ?></b>
                        Section: <b><?php echo $st['sectionname']; ?></b>
                        Roll: <b><?php echo $st['rollno']; ?></b><br>
                        ID: <?php echo $st['stid']; ?>
                    </td>

                    <td style="width:80px; text-align:right;">
                        <img src="http://www.eimbox.com/admit/noimg.jpg" height="90" style="border:1px solid #000;">

                    </td>
                </tr>
            </table>



            <table>
                <tr>
                    <td>
                        <table class="" style="font-size:11px; ">
                            <tr>
                                <th style="border:1px solid gray; padding:2px; text-align:center;">Date</th>
                                <th style="border:1px solid gray; padding:2px; text-align:center;">Day</th>
                                <th style="border:1px solid gray; padding:2px; text-align:center;">Time</th>
                                <th style="border:1px solid gray; padding:2px; text-align:center;">Subject</th>
                            </tr>

                            <?php foreach ($data as $r) { ?>
                                <tr>
                                    <td style="border:1px solid gray; padding:2px; text-align:center;">
                                        <?= date('d/m/Y', strtotime($r['date'])) ?>
                                    </td>
                                    <td style="border:1px solid gray; padding:2px; text-align:center;">
                                        <?= date('l', strtotime($r['date'])) ?>
                                    </td>
                                    <td style="border:1px solid gray; padding:2px; text-align:center;">
                                        <?= date('h:i A', strtotime($r['time'])) ?>
                                    </td>
                                    <td style="border:1px solid gray; padding:2px; text-align:left;"><?= $r['subject'] ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </td>


                    <td>
                        <table style="width:100%; text-align:center;">
                            <tr>
                                <td colspan="2" style="text-align:left; font-size:10px;">
                                    <ul>
                                        <li>Don’t be late. Report to the hall min 15 min. before the exam
                                            starts.</li>
                                        <li>Carry your admit card and occupy the seat where your roll is marked.
                                        </li>
                                        <li>Carry your own stationary with calculator. Programmable Calculator
                                            and any electronic gadgets are not allowed.</li>
                                        <li>Don’t exchange stationary or calculator with others without
                                            invigilator permission.</li>
                                        <li>Don’t tear/damage your seat card on desk.</li>
                                        <li>Don’t any misbehave/argue with invigilator and others.</li>
                                        <li>Submit all of invalid equipment/docs to the invigilator before start
                                            exam and collect them before exiting hall.</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:12px;  text-align:center;">

                                    <!-- <img src="<?php echo 'https://eimbox.com/sign/' . $ctea; ?>.png"
                                                height="40px" /> -->
                                    <br>
                                    <b>(<?php echo $cteaname; ?>)</b> <br> Class Teacher
                                </td style="font-size:12px;">
                                <td style="font-size:12px; text-align:center;">
                                    <img src="<?php echo 'https://eimbox.com/sign/' . $sccode; ?>.png" height="35px" /><br>
                                    <?php echo '<b>' . $headname . '</b><br>' . $headtitle; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-size:8px;">
                                    <?php echo $scname; ?><br>
                                    <?php echo $scaddress; ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <!-- ROUTINE TABLE -->


        </div>
    <?php } ?>
</div>