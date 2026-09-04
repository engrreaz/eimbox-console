<?php
/**
 * EIMBox REST API — Income & Expenditure Accounts & Auditable Financial Reports
 * Endpoint: /api/v1/finance/income-expenditure.php
 */

require_once __DIR__ . '/../bootstrap.php';

$auth = authenticate_token($conn);
$sccode = (int)($auth['sccode'] ?? 0);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'GET') {
    api_response('error', 'Only GET method is supported for financial reports', null, 405);
}

$reportType = $_GET['report_type'] ?? 'income_expenditure'; // 'daily_cashbook', 'receipts_payments', 'income_expenditure', 'head_ledger'
$sessionyear = isset($_GET['sessionyear']) ? (int)$_GET['sessionyear'] : (int)date('Y');
$fromDate = $_GET['from_date'] ?? $_GET['date_from'] ?? date('Y-01-01');
$toDate = $_GET['to_date'] ?? $_GET['date_to'] ?? date('Y-12-31');

// Fetch Institution Info
$scStmt = $conn->prepare("SELECT scname, scadd1, scadd2, ps, dist, sccode, sessionyear as active_session FROM scinfo WHERE sccode = ? LIMIT 1");
$scStmt->bind_param("i", $sccode);
$scStmt->execute();
$school = $scStmt->get_result()->fetch_assoc() ?? [
    'scname' => 'Institutional Accounts Studio',
    'scadd1' => '',
    'dist' => '',
    'sccode' => $sccode
];

