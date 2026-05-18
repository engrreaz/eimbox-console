<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'] ?? '';
$session = $_POST['session'] ?? '';

$date_from = $_POST['date_from'] ?? '';
$date_to = $_POST['date_to'] ?? '';

if (!$date_from || !$date_to) {
    echo "<div class='alert alert-danger'>Invalid date range</div>";
    exit;
}

// escape (mysqli)
$date_from = mysqli_real_escape_string($conn, $date_from);
$date_to = mysqli_real_escape_string($conn, $date_to);
$slot = mysqli_real_escape_string($conn, $slot);
$session = mysqli_real_escape_string($conn, $session);

$acc_head_list = [];
$sql_0 = "SELECT id, account_head_id
          FROM account_sub_head 
          WHERE sccode='$sccode' ";

echo $sql_0;

$result = mysqli_query($conn, $sql_0);

while ($row = mysqli_fetch_assoc($result)) {
    $acc_head_list[$row['id']] = $row['account_head_id'];
}

var_dump($acc_head_list);



$sub_head_list = [];

$sql_1 = "SELECT itemcode, sub_head 
          FROM financesetup 
          WHERE sccode='$sccode' 
          AND sessionyear='$session' 
          AND slot='$slot' 
          AND sub_head IS NOT NULL";

echo $sql_1;

$result = mysqli_query($conn, $sql_1);

while ($row = mysqli_fetch_assoc($result)) {
    $sub_head_list[$row['itemcode']] = $row['sub_head'];
}

var_dump($sub_head_list);

foreach ($sub_head_list as $itemcode => $sub_head) {

    $itemcode = mysqli_real_escape_string($conn, $itemcode);
    $sub_head = mysqli_real_escape_string($conn, $sub_head);

    $sql_update = "UPDATE stfinance 
                   SET sub_head = '$sub_head' 
                   WHERE itemcode = '$itemcode'
                   AND sccode = '$sccode'
                   AND sessionyear = '$session'
                   AND pr1date BETWEEN '$date_from' AND '$date_to'
                   ";
    echo $sql_update;

    mysqli_query($conn, $sql_update);
}


$sql_delete = "DELETE FROM cashbook 
               WHERE date BETWEEN '$date_from' AND '$date_to'
               AND sccode = '$sccode' AND slots='$slot'
               AND module = 'Collection'";
mysqli_query($conn, $sql_delete);


$sql = "SELECT 
            pr1date,
            itemcode,
            particulareng,
            sub_head,
            classname,
            sectionname,
            SUM(pr1) AS total_pr1
        FROM stfinance
        WHERE sccode = '$sccode'
        AND pr1date BETWEEN '$date_from' AND '$date_to'
        GROUP BY pr1date, itemcode, particulareng, sub_head, classname, sectionname";

$result = mysqli_query($conn, $sql);


while ($row = mysqli_fetch_assoc($result)) {

    $pr1date = mysqli_real_escape_string($conn, $row['pr1date']);
    $itemcode = mysqli_real_escape_string($conn, $row['itemcode']);
    $parti = mysqli_real_escape_string($conn, $row['particulareng']);
    $sub_head = mysqli_real_escape_string($conn, $row['sub_head']);
    $class = mysqli_real_escape_string($conn, $row['classname']);
    $section = mysqli_real_escape_string($conn, $row['sectionname']);
    $amount = $row['total_pr1'];

    $particular = $parti . ' - ' . $class . ' (' . $section . ')';
    $acc_head = $acc_head_list[$sub_head];

    $sql_insert = "INSERT INTO cashbook 
        (date, sccode, module, account_sub_head,  amount, slot, sessionyear, account_head, type, particulars, income, expenditure, entryby, entrytime, partid)
        VALUES 
        ('$pr1date', '$sccode', 'Collection', '$sub_head', '$amount', '$slot', '$session', '$acc_head', 'Income', '$particular', '$amount', 0, '$usr', '$cur', '$sub_head')";

    mysqli_query($conn, $sql_insert);
}






/*
01. stfinance table এর আইটেম কোড অনুযায়ী ‍অ্যাকাউন্ট হেড ম্যাপ করতে হবে। ডেট আপডেট করতে হবে।

02. তারিখ, account_head, sub_head, ক্লাস, সেকশন গ্রুপ করে মোট টাকা দিয়ে ক্যাশবুকে ডেটা ইনসার্ট করতে হবে। 
ক্যাশবুকে ইনসার্টের সময় একটা ফ্লাগ সেট করতে হবে (data from stfinance বোঝার জন্য)
আর ইনসার্ট করার আগে এই ফ্লাগ সনাক্ত করে  তারিখ অনুযায়ী আগের ডেটা রিমুভ করে দিতে হবে।
03. ..................
*/












// main query
$sql = "SELECT id, particulars, income, expenditure, amount, type, date 
        FROM cashbook 
        WHERE date BETWEEN '$date_from' AND '$date_to' AND sccode='$sccode'
        ORDER BY date ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "<div class='alert alert-danger'>Query failed</div>";
    exit;
}

$total_income = 0;
$total_expense = 0;
?>

<div class="card mt-3">
    <div class="card-body">

        <h5 class="mb-3">Report: <?= $date_from ?> to <?= $date_to ?></h5>

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>

                    <?php while ($row = mysqli_fetch_assoc($result)) {
                        if ($row['type'] == 'income') {
                            $total_income += $row['amount'];
                        } else {
                            $total_expense += $row['amount'];
                        }
                        ?>
                        <tr>
                            <td><?= $row['date'] ?></td>
                            <td><?= htmlspecialchars($row['particulars']) ?></td>
                            <td>
                                <span class="badge bg-<?= $row['type'] == 'income' ? 'success' : 'danger' ?>">
                                    <?= $row['type'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <?= number_format($row['amount'], 2) ?>
                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
            </table>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-4">
                <div class="alert alert-success p-2">
                    Total Income: <b><?= number_format($total_income, 2) ?></b>
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-danger p-2">
                    Total Expense: <b><?= number_format($total_expense, 2) ?></b>
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert alert-primary p-2">
                    Balance:
                    <b><?= number_format($total_income - $total_expense, 2) ?></b>
                </div>
            </div>
        </div>

    </div>
</div>