<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';

$conn = db_connect();

$id          = intval($_POST['id'] ?? 0);
$stid        = trim($_POST['stid'] ?? '');
$sccode      = intval($_POST['sccode'] ?? ($_SESSION['sccode'] ?? 0));
$sessionyear = trim($_POST['sessionyear'] ?? '');
$pr1no       = trim($_POST['prno'] ?? '');
$source      = trim($_POST['source'] ?? 'payment_pgw');
$trxid       = trim($_POST['trxid'] ?? '');

$trans_data = null;

// 1. Resolve Transaction Record & PR No if not directly provided
if ($source === 'bkash_transactions') {
    $stmt = $conn->prepare("SELECT * FROM bkash_transactions WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $trans_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($trans_data) {
        if (empty($pr1no) && !empty($trans_data['prno'])) $pr1no = trim((string)$trans_data['prno']);
        if (empty($sccode) && !empty($trans_data['sccode'])) $sccode = intval($trans_data['sccode']);
        if (empty($stid) && !empty($trans_data['stid'])) $stid = trim((string)$trans_data['stid']);
        if (empty($sessionyear) && !empty($trans_data['sessionyear'])) $sessionyear = trim($trans_data['sessionyear']);
        if (empty($trxid) && !empty($trans_data['trxid'])) $trxid = trim($trans_data['trxid']);
    }
} elseif ($source === 'rocket_transactions') {
    $stmt = $conn->prepare("SELECT * FROM rocket_transactions WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $trans_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($trans_data) {
        if (empty($pr1no) && !empty($trans_data['prno'])) $pr1no = trim((string)$trans_data['prno']);
        if (empty($sccode) && !empty($trans_data['sccode'])) $sccode = intval($trans_data['sccode']);
        if (empty($stid) && !empty($trans_data['stid'])) $stid = trim((string)$trans_data['stid']);
        if (empty($sessionyear) && !empty($trans_data['sessionyear'])) $sessionyear = trim($trans_data['sessionyear']);
        if (empty($trxid) && !empty($trans_data['txnid'])) $trxid = trim($trans_data['txnid']);
    }
} else {
    // Default: payment_pgw
    $stmt = $conn->prepare("SELECT * FROM payment_pgw WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $trans_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($trans_data) {
        if (empty($sccode) && !empty($trans_data['sccode'])) $sccode = intval($trans_data['sccode']);
        if (empty($stid) && !empty($trans_data['stid'])) $stid = trim((string)$trans_data['stid']);
        if (empty($sessionyear) && !empty($trans_data['sessionyear'])) $sessionyear = trim($trans_data['sessionyear']);
        if (empty($trxid) && !empty($trans_data['trxID'])) $trxid = trim($trans_data['trxID']);
        if (empty($pr1no) && !empty($trans_data['merchantInvoiceNumber'])) {
            $parts = explode('-', $trans_data['merchantInvoiceNumber']);
            $last = end($parts);
            if (is_numeric($last)) {
                $pr1no = trim($last);
            }
        }
    }
}

// Student Details Lookup
$st_name = '';
$st_roll = '';
if (!empty($stid)) {
    $st_stmt = $conn->prepare("SELECT stnameeng, stnameben, rollno, mobileself FROM students WHERE stid = ? LIMIT 1");
    $st_stmt->bind_param("s", $stid);
    $st_stmt->execute();
    $st_row = $st_stmt->get_result()->fetch_assoc();
    $st_stmt->close();
    if ($st_row) {
        $st_name = trim(($st_row['stnameeng'] ?? '') . ($st_row['stnameben'] ? ' (' . $st_row['stnameben'] . ')' : ''));
        $st_roll = $st_row['rollno'] ?? '';
    }
}

// -------------------------------------------------------------
// Query stfinance with: sccode + sessionyear + pr1no + stid
// -------------------------------------------------------------
$finance_items = [];
$total_amount = 0;
$total_paid   = 0;
$total_dues   = 0;
$class_name   = '';
$section_name = '';

