<?php
include('../core/config.php');
include('../core/db.php');

if ($_POST['action'] == 'get_page_settings') {
  $page = $_POST['page_name'];
  $pkg = $_POST['package_id'];

  $q = $conn->prepare("SELECT * FROM package_map WHERE page_name=? AND package_id=?");
  $q->bind_param("si", $page, $pkg);
  $q->execute();
  $r = $q->get_result();
  echo $r->num_rows ? json_encode($r->fetch_assoc()) : '';

  exit;
}

if ($_POST['action'] == 'save_page_settings') {
  $page = $_POST['page_name'];
  $pkg = $_POST['package_id'];
  $access = $_POST['access'];
  $entry = $_POST['entry_limit'];
  $view = $_POST['view_limit'];
  $ttl = $_POST['total_time_limit'];
  $cnt = $_POST['access_count_limit'];
  $stay = $_POST['max_stay_limit'];
  $print = $_POST['print'];


  // আছে কিনা চেক
  $check = $conn->prepare("SELECT id FROM package_map WHERE page_name=? AND package_id=?");
  $check->bind_param("si", $page, $pkg);
  $check->execute();
  $res = $check->get_result();

  if ($res->num_rows > 0) {
    $upd = $conn->prepare("UPDATE package_map SET access=?, entry_limit=?, total_time_limit=?, access_count_limit=?, max_stay_limit=?, view_limit=?, print=? WHERE page_name=? AND package_id=?");
    $upd->bind_param("siiiiissi", $access, $entry, $ttl, $cnt, $stay, $view, $print, $page, $pkg);
    $upd->execute();
    echo "Updated successfully.";
  } else {
    $ins = $conn->prepare("INSERT INTO package_map (page_name, package_id, access, entry_limit, total_time_limit, access_count_limit, max_stay_limit, view_limit, print) VALUES (?,?,?,?,?,?,?,?,?)");
    $ins->bind_param("sisiiiiis", $page, $pkg, $access, $entry, $ttl, $cnt, $stay, $view, $print);
    $ins->execute();
    echo "Saved successfully.";
  }
  exit;
}
