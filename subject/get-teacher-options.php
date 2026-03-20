<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

echo '<option value="">------</option>';

$q = "SELECT tid, tname 
      FROM teacher 
      where sccode='$sccode'
      ORDER BY tname";
$r = $conn->query($q);

while($row=$r->fetch_assoc()){
    echo '<option value="'.$row['tid'].'">'.$row['tname'].'</option>';
}