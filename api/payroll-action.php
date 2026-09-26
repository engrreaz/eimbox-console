<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/global_values.php';
require_once __DIR__ . '/../core/payroll_engine.php';

header('Content-Type: application/json; charset=utf-8');

$conn = db_connect();

$response = [
    'status' => 'error',
    'message' => 'Invalid Request'
];

if (empty($_SESSION['user_id']) || empty($sccode)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access. Please login again.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

try {
    // 1. GENERATE OR FETCH MONTHLY PAYROLL BATCH
    if ($action === 'generate_batch') {
        $month = intval($_POST['month'] ?? date('n'));
        $year = intval($_POST['year'] ?? date('Y'));
        $sessionyear = trim($_POST['sessionyear'] ?? ($sessionyear ?? date('Y')));
        $user_name = $_SESSION['user_name'] ?? ($usr ?? 'Administrator');

        if ($month < 1 || $month > 12 || $year < 2000) {
            throw new Exception("Invalid Month or Year provided.");
        }

        $batch = payroll_get_or_create_batch($conn, $sccode, $sessionyear, $month, $year, $user_name);

        echo json_encode([
            'status' => 'success',
            'message' => 'Payroll batch generated successfully!',
            'batch' => $batch
        ]);
        exit;
    }

    // 2. RECALCULATE BATCH
    if ($action === 'recalculate_batch') {
        $batch_id = intval($_POST['batch_id'] ?? 0);
        if ($batch_id <= 0) {
            throw new Exception("Invalid Batch ID.");
        }

        // Fetch batch details to get month and year
        $b_stmt = $conn->prepare("SELECT * FROM teacher_payroll_batch WHERE id = ? AND sccode = ?");
        $b_stmt->bind_param("ii", $batch_id, $sccode);
        $b_stmt->execute();
        $batch = $b_stmt->get_result()->fetch_assoc();

        if (!$batch) {
            throw new Exception("Batch not found.");
        }

        if ($batch['status'] === 'Disbursed') {
            throw new Exception("Cannot recalculate an already disbursed batch.");
        }

        // Fetch all disbursements in this batch
        $d_stmt = $conn->prepare("SELECT * FROM teacher_salary_disbursement WHERE batch_id = ? AND sccode = ?");
        $d_stmt->bind_param("ii", $batch_id, $sccode);
        $d_stmt->execute();
        $items = $d_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($items as $item) {
            $tid = $item['tid'];

            // Fetch latest salary structure
            $s_stmt = $conn->prepare("SELECT * FROM teacher_salary_structure WHERE tid = ? AND sccode = ? ORDER BY applydate DESC, id DESC LIMIT 1");
            $s_stmt->bind_param("si", $tid, $sccode);
            $s_stmt->execute();
            $struct = $s_stmt->get_result()->fetch_assoc();

            if (!$struct) {
                $t_stmt = $conn->prepare("SELECT * FROM teacher WHERE tid = ? AND sccode = ?");
                $t_stmt->bind_param("si", $tid, $sccode);
                $t_stmt->execute();
                $struct = $t_stmt->get_result()->fetch_assoc() ?: [];
            }

            // MPO figures
            $paycode       = intval($struct['paycode'] ?? 0);
            $payscale      = intval($struct['payscale'] ?? 0);
            $govt_basic    = floatval($struct['basic'] ?? 0);
            $govt_inc      = floatval($struct['incentive'] ?? 0);
            $govt_house    = floatval($struct['house'] ?? 0);
            $govt_med      = floatval($struct['medical'] ?? 0);
            $govt_arrea    = floatval($struct['arrea'] ?? 0);
            $govt_gross    = $govt_basic + $govt_inc + $govt_house + $govt_med + $govt_arrea;

            $govt_welfare  = floatval($struct['welfare'] ?? 0);
            $govt_retire   = floatval($struct['retire'] ?? 0);
            $govt_deduct   = $govt_welfare + $govt_retire;
            $govt_net      = $govt_gross - $govt_deduct;

            // School figures
            $school_basic  = floatval($struct['salary'] ?? 0);
            $school_mobile = floatval($struct['mobilevata'] ?? 0);
            $school_travel = floatval($struct['travel'] ?? 0);
            $school_med    = floatval($struct['medical2'] ?? 0);
            $school_exam   = floatval($struct['exam'] ?? 0);
            $school_fest   = floatval($struct['festival'] ?? 0);
            $school_other_al = 0.00;

            $school_pf     = floatval($struct['pf'] ?? 0);
            $school_fine   = 0.00;
            $school_adv    = 0.00;
            $school_other_ded = 0.00;

            // Adjustments
            $adj_stmt = $conn->prepare("SELECT adjustment_type, SUM(amount) as total_amt FROM teacher_salary_adjustments WHERE sccode = ? AND tid = ? AND salary_month = ? AND salary_year = ? AND status = 1 GROUP BY adjustment_type");
            $m = intval($batch['salary_month']);
            $y = intval($batch['salary_year']);
            $adj_stmt->bind_param("isii", $sccode, $tid, $m, $y);
            $adj_stmt->execute();
            $adjs = $adj_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($adjs as $adj) {
                $type = $adj['adjustment_type'];
                $amt = floatval($adj['total_amt']);
                if ($type === 'Bonus' || $type === 'CustomAllowance') {
                    $school_other_al += $amt;
                } elseif ($type === 'AbsentFine') {
                    $school_fine += $amt;
                } elseif ($type === 'AdvanceDeduction') {
                    $school_adv += $amt;
                } elseif ($type === 'CustomDeduction') {
                    $school_other_ded += $amt;
                }
            }

            $school_gross  = $school_basic + $school_mobile + $school_travel + $school_med + $school_exam + $school_fest + $school_other_al;
            $school_deduct = $school_pf + $school_fine + $school_adv + $school_other_ded;
            $school_net    = $school_gross - $school_deduct;
            $total_net     = $govt_net + $school_net;

            $upd = $conn->prepare("UPDATE teacher_salary_disbursement SET
                paycode = ?, payscale = ?, govt_basic = ?, govt_incentive = ?, govt_house = ?, govt_medical = ?, govt_arrea = ?, govt_gross = ?, govt_welfare = ?, govt_retire = ?, govt_net = ?,
                school_basic = ?, school_mobilevata = ?, school_travel = ?, school_medical = ?, school_exam = ?, school_festival = ?, school_allowances_other = ?, school_gross = ?,
                school_pf = ?, school_fine_absent = ?, school_advance_deduct = ?, school_deductions_other = ?, school_net = ?,
                total_net_payable = ?
            WHERE id = ? AND sccode = ?");

            $upd->bind_param(
                "ii" . "ddddddddd" . "dddddddd" . "ddddd" . "d" . "ii",
                $paycode, $payscale, $govt_basic, $govt_inc, $govt_house, $govt_med, $govt_arrea, $govt_gross, $govt_welfare, $govt_retire, $govt_net,
                $school_basic, $school_mobile, $school_travel, $school_med, $school_exam, $school_fest, $school_other_al, $school_gross,
                $school_pf, $school_fine, $school_adv, $school_other_ded, $school_net,
                $total_net, $item['id'], $sccode
            );
            $upd->execute();
        }

        payroll_recalculate_batch($conn, $batch_id, $sccode);

        echo json_encode([
            'status' => 'success',
            'message' => 'Payroll batch successfully recalculated!'
        ]);
        exit;
    }

    // 3. UPDATE INDIVIDUAL DISBURSEMENT ITEM
    if ($action === 'update_item') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $batch_id = intval($_POST['batch_id'] ?? 0);

        if ($item_id <= 0 || $batch_id <= 0) {
            throw new Exception("Invalid Item or Batch ID.");
        }

        // Fetch current item
        $stmt = $conn->prepare("SELECT * FROM teacher_salary_disbursement WHERE id = ? AND sccode = ?");
        $stmt->bind_param("ii", $item_id, $sccode);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();

        if (!$item) {
            throw new Exception("Disbursement item not found.");
        }

        // Allow updating custom fields
        $school_fine_absent      = isset($_POST['school_fine_absent']) ? floatval($_POST['school_fine_absent']) : floatval($item['school_fine_absent']);
        $school_advance_deduct   = isset($_POST['school_advance_deduct']) ? floatval($_POST['school_advance_deduct']) : floatval($item['school_advance_deduct']);
        $school_allowances_other = isset($_POST['school_allowances_other']) ? floatval($_POST['school_allowances_other']) : floatval($item['school_allowances_other']);
        $school_deductions_other = isset($_POST['school_deductions_other']) ? floatval($_POST['school_deductions_other']) : floatval($item['school_deductions_other']);
        $remarks                 = isset($_POST['remarks']) ? trim($_POST['remarks']) : $item['remarks'];

        $school_gross = floatval($item['school_basic']) + floatval($item['school_mobilevata']) + floatval($item['school_travel']) + floatval($item['school_medical']) + floatval($item['school_exam']) + floatval($item['school_festival']) + $school_allowances_other;
        $school_deduct = floatval($item['school_pf']) + $school_fine_absent + $school_advance_deduct + $school_deductions_other;
        $school_net = $school_gross - $school_deduct;

        $govt_net = floatval($item['govt_net']);
        $total_net_payable = $govt_net + $school_net;

        $upd = $conn->prepare("UPDATE teacher_salary_disbursement SET
            school_allowances_other = ?,
            school_gross = ?,
            school_fine_absent = ?,
            school_advance_deduct = ?,
            school_deductions_other = ?,
            school_net = ?,
            total_net_payable = ?,
            remarks = ?
        WHERE id = ? AND sccode = ?");

        $upd->bind_param(
            "dddddddsii",
            $school_allowances_other,
            $school_gross,
            $school_fine_absent,
            $school_advance_deduct,
            $school_deductions_other,
            $school_net,
            $total_net_payable,
            $remarks,
            $item_id,
            $sccode
        );
        $upd->execute();

        payroll_recalculate_batch($conn, $batch_id, $sccode);

        echo json_encode([
            'status' => 'success',
            'message' => 'Disbursement item updated successfully!'
        ]);
        exit;
    }

    // 4. ADD ADJUSTMENT (Bonus, Advance Deduction, Absent Fine)
    if ($action === 'add_adjustment') {
        $tid = trim($_POST['tid'] ?? '');
        $month = intval($_POST['month'] ?? 0);
        $year = intval($_POST['year'] ?? 0);
        $type = trim($_POST['adjustment_type'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');
        $entryby = $_SESSION['user_name'] ?? ($usr ?? 'Administrator');

        if (empty($tid) || $month < 1 || $month > 12 || $year < 2000 || $amount <= 0 || empty($type) || empty($title)) {
            throw new Exception("Please provide all required adjustment fields.");
        }

        $stmt = $conn->prepare("INSERT INTO teacher_salary_adjustments (sccode, tid, salary_month, salary_year, adjustment_type, amount, title, remarks, status, entryby) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $stmt->bind_param("isiisdsss", $sccode, $tid, $month, $year, $type, $amount, $title, $remarks, $entryby);
        $stmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Adjustment added successfully!',
            'adjustment_id' => $conn->insert_id
        ]);
        exit;
    }

    // 5. APPROVE BATCH
    if ($action === 'approve_batch') {
        $batch_id = intval($_POST['batch_id'] ?? 0);
        $approved_by = $_SESSION['user_name'] ?? ($usr ?? 'Administrator');

        if ($batch_id <= 0) {
            throw new Exception("Invalid Batch ID.");
        }

        payroll_approve_batch($conn, $batch_id, $sccode, $approved_by);

        echo json_encode([
            'status' => 'success',
            'message' => 'Payroll batch approved successfully!'
        ]);
        exit;
    }

    // 6. DISBURSE BATCH
    if ($action === 'disburse_batch') {
        $batch_id = intval($_POST['batch_id'] ?? 0);
        $payment_method = trim($_POST['payment_method'] ?? 'BankTransfer');
        $post_to_cashbook = !empty($_POST['post_to_cashbook']);
        $bank_account_id = intval($_POST['bank_account_id'] ?? 0);
        $disbursed_by = $_SESSION['user_name'] ?? ($usr ?? 'Administrator');

        if ($batch_id <= 0) {
            throw new Exception("Invalid Batch ID.");
        }

        $result = payroll_disburse_batch($conn, $batch_id, $sccode, $payment_method, $disbursed_by, $post_to_cashbook, $bank_account_id);

        if (!$result['status']) {
            throw new Exception($result['message']);
        }

        echo json_encode([
            'status' => 'success',
            'message' => $result['message'],
            'cashbook_id' => $result['cashbook_id']
        ]);
        exit;
    }

    // Default response if no matched action
    echo json_encode(['status' => 'error', 'message' => 'Unrecognized payroll action: ' . htmlspecialchars($action)]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}
