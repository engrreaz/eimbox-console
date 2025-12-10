<?php
require_once "../core/config.php";
require_once "../core/db.php";

db_connect();

$sccode = $_POST['sccode'];

$q = mysqli_query($conn, "SELECT DISTINCT sessionyear FROM sessioninfo WHERE sccode='$sccode'");
while($r = mysqli_fetch_assoc($q)){
    echo "<option value='{$r['sessionyear']}'>{$r['sessionyear']}</option>";
}
