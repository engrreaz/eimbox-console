<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$fid = intval($_POST['fid']);
$offset = intval($_POST['offset']);

$stq = $conn->query("
  SELECT id, class, section
  FROM students
  WHERE sccode='$sccode'
    AND sessionyear='$sy'
  LIMIT $offset,1
");

if(!$stq->num_rows){
  echo 'done';
  exit;
}

$st = $stq->fetch_assoc();

/* get amount */
$aq = $conn->query("
 SELECT amount
 FROM finance_amounts
 WHERE sccode='$sccode'
   AND sessionyear='$sy'
   AND fid='$fid'
   AND class='{$st['class']}'
   AND section='{$st['section']}'
 LIMIT 1
");

if(!$aq->num_rows){
  echo 'skip';
  exit;
}

$amt = $aq->fetch_assoc()['amount'];

/* insert / update */
$conn->query("
 INSERT INTO student_fees
 (student_id,sccode,sessionyear,fid,class,section,amount)
 VALUES
 ('{$st['id']}','$sccode','$sy','$fid','{$st['class']}','{$st['section']}','$amt')
 ON DUPLICATE KEY UPDATE amount='$amt'
");

echo 'ok';
