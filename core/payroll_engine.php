<?php
/**
 * EIMBox Teacher Payroll Engine
 * Handles monthly batch generation, computations, adjustments, disbursement, and cashbook sync.
 */

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}
if (!function_exists('db_connect')) {
    require_once __DIR__ . '/db.php';
}

/**
 * Generate or fetch monthly payroll batch for a school
 */
function payroll_get_or_create_batch($conn, $sccode, $sessionyear, $month, $year, $user = 'Admin') {
    $sccode = intval($sccode);
    $month = intval($month);
    $year = intval($year);
    $sessionyear = trim($sessionyear);

    // 1. Check if batch already exists
    $stmt = $conn->prepare("SELECT * FROM teacher_payroll_batch WHERE sccode = ? AND salary_year = ? AND salary_month = ?");
    $stmt->bind_param("iii", $sccode, $year, $month);
    $stmt->execute();
    $batch = $stmt->get_result()->fetch_assoc();

    if ($batch) {
        return $batch;
    }

    // 2. Create new Draft batch
    $month_names = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    $month_title = ($month_names[$month] ?? "Month $month") . " " . $year;
    $batch_title = "Teacher Payroll - " . $month_title;

    $ins_stmt = $conn->prepare("INSERT INTO teacher_payroll_batch (sccode, sessionyear, salary_month, salary_year, batch_title, status, created_by) VALUES (?, ?, ?, ?, ?, 'Draft', ?)");
    $ins_stmt->bind_param("isiiss", $sccode, $sessionyear, $month, $year, $batch_title, $user);
    $ins_stmt->execute();
    $batch_id = $conn->insert_id;

    // 3. Fetch all active teachers for this institution
    $t_stmt = $conn->prepare("SELECT * FROM teacher WHERE sccode = ? AND (status = 1 OR status = 'Active' OR status IS NULL) ORDER BY CAST(tid AS UNSIGNED) ASC, tid ASC");
    $t_stmt->bind_param("i", $sccode);
    $t_stmt->execute();
    $teachers = $t_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Prepare helper statements outside loop for performance
    $s_stmt = $conn->prepare("SELECT * FROM teacher_salary_structure WHERE tid = ? AND sccode = ? ORDER BY applydate DESC, id DESC LIMIT 1");
    $adj_stmt = $conn->prepare("SELECT adjustment_type, SUM(amount) as total_amt FROM teacher_salary_adjustments WHERE sccode = ? AND tid = ? AND salary_month = ? AND salary_year = ? AND status = 1 GROUP BY adjustment_type");
    $d_stmt = $conn->prepare("INSERT INTO teacher_salary_disbursement (
        batch_id, sccode, tid, payslip_no, salary_month, salary_year,
        paycode, payscale, govt_basic, govt_incentive, govt_house, govt_medical, govt_arrea, govt_gross, govt_welfare, govt_retire, govt_net,
        school_basic, school_mobilevata, school_travel, school_medical, school_exam, school_festival, school_allowances_other, school_gross,
        school_pf, school_fine_absent, school_advance_deduct, school_deductions_other, school_net,
        total_net_payable, paid_amount, payment_method, payment_status
    ) VALUES (
        ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, 0.00, 'BankTransfer', 'Unpaid'
    )");

    foreach ($teachers as $t) {
        $tid = $t['tid'];

        // Get latest salary structure for this teacher, fallback to teacher table
        $s_stmt->bind_param("si", $tid, $sccode);
        $s_stmt->execute();
        $struct = $s_stmt->get_result()->fetch_assoc() ?: $t;

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
        $school_gross  = $school_basic + $school_mobile + $school_travel + $school_med + $school_exam + $school_fest + $school_other_al;

        $school_pf     = floatval($struct['pf'] ?? 0);
        $school_fine   = 0.00;
        $school_adv    = 0.00;
        $school_other_ded = 0.00;
        $school_deduct = $school_pf + $school_fine + $school_adv + $school_other_ded;
        $school_net    = $school_gross - $school_deduct;

        // Check adjustments
        $adj_stmt->bind_param("isii", $sccode, $tid, $month, $year);
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

        // Recompute with adjustments
        $school_gross  = $school_basic + $school_mobile + $school_travel + $school_med + $school_exam + $school_fest + $school_other_al;
        $school_deduct = $school_pf + $school_fine + $school_adv + $school_other_ded;
        $school_net    = $school_gross - $school_deduct;
        $total_net     = $govt_net + $school_net;

        $payslip_no = sprintf("PS-%d-%04d%02d-%s", $sccode, $year, $month, $tid);

        $d_stmt->bind_param(
            "iissiiii" . "ddddddddd" . "dddddddd" . "ddddd" . "d",
            $batch_id, $sccode, $tid, $payslip_no, $month, $year,
            $paycode, $payscale, $govt_basic, $govt_inc, $govt_house, $govt_med, $govt_arrea, $govt_gross, $govt_welfare, $govt_retire, $govt_net,
            $school_basic, $school_mobile, $school_travel, $school_med, $school_exam, $school_fest, $school_other_al, $school_gross,
            $school_pf, $school_fine, $school_adv, $school_other_ded, $school_net,
            $total_net
        );
        $d_stmt->execute();
    }

    // 4. Update batch totals
    payroll_recalculate_batch($conn, $batch_id, $sccode);

    // Fetch updated batch
    $b_stmt = $conn->prepare("SELECT * FROM teacher_payroll_batch WHERE id = ? AND sccode = ?");
    $b_stmt->bind_param("ii", $batch_id, $sccode);
    $b_stmt->execute();
    return $b_stmt->get_result()->fetch_assoc();
}

/**
 * Recalculates batch totals from disbursement line items
 */
