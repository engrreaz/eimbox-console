<?php
require_once 'config.php';
require_once 'db.php';

$id = $_GET['id'] ?? '';

$id = mysqli_real_escape_string($conn, $id);

$q1 = mysqli_query($conn, "SELECT * FROM students WHERE stid='$id'");
$q2 = mysqli_query($conn, "SELECT * FROM sessioninfo WHERE stid='$id' order by sessionyear DESC");

$st = mysqli_fetch_assoc($q1);
$si = mysqli_fetch_assoc($q2);

if (!$st) {
    echo "<div class='alert alert-danger'>Student not found.</div>";
    exit;
}

echo "
<h5>Student Information</h5>
<table class='table table-bordered'>
<tr><th>ID</th><td>{$st['stid']}</td></tr>
<tr><th>Name</th><td>{$st['stnameeng']}</td></tr>
<tr><th>Class</th><td>{$st['previll']}</td></tr>
<tr><th>Mobile</th><td>{$st['guarmobile']}</td></tr>
</table>

<h5>Session Info</h5>
<table class='table table-bordered'>
<tr><th>Session</th><td>{$si['sessionyear']}</td></tr>
<tr><th>Section</th><td>{$si['classname']}</td></tr>
<tr><th>Status</th><td>{$si['sectionname']}</td></tr>
</table>
";
