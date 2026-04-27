<h6 class="fw-bold mt-4">SMS Report</h6>
<hr>

<?php

$date = mysqli_real_escape_string($conn, $date);
$sccode = mysqli_real_escape_string($conn, $sccode);

// query (group by sms_type)
$sql = "
    SELECT 
        sms_type,
        GROUP_CONCAT(DISTINCT mobile_number) AS mobiles,
        GROUP_CONCAT(DISTINCT stid) AS stids,
        COUNT(*) AS total_count,
        SUM(cost) AS total_cost
    FROM sms
    WHERE sccode='$sccode' 
    AND DATE(date)='$date'
    GROUP BY sms_type
";

$res = $conn->query($sql);

$grand_count = 0;
$grand_cost = 0;

if ($res && $res->num_rows > 0):

    while ($row = $res->fetch_assoc()):

        $sms_type = $row['sms_type'];
        $mobiles = $row['mobiles'];
        $stids = $row['stids'];
        $count = $row['total_count'];
        $cost = $row['total_cost'];

        $grand_count += $count;
        $grand_cost += $cost;
?>

<div class="mb-2">
    <b>Type:</b> <?= htmlspecialchars($sms_type) ?> <br>
    <b>Mobiles:</b> <?= htmlspecialchars($mobiles) ?> <br>
    <b>Student IDs:</b> <?= htmlspecialchars($stids) ?> <br>
    <b>Count:</b> <?= $count ?> , 
    <b>Cost:</b> <?= number_format($cost,2) ?>
</div>

<hr>

<?php 
    endwhile; 

else:
    echo "<div class='text-danger'>No SMS data found</div>";
endif;
?>

<!-- Grand Total -->
<div class="mt-3">
    <b>Total SMS:</b> <?= $grand_count ?> , 
    <b>Total Cost:</b> <?= number_format($grand_cost,2) ?>
</div>