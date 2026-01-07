<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';
require_once '../core/func.php';



// GET প্যারামিটার রিড করা
$prsTXT = $_GET['prs'] ?? '';

// কমা দিয়ে আলাদা করে অ্যারে বানানো
$prs = array_filter(explode(',', $prsTXT));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Receipts</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0mm;
        }

        body {
            font-family: "Noto Sans Bengali", sans-serif;
            background-color: white;
            color: black;
        }

        .page {
            page-break-after: always;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0mm;
        }

        .receipt {
            width: 33.33%;
            border: 1px dashed gray;
            padding: 6mm;
            height:205mm;
            box-sizing: border-box;
            font-size: 12px;
        }

        .receipt h4 {
            text-align: center;
            margin: 5px 0;
        }

        .receipt table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        .receipt table th,
        .receipt table td {
            border: 1px solid gray;
            padding: 3px 6px;
            font-size: 11px;
        }

        .totals {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php
$colCount = 0; // current column in row
echo '<div class="page"><div class="receipt-row">';

foreach ($prs as $prno) {
    $prno = intval($prno);

    // stpr থেকে রিসিপ্ট মেইন ডাটা
    $sql = "SELECT * FROM stpr WHERE prno='$prno' AND sccode='$sccode' LIMIT 1";
    $result = $conn->query($sql);
    if (!$result || $result->num_rows == 0) continue;

    $row = $result->fetch_assoc();
    $stid        = $row['stid'];
    $prno        = $row['prno'];
    $prdate      = $row['prdate'];
    $amount      = $row['amount'];
    $entryby     = $row['entryby'];
    $classname   = $row['classname'];
    $sectionname = $row['sectionname'];
    $rollno      = $row['rollno'];
    $sessionyear = $row['sessionyear'];

    // stfinance থেকে রিসিপ্ট আইটেমস
    $sqlItems = "SELECT * FROM stfinance WHERE stid='$stid' AND sccode='$sccode' AND pr1no='$prno' AND pr1date='$prdate'";
    $resItems = $conn->query($sqlItems);
    $items = [];
    if ($resItems && $resItems->num_rows > 0) {
        while ($itemRow = $resItems->fetch_assoc()) {
            $items[] = [
                'particular' => $itemRow['particularben'],
                'amount' => $itemRow['pr1'] + $itemRow['pr2']
            ];
        }
    }

    // entryby থেকে profilename
    $sqlUser = "SELECT profilename FROM usersapp WHERE email='$entryby' AND sccode='$sccode' LIMIT 1";
    $resUser = $conn->query($sqlUser);
    $profileName = $resUser && $resUser->num_rows > 0 ? $resUser->fetch_assoc()['profilename'] : '';

    // Receipt HTML
    echo '<div class="receipt">';
    echo "<h4>Payment Receipt</h4>";
    echo "<b>Receipt #$prno</b><br>";
    echo "Date: " . date('d-m-Y', strtotime($prdate)) . "<br>";
    echo "Student ID: $stid<br>";
    echo "Class: $classname | Section: $sectionname | Roll: $rollno<br>";
    echo "Session: $sessionyear<br>";
    echo "Received By: $profileName<br><hr>";

    echo '<table>';
    echo '<tr><th>#</th><th>Particulars</th><th>Amount</th></tr>';
    $total = 0;
    foreach ($items as $i => $item) {
        echo '<tr>';
        echo '<td>'.($i+1).'</td>';
        echo '<td>'.$item['particular'].'</td>';
        echo '<td style="text-align:right">'.number_format($item['amount'],2).'</td>';
        echo '</tr>';
        $total += $item['amount'];
    }
    echo '<tr><th colspan="2">Total</th><th style="text-align:right">'.number_format($amount,2).'</th></tr>';
    echo '</table>';
    echo '</div>';

    $colCount++;

    // প্রতি 3টি রিসিপ্টের পরে নতুন row শুরু
    if ($colCount % 3 == 0) {
        echo '</div><div class="receipt-row">';
    }
}

// Last row empty receipts fill
$rest = 3 - ($colCount % 3);
if ($rest < 3) {
    for ($i=0; $i<$rest; $i++) echo '<div class="receipt"></div>';
}

echo '</div></div>'; // close row & page
?>

<script>
    // window.onload = function(){
    //     window.print();
    //     window.onafterprint = function(){
    //         window.close();
    //     };
    // };
</script>

</body>
</html>
