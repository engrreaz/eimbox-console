<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sccode = !empty($_SESSION['sccode']) ? (int)$_SESSION['sccode'] : (int)($sccode ?? 0);
$userlevel = $_SESSION['userlevel'] ?? '';
$is_admin = $is_admin ?? 0;
$q = trim($_GET['q'] ?? '');

$data = [];

if (mb_strlen($q) >= 1) {
    $search_param = '%' . $q . '%';

    // Deep Search 1: Search Students by Guardian Name, Father, Mother, Village, NID, BRN
    try {
        $sql_std_deep = "
            SELECT s.id, s.stid, s.stnameeng, s.stnameben, s.fname, s.mname, s.guarname, s.guarmobile, s.brn, s.rollno
            FROM students s
            WHERE (s.sccode = ? OR ? = 0)
              AND (
                s.fname LIKE ?
                OR s.mname LIKE ?
                OR s.guarname LIKE ?
                OR s.brn LIKE ?
                OR s.previll LIKE ?
                OR s.uniqueid LIKE ?
              )
            ORDER BY s.id DESC
            LIMIT 10
        ";

        if ($stmt = $conn->prepare($sql_std_deep)) {
            $stmt->bind_param('iissssss', $sccode, $sccode, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
            $stmt->execute();
            $res = $stmt->get_result();

            while ($row = $res->fetch_assoc()) {
                $name = !empty($row['stnameeng']) ? $row['stnameeng'] : (!empty($row['stnameben']) ? $row['stnameben'] : 'Student');
                $stid = $row['stid'];
                $guardian = !empty($row['guarname']) ? $row['guarname'] : (!empty($row['fname']) ? $row['fname'] : '');
                $roll = !empty($row['rollno']) ? $row['rollno'] : '-';

                $data[] = [
                    'name'     => "$name (ID: $stid)",
                    'url'      => "student-view-profile.php?id=$stid",
                    'icon'     => 'bi-people',
                    'subtitle' => "Guardian: $guardian | Roll: $roll",
                    'meta'     => 'Guardian Match',
                    'category' => 'Deep Search'
                ];
            }
            $stmt->close();
        }
    } catch (Throwable $e) {
        error_log("Deep search student error: " . $e->getMessage());
    }

    // Deep Search 2: Subjects Setup
    try {
        $sql_sub = "
            SELECT DISTINCT s.subcode, s.subject, s.subben
            FROM subjects s
            WHERE (s.sccode = ? OR s.sccode = 0 OR ? = 0)
              AND (s.subject LIKE ? OR s.subben LIKE ? OR s.subcode LIKE ?)
            LIMIT 6
        ";

        if ($stmt = $conn->prepare($sql_sub)) {
            $stmt->bind_param('iisss', $sccode, $sccode, $search_param, $search_param, $search_param);
            $stmt->execute();
            $res = $stmt->get_result();

            while ($row = $res->fetch_assoc()) {
                $subname = !empty($row['subject']) ? $row['subject'] : $row['subben'];
                $code = $row['subcode'];

                $data[] = [
                    'name'     => "$subname (Code: $code)",
                    'url'      => "subjects-list.php?search=" . urlencode($code),
                    'icon'     => 'bi-journal-bookmark-fill',
                    'subtitle' => "Subject Code: $code",
                    'meta'     => 'Academic Subject',
                    'category' => 'Deep Search'
                ];
            }
            $stmt->close();
        }
    } catch (Throwable $e) {
        error_log("Deep search subject error: " . $e->getMessage());
    }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
