<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id = $_POST['id'] ?? '';
$slotname = $_POST['slotname'];
$merit = $_POST['merit'] ?? 'GPA';
$parents = $_POST['parents'] ?? 'FM';
$decimal = $_POST['decimal'] ?? '0';

if ($id == "") {
    // Insert
    $q = "INSERT INTO slots (sccode, slotname, merit, parents, decimal_mark)
          VALUES ('$sccode', '$slotname', '$merit', '$parents', '$decimal')";
    mysqli_query($conn, $q);
    echo "Slot Created";
} else {
    // Update
    $q = "UPDATE slots SET 
            slotname='$slotname',
            merit='$merit',
            parents='$parents', decimal_mark='$decimal'
          WHERE id='$id'";
    mysqli_query($conn, $q);
    echo "Slot Updated";
}
?>