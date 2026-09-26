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

    // 1. SEARCH STUDENTS (From `students` table)
    try {
        $sql_std = "
            SELECT s.id, s.stid, s.stnameeng, s.stnameben, s.rollno, s.guarmobile, s.mobileself, s.guarname
            FROM students s
            WHERE (s.sccode = ? OR ? = 0)
              AND (
                s.stid LIKE ?
                OR s.stnameeng LIKE ?
                OR s.stnameben LIKE ?
                OR s.rollno LIKE ?
                OR s.guarmobile LIKE ?
                OR s.mobileself LIKE ?
                OR s.fname LIKE ?
                OR s.guarname LIKE ?
              )
            ORDER BY s.id DESC
            LIMIT 15
        ";

        if ($stmt = $conn->prepare($sql_std)) {
            $stmt->bind_param('iissssssss', 
                $sccode, $sccode, 
                $search_param, $search_param, $search_param, 
                $search_param, $search_param, $search_param, 
                $search_param, $search_param
            );
            $stmt->execute();
            $res = $stmt->get_result();

            while ($row = $res->fetch_assoc()) {
                $name = !empty($row['stnameeng']) ? $row['stnameeng'] : (!empty($row['stnameben']) ? $row['stnameben'] : 'Student');
                $roll = !empty($row['rollno']) ? $row['rollno'] : '-';
                $stid = $row['stid'];
                $mobile = !empty($row['guarmobile']) ? $row['guarmobile'] : ($row['mobileself'] ?? '');
                $guardian = $row['guarname'] ?? '';

                $subtitle_parts = [];
                if ($roll !== '-') $subtitle_parts[] = "Roll: $roll";
                if (!empty($mobile)) $subtitle_parts[] = "Mob: $mobile";
                if (!empty($guardian)) $subtitle_parts[] = "Guardian: $guardian";

                $data[] = [
                    'name'     => "$name (ID: $stid)",
                    'url'      => "student-view-profile.php?id=$stid",
                    'icon'     => 'bi-person-badge',
                    'subtitle' => !empty($subtitle_parts) ? implode(' | ', $subtitle_parts) : "Student ID: $stid",
                    'meta'     => 'Student',
                    'category' => 'Students'
                ];
            }
            $stmt->close();
        }
    } catch (Throwable $e) {
        error_log("Search student query error: " . $e->getMessage());
    }

    // 2. SEARCH TEACHERS & STAFF (From `teacher` table)
    try {
        $sql_tch = "
            SELECT tid, tname, tnameb, position, mobile
            FROM teacher
            WHERE (sccode = ? OR ? = 0)
              AND (
                tid LIKE ?
                OR tname LIKE ?
                OR tnameb LIKE ?
                OR position LIKE ?
                OR mobile LIKE ?
              )
            ORDER BY ranks ASC, id ASC
            LIMIT 8
        ";

        if ($stmt = $conn->prepare($sql_tch)) {
            $stmt->bind_param('iisssss', $sccode, $sccode, $search_param, $search_param, $search_param, $search_param, $search_param);
            $stmt->execute();
            $res = $stmt->get_result();

            while ($row = $res->fetch_assoc()) {
                $tname = !empty($row['tname']) ? $row['tname'] : (!empty($row['tnameb']) ? $row['tnameb'] : 'Teacher');
                $tid = $row['tid'];
                $pos = !empty($row['position']) ? $row['position'] : 'Staff';
                $mobile = $row['mobile'] ?? '';

                $subtitle_parts = ["Designation: $pos"];
                if (!empty($mobile)) $subtitle_parts[] = "Mob: $mobile";

                $data[] = [
                    'name'     => "$tname (TID: $tid)",
                    'url'      => "teacher-view.php?id=$tid",
                    'icon'     => 'bi-person-workspace',
                    'subtitle' => implode(' | ', $subtitle_parts),
                    'meta'     => 'Teacher',
                    'category' => 'Teachers'
                ];
            }
            $stmt->close();
        }
    } catch (Throwable $e) {
        error_log("Search teacher query error: " . $e->getMessage());
    }

    // 3. SEARCH ACADEMIC SUBJECTS (From `subjects` table)
    try {
        $sql_sub = "
            SELECT DISTINCT subcode, subject, subben
            FROM subjects
            WHERE (sccode = ? OR sccode = 0 OR ? = 0)
              AND (
                subject LIKE ?
                OR subben LIKE ?
                OR subcode LIKE ?
              )
            ORDER BY subcode ASC
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
                    'meta'     => 'Subject',
                    'category' => 'Academic'
                ];
            }
            $stmt->close();
        }
    } catch (Throwable $e) {
        error_log("Search subject query error: " . $e->getMessage());
    }

    // 4. SEARCH INVENTORY ITEMS (From `inv_items` table if exists)
    try {
        $table_check = $conn->query("SHOW TABLES LIKE 'inv_items'");
        if ($table_check && $table_check->num_rows > 0) {
            $sql_inv = "
                SELECT item_code, barcode, item_name, current_stock, sale_price
                FROM inv_items
                WHERE (sccode = ? OR ? = 0)
                  AND (
                    item_name LIKE ?
                    OR item_code LIKE ?
                    OR barcode LIKE ?
                  )
                ORDER BY item_name ASC
                LIMIT 6
            ";

            if ($stmt = $conn->prepare($sql_inv)) {
                $stmt->bind_param('iisss', $sccode, $sccode, $search_param, $search_param, $search_param);
                $stmt->execute();
                $res = $stmt->get_result();

                while ($row = $res->fetch_assoc()) {
                    $item_name = $row['item_name'];
                    $item_code = $row['item_code'];
                    $stock = $row['current_stock'];
                    $price = number_format($row['sale_price'], 2);

                    $data[] = [
                        'name'     => "$item_name ($item_code)",
                        'url'      => "inventory-items.php?search=" . urlencode($item_code),
                        'icon'     => 'bi-box-seam',
                        'subtitle' => "Stock: $stock | Price: ৳$price",
                        'meta'     => 'Inventory',
                        'category' => 'Inventory'
                    ];
                }
                $stmt->close();
            }
        }
    } catch (Throwable $e) {
        error_log("Search inventory query error: " . $e->getMessage());
    }

    // 5. SEARCH CASHBOOK / TRANSACTIONS (From `cashbook` table)
    try {
        if ($is_admin >= 3 || stripos($userlevel, 'account') !== false || stripos($userlevel, 'admin') !== false) {
            $sql_cash = "
                SELECT memono, refno, particulars, amount, type, date
                FROM cashbook
                WHERE (sccode = ? OR ? = 0)
                  AND (
                    memono LIKE ?
                    OR refno LIKE ?
                    OR particulars LIKE ?
                  )
                ORDER BY id DESC
                LIMIT 6
            ";

            if ($stmt = $conn->prepare($sql_cash)) {
                $stmt->bind_param('iisss', $sccode, $sccode, $search_param, $search_param, $search_param);
                $stmt->execute();
                $res = $stmt->get_result();

                while ($row = $res->fetch_assoc()) {
                    $memo = $row['memono'];
                    $part = $row['particulars'] ?? 'Voucher';
                    $amt = number_format($row['amount'], 2);
                    $type = ucfirst($row['type'] ?? 'Cash');
                    $date = $row['date'] ?? '';

                    $data[] = [
                        'name'     => "Memo #$memo: $part",
                        'url'      => "cash-book.php?search=" . urlencode($memo),
                        'icon'     => 'bi-receipt',
                        'subtitle' => "$type | ৳$amt | Date: $date",
                        'meta'     => 'Accounts',
                        'category' => 'Finance'
                    ];
                }
                $stmt->close();
            }
        }
    } catch (Throwable $e) {
        error_log("Search cashbook query error: " . $e->getMessage());
    }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
