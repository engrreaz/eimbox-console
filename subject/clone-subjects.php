<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

// ------------------- POST INPUT -------------------
$tgt_year = $_POST['tgt_year'] ?? '';
$tgt_cls = $_POST['tgt_cls'] ?? '';
$tgt_sec = $_POST['tgt_sec'] ?? '';
$tgt_slot = $_POST['tgt_slot'] ?? '';

$src_year = $_POST['src_year'] ?? '';
$src_cls = $_POST['src_cls'] ?? '';
$src_sec = $_POST['src_sec'] ?? '';
$global = $_POST['global'] ?? '';

$ids = $_POST['ids'] ?? '';
$ids_ex = $_POST['ids_ex'] ?? '';
$act = $_POST['act'] ?? 'merge';  // ✅ dollar sign বাদ

if (!$tgt_year || !$tgt_cls || !$tgt_sec) {
  exit('Invalid target');
}

// ------------------- FINAL ID ARRAY -------------------
$final_id = '';
if ($act == 'replace') {
  if ($ids_ex) {

    $ids_ex_arr = array_filter(array_map('intval', explode(',', $ids_ex))); // empty বাদ, numeric
    $ids_ex_str = implode(',', $ids_ex_arr); // properly comma join

    $q = "DELETE FROM subsetup WHERE sccode='$sccode' AND id IN ($ids_ex_str)";
    $conn->query($q);
  }
  $final_id = $ids . ',' . $ids_ex;



} elseif ($act == 'append') {

  $final_id = $ids . ',' . $ids_ex;

} else {

  $final_id = $ids;

}

// array তৈরি, empty value বাদ
$arr = array_filter(array_map('trim', explode(',', $final_id)));
// var_dump($arr);


// ------------------- COPY ROWS -------------------
foreach ($arr as $id) {

  $d = "SELECT * FROM subsetup WHERE (sccode='$sccode' OR sccode=0) AND id='$id' LIMIT 1";
  $q = $conn->query($d);
  if (!$q || !$q->num_rows)
    continue;

  $row = $q->fetch_assoc();

  // ------------------- MODIFY COLUMNS -------------------
  $row['sessionyear'] = $tgt_year;
  $row['classname'] = $tgt_cls;
  $row['sectionname'] = $tgt_sec;
  $row['slot'] = $tgt_slot;
  $row['sccode'] = $sccode;
  $row['donetime1'] = $cur;
  $row['donetime2'] = $cur;
  $row['modifieddate'] = $cur;

  // ------------------- REMOVE AUTO INCREMENT ID -------------------
  unset($row['id']);

  // ------------------- HANDLE EMPTY VALUES -------------------
  $vals_arr = [];
  foreach ($row as $v) {
    $v = trim($v);
    if ($v === '') {
      $vals_arr[] = 'NULL';
    } else {
      $vals_arr[] = "'" . $conn->real_escape_string($v) . "'";
    }
  }

  // ------------------- INSERT QUERY -------------------
  $cols = implode(',', array_keys($row));
  $vals = implode(',', $vals_arr);
  $sql = "INSERT INTO subsetup ($cols) VALUES ($vals)";
  // echo $sql . '<br>';
  $conn->query($sql);
}

echo 'OK';