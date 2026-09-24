<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/functions.php';

$slot = $_POST['slot'] ?? $sctype;
$session = $_POST['year'] ?? $_POST['session'] ?? $y_v4;
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

/* ================= EVENTS (FROM events TABLE) ================= */
$eventsMap = [];
$qEvents = mysqli_query($conn, "
    SELECT title, start, end, all_day, event_type, color, class, work 
    FROM events 
    WHERE (sccode='$sccode' OR sccode=0)
      AND (
        (DATE(start) <= '$dateto' AND (end IS NULL OR DATE(end) >= '$datefrom'))
        OR (DATE(start) BETWEEN '$datefrom' AND '$dateto')
      )
    ORDER BY start ASC
");

if ($qEvents) {
    while ($ev = mysqli_fetch_assoc($qEvents)) {
        $evStart = date('Y-m-d', strtotime($ev['start']));
        $evEnd = (!empty($ev['end']) && strtotime($ev['end']) > 0) ? date('Y-m-d', strtotime($ev['end'])) : $evStart;
        if ($evEnd < $evStart) {
            $evEnd = $evStart;
        }

        $cur = strtotime(max($evStart, $datefrom));
        $last = strtotime(min($evEnd, $dateto));

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

/* ================= CALENDAR FALLBACK (LEGACY) ================= */
$datam = [];
$q = mysqli_query($conn, "
    SELECT date, descrip 
    FROM calendar
    WHERE (sccode='$sccode' OR sccode=0)
      AND date BETWEEN '$datefrom' AND '$dateto'
      AND class=0
      AND descrip IS NOT NULL
");
if ($q) {
    while ($r = mysqli_fetch_assoc($q)) {
        $datam[] = $r;
    }
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
    .att-table th, .att-table td {
        vertical-align: middle;
        text-align: center;
        padding: 4px 2px;
    }
    .att-table .text-start {
        text-align: left !important;
    }
    .att-table .text-end {
        text-align: right !important;
    }
</style>

<table style="width:80%; margin:auto ;" border="0" id="head-table" class="mb-3">
    <tr>
        <td class="fw-bold"><?= htmlspecialchars($slot) ?></td>
        <td class="fw-bold"><?= htmlspecialchars($session) ?></td>
        <td class="fw-bold"><?= htmlspecialchars($cls) ?></td>
        <td class="fw-bold"><?= htmlspecialchars($sec) ?></td>
        <td class="fw-bold"><?= $month . ' (' . $datefrom . ' &mdash; ' . $dateto . ') ' ?></td>
    </tr>
    <tr>
        <td class="fs-tiny text-muted">Slot</td>
        <td class="fs-tiny text-muted">Session</td>
        <td class="fs-tiny text-muted">Class</td>
        <td class="fs-tiny text-muted">Section</td>
        <td class="fs-tiny text-muted">Month</td>
    </tr>
</table>

<!-- Legend Bar -->
<div class="d-flex flex-wrap align-items-center justify-content-between p-2 mb-3 bg-light rounded border" style="font-size: 12px;">
    <div class="d-flex flex-wrap align-items-center gap-3">
        <span><strong class="text-success fs-6">✓</strong> Present</span>
        <span><span class="text-muted fw-bold">&mdash;</span> Absent / Blank</span>
        <span><span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-1">E</span> Event / Holiday</span>
        <span><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-1">-</span> Weekend</span>
    </div>
    <div class="text-muted">
        Total Students: <strong class="text-primary"><?= $stcnt ?></strong>
    </div>
</div>

<?php
$dates = [];
$start = strtotime($datefrom);
$end = strtotime($dateto);

while ($start <= $end) {
    $dates[] = date('Y-m-d', $start);
    $start = strtotime("+1 day", $start);
}
?>

<div class="table-responsive">
    <table class="table table-bordered table-hover table-sm att-table data-table mb-0" width="100%" style="font-size: 12px;">
        <thead class="table-light">
            <tr>
                <th style="width: 50px;">Roll</th>
                <th class="text-start" style="min-width: 150px;">Student Name</th>

                <?php foreach ($dates as $d): 
                    $dayName = date('l', strtotime($d));
                    $dayShort = substr(date('D', strtotime($d)), 0, 2);
                    $dayNum = date('d', strtotime($d));
                    
                    $isW = ($weeklist !== '' && str_contains($weeklist, $dayName));
                    $evList = $eventsMap[$d] ?? [];
                    $isEv = !empty($evList);
                    $evTitles = [];
                    foreach ($evList as $ev) {
                        $evTitles[] = $ev['title'] . ($ev['event_type'] ? ' (' . ucfirst($ev['event_type']) . ')' : '');
                    }
                    
                    $cIdx = array_search($d, array_column($datam, 'date'));
                    if ($cIdx !== false) {
                        $isEv = true;
                        $evTitles[] = $datam[$cIdx]['descrip'];
                    }
                    
                    $headerTitle = implode(', ', $evTitles);
                    if ($isW) {
                        $headerTitle = ($headerTitle ? $headerTitle . ' | ' : '') . 'Weekend (' . $dayName . ')';
                    }
                    
                    $thStyle = 'min-width: 30px; padding: 2px;';
                    if ($isEv) {
                        $thStyle .= ' background-color: #ffebee; color: #c62828; border-bottom: 2px solid #ef5350;';
                    } elseif ($isW) {
                        $thStyle .= ' background-color: #f1f3f5; color: #6c757d;';
                    }
                ?>
                    <th style="<?= $thStyle ?>" title="<?= htmlspecialchars($headerTitle) ?>">
                        <div class="fw-bold"><?= $dayNum ?></div>
                        <div style="font-size: 9px; opacity: 0.85;"><?= $dayShort ?></div>
                    </th>
                <?php endforeach; ?>

                <th style="width: 60px;">%</th>
            </tr>
        </thead>

        <tbody>
            <?php
            if (empty($sessioninfo)) {
                $totalCols = count($dates) + 3;
                echo "<tr><td colspan='{$totalCols}' class='text-center py-4 text-muted'>No students found for this class and section.</td></tr>";
            } else {
                foreach ($sessioninfo as $st) {
                    $stid = $st['stid'];

                    // student name
                    $idx = array_search($stid, array_column($stprofile, 'stid'));
                    $name = $idx !== false ? $stprofile[$idx]['stnameeng'] : '';

                    // this student's attendance rows
                    $att = [];
                    foreach ($stattnd as $a) {
                        if ($a['stid'] == $stid) {
                            $att[] = $a;
                        }
                    }

                    $present = 0;
                    $working = 0;
                    ?>
                    <tr>
                        <td class="text-end px-2 fw-bold text-muted"><?= $st['rollno'] ?></td>
                        <td class="text-start px-2"><?= htmlspecialchars($name) ?></td>

                        <?php foreach ($dates as $d):
                            $dayName = date('l', strtotime($d));
                            $isW = ($weeklist !== '' && str_contains($weeklist, $dayName));
                            $evList = $eventsMap[$d] ?? [];
                            $isEv = !empty($evList);
                            $isOffDay = false;
                            $evTitles = [];
                            
                            foreach ($evList as $ev) {
                                $evTitles[] = $ev['title'] . ($ev['event_type'] ? ' (' . ucfirst($ev['event_type']) . ')' : '');
                                if ($ev['event_type'] === 'holiday' || (isset($ev['class']) && intval($ev['class']) === 0)) {
                                    $isOffDay = true;
                                }
                            }
                            
                            $cIdx = array_search($d, array_column($datam, 'date'));
                            if ($cIdx !== false) {
                                $isEv = true;
                                $isOffDay = true;
                                $evTitles[] = $datam[$cIdx]['descrip'];
                            }
                            
                            $evTitleStr = implode(', ', $evTitles);

                            // Weekend takes priority for day off style or combined tooltip
                            if ($isW) {
                                $wTitle = 'Weekend (' . $dayName . ')' . ($evTitleStr ? ' | ' . $evTitleStr : '');
                                echo "<td style='background:#f8f9fa; color:#adb5bd; font-size:11px;' title='" . htmlspecialchars($wTitle) . "'>-</td>";
                                continue;
                            }

                            // Event / Holiday
                            if ($isEv) {
                                $cellTitle = htmlspecialchars($evTitleStr ?: 'Event / Holiday');
                                echo "<td style='background:#fff0f3; color:#e63946; font-size:11px; font-weight:bold;' title='{$cellTitle}'>E</td>";
                                if ($isOffDay) {
                                    continue;
                                }
                            }

                            $working++;

                            $ind = array_search($d, array_column($att, 'adate'));
                            if ($ind !== false && $att[$ind]['yn'] == 1) {
                                echo "<td class='text-success fw-bold' style='font-size:13px;'>✓</td>";
                                $present++;
                            } else {
                                echo "<td style='color:#e0e0e0;'></td>";
                            }

                        endforeach; ?>

                        <td class="fw-bold <?= ($working > 0 && ($present * 100 / $working) < 75) ? 'text-danger' : 'text-primary' ?>">
                            <?= $working > 0 ? number_format($present * 100 / $working, 1) : '0.0' ?>%
                        </td>
                    </tr>
                <?php }
            } ?>
        </tbody>
    </table>
</div>