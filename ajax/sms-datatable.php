<?php 
require_once "../vendor/autoload.php";
require_once "../core/config.php";
require_once "../core/db.php";
require_once "../core/global_values.php";


$columns = [
 'date','mobile_number','campaign','status','sms_text','id'
];

$limit  = intval($_POST['length']);
$start  = intval($_POST['start']);
$draw   = intval($_POST['draw']);
$search = $_POST['search']['value'];

$where = " WHERE sccode='$sccode' ";

if($search!=''){
 $where .= " AND (
   mobile_number LIKE '%$search%' OR
   campaign LIKE '%$search%' OR
   sms_text LIKE '%$search%'
 )";
}

if(!empty($_POST['from']) && !empty($_POST['to'])){
 $where .= " AND date BETWEEN '{$_POST['from']}' AND '{$_POST['to']}'";
}

$totalQ = mysqli_query($conn,"SELECT COUNT(*) c FROM sms WHERE sccode='$sccode'");
$total  = mysqli_fetch_assoc($totalQ)['c'];

$dataQ = mysqli_query($conn,"
 SELECT * FROM sms $where
 ORDER BY id DESC
 LIMIT $start,$limit
");

$data=[];
while($r=mysqli_fetch_assoc($dataQ)) $data[]=$r;

$filterQ = mysqli_query($conn,"SELECT COUNT(*) c FROM sms $where");
$filtered = mysqli_fetch_assoc($filterQ)['c'];

echo json_encode([
 "draw"=>$draw,
 "recordsTotal"=>$total,
 "recordsFiltered"=>$filtered,
 "data"=>$data
]);
