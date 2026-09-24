<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

$slot = $_GET['slot'] ?? '';
$year = $_GET['year'] ?? '';
$cls = $_GET['cls'] ?? '';
$sec = $_GET['sec'] ?? '';
$dateFrom = $_GET['dateFrom'] ?? $_COOKIE['datefrom'] ?? date('Y-m-d');
$dateTo = $_GET['dateTo'] ?? $_COOKIE['dateto'] ?? date('Y-m-d');

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
if ($attendance_data) {
    while($row = $attendance_data->fetch_assoc()) {
        $att_map[$row['stid']][$row['adate']] = $row['status'];
    }
}

// ৩. Events ও Holidays আনা
$eventsMap = [];
$qEvents = mysqli_query($conn, "
    SELECT title, start, end, all_day, event_type, color, class, work 
    FROM events 
    WHERE (sccode='$sccode' OR sccode=0)
      AND (
        (DATE(start) <= '$dateTo' AND (end IS NULL OR DATE(end) >= '$dateFrom'))
        OR (DATE(start) BETWEEN '$dateFrom' AND '$dateTo')
      )
    ORDER BY start ASC
");

if ($qEvents) {
    while ($ev = mysqli_fetch_assoc($qEvents)) {
        $evStart = date('Y-m-d', strtotime($ev['start']));
        $evEnd = (!empty($ev['end']) && strtotime($ev['end']) > 0) ? date('Y-m-d', strtotime($ev['end'])) : $evStart;
        if ($evEnd < $evStart) $evEnd = $evStart;

        $cur = strtotime(max($evStart, $dateFrom));
        $last = strtotime(min($evEnd, $dateTo));

        while ($cur <= $last) {
            $curDate = date('Y-m-d', $cur);
            if (!isset($eventsMap[$curDate])) {
                $eventsMap[$curDate] = [];
            }
            $eventsMap[$curDate][] = $ev;
            $cur = strtotime("+1 day", $cur);
        }
    }
}

// ৪. Weekends
$weeklist = '';
$qWeek = mysqli_query($conn, "SELECT settings_value FROM settings WHERE sccode='$sccode' AND setting_title='Weekends'");
if ($r = mysqli_fetch_assoc($qWeek)) {
    $weeklist = $r['settings_value'];
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
    <meta charset="utf-8">
    <title>Attendance Register Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #777; padding: 3px 2px; text-align: center; }
        th { background-color: #f2f2f2; font-size: 10px; }
        .text-left { text-align: left; padding-left: 5px; }
        .text-right { text-align: right; padding-right: 5px; }
        .present { color: #2e7d32; font-weight: bold; }
        .weekend { background-color: #e9ecef; color: #6c757d; }
        .event-day { background-color: #ffebee; color: #c62828; font-weight: bold; }
        .legend-box { display: flex; gap: 15px; justify-content: center; margin-top: 5px; font-size: 10px; }
        @media print {
            .no-print { display: none; }
            @page { size: landscape; margin: 8mm; }
        }
    </style>
</head>
<body onload="<?= (isset($_GET['type']) && $_GET['type'] == 'print') ? 'window.print()' : '' ?>">

    <div style="text-align: center;">
        <h2 style="margin: 0; padding: 0;">Attendance Register Report</h2>
        <p style="margin: 4px 0 0 0; font-size: 12px;">
            <strong>Class:</strong> <?= htmlspecialchars($cls) ?> &nbsp;|&nbsp;
            <strong>Section:</strong> <?= htmlspecialchars($sec) ?> &nbsp;|&nbsp;
            <strong>Period:</strong> <?= htmlspecialchars($dateFrom) ?> to <?= htmlspecialchars($dateTo) ?>
        </p>
        <div class="legend-box">
            <span><strong>✓</strong> Present</span>
            <span><strong>-</strong> Weekend</span>
            <span><strong style="color:#c62828;">E</strong> Event / Holiday</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">Roll</th>
                <th class="text-left" style="min-width: 140px;">Student Name</th>
                <?php foreach ($period as $date): 
                    $d = $date->format('Y-m-d');
                    $dayName = $date->format('l');
                    $isW = ($weeklist !== '' && str_contains($weeklist, $dayName));
                    $isEv = !empty($eventsMap[$d]);
                    $thClass = $isEv ? 'event-day' : ($isW ? 'weekend' : '');
                ?>
                    <th class="<?= $thClass ?>">
                        <?= $date->format('d') ?><br>
                        <span style="font-size: 8px;"><?= substr($dayName, 0, 2) ?></span>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($students && $students->num_rows > 0):
                while($st = $students->fetch_assoc()): 
            ?>
                <tr>
                    <td class="text-right"><?= $st['rollno'] ?></td>
                    <td class="text-left"><?= htmlspecialchars($st['stnameeng']) ?></td>
                    <?php foreach ($period as $date): 
                        $d = $date->format('Y-m-d');
                        $dayName = $date->format('l');
                        $isW = ($weeklist !== '' && str_contains($weeklist, $dayName));
                        $evList = $eventsMap[$d] ?? [];
                        $isEv = !empty($evList);

                        if ($isW) {
                            echo "<td class='weekend'>-</td>";
                            continue;
                        }

                        if ($isEv) {
                            $evTitle = $evList[0]['title'] ?? 'Event';
                            echo "<td class='event-day' title='" . htmlspecialchars($evTitle) . "'>E</td>";
                            continue;
                        }

                        $status = $att_map[$st['stid']][$d] ?? '';
                        if ($status == '1' || $status == 'P') {
                            echo "<td class='present'>✓</td>";
                        } else {
                            echo "<td></td>";
                        }
                    ?>
                    <?php endforeach; ?>
                </tr>
            <?php 
                endwhile;
            else: 
            ?>
                <tr>
                    <td colspan="40" style="padding: 15px;">No students found for this class and section.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>