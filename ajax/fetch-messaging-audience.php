<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/core/global_values.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $audience = $_POST['audience'] ?? 'students';
    $classname = mysqli_real_escape_string($conn, trim($_POST['classname'] ?? ''));
    $sectionname = mysqli_real_escape_string($conn, trim($_POST['sectionname'] ?? ''));
    $filter = $_POST['filter'] ?? 'all';

    if (empty($sccode)) {
        $sccode = $_SESSION['sccode'] ?? '';
    }

    if (empty($sccode)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Institution code (sccode) session not found. Please log in again.'
        ]);
        exit;
    }

    $recipients = [];

    // 1. Students Audience
    if ($audience === 'students') {
        $where = "si.sccode='$sccode'";
        if (!empty($classname)) {
            $where .= " AND si.classname='$classname'";
        }
        if (!empty($sectionname)) {
            $where .= " AND si.sectionname='$sectionname'";
        }

        $sql = "SELECT si.stid, si.classname, si.sectionname, si.rollno, 
                       s.stnameeng, s.stnameben, s.guarname,
                       COALESCE(NULLIF(s.guarmobile, ''), NULLIF(s.mobileself, ''), NULLIF(s.fmobile, ''), NULLIF(s.mmobile, '')) AS mobile
                FROM sessioninfo si
                LEFT JOIN students s ON si.stid = s.stid AND s.sccode = '$sccode'
                WHERE $where
                ORDER BY si.classname ASC, si.sectionname ASC, CAST(si.rollno AS UNSIGNED) ASC";

        $res = $conn->query($sql);
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $mobile = trim($r['mobile'] ?? '');
                if (empty($mobile)) continue;

                $st_name = !empty($r['stnameeng']) ? $r['stnameeng'] : (!empty($r['stnameben']) ? $r['stnameben'] : 'Student');

                $recipients[] = [
                    'id' => $r['stid'] ?? '',
                    'name' => $st_name,
                    'mobile' => $mobile,
                    'recipient_type' => 'guardian',
                    'classname' => $r['classname'] ?? '',
                    'sectionname' => $r['sectionname'] ?? '',
                    'rollno' => intval($r['rollno'] ?? 0),
                    'dueamount' => '0.00',
                    'paymentamount' => '0.00'
                ];
            }
        }
    }
    // 2. Teachers Audience
    else if ($audience === 'teachers') {
        $sql = "SELECT tid, tname, mobile, designation FROM teacher WHERE sccode='$sccode' ORDER BY sl ASC, id ASC";
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
        // Check if managing_committee table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'managing_committee'");
        if ($table_check && $table_check->num_rows > 0) {
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
    }

    echo json_encode([
        'status' => 'success',
        'total' => count($recipients),
        'data' => $recipients
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database Query Error: ' . $e->getMessage()
    ]);
}
