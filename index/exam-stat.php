<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$today   = date("Y-m-d");

// --------------------------------------------
// 1️⃣ sessioninfo → class + section group count
// --------------------------------------------
$clssecCount = [];

$sql = "SELECT classname, sectionname, COUNT(*) AS total
        FROM sessioninfo
        WHERE sccode='$sccode'
          AND sessionyear LIKE '%$y_v2%'
        GROUP BY classname, sectionname";
$q = mysqli_query($conn, $sql);

while($r = mysqli_fetch_assoc($q)){
    $key = $r['classname']."|".$r['sectionname'];
    $clssecCount[$key] = $r['total'];
}

var_dump($clssecCount);
// ------------------;--------------------------
// 2️⃣ examlist → valid exam গুলো বের করা
// --------------------------------------------
$examArr = [];

$sql = "SELECT examtitle
        FROM examlist
        WHERE sccode='$sccode'
          AND datestart <= '$today'
          AND result_publish >= '$today'";
$q = mysqli_query($conn, $sql);

while($r = mysqli_fetch_assoc($q)){
    $examArr[] = $r['examtitle'];
}
var_dump($examArr);
if(empty($examArr)){
    echo "<div class='alert alert-warning'>No Active Exam Found</div>";
    exit;
}

$examIn = "'" . implode("','", $examArr) . "'";

// --------------------------------------------
// 3️⃣ examroutine → class/section বের করা
// --------------------------------------------
$sql = "SELECT clsname, secname, examname
        FROM examroutine
        WHERE examname IN ($examIn)
          AND sessionyear LIKE '%$y_v2%'";
$q = mysqli_query($conn, $sql);

$totalClassCount = 0; // মোট ক্লাস অনুযায়ী ছাত্র সংখ্যা
$totalMarkRows   = 0;

// --------------------------------------------
// 4️⃣ প্রতিটি রো অনুযায়ী sessioninfo কাউন্ট যোগ করা
// --------------------------------------------
while($r = mysqli_fetch_assoc($q)){
    $key = $r['clsname']."|".$r['secname'];

    $cnt = $clssecCount[$key] ?? 0;
    $totalClassCount += $cnt;
}

// --------------------------------------------
// 5️⃣ stmark → এই exam + sessionyear অনুযায়ী মোট রো
// --------------------------------------------
$sql = "SELECT COUNT(*) AS total
        FROM stmark
        WHERE sessionyear LIKE '%$y_v2%'
          AND exam IN ($examIn)";
$q = mysqli_query($conn, $sql);
$mr = mysqli_fetch_assoc($q);
$totalMarkRows = $mr['total'];

// --------------------------------------------
// 6️⃣ শতাংশ হিসাব
// --------------------------------------------
$percent = 0;
if($totalClassCount > 0){
    $percent = ($totalMarkRows / $totalClassCount) * 100;
}
$percent = number_format($percent, 2);

// -------------------------------
// 7️⃣ HTML আউটপুট
// -------------------------------
?>
<div class="p-3 border rounded">
    <h5 class="mb-3">Exam Statistics</h5>

    <table class="table table-sm table-bordered">
        <tr>
            <th>Total Students (Class/Section Count)</th>
            <td><?php echo $totalClassCount; ?></td>
        </tr>

        <tr>
            <th>Total Mark Entries</th>
            <td><?php echo $totalMarkRows; ?></td>
        </tr>

        <tr>
            <th>Completed (%)</th>
            <td><?php echo $percent; ?>%</td>
        </tr>
    </table>
</div>