switch ($reportType) {
    case 'daily_cashbook':
        $targetDate = $_GET['date'] ?? $toDate;
        
        // Opening balance before $targetDate
        $opStmt = $conn->prepare("SELECT COALESCE(SUM(income), 0) - COALESCE(SUM(expenditure), 0) as opening_bal
                                 FROM cashbook
                                 WHERE sccode = ? AND date < ? AND (status = 1 OR status IS NULL)");
        $opStmt->bind_param("is", $sccode, $targetDate);
        $opStmt->execute();
        $openingBalance = (float)($opStmt->get_result()->fetch_assoc()['opening_bal'] ?? 0);

        // Fetch day's entries
        $dStmt = $conn->prepare("SELECT cb.*, ah.head_name, ah.head_name_bn, ash.sub_head, ash.sub_head_bn, bi.bankname
                                FROM cashbook cb
                                LEFT JOIN account_head ah ON cb.account_head = ah.id
                                LEFT JOIN account_sub_head ash ON cb.account_sub_head = ash.id
                                LEFT JOIN bankinfo bi ON cb.bank_account_id = bi.id
                                WHERE cb.sccode = ? AND cb.date = ? AND (cb.status = 1 OR cb.status IS NULL)
                                ORDER BY cb.id ASC");
        $dStmt->bind_param("is", $sccode, $targetDate);
        $dStmt->execute();
        $dRes = $dStmt->get_result();

        $receipts = [];
        $payments = [];
        $totalReceipts = 0;
        $totalPayments = 0;

        while ($row = $dRes->fetch_assoc()) {
            $row['income'] = (float)$row['income'];
            $row['expenditure'] = (float)$row['expenditure'];
            $row['amount'] = (float)$row['amount'];

            if ($row['income'] > 0 || strtolower($row['type']) === 'income') {
                $totalReceipts += $row['income'] > 0 ? $row['income'] : $row['amount'];
                $receipts[] = $row;
            } else {
                $totalPayments += $row['expenditure'] > 0 ? $row['expenditure'] : $row['amount'];
                $payments[] = $row;
            }
        }

        $closingBalance = $openingBalance + $totalReceipts - $totalPayments;

        api_response('success', 'Daily cashbook generated', [
            'school' => $school,
            'report_date' => $targetDate,
            'opening_balance' => $openingBalance,
            'total_receipts' => $totalReceipts,
            'total_payments' => $totalPayments,
            'closing_balance' => $closingBalance,
            'receipts' => $receipts,
            'payments' => $payments
        ]);
        break;

    case 'receipts_payments':
    case 'income_expenditure':
        // Head & Subhead aggregated summary
        $stmt = $conn->prepare("SELECT 
                                    ah.id as head_id,
                                    COALESCE(ah.head_name, 'Uncategorized') as head_name,
                                    COALESCE(ah.head_name_bn, '') as head_name_bn,
                                    COALESCE(ah.head_type, cb.type) as head_type,
                                    ash.id as sub_head_id,
                                    COALESCE(ash.sub_head, cb.category, 'General') as sub_head_name,
                                    COALESCE(ash.sub_head_bn, '') as sub_head_bn,
                                    COALESCE(SUM(cb.income), 0) as total_income,
                                    COALESCE(SUM(cb.expenditure), 0) as total_expense
                                FROM cashbook cb
                                LEFT JOIN account_head ah ON cb.account_head = ah.id
                                LEFT JOIN account_sub_head ash ON cb.account_sub_head = ash.id
                                WHERE cb.sccode = ? AND (cb.date BETWEEN ? AND ?) AND (cb.status = 1 OR cb.status IS NULL)
                                GROUP BY cb.account_head, cb.account_sub_head, ah.head_name, ash.sub_head, cb.type
                                ORDER BY ah.display_order ASC, ah.id ASC, ash.display_order ASC");
        
        $stmt->bind_param("iss", $sccode, $fromDate, $toDate);
        $stmt->execute();
        $res = $stmt->get_result();

        $incomeSections = [];
        $expenseSections = [];
        $grandTotalIncome = 0;
        $grandTotalExpense = 0;

        while ($r = $res->fetch_assoc()) {
            $r['total_income'] = (float)$r['total_income'];
            $r['total_expense'] = (float)$r['total_expense'];

            if ($r['total_income'] > 0) {
                $headKey = $r['head_name'];
                if (!isset($incomeSections[$headKey])) {
                    $incomeSections[$headKey] = [
                        'head_name' => $r['head_name'],
                        'head_name_bn' => $r['head_name_bn'],
                        'sub_total' => 0,
                        'sub_heads' => []
                    ];
                }
                $incomeSections[$headKey]['sub_total'] += $r['total_income'];
                $incomeSections[$headKey]['sub_heads'][] = [
                    'sub_head_name' => $r['sub_head_name'],
                    'sub_head_bn' => $r['sub_head_bn'],
                    'amount' => $r['total_income']
                ];
                $grandTotalIncome += $r['total_income'];
            }

            if ($r['total_expense'] > 0) {
                $headKey = $r['head_name'];
                if (!isset($expenseSections[$headKey])) {
                    $expenseSections[$headKey] = [
                        'head_name' => $r['head_name'],
                        'head_name_bn' => $r['head_name_bn'],
                        'sub_total' => 0,
                        'sub_heads' => []
                    ];
                }
                $expenseSections[$headKey]['sub_total'] += $r['total_expense'];
                $expenseSections[$headKey]['sub_heads'][] = [
                    'sub_head_name' => $r['sub_head_name'],
                    'sub_head_bn' => $r['sub_head_bn'],
                    'amount' => $r['total_expense']
                ];
                $grandTotalExpense += $r['total_expense'];
            }
        }

        $surplusDeficit = $grandTotalIncome - $grandTotalExpense;

        api_response('success', 'Income & Expenditure statement generated', [
            'school' => $school,
            'period' => [
                'sessionyear' => $sessionyear,
                'from_date' => $fromDate,
                'to_date' => $toDate
            ],
            'income_sections' => array_values($incomeSections),
            'expense_sections' => array_values($expenseSections),
            'grand_total_income' => $grandTotalIncome,
            'grand_total_expense' => $grandTotalExpense,
            'net_surplus_deficit' => $surplusDeficit,
            'status' => ($surplusDeficit >= 0) ? 'Surplus (উদ্বৃত্ত)' : 'Deficit (ঘাটতি)'
        ]);
        break;

    case 'head_ledger':
        $headId = (int)($_GET['head_id'] ?? 0);
        $subHeadId = (int)($_GET['sub_head_id'] ?? 0);

        $sql = "SELECT cb.*, ah.head_name, ah.head_name_bn, ash.sub_head as sub_head_name, ash.sub_head_bn, bi.bankname
                FROM cashbook cb
                LEFT JOIN account_head ah ON cb.account_head = ah.id
                LEFT JOIN account_sub_head ash ON cb.account_sub_head = ash.id
                LEFT JOIN bankinfo bi ON cb.bank_account_id = bi.id
                WHERE cb.sccode = ? AND (cb.date BETWEEN ? AND ?) AND (cb.status = 1 OR cb.status IS NULL)";
        
        $params = [$sccode, $fromDate, $toDate];
        $types = "iss";

        if ($headId > 0) {
            $sql .= " AND cb.account_head = ?";
            $params[] = $headId;
            $types .= "i";
        }
        if ($subHeadId > 0) {
            $sql .= " AND cb.account_sub_head = ?";
            $params[] = $subHeadId;
            $types .= "i";
        }

        $sql .= " ORDER BY cb.date ASC, cb.id ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $ledgerEntries = [];
        $totalDr = 0;
        $totalCr = 0;

        while ($row = $res->fetch_assoc()) {
            $row['income'] = (float)$row['income'];
            $row['expenditure'] = (float)$row['expenditure'];
            $row['amount'] = (float)$row['amount'];
            $totalDr += $row['income'];
            $totalCr += $row['expenditure'];
            $ledgerEntries[] = $row;
        }

        api_response('success', 'Head ledger generated', [
            'school' => $school,
            'period' => ['from' => $fromDate, 'to' => $toDate],
            'total_income' => $totalDr,
            'total_expense' => $totalCr,
            'net_balance' => $totalDr - $totalCr,
            'entries' => $ledgerEntries
        ]);
        break;

    default:
        api_response('error', 'Invalid report type requested', null, 400);
}
