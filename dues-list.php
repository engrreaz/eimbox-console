<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';
require_once 'core/functions.php';

$conn = db_connect();

$m_names = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

// Helper: Convert Number to Words (Taka & Paisa)
function convertNumberToWords($number) {
    $hyphen      = ' ';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = [
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
        30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
        80 => 'Eighty', 90 => 'Ninety', 100 => 'Hundred', 1000 => 'Thousand', 100000 => 'Lakh', 10000000 => 'Crore'
    ];

    if (!is_numeric($number) || $number <= 0) return 'Zero Taka Only';
    $number = round($number, 2);
    if ($number < 0) return $negative . convertNumberToWords(abs($number));

    $string = $fraction = null;
    $num_str = sprintf("%.2f", $number);
    if (strpos($num_str, '.') !== false) {
        list($number_part, $fraction) = explode('.', $num_str);
        $number = (int)$number_part;
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number] ?? '';
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) $string .= $hyphen . $dictionary[$units];
            break;
        case $number < 1000:
            $hundreds  = (int)($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) $string .= $conjunction . convertNumberToWords($remainder);
            break;
        case $number < 100000:
            $thousands   = (int)($number / 1000);
            $remainder = $number % 1000;
            $string = convertNumberToWords($thousands) . ' ' . $dictionary[1000];
            if ($remainder) $string .= $separator . convertNumberToWords($remainder);
            break;
        case $number < 10000000:
            $lakhs     = (int)($number / 100000);
            $remainder = $number % 100000;
            $string = convertNumberToWords($lakhs) . ' ' . $dictionary[100000];
            if ($remainder) $string .= $separator . convertNumberToWords($remainder);
            break;
        default:
            $crores    = (int)($number / 10000000);
            $remainder = $number % 10000000;
            $string = convertNumberToWords($crores) . ' ' . $dictionary[10000000];
            if ($remainder) $string .= $separator . convertNumberToWords($remainder);
            break;
    }

    if (null !== $fraction && is_numeric($fraction) && (int)$fraction > 0) {
        $frac_val = (int)$fraction;
        $frac_text = $dictionary[$frac_val] ?? ($dictionary[((int)($frac_val/10))*10] . ' ' . ($dictionary[$frac_val%10] ?? ''));
        $string .= ' and ' . $frac_text . ' Paisa';
    }

    return $string . ' Taka Only';
}

