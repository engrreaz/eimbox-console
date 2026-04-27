<h6 class="fw-bold mt-4">Expense Report</h6>
<hr>

<?php

$sessionyear = mysqli_real_escape_string($conn, $sessionyear);
$sccode = mysqli_real_escape_string($conn, $sccode);
$date = mysqli_real_escape_string($conn, $date);

// query
$sql = "
            SELECT category, memono, particulars, amount
            FROM cashbook
            WHERE sessionyear='$sessionyear'
            AND sccode='$sccode'
            AND date='$date'
            AND type='Expenditure'
            AND status=1
            ORDER BY category, memono
        ";

$res = $conn->query($sql);

$total = 0;

?>

<div class="table-responsive">
    <table class="table table-sm table-bordered">

        <thead class="table-light">
            <tr>
                <th class=" small py-1">Category</th>
                <th class=" small py-1">Memo No</th>
                <th class=" small py-1">Particulars</th>
                <th class="text-end small py-1">Amount</th>
            </tr>
        </thead>

        <tbody>

            <?php if ($res && $res->num_rows > 0): ?>

                <?php while ($row = $res->fetch_assoc()):

                    $amount = (float) $row['amount'];
                    $total += $amount;

                    ?>

                    <tr>
                        <td class="small py-1"><?= htmlspecialchars($row['category']) ?></td>
                        <td class="small py-1"><?= htmlspecialchars($row['memono']) ?></td>
                        <td class="small py-1"><?= htmlspecialchars($row['particulars']) ?></td>
                        <td class="text-end small py-1"><?= number_format($amount, 2) ?></td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="4" class="text-danger text-center small py-1">
                        No expenditure found
                    </td>
                </tr>

            <?php endif; ?>

        </tbody>

        <tfoot class="table-dark">
            <tr>
                <th colspan="3" class="text-end small py-1">Total</th>
                <th class="text-end small py-1"><?= number_format($total, 2) ?></th>
            </tr>
        </tfoot>

    </table>
</div>