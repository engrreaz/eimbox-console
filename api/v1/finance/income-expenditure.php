<?php
/**
 * EIMBox REST API — Income & Expenditure Accounts Ledger
 * Endpoint: /api/v1/finance/income-expenditure.php
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

// 1. GET: Fetch Income & Expenditure Ledger Vouchers with Metrics
if ($method === 'GET') {
    $fromDate = trim($_GET['from_date'] ?? $_GET['date'] ?? '');
    $toDate = trim($_GET['to_date'] ?? $fromDate);
    $type = trim($_GET['type'] ?? 'All');
    $category = trim($_GET['category'] ?? $_GET['cat'] ?? 'All');
    $sessionyear = trim($_GET['sessionyear'] ?? $_GET['session'] ?? '');

    $where = ["c.sccode = ?"];
    $types = "i";
    $params = [$sccode];

    if (!empty($fromDate)) {
        if (!empty($toDate) && $toDate !== $fromDate) {
            $where[] = "c.date BETWEEN ? AND ?";
            $params[] = $fromDate;
            $params[] = $toDate;
            $types .= "ss";
        } else {
            $where[] = "c.date = ?";
            $params[] = $fromDate;
            $types .= "s";
        }
    }

    if (!empty($type) && $type !== 'All') {
        $where[] = "c.type = ?";
        $params[] = $type;
        $types .= "s";
    }

    if (!empty($category) && $category !== 'All') {
        $where[] = "(c.account_head = ? OR c.particulars LIKE ?)";
        $params[] = $category;
        $params[] = "%$category%";
        $types .= "ss";
    }

    if (!empty($sessionyear) && $sessionyear !== 'All') {
        $where[] = "(c.sessionyear = ? OR c.year = ?)";
        $params[] = (int)$sessionyear;
        $params[] = (int)$sessionyear;
        $types .= "ii";
    }

    $sql = "SELECT c.id, c.sccode, c.date, c.account_head, c.partid, c.particulars, c.amount,
                   c.type, c.memono, c.month, c.year, c.entryby, c.entrytime, c.sessionyear,
                   c.payment_by, c.attachment
            FROM cashbook c
            WHERE " . implode(" AND ", $where) . "
            ORDER BY c.date DESC, c.id DESC
            LIMIT 500";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $vouchers = [];
        $totalIncome = 0;
        $totalExpense = 0;
        $byCategory = [];
        $categories = [];

        while ($r = $res->fetch_assoc()) {
            $amt = (float)$r['amount'];
            $vType = ucfirst(strtolower($r['type'] ?? 'Expense'));
            $cat = !empty($r['account_head']) ? $r['account_head'] : 'General Operations';

            if ($vType === 'Income') {
                $totalIncome += $amt;
            } else {
                $totalExpense += $amt;
            }

            if (!isset($byCategory[$cat])) {
                $byCategory[$cat] = ['income' => 0, 'expense' => 0, 'count' => 0];
            }
            if ($vType === 'Income') {
                $byCategory[$cat]['income'] += $amt;
            } else {
                $byCategory[$cat]['expense'] += $amt;
            }
            $byCategory[$cat]['count']++;

            if (!in_array($cat, $categories)) {
                $categories[] = $cat;
            }

            $vouchers[] = [
                'id' => (int)$r['id'],
                'sccode' => (int)$r['sccode'],
                'date' => $r['date'],
                'type' => $vType,
                'category' => $cat,
                'partid' => (int)($r['partid'] ?? 0),
                'particulars' => $r['particulars'],
                'amount' => $amt,
                'memono' => $r['memono'] ? (string)$r['memono'] : 'V-' . $r['id'],
                'payment_by' => !empty($r['payment_by']) ? $r['payment_by'] : 'Cash',
                'entryby' => $r['entryby'] ?: 'Admin',
                'entrytime' => $r['entrytime'] ?: date('Y-m-d H:i:s'),
                'attachment' => $r['attachment'] ?? null
            ];
        }
        $stmt->close();

        // Also fetch default distinct categories for active school
        $catStmt = $conn->prepare("SELECT DISTINCT account_head FROM cashbook WHERE sccode = ? AND account_head IS NOT NULL AND account_head != ''");
        if ($catStmt) {
            $catStmt->bind_param("i", $sccode);
            $catStmt->execute();
            $cRes = $catStmt->get_result();
            while ($cr = $cRes->fetch_assoc()) {
                if (!in_array($cr['account_head'], $categories)) {
                    $categories[] = $cr['account_head'];
                }
            }
            $catStmt->close();
        }

        $surplus = $totalIncome - $totalExpense;

        api_send_response(200, true, "Income and expenditure ledger loaded.", [
            'vouchers' => $vouchers,
            'count' => count($vouchers),
            'total_income' => $totalIncome,
            'total_expenditure' => $totalExpense,
            'net_surplus' => $surplus,
            'by_category' => $byCategory,
            'categories' => $categories
        ]);
    } else {
        api_send_response(500, false, "Database query preparation failed: " . $conn->error);
    }
}

// 2. POST: Create or Update Cashbook Voucher
if ($method === 'POST') {
    $input = get_api_input();
    $id = isset($input['id']) && (int)$input['id'] > 0 ? (int)$input['id'] : null;
    $date = trim($input['date'] ?? date('Y-m-d'));
    $type = ucfirst(strtolower(trim($input['type'] ?? 'Expense')));
    $accountHead = trim($input['category'] ?? $input['account_head'] ?? 'General Operations');
    $partid = (int)($input['partid'] ?? 0);
    $particulars = trim($input['particulars'] ?? $input['description'] ?? '');
    $amount = (float)($input['amount'] ?? 0);
    $memono = trim($input['memono'] ?? $input['voucher_no'] ?? '');
    $paymentBy = trim($input['payment_by'] ?? 'Cash');
    $sessionyear = (int)($input['sessionyear'] ?? date('Y', strtotime($date)));
    $month = (int)date('n', strtotime($date));
    $year = (int)date('Y', strtotime($date));
    $entryby = $user['profilename'] ?? $user['username'] ?? 'Admin';

    if (empty($particulars) || $amount <= 0) {
        api_send_response(400, false, "Particulars description and valid amount > 0 are required.");
    }

    if ($id) {
        $stmt = $conn->prepare("UPDATE cashbook SET
            date = ?, account_head = ?, partid = ?, particulars = ?, amount = ?, type = ?,
            memono = ?, payment_by = ?, month = ?, year = ?, sessionyear = ?
            WHERE id = ? AND sccode = ?");
        $stmt->bind_param("ssisdsssiiiii",
            $date, $accountHead, $partid, $particulars, $amount, $type,
            $memono, $paymentBy, $month, $year, $sessionyear, $id, $sccode
        );
        if ($stmt->execute()) {
            $stmt->close();
            api_send_response(200, true, "Voucher updated successfully.", ['id' => $id]);
        } else {
            $err = $stmt->error;
            $stmt->close();
            api_send_response(500, false, "Failed to update voucher: " . $err);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO cashbook (
            sccode, date, account_head, partid, particulars, amount, type,
            memono, payment_by, month, year, sessionyear, entryby, entrytime
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issisdsssiiis",
            $sccode, $date, $accountHead, $partid, $particulars, $amount, $type,
            $memono, $paymentBy, $month, $year, $sessionyear, $entryby
        );
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt->close();
            api_send_response(200, true, "New voucher recorded successfully.", ['id' => $newId]);
        } else {
            $err = $stmt->error;
            $stmt->close();
            api_send_response(500, false, "Failed to record voucher: " . $err);
        }
    }
}

// 3. DELETE: Delete Voucher
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        $input = get_api_input();
        $id = (int)($input['id'] ?? 0);
    }

    if ($id <= 0) {
        api_send_response(400, false, "Voucher ID is required for deletion.");
    }

    $stmt = $conn->prepare("DELETE FROM cashbook WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ii", $id, $sccode);
    if ($stmt->execute()) {
        $stmt->close();
        api_send_response(200, true, "Voucher deleted successfully.");
    } else {
        $err = $stmt->error;
        $stmt->close();
        api_send_response(500, false, "Failed to delete voucher: " . $err);
    }
}

api_send_response(405, false, "Method not allowed.");
