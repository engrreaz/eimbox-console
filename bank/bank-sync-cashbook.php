<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$cur    = date("Y-m-d H:i:s");

// ============================================
// STEP–1: FETCH ALL VERIFIED BANK TRANSACTIONS
// ============================================
$sql = "SELECT * FROM banktrans 
        WHERE sccode='$sccode' AND verified=1 
        ORDER BY id ASC";

$qr = mysqli_query($conn, $sql);

while ($t = mysqli_fetch_assoc($qr)) {

    $id     = $t['id'];
    $accno  = $t['accno'];
    $date   = $t['date'];
    $type   = $t['transtype'];
    $amt    = $t['amount'];
    $partid = $t['partid'];
    $etime  = $t['entrytime'];

    // --------------------------------------------
    // STEP–2: REFNO GENERATION (Stable + Unique)
    // --------------------------------------------
    $refno = $sccode . date('YmdHis', strtotime($etime));
    mysqli_query($conn, "UPDATE banktrans SET refno='$refno' WHERE id='$id'");

    // --------------------------------------------
    // STEP–3: SESSION + MONTH/YEAR MAPPING
    // --------------------------------------------
    $month = date('m', strtotime($date));
    $year  = date('Y', strtotime($date));
    $slot  = "School";

    // --------------------------------------------
    // STEP–4: TRANSACTION TYPE MAP → Income/Expenditure
    // --------------------------------------------
    if ($type == "Deposit") {
        $category = "Deposit";
        $tipe     = "Expenditure";
        $pid      = 1;
        $income   = 0;
        $expense  = $amt;

    } elseif ($type == "Deduction") {
        $category = "Deduction";
        $tipe     = "Expenditure";
        $pid      = 4;
        $income   = 0;
        $expense  = $amt;

    } elseif ($type == "Interest") {
        $category = "Interest";
        $tipe     = "Income";
        $pid      = 3;
        $income   = $amt;
        $expense  = 0;

    } else { // Withdrawal
        $category = "Withdrawal";
        $tipe     = "Income";
        $pid      = 2;
        $income   = $amt;
        $expense  = 0;
    }

    $particular = "from Bank Transaction";

    // --------------------------------------------
    // STEP–5: AVOID DUPLICATE ENTRIES IN CASHBOOK
    // --------------------------------------------
    $chk = mysqli_query($conn,
        "SELECT id FROM cashbook 
         WHERE sccode='$sccode' AND refno='$refno' LIMIT 1");

    if (mysqli_num_rows($chk) == 0) {

        // ------------------------
        // INSERT MAIN CASHBOOK ROW
  
        $main = "INSERT INTO cashbook
                (sccode,sessionyear,month,year,slots,date,type,refno,
                 partid,category,memono,particulars,income,expenditure,
                 amount,entryby,entrytime,module,status)
                VALUES
                ('$sccode','$year','$month','$year','$slot','$date',
                 '$tipe','$refno','$pid','$category','0','$particular',
                 '$income','$expense','$amt','System-Auto','$cur','BANK','1')";

        mysqli_query($conn, $main);

    }

    // --------------------------------------------
    // STEP–6: SPECIAL CASE → Withdrawal Extra Part
    // partid != 5 → system auto double entry
    // --------------------------------------------
    if (($type == "Withdraw" || $type == "Withdrawal") && $partid != 5) {

        $extra_income  = 0;
        $extra_expense = $amt;

        $extra = "INSERT INTO cashbook
                (sccode,sessionyear,month,year,slots,date,type,refno,
                 partid,category,memono,particulars,income,expenditure,
                 amount,entryby,entrytime,module,status)
                VALUES
                ('$sccode','$year','$month','$year','$slot','$date',
                 '$tipe','$refno','$partid','$category','0','$particular',
                 '$extra_income','$extra_expense','$amt',
                 'System-Auto','$cur','BANK','1')";

        mysqli_query($conn, $extra);
    }
}

echo "<div class='alert alert-success'>Cashbook Sync Completed Successfully.</div>";
?>
