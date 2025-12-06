<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$today = date("Y-m-d");
$type = $_GET['type'] ?? 'teacher';

/* ---------------------------
   1) subsetup → teacher mapping
------------------------------*/


$teacherMap = []; // key: cls|sec|sub → tid
$sql = "SELECT classname, sectionname, subject, tid
        FROM subsetup
        WHERE sccode='$sccode' AND sessionyear LIKE '%$y_v2%'";
$q = mysqli_query($conn, $sql);

while ($r = mysqli_fetch_assoc($q)) {
    $key = $r['classname'] . "|" . $r['sectionname'] . "|" . $r['subject'];
    $teacherMap[$key] = $r['tid'];
}

/* ---------------------------
   2) teacher names
------------------------------*/

$teacherName = []; // tid → tname
$sql = "SELECT tid, tname FROM teacher WHERE sccode='$sccode'";
$q = mysqli_query($conn, $sql);
while ($r = mysqli_fetch_assoc($q)) {
    $teacherName[$r['tid']] = $r['tname'];
}

/* ---------------------------
   3) sessioninfo → student count
------------------------------*/

$clssecCount = [];
$sql = "SELECT classname, sectionname, COUNT(*) AS total
        FROM sessioninfo
        WHERE sccode='$sccode'
          AND sessionyear LIKE '%$y_v2%'
        GROUP BY classname, sectionname";
$q = mysqli_query($conn, $sql);
while ($r = mysqli_fetch_assoc($q)) {
    $clssecCount[$r['classname']."|".$r['sectionname']] = $r['total'];
}

/* ---------------------------
   4) active exams
------------------------------*/

$examArr = [];
$sql = "SELECT examtitle FROM examlist
        WHERE sccode='$sccode'
        AND datestart <= '$today'
        AND result_publish >= '$today'";
$q = mysqli_query($conn, $sql);
while ($r = mysqli_fetch_assoc($q)) {
    $examArr[] = $r['examtitle'];
}
$examIn = "'" . implode("','", $examArr) . "'";

/* ---------------------------
   5) stmark → completed entries
------------------------------*/

$entryCount = []; // key: exam|cls|sec|sub → count
$sql = "SELECT classname, sectionname, exam, subject, COUNT(*) AS t
        FROM stmark
        WHERE sessionyear LIKE '%$y_v2%'
          AND exam IN ($examIn)
          AND sccode='$sccode'
        GROUP BY classname, sectionname, exam, subject";
$q = mysqli_query($conn, $sql);

while ($m = mysqli_fetch_assoc($q)) {
    $key = $m['exam']."|".$m['classname']."|".$m['sectionname']."|".$m['subject'];
    $entryCount[$key] = $m['t'];
}

/* ---------------------------
   6) examroutine → required entries
------------------------------*/

$teacherStats = [];  
// tid → ['tname'=>name, 'required'=>0, 'done'=>0]

$sql = "SELECT clsname, secname, subcode, examname
        FROM examroutine
        WHERE sessionyear LIKE '%$y_v2%' 
        AND examname IN ($examIn)
        ORDER BY examname, clsname, secname, subcode";

$q = mysqli_query($conn, $sql);

while ($r = mysqli_fetch_assoc($q)) {

    $cls = $r['clsname'];
    $sec = $r['secname'];
    $sub = $r['subcode'];
    $exam = $r['examname'];

    // find teacher
    $tKey = $cls . "|" . $sec . "|" . $sub;
    $tid = $teacherMap[$tKey] ?? 0;
    if ($tid == 0) continue; // no teacher assigned

    $tname = $teacherName[$tid] ?? "Unknown";

    // initialize teacher stats
    if (!isset($teacherStats[$tid])) {
        $teacherStats[$tid] = [
            'tname' => $tname,
            'required' => 0,
            'done' => 0
        ];
    }

    // required = total students in that class-section
    $stKey = $cls . "|" . $sec;
    $students = $clssecCount[$stKey] ?? 0;
    $teacherStats[$tid]['required'] += $students;

    // completed entries
    $markKey = $exam."|".$cls."|".$sec."|".$sub;
    $teacherStats[$tid]['done'] += ($entryCount[$markKey] ?? 0);
}

/* ---------------------------
   7) calculate percentage
------------------------------*/

foreach ($teacherStats as $tid => $t) {
    $req = $t['required'];
    $done = $t['done'];
    $perc = ($req > 0) ? number_format(($done / $req) * 100, 2) : 0;
    $teacherStats[$tid]['percent'] = $perc;
}

?>
<h5 class="mb-3">Teacher Wise Mark Entry Statistics</h5>

<canvas id="teacherChart" height="120"></canvas>
<hr>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Teacher</th>
            <th>Required Entries</th>
            <th>Completed</th>
            <th>%</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($teacherStats as $t) { ?>
        <tr>
            <td><?= $t['tname'] ?></td>
            <td><?= $t['required'] ?></td>
            <td><?= $t['done'] ?></td>
            <td><?= $t['percent'] ?>%</td>
        </tr>
    <?php } ?>
    </tbody>
</table>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
function renderExamChart() {

    // Labels = teacher names
    const labels = [
        <?php foreach($teacherStats as $t){ echo "'".$t['tname']."',"; } ?>
    ];

    // Completed entries
    const doneData = [
        <?php foreach($teacherStats as $t){ echo $t['done'].","; } ?>
    ];

    // Required entries
    const reqData = [
        <?php foreach($teacherStats as $t){ echo $t['required'].","; } ?>
    ];

    // Percent data
    const percentData = [
        <?php foreach($teacherStats as $t){ echo $t['percent'].","; } ?>
    ];

    const ctx = document.getElementById("teacherChart");

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Completed Entries",
                    data: doneData
                },
                {
                    label: "Required Entries",
                    data: reqData
                },
                {
                    label: "Completion (%)",
                    data: percentData,
                    type: "line",
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true },
                y1: {
                    beginAtZero: true,
                    position: 'right'
                }
            }
        }
    });
}
</script>
