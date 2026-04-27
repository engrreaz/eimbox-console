<h6 class="fw-bold mt-4">Teachers Attendance</h6>


<?php
$tidList = [];

$date = mysqli_real_escape_string($conn, $date);
$sccode = mysqli_real_escape_string($conn, $sccode);

// main query
$sql = "
    SELECT 
        t.tname,
        t.sl,
        a.tid,
        a.realin,
        a.realout,
        a.statusin,
        a.statusout,
        a.detectin,
        a.detectout
    FROM teacherattnd a
    LEFT JOIN teacher t ON t.tid = a.tid
    WHERE a.sccode='$sccode' 
    AND a.adate='$date' 
    AND t.sccode='$sccode'
    ORDER BY t.sl, a.tid
";

$res = $conn->query($sql);

function statusColor($status, $type)
{
    $status = strtolower($status);

    if ($type == 'in') {
        return match ($status) {
            'fast' => 'success',
            'late' => 'danger',
            'early' => 'primary',
            'absent' => 'secondary',
            default => 'dark'
        };
    } else {
        return match ($status) {
            'fast' => 'danger',
            'late' => 'success',
            'early' => 'primary',
            'absent' => 'secondary',
            default => 'dark'
        };
    }
}

function detectIcon($type)
{
    return match (strtolower($type)) {
        'gps' => 'geo-alt-fill',
        'card' => 'credit-card',
        'fingerprint' => 'fingerprint',
        'manual' => 'hand-index-thumb',
        default => 'question-circle'
    };
}

?>

<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th class="small py-1">Teacher</th>
                <th class="small py-1">In Time</th>
                <th class="small py-1">Status In</th>
                <th class="small py-1">Out Time</th>
                <th class="small py-1">Status Out</th>
                <th class="small py-1">Detect In</th>
                <th class="small py-1">Detect Out</th>
            </tr>
        </thead>
        <tbody>

            <?php if ($res && $res->num_rows > 0): ?>

                <?php while ($row = $res->fetch_assoc()):
                    $tidList[] = $row['tid'];
                    ?>

                    <tr>
                        <td class="small py-1"><?= htmlspecialchars($row['tname']) ?></td>

                        <td class="small py-1"><?= $row['realin'] ?></td>

                        <td class="small py-1">
                            <span class="badge bg-<?= statusColor($row['statusin'], 'in') ?>">
                                <?= ucfirst($row['statusin']) ?>
                            </span>
                        </td>

                        <td class="small py-1"><?= $row['realout'] ?></td>

                        <td class="small py-1">
                            <span class="badge bg-<?= statusColor($row['statusout'], 'out') ?>">
                                <?= ucfirst($row['statusout']) ?>
                            </span>
                        </td>

                        <td class="small py-1">
                            <i class="bi bi-<?= detectIcon($row['detectin']) ?>"></i>
                            <?= $row['detectin'] ?>
                        </td>

                        <td class="small py-1">
                            <i class="bi bi-<?= detectIcon($row['detectout']) ?>"></i>
                            <?= $row['detectout'] ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-danger text-center">No attendance found</td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>
</div>




<!-- LEAVE  -->

<h6 class="fw-bold mt-4">Teacher Leave Records</h6>


<?php

$date = mysqli_real_escape_string($conn, $date);
$sccode = mysqli_real_escape_string($conn, $sccode);

// leave table query (range match)
$sql = "
    SELECT 
        l.tid,
        t.tname,
        l.leave_type,
        l.leave_reason,
        l.date_from,
        l.date_to
    FROM teacher_leave_app l
    LEFT JOIN teacher t ON t.tid = l.tid
    WHERE l.sccode='$sccode'
    AND l.status=1
    AND '$date' BETWEEN l.date_from AND l.date_to
    ORDER BY l.tid
";

$res = $conn->query($sql);

?>

<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th class="small py-1">Teacher</th>
                <th class="small py-1">Leave Type</th>
                <th class="small py-1">Reason</th>
                <th class="small py-1">From</th>
                <th class="small py-1">To</th>
            </tr>
        </thead>
        <tbody>

            <?php if ($res && $res->num_rows > 0): ?>

                <?php while ($row = $res->fetch_assoc()):
                    $tidList[] = $row['tid'];
                    ?>

                    <tr>
                        <td class="small py-1"><?= htmlspecialchars($row['tname']) ?></td>
                        <td class="small py-1"><?= htmlspecialchars($row['leave_type']) ?></td>
                        <td class="small py-1"><?= htmlspecialchars($row['leave_reason']) ?></td>
                        <td class="small py-1"><?= $row['date_from'] ?></td>
                        <td class="small py-1"><?= $row['date_to'] ?></td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-danger text-center">
                        No leave record found
                    </td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>
</div>


<?php

$sccode = mysqli_real_escape_string($conn, $sccode);

// all teachers
$teacherQ = $conn->query("
    SELECT tid, tname 
    FROM teacher
    WHERE sccode='$sccode' order by sl
");

// $tidList already exists (present + leave)
?>

<h6 class="fw-bold mt-4">Teachers not in records above</h6>


<div class="row mx-4">

    <?php

    while ($row = $teacherQ->fetch_assoc()) {

        $tid = $row['tid'];

        // 🔹 যদি tid list এ না থাকে
        if (!in_array($tid, $tidList)) {
            echo "<span class='col-md-4 small px-0 py-1' style='width:33%;'>" . htmlspecialchars($row['tname']) . "</span>";
        }
    } 
    ?>
</div>