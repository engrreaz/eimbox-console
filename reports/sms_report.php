<h6 class="fw-bold mt-4">SMS Report</h6>
<hr>
<div class="small">
<?php

$date = mysqli_real_escape_string($conn, $date);
$sccode = mysqli_real_escape_string($conn, $sccode);

// 🔹 mobile + stid wise group
$sql = "
    SELECT 
        mobile_number,
        stid,
        SUM(count) AS total_count,
        SUM(cost) AS total_cost
    FROM sms
    WHERE sccode='$sccode' 
    AND DATE(date)='$date'
    GROUP BY mobile_number, stid
";

$res = $conn->query($sql);

$grand_count = 0;
$grand_cost = 0;

$output = [];

if ($res && $res->num_rows > 0):

    while ($row = $res->fetch_assoc()):

        $mobile = $row['mobile_number'];
        $stid = $row['stid'] ?: 'N/A';
        $count = $row['total_count'];
        $cost = $row['total_cost'];

        $grand_count += $count;
        $grand_cost += $cost;

        // 🔹 format: 01xxx / stid (count) - cost
        $output[] = htmlspecialchars($mobile) . ' / ' .
                    htmlspecialchars($stid) . 
                    " ($count) - " . number_format($cost,2);

    endwhile;

    // 🔹 comma separated output
    echo implode(', ', $output);

else:
    echo "<div class='text-danger'>No SMS data found</div>";
endif;
?>


</div>
<hr>

<div class="mt-3">
    <b>Total SMS:</b> <?= $grand_count ?> , 
    <b>Total Cost:</b> <?= number_format($grand_cost,2) ?>
</div>