// -------------------------------------------------------------
// ACTION 1: AJAX Fetch Student Dues List
// -------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'fetch_dues_list') {
    if (empty($_SESSION['user_id']) || empty($sccode)) {
        echo '<tr><td colspan="4" class="text-center text-danger py-4 fw-normal">Unauthorized or session expired.</td></tr>';
        exit;
    }

    $cls2 = trim($_POST['cls'] ?? '');
    $sec2 = trim($_POST['sec'] ?? '');
    $sy = trim($_POST['year'] ?? date('Y'));

    if (!$cls2 || !$sec2) {
        exit;
    }

    $month = (int)date('m');
    if ((int)date('d') > 15) {
        $month++;
        if ($month > 12) {
            $month = 12;
        }
    }

    $sql = "
    SELECT 
        si.stid,
        si.classname,
        si.sectionname,
        si.rollno,
        si.lastpr,

        st.stnameeng,
        st.stnameben,
        st.previll,
        st.guarmobile,

        IFNULL(SUM(sf.dues),0)       AS total_dues,
        IFNULL(SUM(sf.payableamt),0) AS total_payable,
        IFNULL(SUM(sf.paid),0)       AS total_paid

    FROM sessioninfo si
    LEFT JOIN students st 
        ON st.stid = si.stid AND st.sccode = si.sccode

    LEFT JOIN stfinance sf 
        ON sf.stid = si.stid 
        AND sf.sccode = si.sccode
        AND sf.sessionyear LIKE ?
        AND sf.month <= ?

    WHERE 
        si.sccode = ?
        AND si.sessionyear LIKE ?
        AND si.classname = ?
        AND si.sectionname = ?

    GROUP BY 
        si.stid,
        si.classname,
        si.sectionname,
        si.rollno,
        si.lastpr,
        st.stnameeng,
        st.stnameben,
        st.previll,
        st.guarmobile
    ORDER BY CAST(si.rollno AS UNSIGNED) ASC, si.rollno ASC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo '<tr><td colspan="4" class="text-center text-danger py-4 fw-normal">Failed to prepare query.</td></tr>';
        exit;
    }

    $likeSy = "%$sy%";
    $stmt->bind_param("sissss", $likeSy, $month, $sccode, $likeSy, $cls2, $sec2);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo '<tr><td colspan="4" class="text-center text-muted py-4 fw-normal">No student records found.</td></tr>';
        exit;
    }

    while ($row = $result->fetch_assoc()) {
        $name = $row['stnameeng'];
        $nameb = $row['stnameben'];
        $totaldues = (float)$row['total_dues'];
        $lastpr = $row['lastpr'] ?? 0;
        $stid = (int)$row['stid'];
        $roll = $row['rollno'];

        $mno = $row['guarmobile'] ?? '';
        $datam = $cls2 . '_' . $sec2 . '_' . $row['rollno'] . '_' . $name . '_' . $nameb . '_' . $mno;
        ?>
        <tr class="click-row" style="cursor:pointer" data-stid="<?= $stid ?>" onclick="openBreakdownModal(<?= $stid ?>, '<?= addslashes($name) ?>', '<?= addslashes($roll) ?>', <?= $totaldues ?>)">
            <td class="text-center fw-normal"><?= htmlspecialchars($roll) ?></td>
            <td>
                <div class="fw-normal"><?= htmlspecialchars($name) ?></div>
                <?php if (!empty($nameb)): ?>
                    <small class="text-muted fw-normal"><?= htmlspecialchars($nameb) ?></small>
                <?php endif; ?>
            </td>
            <td class="text-end fw-normal <?= ($totaldues > 0 ? 'text-danger' : 'text-muted') ?>">
                <?= number_format($totaldues, 2) ?>
            </td>
            <td class="text-center text-muted fw-normal">
                <i class="bi bi-arrow-right"></i>
            </td>
        </tr>
        <?php
    }
    exit;
}

// -------------------------------------------------------------
// ACTION 2: AJAX Fetch Student Dues Breakdown Modal
// -------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'fetch_student_breakdown') {
    if (empty($_SESSION['user_id']) || empty($sccode)) {
        echo '<div class="alert alert-danger mb-0 fw-normal">Unauthorized request.</div>';
        exit;
    }

    $stid = (int)($_POST['stid'] ?? 0);
    $sy = trim($_POST['year'] ?? date('Y'));

    if ($stid <= 0) {
        echo '<div class="alert alert-warning mb-0 fw-normal">Invalid student ID.</div>';
        exit;
    }

    $month = (int)date('m');
    if ((int)date('d') > 15) {
        $month++;
        if ($month > 12) {
            $month = 12;
        }
    }

    $sql = "
    SELECT id, itemcode, particulareng, particularben, month, payableamt, paid, dues
    FROM stfinance
    WHERE sccode = ? AND stid = ? AND sessionyear LIKE ? AND month <= ? AND dues > 0
    ORDER BY month ASC, id ASC
    ";

    $stmt = $conn->prepare($sql);
    $likeSy = "%$sy%";
    $stmt->bind_param("iisi", $sccode, $stid, $likeSy, $month);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo '<div class="text-center py-4 text-success fw-normal">';
        echo '<i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>';
        echo 'All dues are cleared for this student!';
        echo '</div>';
        exit;
    }

    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-sm m-0">';
    echo '<thead class="table-light">';
    echo '<tr>';
    echo '<th class="text-center fw-normal" style="width: 40px;">#</th>';
    echo '<th class="fw-normal">Month</th>';
    echo '<th class="fw-normal">Fee Particulars</th>';
    echo '<th class="text-end fw-normal">Payable (৳)</th>';
    echo '<th class="text-end fw-normal">Paid (৳)</th>';
    echo '<th class="text-end fw-normal text-danger">Dues (৳)</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $i = 1;
    $b_payable = 0;
    $b_paid = 0;
    $b_dues = 0;

    while ($r = $res->fetch_assoc()) {
        $m_text = $m_names[(int)$r['month']] ?? ('Month ' . $r['month']);
        $p_text = $r['particulareng'] ?: ($r['particularben'] ?: 'Fee Item');
        $p_ben = $r['particularben'] ?? '';
        $b_payable += (float)$r['payableamt'];
        $b_paid += (float)$r['paid'];
        $b_dues += (float)$r['dues'];

        echo '<tr>';
        echo '<td class="text-center fw-normal">' . $i++ . '</td>';
        echo '<td class="fw-normal">' . htmlspecialchars($m_text) . '</td>';
        echo '<td class="fw-normal">';
        echo htmlspecialchars($p_text);
        if (!empty($p_ben) && $p_ben !== $p_text) {
            echo ' <small class="text-muted">(' . htmlspecialchars($p_ben) . ')</small>';
        }
        echo '</td>';
        echo '<td class="text-end fw-normal">' . number_format($r['payableamt'], 2) . '</td>';
        echo '<td class="text-end fw-normal text-success">' . number_format($r['paid'], 2) . '</td>';
        echo '<td class="text-end fw-normal text-danger">' . number_format($r['dues'], 2) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '<tfoot class="table-light">';
    echo '<tr>';
    echo '<th colspan="3" class="text-end fw-normal">Total Pending:</th>';
    echo '<th class="text-end fw-normal">' . number_format($b_payable, 2) . '</th>';
    echo '<th class="text-end fw-normal text-success">' . number_format($b_paid, 2) . '</th>';
    echo '<th class="text-end fw-normal text-danger">' . number_format($b_dues, 2) . '</th>';
    echo '</tr>';
    echo '</tfoot>';
    echo '</table>';
    echo '</div>';
    exit;
}