if (!empty($pr1no) && !empty($stid)) {
    // 1. Primary Query: sccode + sessionyear + pr1no + stid
    $fin_sql = "SELECT id, sccode, sessionyear, classname, sectionname, stid, rollno, partid, particulareng, particularben, amount, month, paid, dues, pr1, pr1no, pr1date, pr1by 
                FROM stfinance 
                WHERE sccode = ? AND sessionyear = ? AND pr1no = ? AND stid = ?
                ORDER BY id ASC";
    $stmt_fin = $conn->prepare($fin_sql);
    $stmt_fin->bind_param("isis", $sccode, $sessionyear, $pr1no, $stid);
    $stmt_fin->execute();
    $res_fin = $stmt_fin->get_result();
    while ($f_row = $res_fin->fetch_assoc()) {
        $finance_items[] = $f_row;
    }
    $stmt_fin->close();

    // 2. Fallback Query without strict sessionyear if 0 rows returned
    if (empty($finance_items)) {
        $fin_sql2 = "SELECT id, sccode, sessionyear, classname, sectionname, stid, rollno, partid, particulareng, particularben, amount, month, paid, dues, pr1, pr1no, pr1date, pr1by 
                     FROM stfinance 
                     WHERE sccode = ? AND pr1no = ? AND stid = ?
                     ORDER BY id ASC";
        $stmt_fin2 = $conn->prepare($fin_sql2);
        $stmt_fin2->bind_param("iis", $sccode, $pr1no, $stid);
        $stmt_fin2->execute();
        $res_fin2 = $stmt_fin2->get_result();
        while ($f_row = $res_fin2->fetch_assoc()) {
            $finance_items[] = $f_row;
        }
        $stmt_fin2->close();
    }
}

// Compute sums and capture class/section from first item
if (!empty($finance_items)) {
    $class_name   = $finance_items[0]['classname'] ?? '';
    $section_name = $finance_items[0]['sectionname'] ?? '';
    foreach ($finance_items as $item) {
        $total_amount += (float)($item['amount'] ?? 0);
        $total_paid   += (float)($item['paid'] ?? 0);
        $total_dues   += (float)($item['dues'] ?? 0);
    }
}

$month_map = [
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
    7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
];
?>

