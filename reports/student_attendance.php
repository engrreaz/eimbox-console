<h6 class="fw-bold mt-4">Students Attendance</h6>


<?php

$date = mysqli_real_escape_string($conn, $date);
$sccode = mysqli_real_escape_string($conn, $sccode);
$totalBunk = 0;
if (!empty($classList)):

    foreach ($classList as $cls):

        $classname = $cls['classname'];
        $sectionname = $cls['sectionname'];

        // attendance query
        $sql = "
            SELECT rollno, bunk
            FROM stattnd
            WHERE sccode='$sccode'
            AND adate='$date'
            AND classname='$classname'
            AND sectionname='$sectionname'
            AND yn=1
            ORDER BY rollno
        ";

        $res = $conn->query($sql);

        $rolls = [];

        if ($res && $res->num_rows > 0) {

            while ($row = $res->fetch_assoc()) {

                $roll = $row['rollno'];

                // bunk highlight
                if ($row['bunk'] == 1) {
                    $rolls[] = "<span style='color:darkorange;font-weight:bold'>{$roll}</span>";
                    $totalBunk++;
                } else {
                    $rolls[] = $roll;
                }
            }
        }

        // output only if data exists
        if (!empty($rolls)):

?>

<div class="mb-2">
    <b><?= htmlspecialchars($classname) ?> - <?= htmlspecialchars($sectionname) ?> :</b>
    <?= implode(', ', $rolls) ?> <b>= <?= count($rolls) ?> students</b>
</div>

<?php
        endif;

    endforeach;

else:
    echo "<div class='text-danger'>No class data found</div>";
endif;

echo "<div class='text-muted fw-bold'>Total Bunk: <b>$totalBunk</b></div>";
?>
<hr>