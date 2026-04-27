<h6 class="fw-bold mt-4">Teachers Attendance</h6>
<hr>

<?php

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
                <th>Teacher</th>
                <th>In Time</th>
                <th>Status In</th>
                <th>Out Time</th>
                <th>Status Out</th>
                <th>Detect In</th>
                <th>Detect Out</th>
            </tr>
        </thead>
        <tbody>

            <?php if ($res && $res->num_rows > 0): ?>

                <?php while ($row = $res->fetch_assoc()): ?>

                    <tr>
                        <td><?= htmlspecialchars($row['tname']) ?></td>

                        <td><?= $row['realin'] ?></td>

                        <td>
                            <span class="badge bg-<?= statusColor($row['statusin'], 'in') ?>">
                                <?= ucfirst($row['statusin']) ?>
                            </span>
                        </td>

                        <td><?= $row['realout'] ?></td>

                        <td>
                            <span class="badge bg-<?= statusColor($row['statusout'], 'out') ?>">
                                <?= ucfirst($row['statusout']) ?>
                            </span>
                        </td>

                        <td>
                            <i class="bi bi-<?= detectIcon($row['detectin']) ?>"></i>
                            <?= $row['detectin'] ?>
                        </td>

                        <td>
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

<h6 class="fw-bold mt-4">Teacher Leave Report</h6>
<hr>

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
    LEFT JOIN teachers t ON t.tid = l.tid
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
                <th>Teacher</th>
                <th>Leave Type</th>
                <th>Reason</th>
                <th>From</th>
                <th>To</th>
            </tr>
        </thead>
        <tbody>

            <?php if ($res && $res->num_rows > 0): ?>

                <?php while ($row = $res->fetch_assoc()): ?>

                    <tr>
                        <td><?= htmlspecialchars($row['tname']) ?></td>
                        <td><?= htmlspecialchars($row['leave_type']) ?></td>
                        <td><?= htmlspecialchars($row['leave_reason']) ?></td>
                        <td><?= $row['date_from'] ?></td>
                        <td><?= $row['date_to'] ?></td>
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