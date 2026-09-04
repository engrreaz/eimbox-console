<?php
/**
 * EIMBox REST API — Cashbook Voucher Entry & Transactions
 * Endpoint: /api/v1/finance/cashbook-entry.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = (int)($auth['sccode'] ?? 0);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sessionyear = isset($_GET['sessionyear']) ? (int)$_GET['sessionyear'] : (int)date('Y');
        $fromDate = $_GET['date_from'] ?? date('Y-m-01');
        $toDate = $_GET['date_to'] ?? date('Y-m-d');
        $headId = isset($_GET['head_id']) ? (int)$_GET['head_id'] : 0;
        $type = $_GET['type'] ?? ''; // 'Income', 'Expense', or '' for all
        $paymentMethod = $_GET['payment_method'] ?? '';

        $sql = "SELECT cb.*, 
                       ah.head_name, ah.head_name_bn,
                       ash.sub_head as sub_head_name, ash.sub_head_bn,
                       bi.bankname, bi.accno as bank_accno, bi.account_title as bank_title
                FROM cashbook cb
                LEFT JOIN account_head ah ON cb.account_head = ah.id
                LEFT JOIN account_sub_head ash ON cb.account_sub_head = ash.id
                LEFT JOIN bankinfo bi ON cb.bank_account_id = bi.id
                WHERE cb.sccode = ? AND (cb.date BETWEEN ? AND ?)";
        
        $params = [$sccode, $fromDate, $toDate];
        $types = "iss";

        if ($headId > 0) {
            $sql .= " AND cb.account_head = ?";
            $params[] = $headId;
            $types .= "i";
        }
        if (!empty($type)) {
            $sql .= " AND LOWER(cb.type) = LOWER(?)";
            $params[] = $type;
            $types .= "s";
        }
        if (!empty($paymentMethod) && $paymentMethod !== 'all') {
            $sql .= " AND LOWER(cb.payment_method) = LOWER(?)";
            $params[] = $paymentMethod;
            $types .= "s";
        }

        $sql .= " ORDER BY cb.date DESC, cb.id DESC LIMIT 500";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $transactions = [];
        $totalIncome = 0;
        $totalExpense = 0;

        while ($r = $res->fetch_assoc()) {
            $r['id'] = (int)$r['id'];
            $r['income'] = (float)$r['income'];
            $r['expenditure'] = (float)$r['expenditure'];
            $r['amount'] = (float)$r['amount'];
            $r['status'] = (int)($r['status'] ?? 1);

            $totalIncome += $r['income'];
            $totalExpense += $r['expenditure'];
            $transactions[] = $r;
        }

        // Calculate opening balance before $fromDate
        $opStmt = $conn->prepare("SELECT COALESCE(SUM(income), 0) - COALESCE(SUM(expenditure), 0) as opening_bal
                                 FROM cashbook
                                 WHERE sccode = ? AND date < ? AND (status = 1 OR status IS NULL)");
        $opStmt->bind_param("is", $sccode, $fromDate);
        $opStmt->execute();
        $openingBalance = (float)($opStmt->get_result()->fetch_assoc()['opening_bal'] ?? 0);
        $closingBalance = $openingBalance + $totalIncome - $totalExpense;

        api_response('success', 'Cashbook transactions loaded', [
            'summary' => [
                'opening_balance' => $openingBalance,
                'total_income' => $totalIncome,
                'total_expenditure' => $totalExpense,
                'net_balance' => $totalIncome - $totalExpense,
                'closing_balance' => $closingBalance,
                'count' => count($transactions)
            ],
            'transactions' => $transactions
        ]);
        break;

    case 'POST':
        $data = get_api_input();

        $date = trim($data['date'] ?? date('Y-m-d'));
        $type = ucfirst(strtolower(trim($data['type'] ?? $data['voucher_type'] ?? 'Expense')));
        $accountHeadId = (int)($data['account_head'] ?? $data['account_head_id'] ?? $data['head_id'] ?? 0);
        $accountSubHeadId = (int)($data['account_sub_head'] ?? $data['sub_head_id'] ?? $data['partid'] ?? 0);
        $particulars = trim($data['particulars'] ?? $data['description'] ?? '');
        $amount = (float)($data['amount'] ?? 0);
        $sessionyear = (int)($data['sessionyear'] ?? (int)date('Y', strtotime($date)));
        $month = (int)date('n', strtotime($date));
        $year = (int)date('Y', strtotime($date));
        $slots = trim($data['slots'] ?? 'School');
        $paymentMethod = strtolower(trim($data['payment_method'] ?? 'cash'));
        $bankAccountId = !empty($data['bank_account_id']) ? (int)$data['bank_account_id'] : null;
        $chequeNo = trim($data['cheque_no'] ?? $data['chqno'] ?? '');
        $chequeDate = !empty($data['cheque_date']) ? $data['cheque_date'] : null;
        $refno = trim($data['refno'] ?? '0');
        $category = trim($data['category'] ?? '');
        $entryby = $auth['fullname'] ?? $auth['username'] ?? 'Accounts Admin';
        $status = 1;

        // Auto-generate voucher number if not supplied
        $voucherNo = trim($data['voucher_no'] ?? '');
        if (empty($voucherNo)) {
            $prefix = ($type === 'Income') ? 'REC' : 'PAY';
            $voucherNo = "VCH-" . date('Ym', strtotime($date)) . "-" . strtoupper(substr(uniqid(), -4));
        }

        if ($amount <= 0 || empty($particulars)) {
            api_response('error', 'Amount > 0 and particulars description are required', null, 400);
        }

        $income = ($type === 'Income') ? $amount : 0;
        $expenditure = ($type === 'Expense') ? $amount : 0;

        $stmt = $conn->prepare("INSERT INTO cashbook (
            sccode, sessionyear, month, year, slots, voucher_no, date, account_head, account_sub_head, 
            type, payment_method, bank_account_id, cheque_no, cheque_date, refno, category, particulars, 
            income, expenditure, amount, entryby, entrytime, module, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'CASHBOOK', ?)");

        $stmt->bind_param(
            "iiiisssiisssssssdddssi",
            $sccode, $sessionyear, $month, $year, $slots, $voucherNo, $date, $accountHeadId, $accountSubHeadId,
            $type, $paymentMethod, $bankAccountId, $chequeNo, $chequeDate, $refno, $category, $particulars,
            $income, $expenditure, $amount, $entryby, $status
        );

        if ($stmt->execute()) {
            $cashbookId = $stmt->insert_id;

            // If payment_method is bank_cheque and bank_account_id is provided, create dual-entry in banktrans
            if ($paymentMethod === 'bank_cheque' && $bankAccountId) {
                // Fetch account number
                $bStmt = $conn->prepare("SELECT accno FROM bankinfo WHERE id = ? AND sccode = ?");
                $bStmt->bind_param("ii", $bankAccountId, $sccode);
                $bStmt->execute();
                $bAcc = $bStmt->get_result()->fetch_assoc()['accno'] ?? '';

                $transtype = ($type === 'Income') ? 'Deposit' : 'Cheque Payment';
                $dep = ($type === 'Income') ? $amount : 0;
                $wth = ($type === 'Expense') ? $amount : 0;

                $btIns = $conn->prepare("INSERT INTO banktrans (
                    sccode, accid, accno, date, transtype, voucher_no, cashbook_id, account_head, account_sub_head, 
                    deposit, withdraw, amount, particulareng, chqno, chqdate, entryby, entrytime, verified
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)");
                
                $btIns->bind_param("iissssiiiddsssss", $sccode, $bankAccountId, $bAcc, $date, $transtype, $voucherNo, $cashbookId, $accountHeadId, $accountSubHeadId, $dep, $wth, $amount, $particulars, $chequeNo, $chequeDate, $entryby);
                $btIns->execute();
            }

            api_response('success', 'Voucher transaction recorded successfully', [
                'id' => $cashbookId,
                'voucher_no' => $voucherNo,
                'date' => $date,
                'amount' => $amount,
                'type' => $type
            ]);
        } else {
            api_response('error', 'Failed to save cashbook record: ' . $conn->error, null, 500);
        }
        break;

    case 'PUT':
        $data = get_api_input();
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            api_response('error', 'Valid transaction ID is required', null, 400);
        }

        $particulars = trim($data['particulars'] ?? '');
        $accountHead = (int)($data['account_head'] ?? 0);
        $accountSubHead = (int)($data['account_sub_head'] ?? 0);
        $approvedBy = trim($data['approved_by'] ?? '');
        $status = isset($data['status']) ? (int)$data['status'] : 1;

        $upd = $conn->prepare("UPDATE cashbook SET particulars = ?, account_head = ?, account_sub_head = ?, approved_by = ?, status = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $upd->bind_param("siisiii", $particulars, $accountHead, $accountSubHead, $approvedBy, $status, $id, $sccode);
        $upd->execute();

        api_response('success', 'Transaction updated successfully');
        break;

    case 'DELETE':
        $id = (int)($_GET['id'] ?? (get_api_input()['id'] ?? 0));
        if (!$id) {
            api_response('error', 'Valid transaction ID is required', null, 400);
        }

        // Soft delete / void cashbook and related banktrans
        $upd = $conn->prepare("UPDATE cashbook SET status = 2, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $upd->bind_param("ii", $id, $sccode);
        $upd->execute();

        $bUpd = $conn->prepare("DELETE FROM banktrans WHERE cashbook_id = ? AND sccode = ?");
        $bUpd->bind_param("ii", $id, $sccode);
        $bUpd->execute();

        api_response('success', 'Transaction voided / deleted successfully');
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
