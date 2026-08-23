<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

echo '<option value="">------</option>';

$q = "SELECT subcode, subject 
      FROM subjects 
      WHERE (sccode='$sccode' OR sccode=0) AND (sccategory='$sctype' OR sccategory='' OR sccategory IS NULL)
      ORDER BY subcode ASC, (sccode='$sccode') DESC";
$r = $conn->query($q);

$seen = [];
while($row=$r->fetch_assoc()){
    $code = $row['subcode'];
    if (isset($seen[$code])) continue;
    $seen[$code] = true;
    echo '<option value="'.$row['subcode'].'">'.$row['subcode'] . ' - ' . $row['subject'].'</option>';
}