<?php
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

header('Content-Type: application/json');

$audience = $_POST['audience'] ?? 'students';
$classname = mysqli_real_escape_string($conn, $_POST['classname'] ?? '');
$sectionname = mysqli_real_escape_string($conn, $_POST['sectionname'] ?? '');
$filter = $_POST['filter'] ?? 'all';

$recipients = [];

// 1. Students Audience
if ($audience === 'students') {
    $where = "si.sccode='$sccode' AND si.sessionyear LIKE '%$y_v2%'";
    if (!empty($classname)) {
        $where .= " AND si.classname='$classname'";
    }
    if (!empty($sectionname)) {
        $where .= " AND si.sectionname='$sectionname'";
    }

    $sql = "SELECT si.stid, si.classname, si.sectionname, si.rollno, 
                   s.stnameeng, s.stnameben, s.guarname, s.guarmobile, s.prevdue 
            FROM sessioninfo si
            LEFT JOIN students s ON si.stid = s.stid AND s.sccode = '$sccode'
            WHERE $where
            ORDER BY si.classname ASC, si.sectionname ASC, CAST(si.rollno AS UNSIGNED) ASC";

    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $mobile = trim($r['guarmobile'] ?? '');
            if (empty($mobile)) continue;

            $due = number_format(floatval($r['prevdue'] ?? 0), 2);
            if ($filter === 'dues' && floatval($r['prevdue'] ?? 0) <= 0) {
                continue; // Skip if only dues filter active and no dues
            }

            $recipients[] = [
                'id' => $r['stid'] ?? '',
                'name' => $r['stnameeng'] ?? ($r['stnameben'] ?? 'Student'),
                'mobile' => $mobile,
                'recipient_type' => 'guardian',
                'classname' => $r['classname'] ?? '',
                'sectionname' => $r['sectionname'] ?? '',
                'rollno' => intval($r['rollno'] ?? 0),
                'dueamount' => $due,
                'paymentamount' => '0.00'
            ];
        }
    }
}
// 2. Teachers Audience
else if ($audience === 'teachers') {
    $sql = "SELECT tid, tname, mobile, designation FROM teacher WHERE sccode='$sccode' AND status=1 ORDER BY sl ASC, id ASC";
    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $mobile = trim($r['mobile'] ?? '');
            if (empty($mobile)) continue;

            $recipients[] = [
                'id' => $r['tid'] ?? '',
                'name' => $r['tname'] ?? 'Teacher',
                'mobile' => $mobile,
                'recipient_type' => 'teacher',
                'classname' => $r['designation'] ?? 'Teacher',
                'sectionname' => '',
                'rollno' => 0
            ];
        }
    }
}
// 3. Committee Audience
else if ($audience === 'committee') {
    $sql = "SELECT id, member_name, mobile, designation FROM managing_committee WHERE sccode='$sccode' ORDER BY id ASC";
    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $mobile = trim($r['mobile'] ?? '');
            if (empty($mobile)) continue;

            $recipients[] = [
                'id' => (string)$r['id'],
                'name' => $r['member_name'] ?? 'Member',
                'mobile' => $mobile,
                'recipient_type' => 'committee',
                'classname' => $r['designation'] ?? 'SMC Member',
                'sectionname' => '',
                'rollno' => 0
            ];
        }
    }
}

echo json_encode([
    'status' => 'success',
    'total' => count($recipients),
    'data' => $recipients
]);
