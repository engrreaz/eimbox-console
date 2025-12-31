<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$cls = $_POST['cls'] ?? '';

echo '<option value=""></option>';

if ($cls == '')
    exit;

$q = "SELECT DISTINCT subarea
      FROM areas
      WHERE sccode='$sccode'
        AND sessionyear LIKE '%$sy%'
        AND areaname='$cls'
      ORDER BY subarea";

$r = $conn->query($q);
while ($row = $r->fetch_assoc()) {
    echo "<option value='{$row['subarea']}'>{$row['subarea']}</option>";
}
