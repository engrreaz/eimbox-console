<?php
session_start();

require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';
include_once (__DIR__) . '/templete/letter-head-01.php'; // Letter Head include



$sccode = $_GET['sccode'] ?? '';
if (!$sccode) die('School code missing');

$qry = $conn->query("SELECT * FROM registrations WHERE sccode='$sccode' ORDER BY meritplace ASC, roll_no ASC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admission List</title>
<style>
    body { font-family: Arial, sans-serif; font-size: 11px; margin: 17px; }
    h4 { text-align: center; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #333; padding: 4px; text-align: center; }
    th { background: #f0f0f0; }
</style>
</head>
<body>
<h4>Submitted Admission Forms</h4>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Class</th>
            <th>Roll</th>
            <th>Name (English)</th>
            <th>Name (বাংলা)</th>
            <th>Father</th>
            <th>Mother</th>
            <th>Mobile</th>
            <th>Mark</th>
            <th>Merit</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $i=1;
    while($row = $qry->fetch_assoc()){
      $exMark =  $row['adm_test_mark'] > 0 ? $row['adm_test_mark'] : '';
        echo "<tr>
                <td>{$i}</td>
                <td>{$row['admit_class']}</td>
                <td>{$row['roll_no']}</td>
                <td>{$row['stnameeng']}</td>
                <td>{$row['stnameben']}</td>
                <td>{$row['fname']}</td>
                <td>{$row['mname']}</td>
                <td>{$row['mnumber']}</td>
                <td>{$exMark}</td>
                <td>{$row['meritplace']}</td>
              </tr>";
        $i++;
    }
    ?>
    </tbody>
</table>

<script>
window.onload = function(){
    window.print(); // auto open print dialog
};
</script>

</body>
</html>
