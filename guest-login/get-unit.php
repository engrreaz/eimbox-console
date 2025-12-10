<?php
require_once "../core/config.php";
require_once "../core/db.php";

db_connect();

$sccode = $_POST['sccode'] ?? '';

if(empty($sccode)){
    echo "<option value=''>Select Unit</option>";
    exit;
}

// ইউনিক ইউনিট লিস্ট
$sql = "SELECT DISTINCT slotname FROM slots WHERE sccode='$sccode' ORDER BY slotname ASC";
$result = mysqli_query($conn, $sql);

echo "<option value=''>Select Unit</option>";
while($row = mysqli_fetch_assoc($result)){
    echo "<option value='{$row['slotname']}'>{$row['slotname']}</option>";
}
