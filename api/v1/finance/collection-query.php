<?php
/**
 * EIMBox REST API — Collection Audit, PR Search & Dues Query
 * Endpoint: /api/v1/finance/collection-query.php
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate Request with Fallback
$user = null;
$headers = function_exists('getallheaders') ? getallheaders() : [];
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

if (!empty($authHeader)) {
    try {
        $user = api_authenticate_request();
    } catch (Exception $e) {
        // Fallback below
    }
}

$inputData = get_api_input();
$sccode = (int)($user['sccode'] ?? $_GET['sccode'] ?? $_POST['sccode'] ?? $inputData['sccode'] ?? $headers['X-School-Code'] ?? $headers['x-school-code'] ?? 0);

if ($sccode <= 0) {
    api_send_response(400, false, "Valid school institution code (sccode) is required.");
}

$conn = api_get_db_connection();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = trim($_GET['action'] ?? $inputData['action'] ?? 'search_pr');

if ($method === 'GET') {
    if ($action === 'search_pr') {
        $keyword = trim($_GET['keyword'] ?? $_GET['q'] ?? '');
        $fromDate = trim($_GET['from_date'] ?? $_GET['date'] ?? '');
        $toDate = trim($_GET['to_date'] ?? $fromDate);
        $mode = trim($_GET['mode'] ?? 'All');
        $clsname = trim($_GET['class'] ?? $_GET['clsname'] ?? 'All');
        $secname = trim($_GET['section'] ?? $_GET['secname'] ?? 'All');
        $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? '');

        $where = ["p.sccode = ?"];
        $types = "i";
        $params = [$sccode];

        if (!empty($keyword)) {
            $where[] = "(p.prno LIKE ? OR p.stid LIKE ? OR s.stnameeng LIKE ? OR s.stnameben LIKE ?)";
            $kw = "%" . $keyword . "%";
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
            $types .= "ssss";
        }

        if (!empty($fromDate)) {
            if (!empty($toDate) && $toDate !== $fromDate) {
                $where[] = "p.prdate BETWEEN ? AND ?";
                $params[] = $fromDate;
                $params[] = $toDate;
                $types .= "ss";
            } else {
                $where[] = "p.prdate = ?";
                $params[] = $fromDate;
                $types .= "s";
            }
        }

        if (!empty($mode) && $mode !== 'All') {
            $where[] = "p.collection_media = ?";
            $params[] = $mode;
            $types .= "s";
        }

        if (!empty($clsname) && $clsname !== 'All') {
            $where[] = "p.classname = ?";
            $params[] = $clsname;
            $types .= "s";
        }

        if (!empty($secname) && $secname !== 'All') {
            $where[] = "p.sectionname = ?";
            $params[] = $secname;
            $types .= "s";
        }

        if (!empty($sessionyear) && $sessionyear !== 'All') {
            $where[] = "p.sessionyear = ?";
            $params[] = (int)$sessionyear;
            $types .= "i";
        }

        $sql = "SELECT p.id, p.sccode, p.sessionyear, p.classname, p.sectionname, p.stid, p.rollno,
                       p.prno, p.prdate, p.partid, p.peng, p.pben, p.amount, p.entryby, p.entrytime,
                       p.collection_media, p.mobileno,
                       COALESCE(s.stnameeng, '') AS stnameeng,
                       COALESCE(s.stnameben, '') AS stnameben,
                       COALESCE(s.guarmobile, '') AS guarmobile
                FROM stpr p
                LEFT JOIN students s ON p.stid = s.stid AND (p.sccode = s.sccode OR s.sccode = 0)
                WHERE " . implode(" AND ", $where) . "
                ORDER BY p.prdate DESC, p.id DESC
                LIMIT 500";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();

            $receipts = [];
            $totalAmount = 0;
            $byMedia = [];
            $byCollector = [];

            while ($r = $res->fetch_assoc()) {
                $amt = (float)$r['amount'];
                $totalAmount += $amt;
                $media = !empty($r['collection_media']) ? $r['collection_media'] : 'Cash';
                $collector = !empty($r['entryby']) ? $r['entryby'] : 'Admin';

                if (!isset($byMedia[$media])) $byMedia[$media] = 0;
                $byMedia[$media] += $amt;

                if (!isset($byCollector[$collector])) $byCollector[$collector] = 0;
                $byCollector[$collector] += $amt;

                $receipts[] = [
                    'id' => (int)$r['id'],
                    'sccode' => (int)$r['sccode'],
                    'sessionyear' => (int)$r['sessionyear'],
                    'prno' => (string)$r['prno'],
                    'prdate' => $r['prdate'],
                    'stid' => (string)$r['stid'],
                    'name_eng' => $r['stnameeng'] ?: ($r['stnameben'] ?: 'Student'),
                    'name_ben' => $r['stnameben'] ?: '',
                    'classname' => $r['classname'] ?: '',
                    'sectionname' => $r['sectionname'] ?: '',
                    'rollno' => (int)$r['rollno'],
                    'particulars' => $r['peng'] ?: ($r['pben'] ?: 'Fee Collection'),
                    'amount' => $amt,
                    'mode' => $media,
                    'collector' => $collector,
                    'mobileno' => $r['mobileno'] ?: ($r['guarmobile'] ?: ''),
                    'entrytime' => $r['entrytime']
                ];
            }
            $stmt->close();

            api_send_response(200, true, "Collection receipts retrieved.", [
                'receipts' => $receipts,
                'count' => count($receipts),
                'total_amount' => $totalAmount,
                'breakdown_by_media' => $byMedia,
                'breakdown_by_collector' => $byCollector
            ]);
        } else {
            api_send_response(500, false, "Database query preparation failed: " . $conn->error);
        }
    } elseif ($action === 'dues_query') {
        $clsname = trim($_GET['class'] ?? $_GET['clsname'] ?? 'All');
        $secname = trim($_GET['section'] ?? $_GET['secname'] ?? 'All');
        $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? date('Y'));

        $where = ["f.sccode = ?", "(f.dues > 0 OR f.due > 0)"];
        $types = "i";
        $params = [$sccode];

        if (!empty($clsname) && $clsname !== 'All') {
            $where[] = "f.classname = ?";
            $params[] = $clsname;
            $types .= "s";
        }
        if (!empty($secname) && $secname !== 'All') {
            $where[] = "f.sectionname = ?";
            $params[] = $secname;
            $types .= "s";
        }
        if (!empty($sessionyear) && $sessionyear !== 'All') {
            $where[] = "f.sessionyear = ?";
            $params[] = (int)$sessionyear;
            $types .= "i";
        }

        $sql = "SELECT f.id, f.sccode, f.sessionyear, f.classname, f.sectionname, f.stid, f.rollno,
                       f.partid, f.particulars, f.amount, f.paid, COALESCE(f.dues, f.due, 0) AS due,
                       COALESCE(s.stnameeng, '') AS stnameeng,
                       COALESCE(s.stnameben, '') AS stnameben,
                       COALESCE(s.guarmobile, '') AS guarmobile
                FROM stfinance f
                LEFT JOIN students s ON f.stid = s.stid AND (f.sccode = s.sccode OR s.sccode = 0)
                WHERE " . implode(" AND ", $where) . "
                ORDER BY f.classname ASC, f.sectionname ASC, f.rollno ASC
                LIMIT 500";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();

            $duesList = [];
            $totalDue = 0;
            while ($r = $res->fetch_assoc()) {
                $dueAmt = (float)$r['due'];
                $totalDue += $dueAmt;
                $duesList[] = [
                    'id' => (int)$r['id'],
                    'stid' => (string)$r['stid'],
                    'name_eng' => $r['stnameeng'] ?: ($r['stnameben'] ?: 'Student'),
                    'name_ben' => $r['stnameben'] ?: '',
                    'classname' => $r['classname'],
                    'sectionname' => $r['sectionname'],
                    'rollno' => (int)$r['rollno'],
                    'particulars' => $r['particulars'] ?: 'Tuition Fee',
                    'payable' => (float)$r['amount'],
                    'paid' => (float)$r['paid'],
                    'due' => $dueAmt,
                    'guarmobile' => $r['guarmobile']
                ];
            }
            $stmt->close();

            api_send_response(200, true, "Student dues list retrieved.", [
                'dues' => $duesList,
                'count' => count($duesList),
                'total_due' => $totalDue
            ]);
        } else {
            api_send_response(500, false, "Database query error: " . $conn->error);
        }
    }
}

api_send_response(400, false, "Invalid request action or method.");
