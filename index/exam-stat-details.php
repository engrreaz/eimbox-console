<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$today = date("Y-m-d");

// 1) sessioninfo count
$clssecCount = [];
$sql = "SELECT classname, sectionname, COUNT(*) AS total
        FROM sessioninfo
        WHERE sccode='$sccode'
          AND sessionyear LIKE '%$y_v2%'
        GROUP BY classname, sectionname";
$q = mysqli_query($conn, $sql);
while ($r = mysqli_fetch_assoc($q)) {
    $key = $r['classname'] . "|" . $r['sectionname'];
    $clssecCount[$key] = $r['total'];
}

// 2) examlist active exams
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


$stmark = [];

$sql2 = "SELECT classname, sectionname, exam, subject, COUNT(*) AS t
         FROM stmark
         WHERE sessionyear LIKE '%$y_v2%'
           AND exam IN ($examIn)
           AND sccode='$sccode'
         GROUP BY classname, sectionname, exam, subject";

$q2 = mysqli_query($conn, $sql2);

if ($q2 && mysqli_num_rows($q2) > 0) {
    while ($m = mysqli_fetch_assoc($q2)) {
        $stmark[] = $m;   // FIX: array name corrected
    }
}




// 3) examroutine → cls/sec/subject
$sql = "SELECT clsname, secname, subcode, examname
        FROM examroutine
        WHERE sessionyear LIKE '%$y_v2%'
          AND examname IN ($examIn) ORDER BY examname, clsname, secname, subcode";
$q = mysqli_query($conn, $sql);

// details array
$details = [];

while ($r = mysqli_fetch_assoc($q)) {

    $cls = $r['clsname'];
    $sec = $r['secname'];
    $sub = $r['subcode'];
    $examt = $r['examname'];

    $key = $cls . "|" . $sec . "|" . $sub;

    // total students
    $stKey = $cls . "|" . $sec;
    $totalStudents = $clssecCount[$stKey] ?? 0;

    $markCount = 0;

    foreach ($stmark as $idx => $stm) {

        $e = $stm['exam'];
        $c = $stm['classname'];
        $s = $stm['sectionname'];
        $b = $stm['subject'];

        if ($e == $examt && $c == $cls && $s == $sec && $b == $sub) {
            $markCount = $stm['t'];
            unset($stmark[$idx]);
            break;
        }
    }



    if ($totalStudents > 0) {
        $details[] = [
            'exam' => $examt,
            'cls' => $cls,
            'sec' => $sec,
            'sub' => $sub,
            'students' => $totalStudents,
            'marks' => $markCount,
            'percent' => $totalStudents > 0 ? number_format(($markCount / $totalStudents) * 100, 2) : 0
        ];
    }

}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<h5 class="mb-3">Class / Section / Subject Wise Statistics</h5>


<canvas id="examChart" height="100"></canvas>
<hr>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Exam</th>
            <th>Class</th>
            <th>Section</th>
            <th>Subject</th>
            <th>Total Students</th>
            <th>Mark Entries</th>
            <th>%</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($details as $d) { ?>
            <tr>
                <td><?= $d['exam'] ?></td>
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



<script>
    function renderExamChart() {
        const labels = [
            <?php
            foreach ($details as $d) {
                echo "'" . $d['cls'] . "-" . $d['sec'] . "-" . $d['sub'] . "',";
            }
            ?>
        ];

        const marks = [
            <?php foreach ($details as $d) {
                echo $d['marks'] . ",";
            } ?>
        ];

        const students = [
            <?php foreach ($details as $d) {
                echo $d['students'] . ",";
            } ?>
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