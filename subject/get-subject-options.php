<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

echo '<option value="">------</option>';

$q = "SELECT subcode, subject 
      FROM subjects 
      where (sccode='$sccode' or sccode=0) AND sccategory='$sctype'
      ORDER BY subcode";
$r = $conn->query($q);

while($row=$r->fetch_assoc()){
    echo '<option value="'.$row['subcode'].'">'.$row['subcode'] . ' - ' . $row['subject'].'</option>';
}