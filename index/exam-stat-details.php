<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$today  = date("Y-m-d");

// 1) sessioninfo count
$clssecCount = [];
$sql = "SELECT classname, sectionname, COUNT(*) AS total
        FROM sessioninfo
        WHERE sccode='$sccode'
          AND sessionyear LIKE '%$y_v2%'
        GROUP BY classname, sectionname";
$q = mysqli_query($conn, $sql);
while($r = mysqli_fetch_assoc($q)){
    $key = $r['classname']."|".$r['sectionname'];
    $clssecCount[$key] = $r['total'];
}

// 2) examlist active exams
$examArr = [];
$sql = "SELECT examtitle FROM examlist
        WHERE sccode='$sccode'
        AND datestart <= '$today'
        AND result_publish >= '$today'";
$q = mysqli_query($conn, $sql);
while($r = mysqli_fetch_assoc($q)){
    $examArr[] = $r['examtitle'];
}
$examIn = "'" . implode("','", $examArr) . "'";

// 3) examroutine → cls/sec/subject
$sql = "SELECT clsname, secname, subcode, examname
        FROM examroutine
        WHERE sessionyear LIKE '%$y_v2%'
          AND examname IN ($examIn)";
$q = mysqli_query($conn, $sql);

// details array
$details = [];

while($r = mysqli_fetch_assoc($q)){

    $cls = $r['clsname'];
    $sec = $r['secname'];
    $sub = $r['subcode'];

    $key = $cls."|".$sec."|".$sub;

    // total students
    $stKey = $cls . "|" . $sec;
    $totalStudents = $clssecCount[$stKey] ?? 0;

    // mark entries
    $sql2 = "SELECT COUNT(*) AS t
             FROM stmark
             WHERE sessionyear LIKE '%$y_v2%'
               AND exam='{$r['examname']}'
               AND classname='$cls'
               AND sectionname='$sec'
              AND sccode = '$sccode'
               AND subject='$sub'";
    $q2 = mysqli_query($conn, $sql2);
    $m = mysqli_fetch_assoc($q2);
    $markCount = $m['t'];

    $details[] = [
        'cls' => $cls,
        'sec' => $sec,
        'sub' => $sub,
        'students' => $totalStudents,
        'marks' => $markCount,
        'percent' => $totalStudents > 0 ? number_format(($markCount / $totalStudents) * 100, 2) : 0
    ];
}
?>

<h5 class="mb-3">Class / Section / Subject Wise Statistics</h5>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Class</th>
            <th>Section</th>
            <th>Subject</th>
            <th>Total Students</th>
            <th>Mark Entries</th>
            <th>%</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($details as $d){ ?>
        <tr>
            <td><?= $d['cls'] ?></td>
            <td><?= $d['sec'] ?></td>
            <td><?= $d['sub'] ?></td>
            <td><?= $d['students'] ?></td>
            <td><?= $d['marks'] ?></td>
            <td><?= $d['percent'] ?>%</td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<hr>

<canvas id="examChart" height="100"></canvas>



<script>
function renderExamChart(){
    const labels = [
        <?php
            foreach($details as $d){
                echo "'" . $d['cls'] . "-" . $d['sec'] . "-" . $d['sub'] . "',";
            }
        ?>
    ];

    const marks = [
        <?php foreach($details as $d){ echo $d['marks'] . ","; } ?>
    ];

    const students = [
        <?php foreach($details as $d){ echo $d['students'] . ","; } ?>
    ];

    const ctx = document.getElementById("examChart");

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Mark Entries",
                    data: marks
                },
                {
                    label: "Total Students",
                    data: students
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}
</script>
