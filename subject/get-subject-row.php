<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$id = intval($_GET['id']);

$q = "SELECT id, subject, tid 
      FROM subsetup 
      WHERE id='$id' 
      LIMIT 1";

$r = $conn->query($q);
echo json_encode($r->fetch_assoc());