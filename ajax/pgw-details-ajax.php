
<?php
// require_once 'header.php'; // header.php loads db + config
require_once '../core/config.php';
require_once '../core/db.php';

$id         = $_POST['id'];
$stid       = $_POST['stid'];
$sccode     = $_POST['sccode'];
$session    = $_POST['sessionyear'];
$tokenid    = $_POST['tokenid'];

/* ----------------------------
   1. stpr টেবিল থেকে রশিদ ডিটেইলস
   ---------------------------- */
$sql_pr = "SELECT * FROM stpr 
           WHERE stid='$stid' 
           AND sccode='$sccode' 
           AND sessionyear='$session'";

$r_pr = $conn->query($sql_pr)->fetch_assoc();

/* ----------------------------
   2. stfinance টেবিল থেকে ফি আইটেম
   ---------------------------- */
$sql_fin = "SELECT * FROM stfinance 
            WHERE stid='$stid' 
            AND sccode='$sccode' 
            AND sessionyear='$session'";

$r_fin = $conn->query($sql_fin);

/* ----------------------------
   3. payment_pgw → gateway.token_id → bkash_token_list
   ---------------------------- */
$tokenData = "";
if (!empty($tokenid)) {
    $sql_tok = "SELECT token FROM bkash_token_list WHERE id='$tokenid'";
    $tok = $conn->query($sql_tok)->fetch_assoc();
    $tokenData = $tok['token'] ?? "";
}
?>

<div class="p-2">

    <h5 class="mb-3">Receipt Information</h5>
    <table class="table table-bordered">
        <tr><th>Student ID</th><td><?= $stid ?></td></tr>
        <tr><th>SC Code</th><td><?= $sccode ?></td></tr>
        <tr><th>Session</th><td><?= $session ?></td></tr>
        <tr><th>Receipt No</th><td><?= $r_pr['receiptno'] ?? 'N/A' ?></td></tr>
        <tr><th>Paid Date</th><td><?= $r_pr['paydate'] ?? 'N/A' ?></td></tr>
    </table>

    <h5 class="mt-4">Fee Items</h5>
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Item</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
        <?php while($f = $r_fin->fetch_assoc()): ?>
            <tr>
                <td><?= $f['particulareng'] ?></td>
                <td><?= $f['amount'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <h5 class="mt-4">Gateway Token</h5>
    <div class="alert alert-info">
        <strong>Token:</strong> <?= $tokenData ?: 'No Token Found' ?>
    </div>

</div>