<div class="p-1">
    <!-- Header Summary Card -->
    <div class="card bg-light border mb-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <?php if ($source === 'bkash_transactions'): ?>
                            <span class="badge bg-danger fw-normal">bKash (Direct API)</span>
                        <?php elseif ($source === 'rocket_transactions'): ?>
                            <span class="badge bg-info fw-normal">Rocket / DBBL</span>
                        <?php else: ?>
                            <span class="badge bg-primary fw-normal"><?= htmlspecialchars($trans_data['gateway'] ?? 'Payment PGW') ?></span>
                        <?php endif; ?>
                        <span class="badge bg-success fw-normal"><?= htmlspecialchars($trans_data['status'] ?? ($trans_data['transactionStatus'] ?? 'Success')) ?></span>
                        <span class="font-monospace text-muted small">Trx: <?= htmlspecialchars($trxid ?: '&mdash;') ?></span>
                    </div>
                    <div class="fw-normal text-dark fs-6">
                        <?= htmlspecialchars($st_name ?: 'Student: ' . $stid) ?>
                    </div>
                    <div class="text-muted small">
                        <span>STID: <strong><?= htmlspecialchars($stid) ?></strong></span>
                        <?php if ($st_roll): ?> | <span>Roll: <?= htmlspecialchars($st_roll) ?></span><?php endif; ?>
                        <?php if ($class_name): ?> | <span>Class: <?= htmlspecialchars($class_name) ?> (<?= htmlspecialchars($section_name) ?>)</span><?php endif; ?>
                        | <span>Session: <?= htmlspecialchars($sessionyear) ?></span>
                    </div>
                </div>

                <div class="col-12 col-md-6 text-md-end">
                    <div class="text-muted small">Receipt No (PR1 No):</div>
                    <div class="fw-normal fs-5 text-primary font-monospace"><?= htmlspecialchars($pr1no ?: '&mdash;') ?></div>
                    <div class="text-muted small">
                        Total Paid: <strong class="text-success">৳ <?= number_format($total_paid > 0 ? $total_paid : (float)($trans_data['amount'] ?? 0), 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Items from stfinance -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="m-0 text-primary fw-normal">
            <i class="bi bi-list-check me-1"></i> Fee Items Settled (stfinance)
        </h6>
        <span class="badge bg-label-secondary fw-normal"><?= count($finance_items) ?> Items</span>
    </div>

    <?php if (!empty($finance_items)): ?>
    <div class="table-responsive border rounded mb-3">
        <table class="table table-sm table-bordered table-striped table-hover m-0" style="font-size: 12px;">
            <thead class="table-light">
                <tr>
                    <th class="text-center fw-normal" style="width: 35px;">#</th>
                    <th class="fw-normal">Fee Particular / বিবরণী</th>
                    <th class="text-center fw-normal" style="width: 70px;">Month</th>
                    <th class="text-end fw-normal" style="width: 90px;">Total (৳)</th>
                    <th class="text-end fw-normal" style="width: 90px;">Paid (৳)</th>
                    <th class="text-end fw-normal" style="width: 80px;">Dues (৳)</th>
                    <th class="text-center fw-normal" style="width: 85px;">PR Date</th>
                    <th class="text-center fw-normal" style="width: 80px;">Cashier</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $f_sl = 1;
                foreach ($finance_items as $item): 
                    $m_val = intval($item['month'] ?? 0);
                    $m_text = $month_map[$m_val] ?? ($m_val > 0 ? $m_val : '&mdash;');
                    $p_name = htmlspecialchars($item['particulareng'] ?: ($item['particularben'] ?: 'Fee Item'));
                ?>
                <tr>
                    <td class="text-center fw-normal"><?= $f_sl++ ?></td>
                    <td class="fw-normal">
                        <div class="text-dark"><?= $p_name ?></div>
                        <?php if (!empty($item['particularben']) && !empty($item['particulareng'])): ?>
                            <small class="text-muted"><?= htmlspecialchars($item['particularben']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center fw-normal"><?= $m_text ?></td>
                    <td class="text-end fw-normal"><?= number_format((float)$item['amount'], 2) ?></td>
                    <td class="text-end fw-normal text-success"><?= number_format((float)$item['paid'], 2) ?></td>
                    <td class="text-end fw-normal <?= (float)$item['dues'] > 0 ? 'text-danger' : 'text-muted' ?>">
                        <?= number_format((float)$item['dues'], 2) ?>
                    </td>
                    <td class="text-center fw-normal text-muted" style="font-size: 11px;">
                        <?= !empty($item['pr1date']) ? date('d/m/Y', strtotime($item['pr1date'])) : '&mdash;' ?>
                    </td>
                    <td class="text-center fw-normal text-muted" style="font-size: 11px;">
                        <?= htmlspecialchars($item['pr1by'] ?: 'System') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="3" class="text-end fw-normal">Total Summary:</th>
                    <th class="text-end fw-normal"><?= number_format($total_amount, 2) ?></th>
                    <th class="text-end fw-normal text-success">৳ <?= number_format($total_paid, 2) ?></th>
                    <th class="text-end fw-normal text-danger">৳ <?= number_format($total_dues, 2) ?></th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php else: ?>
    <div class="alert alert-warning py-3 text-center fw-normal mb-3">
        <i class="bi bi-exclamation-triangle me-1"></i> 
        No fee item records found in <strong>stfinance</strong> for Receipt No: <strong><?= htmlspecialchars($pr1no ?: 'N/A') ?></strong> (Student ID: <?= htmlspecialchars($stid) ?>, Session: <?= htmlspecialchars($sessionyear) ?>).
    </div>
    <?php endif; ?>

    <!-- Gateway Payload (if available) -->
    <?php if (!empty($trans_data['raw_request'])): ?>
        <div class="mt-2">
            <span class="text-muted small fw-normal"><i class="bi bi-code-slash me-1"></i> Gateway Raw Payload</span>
            <pre class="bg-light p-2 border rounded font-monospace text-muted mb-0 mt-1" style="font-size: 10px; max-height: 80px; overflow-y: auto;"><?= htmlspecialchars($trans_data['raw_request']) ?></pre>
        </div>
    <?php endif; ?>
</div>
