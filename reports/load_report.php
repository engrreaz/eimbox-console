<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'] ?? '';
$date = $_POST['date'] ?? '';
$sessionyear = $_POST['sessionyear'] ?? date('Y');

?>

<div class="card">
  <div class="card-body">

    <?php


    // নিরাপদ করা
    $sccode = mysqli_real_escape_string($conn, $sccode);
    $slot = mysqli_real_escape_string($conn, $slot);
    $sessionyear = mysqli_real_escape_string($conn, $sessionyear);

    // query
    $sql1 = "
    SELECT 
        areaname AS classname, 
        subarea AS sectionname 
    FROM areas 
    WHERE sccode='$sccode' 
    AND slot='$slot' 
    AND sessionyear='$sessionyear' 
    ORDER BY idno
";
    $areaQ = $conn->query($sql1);

    $classList = [];
    // check + fetch
    if ($areaQ && $areaQ->num_rows > 0) {

      while ($row = $areaQ->fetch_assoc()) {
        $classList[] = $row;
      }

    } else {
      echo "<div class='text-danger'>No data found</div>";
    }

    // var_dump($classList);
    
    include 'events.php';
    // include 'teacher_attendance.php';
    // include 'student_attendance.php';
    include 'student_performance.php';
    // include 'collection_summary.php';
    // include 'student_collection.php';
    include 'payment_gateway.php';
    // include 'bank_transaction.php';
    include ' expense_report.php';
    // include 'sms_report.php';
    ?>

  </div>
</div>