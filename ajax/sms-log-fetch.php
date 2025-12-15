<?php
require_once "../core/config.php";
require_once "../core/db.php";
require_once "../core/global_values.php";

$limit = 10;
$page  = isset($_POST['page']) ? intval($_POST['page']) : 1;
$start = ($page - 1) * $limit;

$where = " WHERE sccode='$sccode' ";

if (!empty($_POST['keyword'])) {
    $k = mysqli_real_escape_string($conn, $_POST['keyword']);
    $where .= " AND (mobile_number LIKE '%$k%' OR sms_text LIKE '%$k%')";
}

if (!empty($_POST['from']) && !empty($_POST['to'])) {
    $where .= " AND date BETWEEN '{$_POST['from']}' AND '{$_POST['to']}'";
}

$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM sms $where ORDER BY id DESC LIMIT $start,$limit";
$res = mysqli_query($conn,$sql);

$totalQ = mysqli_query($conn,"SELECT FOUND_ROWS() total");
$total  = mysqli_fetch_assoc($totalQ)['total'];

$data = [];
while($r=mysqli_fetch_assoc($res)){ $data[]=$r; }

echo json_encode([
    'data'=>$data,
    'total'=>$total,
    'pages'=>ceil($total/$limit)
]);
