<?php
require_once "../core/config.php";
require_once "../core/db.php";

db_connect();

$sccode = mysqli_real_escape_string($conn, $_POST['sccode'] ?? '');

/* --------------------
   Session list
-------------------- */
$sessions = [];
$q = mysqli_query($conn, "
    SELECT DISTINCT sessionyear 
    FROM sessioninfo 
    WHERE sccode='$sccode'
    ORDER BY sessionyear DESC
");

while ($r = mysqli_fetch_assoc($q)) {
    $sessions[] = $r['sessionyear'];
}

/* --------------------
   Admin data
-------------------- */
$sql = "SELECT admin_data FROM scinfo WHERE sccode='$sccode' LIMIT 1";
$q   = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($q);

$adminData = json_decode($row['admin_data'] ?? '{}', true);

/* --------------------
   Final response
-------------------- */
echo json_encode([
    'sessions'   => $sessions,
    'admin_data' => $adminData
]);
