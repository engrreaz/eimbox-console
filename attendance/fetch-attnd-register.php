<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

$slot = $_POST['slot'] ?? $sctype;
$session = $_POST['session'] ?? $y_v4;
$cls = $_POST['cls'] ?? '';
$sec = $_POST['sec'] ?? '';
$datefrom = $_POST['dateFrom'] ?? date('Y-m-d');
$dateto = $_POST['dateTo'] ?? date('Y-m-d');

$month = formatMonthYearRange($datefrom, $dateto);

/* ================= STUDENT MASTER ================= */
$stprofile = [];
$q = mysqli_query($conn, "SELECT stid, stnameeng FROM students WHERE sccode='$sccode'");
while ($r = mysqli_fetch_assoc($q)) {
    $stprofile[] = $r;
}




/* ================= SESSION STUDENTS ================= */
$sessioninfo = [];
$q = mysqli_query($conn, "
    SELECT stid, rollno 
    FROM sessioninfo
    WHERE sessionyear LIKE '$session%' 
      AND sccode='$sccode'
      AND classname='$cls'
      AND sectionname='$sec'
    ORDER BY rollno
");

while ($r = mysqli_fetch_assoc($q)) {
    $sessioninfo[] = $r;
}
$stcnt = count($sessioninfo);

/* ================= CALENDAR (HOLIDAY / EVENTS) ================= */
$datam = [];
$q = mysqli_query($conn, "
    SELECT date, descrip 
    FROM calendar
    WHERE (sccode='$sccode' OR sccode=0)
      AND date BETWEEN '$datefrom' AND '$dateto'
      AND class=0
      AND descrip IS NOT NULL
");
while ($r = mysqli_fetch_assoc($q)) {
    $datam[] = $r;
}


/* ================= WEEKENDS ================= */
$weeklist = '';
$q = mysqli_query($conn, "SELECT settings_value FROM settings WHERE sccode='$sccode' AND setting_title='Weekends'");
if ($r = mysqli_fetch_assoc($q)) {
    $weeklist = $r['settings_value']; // e.g: Friday,Saturday
}


/* ================= ATTENDANCE ================= */
$stattnd = [];
$q = mysqli_query($conn, "
    SELECT stid, adate, yn 
    FROM stattnd
    WHERE sccode='$sccode'
      AND classname='$cls'
      AND sectionname='$sec'
      AND adate BETWEEN '$datefrom' AND '$dateto'
");
while ($r = mysqli_fetch_assoc($q)) {
    $stattnd[] = $r;
}
?>

<style>
    #head-table td {
        text-align: center;
    }
</style>

<table style="width:80%; margin:auto ;" border="0" id="head-table">
    <tr>
        <td><?= $slot ?></td>
        <td><?= $session ?></td>
        <td><?= $cls ?></td>
        <td><?= $sec ?></td>
        <td><?= $month . ' (' . $datefrom . ' &mdash; ' . $dateto . ') ' ?></td>
    </tr>
    <tr>
        <td class="fs-tiny">Slot</td>
        <td class="fs-tiny">Session</td>
        <td class="fs-tiny">Class</td>
        <td class="fs-tiny">Section</td>
        <td class="fs-tiny">Month</td>
    </tr>
</table>

<?php
$dates = [];
$start = strtotime($datefrom);
$end = strtotime($dateto);

while ($start <= $end) {
    $dates[] = date('Y-m-d', $start);
    $start = strtotime("+1 day", $start);
}
?>


<table class="table table-striped table-sm data-table" border="1" width="100%">
    <thead>
        <tr>
            <th>Roll</th>
            <th>Name</th>

            <?php foreach ($dates as $d): ?>
                <th><?= date('d', strtotime($d)) ?></th>
            <?php endforeach; ?>

            <th>%</th>
        </tr>
    </thead>

    <tbody>
        <?php
        foreach ($sessioninfo as $st) {

            $stid = $st['stid'];

            // student name
            $idx = array_search($stid, array_column($stprofile, 'stid'));
            $name = $idx !== false ? $stprofile[$idx]['stnameeng'] : '';

            // this student's attendance rows
            $att = [];
            foreach ($stattnd as $a) {
                if ($a['stid'] == $stid)
                    $att[] = $a;
            }

            $present = 0;
            $working = 0;
            ?>
            <tr>
                <td class="px-3 text-end"><?= $st['rollno'] ?></td>
                <td class="p-1" style="text-align:left"><?= $name ?></td>

                <?php foreach ($dates as $d):

                    $isHoliday = false;

                    // calendar holiday
                    $cind = array_search($d, array_column($datam, 'date'));
                    if ($cind !== false)
                        $isHoliday = true;

                    // weekend
                    $day = date('l', strtotime($d));
                    if (str_contains($weeklist, $day))
                        $isHoliday = true;

                    if ($isHoliday) {
                        echo "<td style='background:#f3f3f3'>-</td>";
                        continue;
                    }

                    $working++;

                    $ind = array_search($d, array_column($att, 'adate'));
                    if ($ind !== false && $att[$ind]['yn'] == 1) {
                        echo "<td>✓</td>";
                        $present++;
                    } else {
                        echo "<td></td>";
                    }

                endforeach; ?>

                <td>
                    <?= $working > 0 ? number_format($present * 100 / $working, 2) : '0.00' ?>%
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>