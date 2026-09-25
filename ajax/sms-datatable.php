<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

$sccode = $sccode ?? ($_SESSION['sccode'] ?? '');
if (empty($sccode)) {
    ob_clean();
    echo json_encode([
        "draw" => intval($_POST['draw'] ?? 1),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$limit  = isset($_POST['length']) && intval($_POST['length']) > 0 ? intval($_POST['length']) : 25;
$start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
$draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$search = trim($_POST['search']['value'] ?? '');
$status_filter = trim($_POST['status'] ?? '');
$type_filter = trim($_POST['sms_type'] ?? '');
$from_date = trim($_POST['from'] ?? '');
$to_date = trim($_POST['to'] ?? '');

$where = "WHERE sccode='$sccode'";

if (!empty($search)) {
    $search_esc = mysqli_real_escape_string($conn, $search);
    $where .= " AND (
        mobile_number LIKE '%$search_esc%' OR
        recipient_name LIKE '%$search_esc%' OR
        classname LIKE '%$search_esc%' OR
        campaign LIKE '%$search_esc%' OR
        sms_type LIKE '%$search_esc%' OR
        sms_text LIKE '%$search_esc%' OR
        batch_id LIKE '%$search_esc%'
    )";
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $st_esc = mysqli_real_escape_string($conn, $status_filter);
    $where .= " AND status='$st_esc'";
}

if (!empty($type_filter) && $type_filter !== 'all') {
    $tp_esc = mysqli_real_escape_string($conn, $type_filter);
    $where .= " AND sms_type='$tp_esc'";
}

if (!empty($from_date) && !empty($to_date)) {
    $f_esc = mysqli_real_escape_string($conn, $from_date);
    $t_esc = mysqli_real_escape_string($conn, $to_date);
    $where .= " AND date BETWEEN '$f_esc' AND '$t_esc'";
} elseif (!empty($from_date)) {
    $f_esc = mysqli_real_escape_string($conn, $from_date);
    $where .= " AND date >= '$f_esc'";
} elseif (!empty($to_date)) {
    $t_esc = mysqli_real_escape_string($conn, $to_date);
    $where .= " AND date <= '$t_esc'";
}

$totalQ = mysqli_query($conn, "SELECT COUNT(*) AS c FROM sms WHERE sccode='$sccode'");
$total  = ($totalQ && $row = mysqli_fetch_assoc($totalQ)) ? intval($row['c']) : 0;

$filterQ = mysqli_query($conn, "SELECT COUNT(*) AS c FROM sms $where");
$filtered = ($filterQ && $row = mysqli_fetch_assoc($filterQ)) ? intval($row['c']) : 0;

$dataQ = mysqli_query($conn, "
    SELECT id, sccode, sessionyear, recipient_type, recipient_id, recipient_name, 
           classname, sectionname, rollno, date, campaign, sms_type, mobile_number, 
           sms_text, sms_len, sms_parts, count, is_unicode, gateway_provider, 
           cost, response_code, message_id, success_message, error_message, 
           status, batch_id, send_by, send_time, delivered_time
    FROM sms 
    $where 
    ORDER BY id DESC 
    LIMIT $start, $limit
");

$data = [];
if ($dataQ) {
    while ($r = mysqli_fetch_assoc($dataQ)) {
        $data[] = $r;
    }
}

ob_clean();
echo json_encode([
    "draw" => $draw,
    "recordsTotal" => $total,
    "recordsFiltered" => $filtered,
    "data" => $data
], JSON_UNESCAPED_UNICODE);
