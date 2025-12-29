<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$fid = intval($_POST['fid']);

$q = $conn->query("
  SELECT COUNT(*) c
  FROM students
  WHERE sccode='$sccode'
    AND sessionyear='$sy'
");

$r = $q->fetch_assoc();
echo $r['c'];