function payroll_recalculate_batch($conn, $batch_id, $sccode) {
    $batch_id = intval($batch_id);
    $sccode = intval($sccode);

    $stmt = $conn->prepare("SELECT 
        COUNT(*) as total_teachers,
        COALESCE(SUM(govt_gross), 0) as total_govt_gross,
        COALESCE(SUM(govt_welfare + govt_retire), 0) as total_govt_deduction,
        COALESCE(SUM(govt_net), 0) as total_govt_net,
        COALESCE(SUM(school_gross), 0) as total_school_gross,
        COALESCE(SUM(school_pf + school_fine_absent + school_advance_deduct + school_deductions_other), 0) as total_school_deduction,
        COALESCE(SUM(school_net), 0) as total_school_net,
        COALESCE(SUM(total_net_payable), 0) as grand_total_net
    FROM teacher_salary_disbursement 
    WHERE batch_id = ? AND sccode = ?");
    
    $stmt->bind_param("ii", $batch_id, $sccode);
    $stmt->execute();
    $totals = $stmt->get_result()->fetch_assoc();

    if ($totals) {
        $upd = $conn->prepare("UPDATE teacher_payroll_batch SET
            total_teachers = ?,
            total_govt_gross = ?,
            total_govt_deduction = ?,
            total_govt_net = ?,
            total_school_gross = ?,
            total_school_deduction = ?,
            total_school_net = ?,
            grand_total_net = ?
        WHERE id = ? AND sccode = ?");

        $upd->bind_param(
            "idddddddii",
            $totals['total_teachers'],
            $totals['total_govt_gross'],
            $totals['total_govt_deduction'],
            $totals['total_govt_net'],
            $totals['total_school_gross'],
            $totals['total_school_deduction'],
            $totals['total_school_net'],
            $totals['grand_total_net'],
            $batch_id,
            $sccode
        );
        $upd->execute();
    }
}

/**
 * Approve a payroll batch
 */
function payroll_approve_batch($conn, $batch_id, $sccode, $approved_by = 'Administrator') {
    $batch_id = intval($batch_id);
    $sccode = intval($sccode);
    $now = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("UPDATE teacher_payroll_batch SET status = 'Approved', approved_by = ?, approved_at = ? WHERE id = ? AND sccode = ?");
    $stmt->bind_param("ssii", $approved_by, $now, $batch_id, $sccode);
    return $stmt->execute();
}

/**
 * Disburse payroll batch and optionally write expenditure voucher to cashbook
 */
function payroll_disburse_batch($conn, $batch_id, $sccode, $payment_method = 'BankTransfer', $disbursed_by = 'Administrator', $post_to_cashbook = true, $bank_acc_id = 0) {
    $batch_id = intval($batch_id);
    $sccode = intval($sccode);
    $bank_acc_id = intval($bank_acc_id);
    $now = date('Y-m-d H:i:s');
    $today = date('Y-m-d');

    // Fetch batch
    $b_stmt = $conn->prepare("SELECT * FROM teacher_payroll_batch WHERE id = ? AND sccode = ?");
    $b_stmt->bind_param("ii", $batch_id, $sccode);
    $b_stmt->execute();
    $batch = $b_stmt->get_result()->fetch_assoc();

    if (!$batch) {
        return ['status' => false, 'message' => 'Payroll batch not found!'];
    }

    $cashbook_id = null;

    // Post to cashbook for School/Institutional net amount if requested and > 0
    if ($post_to_cashbook && floatval($batch['total_school_net']) > 0) {
        $amount = floatval($batch['total_school_net']);
        $month_names = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        $month_name = $month_names[$batch['salary_month']] ?? "Month " . $batch['salary_month'];
        $particulars = "Staff Salary Disbursement for " . $month_name . " " . $batch['salary_year'] . " (Batch #" . $batch_id . ")";
        $voucher_no = "SAL-" . $batch['salary_year'] . sprintf("%02d", $batch['salary_month']) . "-" . $batch_id;

        $cb_stmt = $conn->prepare("INSERT INTO cashbook (
            sccode, sessionyear, month, year, voucher_no, date,
            type, payment_method, bank_account_id, category, particulars,
            expenditure, amount, entryby, entrytime, status
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            'Expenditure', ?, ?, 'Staff Salary', ?,
            ?, ?, ?, ?, 1
        )");

        $sess = intval($batch['sessionyear']);
        $m = intval($batch['salary_month']);
        $y = intval($batch['salary_year']);

        $cb_stmt->bind_param(
            "iiiisssisddss",
            $sccode, $sess, $m, $y, $voucher_no, $today,
            $payment_method, $bank_acc_id, $particulars,
            $amount, $amount, $disbursed_by, $now
        );
        $cb_stmt->execute();
        $cashbook_id = $conn->insert_id;
    }

    // Update batch status
    $upd_batch = $conn->prepare("UPDATE teacher_payroll_batch SET status = 'Disbursed', disbursed_at = ? WHERE id = ? AND sccode = ?");
    $upd_batch->bind_param("sii", $now, $batch_id, $sccode);
    $upd_batch->execute();

    // Update all disbursement items
    $upd_items = $conn->prepare("UPDATE teacher_salary_disbursement SET 
        payment_status = 'Paid',
        paid_amount = total_net_payable,
        payment_method = ?,
        payment_date = ?,
        cashbook_memono = ?
    WHERE batch_id = ? AND sccode = ?");

    $upd_items->bind_param("ssiii", $payment_method, $today, $cashbook_id, $batch_id, $sccode);
    $upd_items->execute();

    return [
        'status' => true,
        'message' => 'Payroll disbursed successfully!',
        'cashbook_id' => $cashbook_id
    ];
}
