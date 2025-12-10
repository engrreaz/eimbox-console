<?php
require_once "../core/config.php";
require_once "../core/db.php";

db_connect();

// POST থেকে ডেটা নেওয়া
$sccode = $_POST['sccode'] ?? '';
$classname = $_POST['class'] ?? '';

if(empty($sccode) || empty($classname)){
    echo "<option value=''>Select Section</option>";
    exit;
}

// ইউনিক Section লিস্ট
$sql = "SELECT DISTINCT subarea 
        FROM areas 
        WHERE sccode='$sccode' AND areaname='$classname'
        ORDER BY subarea ASC";

$result = mysqli_query($conn, $sql);

echo "<option value=''>Select Section</option>";
while($row = mysqli_fetch_assoc($result)){
    echo "<option value='{$row['subarea']}'>{$row['subarea']}</option>";
}
