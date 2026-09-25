<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

$sccode = $sccode ?? ($_SESSION['sccode'] ?? '');
$query = mysqli_real_escape_string($conn, trim($_POST['keyword'] ?? ''));
$type_filter = $_POST['type_filter'] ?? 'all';
$sessionyear = mysqli_real_escape_string($conn, trim($_POST['sessionyear'] ?? ''));

if (empty($sccode)) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
    exit;
}

if (strlen($query) < 1) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

$results = [];

// 1. Search Students
if ($type_filter === 'all' || $type_filter === 'students') {
    $where = "si.sccode='$sccode'";
    $order_sy = !empty($sessionyear) ? "(si.sessionyear = '$sessionyear') DESC, " : "";

    $sql = "SELECT si.stid, si.sessionyear, si.slot, si.classname, si.sectionname, si.rollno, 
                   s.stnameeng, s.stnameben, s.guarname,
                   COALESCE(NULLIF(s.guarmobile, ''), NULLIF(s.mobileself, ''), NULLIF(s.fmobile, ''), NULLIF(s.mmobile, '')) AS mobile
            FROM sessioninfo si
            LEFT JOIN students s ON si.stid = s.stid AND s.sccode = '$sccode'
            WHERE $where 
              AND (
                si.stid LIKE '%$query%' OR 
                si.rollno LIKE '%$query%' OR 
                s.stnameeng LIKE '%$query%' OR 
                s.stnameben LIKE '%$query%' OR 
                s.guarmobile LIKE '%$query%' OR 
                s.mobileself LIKE '%$query%' OR 
                s.fmobile LIKE '%$query%'
              )
            ORDER BY $order_sy si.sessionyear DESC, si.classname ASC, CAST(si.rollno AS UNSIGNED) ASC
            LIMIT 30";

    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $mobile = trim($r['mobile'] ?? '');
            $st_name = !empty($r['stnameeng']) ? $r['stnameeng'] : (!empty($r['stnameben']) ? $r['stnameben'] : 'Student');

            $results[] = [
                'id' => $r['stid'] ?? '',
                'name' => $st_name,
                'mobile' => $mobile,
                'type' => 'student',
                'type_label' => 'Student',
                'sessionyear' => $r['sessionyear'] ?? '',
                'classname' => $r['classname'] ?? '',
                'sectionname' => $r['sectionname'] ?? '',
                'rollno' => intval($r['rollno'] ?? 0),
                'meta' => ($r['sessionyear'] ? $r['sessionyear'] . ' | ' : '') . $r['classname'] . ($r['sectionname'] ? '-' . $r['sectionname'] : '') . ($r['rollno'] ? ' | Roll: ' . $r['rollno'] : '') . ($r['stid'] ? ' | ID: ' . $r['stid'] : '')
            ];
        }
    }
}

// 2. Search Teachers
if ($type_filter === 'all' || $type_filter === 'teachers') {
    $sql = "SELECT tid, tname, tnameb, mobile, position 
            FROM teacher 
            WHERE sccode='$sccode' 
              AND (tid LIKE '%$query%' OR tname LIKE '%$query%' OR tnameb LIKE '%$query%' OR mobile LIKE '%$query%' OR position LIKE '%$query%')
            ORDER BY sl ASC, id ASC 
            LIMIT 20";
    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $raw_mobile = $r['mobile'] ?? '';
            $clean_mobile = preg_replace('/[^0-9]/', '', $raw_mobile);
            $t_name = !empty($r['tname']) ? $r['tname'] : (!empty($r['tnameb']) ? $r['tnameb'] : 'Teacher');

            $results[] = [
                'id' => $r['tid'] ?? '',
                'name' => $t_name,
                'mobile' => $clean_mobile,
                'type' => 'teacher',
                'type_label' => 'Teacher',
                'sessionyear' => '',
                'classname' => $r['position'] ?? 'Teacher',
                'sectionname' => '',
                'rollno' => 0,
                'meta' => ($r['position'] ?? 'Faculty') . ($r['tid'] ? ' | ID: ' . $r['tid'] : '')
            ];
        }
    }
}

// 3. Search Committee
if ($type_filter === 'all' || $type_filter === 'committee') {
    $table_check = $conn->query("SHOW TABLES LIKE 'managing_committee'");
    if ($table_check && $table_check->num_rows > 0) {
        $sql = "SELECT id, member_name, mobile, designation 
                FROM managing_committee 
                WHERE sccode='$sccode' 
                  AND (member_name LIKE '%$query%' OR mobile LIKE '%$query%' OR designation LIKE '%$query%')
                ORDER BY id ASC 
                LIMIT 20";
        $res = $conn->query($sql);
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $raw_mobile = $r['mobile'] ?? '';
                $clean_mobile = preg_replace('/[^0-9]/', '', $raw_mobile);
                $results[] = [
                    'id' => (string)$r['id'],
                    'name' => $r['member_name'] ?? 'Member',
                    'mobile' => $clean_mobile,
                    'type' => 'committee',
                    'type_label' => 'SMC Member',
                    'sessionyear' => '',
                    'classname' => $r['designation'] ?? 'Committee',
                    'sectionname' => '',
                    'rollno' => 0,
                    'meta' => $r['designation'] ?? 'SMC'
                ];
            }
        }
    }
}

echo json_encode([
    'status' => 'success',
    'total' => count($results),
    'data' => $results
]);
