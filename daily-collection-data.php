<?php 

$classList = [];
$sql0x2 = "SELECT areaname, subarea from areas where  sessionyear LIKE '%$y_v2%' and (user='$rootuser' or sccode='$sccode') order by idno";
// echo $sql0x2 ;
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $classList[] = $row0x2;
    }
}


$codeList = [];
$sql0x2 = "SELECT itemcode, particulareng, particularben from financesetup where  sessionyear LIKE '%$y_v2%'  and sccode='$sccode' ";
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $codeList[] = $row0x2;
    }
}


$itemList = [];
$sql0x2 = "SELECT itemcode, max(particulareng), max(particularben), sum(pr1) as tk  from stfinance where  sessionyear LIKE '%$y_v2%'  and pr1date between '$dtf' and '$dtt' and sccode='$sccode'  group by itemcode order by itemcode ";
$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $itemList[] = $row0x2;
    }
}


$dataList = [];
$sql0x2 = "SELECT classname, sectionname, itemcode, max(particulareng), max(particularben), sum(pr1) as taka from stfinance where  sessionyear LIKE '%$y_v2%'  and pr1date between '$dtf' and '$dtt' and sccode='$sccode'  group by classname, sectionname, itemcode ";

$result0x2 = $conn->query($sql0x2);
if ($result0x2->num_rows > 0) {
    while ($row0x2 = $result0x2->fetch_assoc()) {
        $dataList[] = $row0x2;
    }
}

// echo '<pre>';
// print_r($dataList);
// echo '</pre>';
// input



// classList তোমার দেওয়া অ্যারে (already loaded)

// ======================
// REPORT-1 DATA PROCESS
// ======================

$report1 = []; // [classname][section][itemcode] = total

foreach ($classList as $cls) {

    $classname = $conn->real_escape_string($cls['areaname']);
    $sectionname = $conn->real_escape_string($cls['subarea']);

    $sql1 = "
        SELECT itemcode, SUM(pr1) AS total
        FROM stfinance
        WHERE sessionyear LIKE '%$y_v2%'
          AND pr1date BETWEEN '$dtf' AND '$dtt'
          AND sccode = '$sccode'
          AND classname = '$classname'
          AND sectionname = '$sectionname'
        GROUP BY itemcode
        order by itemcode
    ";

    $q1 = $conn->query($sql1);

    while ($row = $q1->fetch_assoc()) {
        $item = $row['itemcode'];
        $report1[$classname][$sectionname][$item] = $row['total'];
    }
}



// ======================
// REPORT-2 DATA PROCESS
// ======================

$sql2 = "
    SELECT itemcode, 
           max(particulareng) AS en, 
           max(particularben) AS bn,
           SUM(pr1) AS total
    FROM stfinance
    WHERE sessionyear LIKE '%$y_v2%'
      AND pr1date BETWEEN '$dtf' AND '$dtt'
      AND sccode = '$sccode'
    GROUP BY itemcode
    ORDER BY itemcode
";

$q2 = $conn->query($sql2);

$report2 = [];
while ($row = $q2->fetch_assoc()) {
    $report2[] = $row;
}

?>