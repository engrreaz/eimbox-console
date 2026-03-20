<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

$slot = $_GET['slot'];
$year = $_GET['year'];
$cls = $_GET['cls'];
$sec = $_GET['sec'];
$dateFrom = $_GET['dateFrom'] ?? $_COOKIE['datefrom'] ?? date('Y-m-d') ;
$dateTo = $_GET['dateTo'] ?? $_COOKIE['dateto'] ?? date('Y-m-d') ;

// ১. শিক্ষার্থীদের তথ্য আনা (sessioninfo এবং students টেবিল থেকে)
$query = "SELECT si.stid, si.rollno, s.stnameeng FROM sessioninfo si 
          JOIN students s ON si.stid = s.stid 
          WHERE si.sccode='$sccode' AND si.classname='$cls' AND si.sectionname='$sec' 
          ORDER BY si.rollno ASC";
$students = $conn->query($query);

// ২. নির্দিষ্ট তারিখের মধ্যে উপস্থিতির তথ্য আনা
$att_query = "SELECT stid, adate, yn AS status FROM stattnd 
              WHERE sccode='$sccode' AND adate BETWEEN '$dateFrom' AND '$dateTo'";
$attendance_data = $conn->query($att_query);

// এটেনডেন্স ডাটাকে একটি সহজ অ্যারেতে সাজানো
$att_map = [];
while($row = $attendance_data->fetch_assoc()) {
    $att_map[$row['stid']][$row['adate']] = $row['status'];
}

// তারিখের রেঞ্জ তৈরি করা
$period = new DatePeriod(
     new DateTime($dateFrom),
     new DateInterval('P1D'),
     new DateTime($dateTo . ' +1 day')
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        .text-left { text-align: left; }
        .present { color: green; font-weight: bold; }
        .absent { color: red; font-weight: bold; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="<?= ($_GET['type'] == 'print') ? 'window.print()' : '' ?>">

    <div style="text-align: center;">
        <h2>Institute Name</h2>
        <h3>Attendance Register Report</h3>
        <p>Class: <?= $cls ?> | Section: <?= $sec ?> | Period: <?= $dateFrom ?> to <?= $dateTo ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Roll</th>
                <th class="text-left">Student Name</th>
                <?php foreach ($period as $date): ?>
                    <th><?= $date->format('d') ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php while($st = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= $st['rollno'] ?></td>
                    <td class="text-left"><?= $st['stnameeng'] ?></td>
                    <?php foreach ($period as $date): 
                        $d = $date->format('Y-m-d');
                        $status = $att_map[$st['stid']][$d] ?? '-';
                        $class = ($status == 'P') ? 'present' : (($status == 'A') ? 'absent' : '*');
                    ?>
                        <td class="<?= $class ?>"><?= $status ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>