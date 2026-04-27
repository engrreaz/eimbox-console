<h6 class="fw-bold mt-4">Bank Transactions</h6>
<hr>

<?php

$date = mysqli_real_escape_string($conn, $date);
$sccode = mysqli_real_escape_string($conn, $sccode);

// query
$sql = "
    SELECT accno, transtype, particulareng, amount, balance
    FROM banktrans
    WHERE sccode='$sccode' 
    AND DATE(date)='$date'
    ORDER BY accno ASC
";

$res = $conn->query($sql);

if ($res && $res->num_rows > 0):
?>

<div class="table-responsive">
<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th class="  small py-1">Acc No</th>
            <th class="  small py-1">Type</th>
            <th class="  small py-1">Particular</th>
            <th class="text-end  small py-1">Amount</th>
            <th class="text-end  small py-1">Balance</th>
        </tr>
    </thead>
    <tbody>

<?php
    $prev_acc = '';

    while ($row = $res->fetch_assoc()):

        $accno = $row['accno'];

        // 🔹 accno change হলে blank row
        if ($prev_acc != '' && $prev_acc != $accno) {
            echo "<tr><td colspan='5'>&nbsp;</td></tr>";
        }

?>

        <tr>
            <td class="small"><?= htmlspecialchars($accno) ?></td>
            <td class="small"><?= htmlspecialchars($row['transtype']) ?></td>
            <td class="small"><?= htmlspecialchars($row['particulareng']) ?></td>
            <td class="text-end small"><?= number_format($row['amount'],2) ?></td>
            <td class="text-end small"><?= number_format($row['balance'],2) ?></td>
        </tr>

<?php
        $prev_acc = $accno;

    endwhile;
?>

    </tbody>
</table>
</div>

<?php
else:
    echo "<div class='text-danger'>No bank transaction found</div>";
endif;
?>