// -------------------------------------------------------------
// MODE B: Slim Print Bar Screen (?print=1)
// -------------------------------------------------------------
if (isset($_GET['print'])) {
    if (empty($_SESSION['user_id']) || empty($sccode)) {
        echo "<div style='font-family:sans-serif; padding:30px; text-align:center;'><h3>Please log in to view dues print report.</h3></div>";
        exit;
    }

    $sy = trim($_GET['year'] ?? date('Y'));
    $cls2 = trim($_GET['cls'] ?? '');
    $sec2 = trim($_GET['sec'] ?? '');

    $month = (int)date('m');
    if ((int)date('d') > 15) {
        $month++;
        if ($month > 12) {
            $month = 12;
        }
    }

    // Fetch Institute Details
    $sc_stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
    $sc_stmt->bind_param("i", $sccode);
    $sc_stmt->execute();
    $institute = $sc_stmt->get_result()->fetch_assoc();
    $sc_stmt->close();

    $report_scname = $institute['scname'] ?? ($scname ?? 'EDUCATIONAL INSTITUTION');
    $report_scaddress = $institute['scaddress'] ?? ($scaddress ?? '');
    if (empty($report_scaddress)) {
        $addr_parts = array_filter([$institute['scadd1'] ?? '', $institute['scadd2'] ?? '', $institute['ps'] ?? '', $institute['dist'] ?? '']);
        $report_scaddress = implode(', ', $addr_parts);
    }
    $report_mobile = $institute['mobile'] ?? ($scmobile ?? '');
    $report_mail = $institute['email'] ?? ($scmail ?? '');

    // Query Dues List
    $sql = "
    SELECT 
        si.stid,
        si.classname,
        si.sectionname,
        si.rollno,
        st.stnameeng,
        st.stnameben,
        st.guarmobile,
        IFNULL(SUM(sf.dues),0)       AS total_dues,
        IFNULL(SUM(sf.payableamt),0) AS total_payable,
        IFNULL(SUM(sf.paid),0)       AS total_paid
    FROM sessioninfo si
    LEFT JOIN students st 
        ON st.stid = si.stid AND st.sccode = si.sccode
    LEFT JOIN stfinance sf 
        ON sf.stid = si.stid 
        AND sf.sccode = si.sccode
        AND sf.sessionyear LIKE ?
        AND sf.month <= ?
    WHERE 
        si.sccode = ?
        AND si.sessionyear LIKE ?
        AND si.classname = ?
        AND si.sectionname = ?
    GROUP BY 
        si.stid,
        si.classname,
        si.sectionname,
        si.rollno,
        st.stnameeng,
        st.stnameben,
        st.guarmobile
    ORDER BY CAST(si.rollno AS UNSIGNED) ASC, si.rollno ASC
    ";

    $stmt = $conn->prepare($sql);
    $likeSy = "%$sy%";
    $stmt->bind_param("sissss", $likeSy, $month, $sccode, $likeSy, $cls2, $sec2);
    $stmt->execute();
    $result = $stmt->get_result();

    $report_students = [];
    $total_payable_sum = 0;
    $total_paid_sum = 0;
    $total_dues_sum = 0;

    while ($row = $result->fetch_assoc()) {
        $d = (float)$row['total_dues'];
        $paya = (float)$row['total_payable'];
        $paid = (float)$row['total_paid'];

        $total_payable_sum += $paya;
        $total_paid_sum += $paid;
        $total_dues_sum += $d;

        $report_students[] = $row;
    }
    $stmt->close();

    $total_students_count = count($report_students);
    $amount_in_words = convertNumberToWords($total_dues_sum);
    $month_label = $m_names[$month] ?? ('Month ' . $month);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Dues List Report - <?= htmlspecialchars($cls2) ?> (<?= htmlspecialchars($sec2) ?>) - <?= htmlspecialchars($sy) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <style>
            @page {
                size: A4 portrait;
                margin: 7mm 7mm 10mm 7mm;
            }
            body {
                background-color: #f8f9fa;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                color: #111;
                font-size: 11px;
                line-height: 1.25;
                margin: 0;
                padding: 0;
            }
            .report-wrapper {
                background: #fff;
                max-width: 950px;
                margin: 0 auto;
                padding: 16px 20px;
                min-height: 100vh;
            }
            .inst-header {
                text-align: center;
                border-bottom: 2px solid #222;
                padding-bottom: 6px;
                margin-bottom: 8px;
            }
            .report-title-badge {
                display: inline-block;
                border: 1px solid #1e293b;
                border-radius: 4px;
                padding: 2px 14px;
                font-size: 12.5px;
                text-transform: uppercase;
                background-color: #f1f5f9;
                color: #0f172a;
                margin: 4px 0;
            }
            .report-meta-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 8px;
                font-size: 10px;
            }
            .report-meta-table td {
                border: 1px solid #cbd5e1;
                padding: 3px 6px;
                background-color: #f8fafc;
            }
            .report-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10.5px;
            }
            .report-table th, .report-table td {
                border: 1px solid #64748b;
                padding: 4px 5px;
                vertical-align: middle;
            }
            .report-table th {
                background-color: #f1f5f9 !important;
                text-align: center;
                color: #0f172a;
                font-weight: normal;
            }
            .no-print-bar {
                background: #1e293b;
                padding: 8px 18px;
                color: #fff;
                position: sticky;
                top: 0;
                z-index: 1000;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            .signature-section {
                margin-top: 40px;
                display: flex;
                justify-content: space-between;
                page-break-inside: avoid;
            }
            .sig-block {
                text-align: center;
                width: 200px;
            }
            .sig-line {
                border-top: 1px dashed #333;
                padding-top: 4px;
                font-size: 10px;
                color: #111;
            }
            @media print {
                body {
                    background: #fff !important;
                    color: #000 !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                .no-print-bar {
                    display: none !important;
                }
                .report-wrapper {
                    padding: 0 !important;
                    max-width: 100% !important;
                }
                .report-table th, .report-table td {
                    border-color: #333 !important;
                }
                .report-table tr {
                    page-break-inside: avoid !important;
                }
                thead {
                    display: table-header-group !important;
                }
            }
        </style>
    </head>
    <body>

    <!-- Slim Print Bar Screen -->
    <div class="no-print-bar d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2 flex-grow-1">
            <span class="fs-6 text-info"><i class="bi bi-receipt-cutoff"></i></span>
            <span class="text-white">Student Dues List Report | শিক্ষার্থীদের বকেয়া তালিকা</span>
            <span class="badge bg-secondary fw-normal">Session: <?= htmlspecialchars($sy) ?></span>
            <span class="badge bg-secondary fw-normal">Class: <?= htmlspecialchars($cls2) ?> (<?= htmlspecialchars($sec2) ?>)</span>
            <span class="badge bg-info fw-normal">Total: <?= $total_students_count ?> Students</span>
            <span class="badge bg-danger fw-normal">Dues: ৳ <?= number_format($total_dues_sum, 2) ?></span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-light py-1 px-3 fw-normal" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i> Print / Save PDF
            </button>
            <button type="button" class="btn btn-sm btn-outline-light py-1 px-2 fw-normal" onclick="window.close()">
                <i class="bi bi-x-lg me-1"></i> Close
            </button>
        </div>
    </div>

    <!-- Printable Report Container -->
    <div class="report-wrapper">
        <!-- Institution Header -->
        <div class="inst-header">
            <div class="d-flex align-items-center justify-content-center gap-3">
                <img src="<?= institute_logo($sccode) ?>" onerror="this.onerror=null; this.src='https://eimbox.com/logo/<?= $sccode ?>.png';" style="max-height: 55px; max-width: 65px; object-fit: contain;">
                <div class="text-center">
                    <h4 class="m-0 text-uppercase fw-normal text-dark"><?= htmlspecialchars($report_scname) ?></h4>
                    <div class="small text-muted">
                        <?= htmlspecialchars($report_scaddress) ?>
                        <?php if (!empty($sccode)): ?> | EIIN / Code: <?= htmlspecialchars($sccode) ?><?php endif; ?>
                        <?php if (!empty($report_mobile)): ?> | Phone: <?= htmlspecialchars($report_mobile) ?><?php endif; ?>
                        <?php if (!empty($report_mail)): ?> | Email: <?= htmlspecialchars($report_mail) ?><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="report-title-badge fw-normal">
                STUDENT DUES REPORT / শিক্ষার্থীদের বকেয়া তালিকা ও বিবরণী
            </div>
        </div>

        <!-- Academic Information Strip -->
        <table class="report-meta-table">
            <tr>
                <td style="width: 25%;">Academic Session: <span><?= htmlspecialchars($sy) ?></span></td>
                <td style="width: 25%;">Class: <span><?= htmlspecialchars($cls2) ?></span></td>
                <td style="width: 25%;">Section: <span><?= htmlspecialchars($sec2) ?></span></td>
                <td style="width: 25%;">Cut-off Month: <span><?= htmlspecialchars($month_label) ?></span></td>
            </tr>
            <tr>
                <td>Total Students: <span><?= $total_students_count ?></span></td>
                <td>Total Payable: <span>৳ <?= number_format($total_payable_sum, 2) ?></span></td>
                <td>Total Paid: <span>৳ <?= number_format($total_paid_sum, 2) ?></span></td>
                <td>Print Date: <span><?= date('d M, Y h:i A') ?></span></td>
            </tr>
        </table>

        <!-- Printable Dues Table -->
        <table class="report-table" id="report-print-table">
            <thead>
                <tr>
                    <th style="width: 35px;">Roll</th>
                    <th style="width: 65px;">Student ID</th>
                    <th class="text-start">Name of Student</th>
                    <th style="width: 95px;">Guardian Mobile</th>
                    <th class="text-end" style="width: 85px;">Payable (৳)</th>
                    <th class="text-end" style="width: 85px;">Paid (৳)</th>
                    <th class="text-end" style="width: 85px;">Dues (৳)</th>
                    <th style="width: 95px;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($report_students)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No student records found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($report_students as $st): 
                        $due_val = (float)$st['total_dues'];
                    ?>
                        <tr class="st-print-row">
                            <td class="text-center fw-normal"><?= htmlspecialchars($st['rollno']) ?></td>
                            <td class="text-center text-muted fw-normal"><?= (int)$st['stid'] ?></td>
                            <td>
                                <div><?= htmlspecialchars($st['stnameeng']) ?></div>
                                <?php if (!empty($st['stnameben'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($st['stnameben']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center fw-normal"><?= !empty($st['guarmobile']) ? htmlspecialchars($st['guarmobile']) : '&mdash;' ?></td>
                            <td class="text-end fw-normal"><?= number_format($st['total_payable'], 2) ?></td>
                            <td class="text-end fw-normal text-success"><?= number_format($st['total_paid'], 2) ?></td>
                            <td class="text-end fw-normal <?= ($due_val > 0) ? 'text-danger' : 'text-muted' ?>">
                                <?= number_format($due_val, 2) ?>
                            </td>
                            <td class="text-center text-muted">&nbsp;</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9;">
                    <th colspan="4" class="text-end fw-normal">Grand Total:</th>
                    <th class="text-end fw-normal"><?= number_format($total_payable_sum, 2) ?></th>
                    <th class="text-end fw-normal text-success"><?= number_format($total_paid_sum, 2) ?></th>
                    <th class="text-end fw-normal text-danger"><?= number_format($total_dues_sum, 2) ?></th>
                    <th></th>
                </tr>
                <tr>
                    <td colspan="8" class="p-2" style="background-color: #fafafa; font-size: 10px;">
                        <span>In Words (Total Dues):</span> <em><?= htmlspecialchars($amount_in_words) ?></em>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures Block -->
        <div class="signature-section">
            <div class="sig-block">
                <div class="sig-line">Prepared By / প্রস্তুতকারী</div>
            </div>
            <div class="sig-block">
                <div class="sig-line">Accountant / হিসাবরক্ষক</div>
            </div>
            <div class="sig-block">
                <div class="sig-line">Headmaster / Principal / অধ্যক্ষ</div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4 pt-2 border-top text-muted" style="font-size: 9px;">
            <span>Generated from EIMBox Educational Institution Management System</span>
            <span>Printed by: <?= htmlspecialchars($_SESSION['fullname'] ?? ($_SESSION['user_email'] ?? 'User')) ?> | Time: <?= date('d-m-Y H:i:s') ?></span>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// -------------------------------------------------------------
// MODE A: Interactive Dashboard Page (Direct Table Like students-payment.php)
// -------------------------------------------------------------
require_once 'header.php';
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="m-0 fw-normal">Student Dues List</h3>
        <button type="button" class="btn btn-sm btn-outline-info text-end fw-normal" onclick="printDuesList()">
            <i class="bi bi-printer me-1"></i> Print List
        </button>
    </div>

    <div class="row">
        <!-- Left: Slot Tree UI Selector -->
        <div class="col-md-4">
            <?php
            $chain_param = '-c 4 -t Choose Values -u -r -b View List';
            include 'components/slot-tree-ui.php';
            ?>
        </div>

        <!-- Right: Student Dues Table Card (modeled strictly after students-payment.php) -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-normal text-center text-info flex-grow-1 m-0">Student Dues List</h5>
                    <button type="button" class="btn btn-sm btn-outline-info text-end fw-normal" onclick="printDuesList()">
                        <i class="bi bi-printer me-1"></i> Print List
                    </button>
                </div>
                <div class="form-group row m-0">
                    <div class="col-12 p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm m-0" id="dues-table">
                                <thead>
                                    <tr class="text-primary">
                                        <td class="text-center fw-normal" style="width: 70px;">Roll</td>
                                        <td class="fw-normal">Name of Student</td>
                                        <td class="text-end fw-normal" style="width: 140px;">Dues</td>
                                        <td class="text-center fw-normal" style="width: 50px;"></td>
                                    </tr>
                                </thead>
                                <tbody id="dues-body">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5 fw-normal">
                                            Please select values from the left panel.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student Dues Breakdown Modal -->
<div class="modal fade" id="breakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-normal m-0" id="modalStudentName">
                        <i class="bi bi-receipt-cutoff text-info me-2"></i> Student Dues Breakdown
                    </h5>
                    <small class="text-muted fw-normal" id="modalStudentSub">Roll: - | ID: -</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="breakdown-body">
                <div class="text-center py-4 text-muted fw-normal">
                    <div class="spinner-border spinner-border-sm text-info me-1" role="status"></div> Loading breakdown...
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <span class="text-muted small">Total Pending: </span>
                    <span class="text-danger fw-normal" id="modalDuesTotal">৳ 0.00</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-secondary fw-normal" data-bs-dismiss="modal">Close</button>
                    <a href="students-payment.php" id="btn-collect-payment" class="btn btn-sm btn-primary fw-normal">
                        <i class="bi bi-cash-coin me-1"></i> Collect Payment
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    // -------------------------------------------------------------
    // Set Student Cookie for Jump to Payment Counter
    // -------------------------------------------------------------
    function setStudentCookie(stid) {
        setCookie('payment-stid', stid);
    }

    // -------------------------------------------------------------
    // Open Breakdown Modal
    // -------------------------------------------------------------
    function openBreakdownModal(stid, name, roll, totalDues) {
        $('#modalStudentName').html('<i class="bi bi-receipt-cutoff text-info me-2"></i> ' + name);
        $('#modalStudentSub').text('Roll: ' + roll + ' | Student ID: ' + stid);
        $('#modalDuesTotal').text('৳ ' + parseFloat(totalDues).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        $('#btn-collect-payment').off('click').on('click', function() {
            setStudentCookie(stid);
        });

        let year = $('#session-main').val();

        $('#breakdown-body').html('<div class="text-center py-4 text-muted fw-normal"><div class="spinner-border spinner-border-sm text-info me-1" role="status"></div> Loading breakdown...</div>');

        let modalEl = document.getElementById('breakdownModal');
        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        $.ajax({
            type: "POST",
            url: "dues-list.php",
            data: {
                action: 'fetch_student_breakdown',
                stid: stid,
                year: year
            },
            success: function(html) {
                $('#breakdown-body').html(html);
            },
            error: function() {
                $('#breakdown-body').html('<div class="alert alert-danger mb-0 fw-normal">Failed to retrieve breakdown details. Please try again.</div>');
            }
        });
    }

    // -------------------------------------------------------------
    // Print Dues List via Slim Print Bar
    // -------------------------------------------------------------
    function printDuesList() {
        let year = $('#session-main').val() || '';
        let cls = $('#class-main').val() || '';
        let sec = $('#section-main').val() || '';

        if (!cls || !sec) {
            showToast('warning', 'Please select Class and Section from the left panel first.', 'Select Class & Section');
            return;
        }

        let url = 'dues-list.php?print=1'
                + '&year=' + encodeURIComponent(year)
                + '&cls=' + encodeURIComponent(cls)
                + '&sec=' + encodeURIComponent(sec);

        window.open(url, '_blank');
    }

    // -------------------------------------------------------------
    // Main Fetch Function (Triggered by slot-tree-ui / changes)
    // -------------------------------------------------------------
    function getlist() {
        let year = $('#session-main').val();
        let cls = $('#class-main').val();
        let sec = $('#section-main').val();

        if (!cls || !sec) {
            return;
        }

        var infor = "action=fetch_dues_list&year=" + encodeURIComponent(year) + "&cls=" + encodeURIComponent(cls) + "&sec=" + encodeURIComponent(sec);

        $("#dues-body").html("");

        $.ajax({
            type: "POST",
            url: "dues-list.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $('#dues-body').html('<tr><td colspan="4" class="text-center text-info pt-5 fw-normal">Retrieve Dues List ...</td></tr>');
            },
            success: function (html) {
                $("#dues-body").html(html);
            },
            error: function() {
                $('#dues-body').html('<tr><td colspan="4" class="text-center text-danger py-4 fw-normal">An error occurred while loading dues list.</td></tr>');
            }
        });
    }

    // -------------------------------------------------------------
    // Cascading Dropdown Listeners
    // -------------------------------------------------------------
    $(function () {
        function tryGetList() {
            let cls = $('#class-main').val();
            let sec = $('#section-main').val();
            if (cls && sec) {
                getlist();
            }
        }

        $('#class-main').on('change', function () {
            setTimeout(tryGetList, 500);
        });

        $('#section-main').on('change', function () {
            tryGetList();
        });

        setTimeout(tryGetList, 600);
    });

    $(document).on('click', '.btn-chain', function () {
        getlist();
    });
</script>
</body>
</html>
