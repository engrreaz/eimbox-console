<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$id = intval($_POST['id'] ?? 0);
$sub = intval($_POST['sub'] ?? 0);
$tid = intval($_POST['tid'] ?? 0);
$year = $_POST['year'] ?? '';
$cls = $_POST['cls'] ?? '';
$sec = $_POST['sec'] ?? '';
$slot = $_POST['u'] ?? '';

if (!$sub)
  exit('Subject required');
if (!$cls || !$sec || !$year)
  exit('Invalid class info');

// prevent duplicate subject in same class/section/year
$dup = $conn->query("
  SELECT id FROM subsetup
  WHERE subject='$sub'
  AND classname='$cls'
  AND sectionname='$sec'
  AND sessionyear='$year'
  AND slot='$slot'
  LIMIT 1
");

if ($dup->num_rows && $id == 0) {
  exit('Subject already exists');
}

if ($id == 0) {
  // ---------------- ADD ----------------
  $slq = $conn->query("
    SELECT IFNULL(MAX(slno),0)+1 sl 
    FROM subsetup
    WHERE classname='$cls'
    AND sectionname='$sec'
    AND sessionyear='$year'
    AND slot='$slot'
  ");
  $sl = $slq->fetch_assoc()['sl'];

  $q = "
    INSERT INTO subsetup
    (subject, tid, classname, sectionname, sessionyear, slot, slno, sccode)
    VALUES
    ('$sub','$tid','$cls','$sec','$year','$slot','$sl', '$sccode')
  ";

} else {
  // ---------------- UPDATE ----------------
  $q = "
    UPDATE subsetup SET
      subject='$sub',
      tid='$tid'
    WHERE id='$id'
    LIMIT 1
  ";
}

if ($conn->query($q)) {
  echo 'OK';
} else {
  echo 'DB Error';
}