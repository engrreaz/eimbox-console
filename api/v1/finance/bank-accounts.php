<?php
/**
 * EIMBox REST API - Bank Accounts & Ledger Transactions Management
 * Endpoint: /api/v1/finance/bank-accounts.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = (int)($auth['sccode'] ?? 0);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? 'list';

        if ($action === 'ledger') {
            // Load ledger transactions for a specific bank account or all accounts
            $accId = isset($_GET['accid']) ? (int)$_GET['accid'] : 0;
            $fromDate = $_GET['date_from'] ?? date('Y-01-01');
            $toDate = $_GET['date_to'] ?? date('Y-12-31');

            $sql = "SELECT bt.*, bi.bankname, bi.accno as account_no, bi.account_title,
                           ah.head_name, ash.sub_head as sub_head_name
                    FROM banktrans bt
                    LEFT JOIN bankinfo bi ON bt.accid = bi.id
                    LEFT JOIN account_head ah ON bt.account_head = ah.id
                    LEFT JOIN account_sub_head ash ON bt.account_sub_head = ash.id
                    WHERE bt.sccode = ? AND (bt.date BETWEEN ? AND ?)";
            
            $params = [$sccode, $fromDate, $toDate];
            $types = "iss";

            if ($accId > 0) {
                $sql .= " AND bt.accid = ?";
                $params[] = $accId;
                $types .= "i";
            }
            $sql .= " ORDER BY bt.date DESC, bt.id DESC LIMIT 300";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();

            $ledger = [];
            while ($r = $res->fetch_assoc()) {
                $r['id'] = (int)$r['id'];
                $r['accid'] = (int)$r['accid'];
                $r['deposit'] = (float)$r['deposit'];
                $r['withdraw'] = (float)$r['withdraw'];
                $r['amount'] = (float)$r['amount'];
                $r['balance'] = (float)$r['balance'];
                $r['verified'] = (int)$r['verified'];
                $ledger[] = $r;
            }

            api_response('success', 'Bank ledger loaded', $ledger);
            break;
        }

        // Default list: Load all institutional bank accounts with calculated balances
        $bStmt = $conn->prepare("SELECT bi.*, 
                                        COALESCE(SUM(bt.deposit), 0) as total_deposit,
                                        COALESCE(SUM(bt.withdraw), 0) as total_withdraw
                                 FROM bankinfo bi
                                 LEFT JOIN banktrans bt ON bi.id = bt.accid AND bt.sccode = bi.sccode
                                 WHERE bi.sccode = ?
                                 GROUP BY bi.id
                                 ORDER BY bi.status DESC, bi.id ASC");
        $bStmt->bind_param("i", $sccode);
        $bStmt->execute();
        $bRes = $bStmt->get_result();

        $accounts = [];
        while ($bRow = $bRes->fetch_assoc()) {
            $bRow['id'] = (int)$bRow['id'];
            $bRow['opening_balance'] = (float)$bRow['opening_balance'];
            $bRow['total_deposit'] = (float)$bRow['total_deposit'];
            $bRow['total_withdraw'] = (float)$bRow['total_withdraw'];
            $bRow['current_balance'] = $bRow['opening_balance'] + $bRow['total_deposit'] - $bRow['total_withdraw'];
            $bRow['status'] = (int)$bRow['status'];
            $accounts[] = $bRow;
        }

        api_response('success', 'Bank accounts loaded', $accounts);
        break;

    case 'POST':
        $data = get_api_input();
        $action = $data['action'] ?? 'create_account';

        if ($action === 'create_trans' || $action === 'contra_transfer') {
            $accId = (int)($data['accid'] ?? 0);
            $transtype = trim($data['transtype'] ?? 'Deposit'); // Deposit, Withdrawal, Transfer, Charges, Interest
            $amount = (float)($data['amount'] ?? 0);
            $date = $data['date'] ?? date('Y-m-d');
            $particulars = trim($data['particulars'] ?? $data['particulareng'] ?? '');
            $chqno = trim($data['chqno'] ?? '');
            $chqdate = !empty($data['chqdate']) ? $data['chqdate'] : null;
            $voucherNo = trim($data['voucher_no'] ?? '');
            $accountHead = isset($data['account_head']) ? (int)$data['account_head'] : null;
            $accountSubHead = isset($data['account_sub_head']) ? (int)$data['account_sub_head'] : null;
            $transferAccId = isset($data['transfer_accid']) ? (int)$data['transfer_accid'] : null;
            $entryBy = $auth['fullname'] ?? $auth['username'] ?? 'Accounts Admin';

            if (!$accId || $amount <= 0) {
                api_response('error', 'Valid Bank Account and positive amount are required', null, 400);
            }

            // Fetch current account number and title
            $accStmt = $conn->prepare("SELECT accno, bankname FROM bankinfo WHERE id = ? AND sccode = ?");
            $accStmt->bind_param("ii", $accId, $sccode);
            $accStmt->execute();
            $accInfo = $accStmt->get_result()->fetch_assoc();
            $accNo = $accInfo['accno'] ?? '';

            $isDeposit = in_array(strtolower($transtype), ['deposit', 'interest', 'receive', 'govt grant']);
            $deposit = $isDeposit ? $amount : 0;
            $withdraw = $isDeposit ? 0 : $amount;

            $ins = $conn->prepare("INSERT INTO banktrans (sccode, accid, accno, date, transtype, voucher_no, account_head, account_sub_head, deposit, withdraw, amount, particulareng, chqno, chqdate, transfer_accid, entryby, entrytime, verified)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)");
            $ins->bind_param("iissssiiiddsssss", $sccode, $accId, $accNo, $date, $transtype, $voucherNo, $accountHead, $accountSubHead, $deposit, $withdraw, $amount, $particulars, $chqno, $chqdate, $transferAccId, $entryBy);
            
            if ($ins->execute()) {
                $transId = $ins->insert_id;

                // If Contra transfer to another bank account, create destination deposit
                if ($action === 'contra_transfer' && $transferAccId && $transferAccId !== $accId) {
                    $dstStmt = $conn->prepare("SELECT accno, bankname FROM bankinfo WHERE id = ? AND sccode = ?");
                    $dstStmt->bind_param("ii", $transferAccId, $sccode);
                    $dstStmt->execute();
                    $dstInfo = $dstStmt->get_result()->fetch_assoc();
                    $dstAccNo = $dstInfo['accno'] ?? '';

                    $dstType = 'Transfer In';
                    $dstParticulars = "Transfer received from {$accInfo['bankname']} ({$accNo})";
                    $dstIns = $conn->prepare("INSERT INTO banktrans (sccode, accid, accno, date, transtype, voucher_no, deposit, withdraw, amount, particulareng, chqno, transfer_accid, entryby, entrytime, verified)
                                              VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, NOW(), 1)");
                    $dstIns->bind_param("iissssddsds", $sccode, $transferAccId, $dstAccNo, $date, $dstType, $voucherNo, $amount, $amount, $dstParticulars, $chqno, $accId, $entryBy);
                    $dstIns->execute();
                }

                api_response('success', 'Bank transaction recorded successfully', ['id' => $transId]);
            } else {
                api_response('error', 'Failed to record bank transaction: ' . $conn->error, null, 500);
            }
            break;
        }

        // Default action: create_account
        $slot = trim($data['slot'] ?? 'School');
        $accNo = trim($data['accno'] ?? '');
        $accType = trim($data['acctype'] ?? 'General');
        $bankName = trim($data['bankname'] ?? '');
        $branch = trim($data['branch'] ?? '');
        $routingNo = trim($data['routing_no'] ?? '');
        $openingBalance = (float)($data['opening_balance'] ?? 0);
        $accountTitle = trim($data['account_title'] ?? "{$bankName} - {$accType} ({$accNo})");
        $status = isset($data['status']) ? (int)$data['status'] : 1;

        if (empty($accNo) || empty($bankName)) {
            api_response('error', 'Bank Name and Account Number are required', null, 400);
        }

        $insAcc = $conn->prepare("INSERT INTO bankinfo (sccode, slot, account_title, accno, acctype, bankname, branch, routing_no, opening_balance, current_balance, status, created_at)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $insAcc->bind_param("isssssssddi", $sccode, $slot, $accountTitle, $accNo, $accType, $bankName, $branch, $routingNo, $openingBalance, $openingBalance, $status);

        if ($insAcc->execute()) {
            api_response('success', 'Bank Account registered successfully', ['id' => $insAcc->insert_id]);
        } else {
            api_response('error', 'Failed to register bank account: ' . $conn->error, null, 500);
        }
        break;

    case 'PUT':
        $data = get_api_input();
        $id = (int)($data['id'] ?? 0);

        if (!$id) {
            api_response('error', 'Valid Bank Account ID is required', null, 400);
        }

        $accountTitle = trim($data['account_title'] ?? '');
        $accNo = trim($data['accno'] ?? '');
        $accType = trim($data['acctype'] ?? '');
        $bankName = trim($data['bankname'] ?? '');
        $branch = trim($data['branch'] ?? '');
        $routingNo = trim($data['routing_no'] ?? '');
        $status = (int)($data['status'] ?? 1);

        $upd = $conn->prepare("UPDATE bankinfo SET account_title = ?, accno = ?, acctype = ?, bankname = ?, branch = ?, routing_no = ?, status = ?, modifieddate = NOW() WHERE id = ? AND sccode = ?");
        $upd->bind_param("ssssssiii", $accountTitle, $accNo, $accType, $bankName, $branch, $routingNo, $status, $id, $sccode);
        $upd->execute();

        api_response('success', 'Bank Account updated successfully');
        break;

    case 'DELETE':
        $id = (int)($_GET['id'] ?? (get_api_input()['id'] ?? 0));
        if (!$id) {
            api_response('error', 'Valid ID is required', null, 400);
        }

        // Check if transactions exist
        $chk = $conn->prepare("SELECT COUNT(*) as cnt FROM banktrans WHERE accid = ? AND sccode = ?");
        $chk->bind_param("ii", $id, $sccode);
        $chk->execute();
        $cnt = $chk->get_result()->fetch_assoc()['cnt'] ?? 0;
        if ($cnt > 0) {
            api_response('error', 'Cannot delete bank account with existing transaction records. You can deactivate it instead.', null, 400);
        }

        $del = $conn->prepare("DELETE FROM bankinfo WHERE id = ? AND sccode = ?");
        $del->bind_param("ii", $id, $sccode);
        $del->execute();
        api_response('success', 'Bank Account deleted successfully');
        break;

    default:
        api_response('error', 'Method not allowed', null, 405);
}
