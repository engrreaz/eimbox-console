<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_COOKIE['chain-slot'] ?? $sctype;
$sy = $_COOKIE['chain-session'] ?? $y_v4;


echo '<option value=""></option>';

$q = "SELECT DISTINCT areaname
      FROM areas
      WHERE sccode='$sccode'
        AND sessionyear LIKE '%$sy%'
        AND slot='$slot'
      ORDER BY idno";

$r = $conn->query($q);
while ($row = $r->fetch_assoc()) {
    echo "<option value='{$row['areaname']}'>{$row['areaname']}</option>";
}
