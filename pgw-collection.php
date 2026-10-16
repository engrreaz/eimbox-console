<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';
require_once 'core/functions.php';

$conn = db_connect();

if (empty($_SESSION['user_id']) || empty($sccode)) {
    echo "<div style='font-family:sans-serif; padding:30px; text-align:center;'><h3>Please log in to view payment gateway collection.</h3></div>";
    exit;
}

// -------------------------------------------------------------
// Parameters: date_from, date_to, gateway, search
// -------------------------------------------------------------
$date_from = trim($_GET['date_from'] ?? '');
$date_to   = trim($_GET['date_to'] ?? '');
$gateway   = trim($_GET['gateway'] ?? 'all');
$search    = trim($_GET['search'] ?? '');

// Helper: Convert Number to Words
if (!function_exists('convertNumberToWords')) {
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
}

// -------------------------------------------------------------
// Build Unified Query Across payment_pgw, bkash_transactions & rocket_transactions
// -------------------------------------------------------------
$where_p = "WHERE p.sccode = " . intval($sccode);
$where_b = "WHERE b.sccode = " . intval($sccode);
$where_r = "WHERE r.sccode = " . intval($sccode);

if (!empty($date_from)) {
    $esc_from = $conn->real_escape_string($date_from);
    $where_p .= " AND p.paydate >= '$esc_from'";
    $where_b .= " AND DATE(b.created_at) >= '$esc_from'";
    $where_r .= " AND DATE(r.created_at) >= '$esc_from'";
}

if (!empty($date_to)) {
    $esc_to = $conn->real_escape_string($date_to);
    $where_p .= " AND p.paydate <= '$esc_to'";
    $where_b .= " AND DATE(b.created_at) <= '$esc_to'";
    $where_r .= " AND DATE(r.created_at) <= '$esc_to'";
}

if (!empty($gateway) && $gateway !== 'all') {
    $esc_gw = strtolower($conn->real_escape_string($gateway));
    if ($esc_gw === 'bkash') {
        $where_p .= " AND LOWER(p.gateway) = 'bkash'";
        $where_r .= " AND 1=0"; // exclude rocket
    } elseif ($esc_gw === 'rocket') {
        $where_p .= " AND LOWER(p.gateway) = 'rocket'";
        $where_b .= " AND 1=0"; // exclude bkash
    } elseif ($esc_gw === 'nagad') {
        $where_p .= " AND LOWER(p.gateway) = 'nagad'";
        $where_b .= " AND 1=0";
        $where_r .= " AND 1=0";
    } else {
        $where_p .= " AND LOWER(p.gateway) = '$esc_gw'";
        $where_b .= " AND 1=0";
        $where_r .= " AND 1=0";
    }
}

if (!empty($search)) {
    $esc_s = $conn->real_escape_string($search);
    $where_p .= " AND (p.stid LIKE '%$esc_s%' OR p.trxID LIKE '%$esc_s%' OR p.customerMsisdn LIKE '%$esc_s%' OR st.stnameeng LIKE '%$esc_s%' OR st.stnameben LIKE '%$esc_s%')";
    $where_b .= " AND (b.stid LIKE '%$esc_s%' OR b.trxid LIKE '%$esc_s%' OR b.user_mobile LIKE '%$esc_s%' OR b.prno LIKE '%$esc_s%' OR st.stnameeng LIKE '%$esc_s%' OR st.stnameben LIKE '%$esc_s%')";
    $where_r .= " AND (r.stid LIKE '%$esc_s%' OR r.txnid LIKE '%$esc_s%' OR r.refno1 LIKE '%$esc_s%' OR r.prno LIKE '%$esc_s%' OR st.stnameeng LIKE '%$esc_s%' OR st.stnameben LIKE '%$esc_s%')";
}

$sql_all = "
SELECT 
    p.id,
    'payment_pgw' AS source_table,
    p.sccode,
    p.sessionyear,
    p.stid,
    st.stnameeng,
    st.stnameben,
    p.paydate AS trans_date,
    p.amount,
    COALESCE(NULLIF(p.gateway,''), 'bKash') AS gateway,
    p.trxID AS trxid,
    COALESCE(NULLIF(p.transactionStatus,''), p.statusMessage, 'Completed') AS status,
    p.token_id,
    NULLIF(SUBSTRING_INDEX(p.merchantInvoiceNumber, '-', -1), '') AS prno,
    COALESCE(NULLIF(p.payerAccount,''), p.customerMsisdn, '') AS payer_mobile,
    p.entrytime
FROM payment_pgw p
LEFT JOIN students st ON st.stid = p.stid AND (st.sccode = p.sccode OR st.sccode = 0)
$where_p

UNION ALL

SELECT 
    b.id,
    'bkash_transactions' AS source_table,
    b.sccode,
    b.sessionyear,
    b.stid,
    st.stnameeng,
    st.stnameben,
    DATE(b.created_at) AS trans_date,
    b.amount,
    'bKash' AS gateway,
    b.trxid,
    b.status,
    0 AS token_id,
    b.prno,
    b.user_mobile AS payer_mobile,
    b.created_at AS entrytime
FROM bkash_transactions b
LEFT JOIN students st ON st.stid = b.stid AND (st.sccode = b.sccode OR st.sccode = 0)
$where_b

UNION ALL

SELECT 
    r.id,
    'rocket_transactions' AS source_table,
    r.sccode,
    r.sessionyear,
    r.stid,
    st.stnameeng,
    st.stnameben,
    DATE(r.created_at) AS trans_date,
    r.amount,
    'Rocket' AS gateway,
    r.txnid AS trxid,
    r.status,
    0 AS token_id,
    r.prno,
    COALESCE(NULLIF(r.refno1,''), '') AS payer_mobile,
    r.created_at AS entrytime
FROM rocket_transactions r
LEFT JOIN students st ON st.stid = r.stid AND (st.sccode = r.sccode OR st.sccode = 0)
$where_r

ORDER BY trans_date DESC, id DESC
";

$q_res = $conn->query($sql_all);

$transactions = [];
$total_amount_sum = 0;
$total_bkash_sum = 0;
$total_rocket_sum = 0;
$total_other_sum = 0;

if ($q_res && $q_res->num_rows > 0) {
    while ($row = $q_res->fetch_assoc()) {
        $amt = (float)$row['amount'];
        $total_amount_sum += $amt;

        $gw_lower = strtolower($row['gateway']);
        if (str_contains($gw_lower, 'bkash')) {
            $total_bkash_sum += $amt;
        } elseif (str_contains($gw_lower, 'rocket')) {
            $total_rocket_sum += $amt;
        } else {
            $total_other_sum += $amt;
        }

        $transactions[] = $row;
    }
}

// -------------------------------------------------------------
// PRINT VIEW (?print=1)
// -------------------------------------------------------------
if (isset($_GET['print'])) {
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

    $date_range_label = 'All Dates';
    if (!empty($date_from) && !empty($date_to)) {
        $date_range_label = date('d/m/Y', strtotime($date_from)) . ' to ' . date('d/m/Y', strtotime($date_to));
    } elseif (!empty($date_from)) {
        $date_range_label = 'From ' . date('d/m/Y', strtotime($date_from));
    } elseif (!empty($date_to)) {
        $date_range_label = 'Up to ' . date('d/m/Y', strtotime($date_to));
    }

    $amount_in_words = convertNumberToWords($total_amount_sum);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Gateway Collection Report - <?= htmlspecialchars($sccode) ?></title>
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
            <span class="fs-6 text-info"><i class="bi bi-wallet2"></i></span>
            <span class="text-white">Payment Gateway Collection Report | পেমেন্ট গেটওয়ে কালেকশন রিপোর্ট</span>
            <span class="badge bg-secondary fw-normal"><?= htmlspecialchars($date_range_label) ?></span>
            <span class="badge bg-secondary fw-normal">Gateway: <?= htmlspecialchars(ucfirst($gateway)) ?></span>
            <span class="badge bg-info fw-normal">Total: <?= count($transactions) ?> Txns</span>
            <span class="badge bg-success fw-normal">Amount: ৳ <?= number_format($total_amount_sum, 2) ?></span>
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

    <!-- Printable Report Wrapper -->
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
                PAYMENT GATEWAY COLLECTION REPORT / পেমেন্ট গেটওয়ে কালেকশন বিবরণী
            </div>
        </div>

        <!-- Meta Information Strip -->
        <table class="report-meta-table">
            <tr>
                <td style="width: 25%;">Date Range: <span><?= htmlspecialchars($date_range_label) ?></span></td>
                <td style="width: 25%;">Gateway Filter: <span><?= htmlspecialchars(ucfirst($gateway)) ?></span></td>
                <td style="width: 25%;">Total Transactions: <span><?= count($transactions) ?></span></td>
                <td style="width: 25%;">Print Date: <span><?= date('d M, Y h:i A') ?></span></td>
            </tr>
            <tr>
                <td>bKash Total: <span>৳ <?= number_format($total_bkash_sum, 2) ?></span></td>
                <td>Rocket Total: <span>৳ <?= number_format($total_rocket_sum, 2) ?></span></td>
                <td>Other Total: <span>৳ <?= number_format($total_other_sum, 2) ?></span></td>
                <td>Grand Total: <span>৳ <?= number_format($total_amount_sum, 2) ?></span></td>
            </tr>
        </table>

        <!-- Printable Data Table -->
        <table class="report-table" id="report-print-table">
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th style="width: 70px;">Date</th>
                    <th style="width: 65px;">Gateway</th>
                    <th style="width: 75px;">STID</th>
                    <th class="text-start">Name of Student</th>
                    <th style="width: 110px;">TrxID</th>
                    <th style="width: 90px;">Payer Mobile / Ref</th>
                    <th style="width: 70px;">PR No</th>
                    <th class="text-end" style="width: 80px;">Amount (৳)</th>
                    <th style="width: 65px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">No gateway transactions found for the selected criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $sl = 1;
                    foreach ($transactions as $t): 
                    ?>
                        <tr>
                            <td class="text-center fw-normal"><?= $sl++ ?></td>
                            <td class="text-center fw-normal"><?= !empty($t['trans_date']) ? date('d/m/Y', strtotime($t['trans_date'])) : '&mdash;' ?></td>
                            <td class="text-center fw-normal"><?= htmlspecialchars($t['gateway']) ?></td>
                            <td class="text-center text-muted fw-normal"><?= (int)$t['stid'] ?></td>
                            <td class="text-start">
                                <div><?= htmlspecialchars($t['stnameeng'] ?? '') ?></div>
                                <?php if (!empty($t['stnameben'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($t['stnameben']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center font-monospace fw-normal" style="font-size: 10px;"><?= htmlspecialchars($t['trxid']) ?></td>
                            <td class="text-center fw-normal"><?= !empty($t['payer_mobile']) ? htmlspecialchars($t['payer_mobile']) : '&mdash;' ?></td>
                            <td class="text-center text-muted fw-normal"><?= !empty($t['prno']) ? htmlspecialchars($t['prno']) : '&mdash;' ?></td>
                            <td class="text-end fw-normal"><?= number_format((float)$t['amount'], 2) ?></td>
                            <td class="text-center fw-normal"><?= htmlspecialchars($t['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9;">
                    <th colspan="8" class="text-end fw-normal">Grand Total:</th>
                    <th class="text-end fw-normal"><?= number_format($total_amount_sum, 2) ?></th>
                    <th></th>
                </tr>
                <tr>
                    <td colspan="10" class="p-2" style="background-color: #fafafa; font-size: 10px;">
                        <span>In Words (Total Amount):</span> <em><?= htmlspecialchars($amount_in_words) ?></em>
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
// INTERACTIVE DASHBOARD VIEW (Default)
// -------------------------------------------------------------
require_once 'header.php';
?>

<style>
    #detailsModal_pgw .modal-dialog {
        max-width: 800px;
        margin: 1.75rem auto;
    }

    #detailsModal_pgw .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="m-0 text-primary fw-normal">Collection Through Payment Gateways</h4>
            <span class="text-muted small">Monitor, search and print transactions from bKash, Rocket, and Payment Gateways</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-info text-white fw-normal" onclick="printReport()">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Filter & Search Toolbar Card -->
    <div class="card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="pgw-collection.php" id="filterForm" class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small mb-1 fw-normal">
                        <i class="bi bi-calendar-event me-1"></i> Date From
                    </label>
                    <input type="date" class="form-control form-control-sm" name="date_from" id="date_from" value="<?= htmlspecialchars($date_from) ?>">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small mb-1 fw-normal">
                        <i class="bi bi-calendar-check me-1"></i> Date To
                    </label>
                    <input type="date" class="form-control form-control-sm" name="date_to" id="date_to" value="<?= htmlspecialchars($date_to) ?>">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small mb-1 fw-normal">
                        <i class="bi bi-wallet2 me-1"></i> Gateway
                    </label>
                    <select class="form-select form-select-sm" name="gateway" id="gateway">
                        <option value="all" <?= ($gateway === 'all' || empty($gateway)) ? 'selected' : '' ?>>All Gateways</option>
                        <option value="bkash" <?= (strtolower($gateway) === 'bkash') ? 'selected' : '' ?>>bKash</option>
                        <option value="rocket" <?= (strtolower($gateway) === 'rocket') ? 'selected' : '' ?>>Rocket</option>
                        <option value="nagad" <?= (strtolower($gateway) === 'nagad') ? 'selected' : '' ?>>Nagad</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label text-muted small mb-1 fw-normal">
                        <i class="bi bi-search me-1"></i> Search (STID, Trx, Mobile)
                    </label>
                    <input type="text" class="form-control form-control-sm" name="search" id="search" placeholder="Enter keyword..." value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1 fw-normal">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="pgw-collection.php" class="btn btn-sm btn-outline-secondary fw-normal" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-info fw-normal" onclick="printReport()" title="Print List">
                        <i class="bi bi-printer"></i>
                    </button>
                </div>
            </form>

            <!-- Quick Date Filters -->
            <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                <span class="text-muted small align-self-center">Quick Date:</span>
                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fw-normal" onclick="setDateRange('today')">Today</button>
                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fw-normal" onclick="setDateRange('this_month')">This Month</button>
                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fw-normal" onclick="setDateRange('last_30')">Last 30 Days</button>
                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 fw-normal" onclick="setDateRange('this_year')">This Year</button>
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 fw-normal" onclick="setDateRange('all')">Clear Dates</button>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card p-2 text-center h-100">
                <span class="text-muted small">Total Transactions</span>
                <h5 class="m-0 fw-normal text-primary"><?= count($transactions) ?></h5>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card p-2 text-center h-100">
                <span class="text-muted small">bKash Collection</span>
                <h5 class="m-0 fw-normal text-danger">৳ <?= number_format($total_bkash_sum, 2) ?></h5>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card p-2 text-center h-100">
                <span class="text-muted small">Rocket Collection</span>
                <h5 class="m-0 fw-normal text-info">৳ <?= number_format($total_rocket_sum, 2) ?></h5>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card p-2 text-center h-100">
                <span class="text-muted small">Total Collection</span>
                <h5 class="m-0 fw-normal text-success">৳ <?= number_format($total_amount_sum, 2) ?></h5>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card">
        <div class="table-responsive p-2">
            <table id="pgwTable" class="table table-bordered table-striped table-hover table-sm m-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center fw-normal" style="width: 40px;">#</th>
                        <th class="text-center fw-normal" style="width: 80px;">Date</th>
                        <th class="text-center fw-normal" style="width: 80px;">Gateway</th>
                        <th class="text-center fw-normal" style="width: 85px;">STID</th>
                        <th class="fw-normal">Name of Student</th>
                        <th class="text-center fw-normal" style="width: 70px;">Session</th>
                        <th class="text-center fw-normal" style="width: 120px;">TrxID</th>
                        <th class="text-center fw-normal" style="width: 100px;">Mobile / Ref</th>
                        <th class="text-center fw-normal" style="width: 80px;">PR No</th>
                        <th class="text-end fw-normal" style="width: 90px;">Amount (৳)</th>
                        <th class="text-center fw-normal" style="width: 75px;">Status</th>
                        <th class="text-center fw-normal" style="width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sl = 1;
                    foreach ($transactions as $row): 
                        $gw = strtolower($row['gateway']);
                        $gw_badge_class = 'bg-label-primary';
                        if (str_contains($gw, 'bkash')) {
                            $gw_badge_class = 'bg-label-danger';
                        } elseif (str_contains($gw, 'rocket')) {
                            $gw_badge_class = 'bg-label-info';
                        } elseif (str_contains($gw, 'nagad')) {
                            $gw_badge_class = 'bg-label-warning';
                        }
                    ?>
                        <tr>
                            <td class="text-center fw-normal"><?= $sl++ ?></td>
                            <td class="text-center fw-normal"><?= !empty($row['trans_date']) ? date('d/m/Y', strtotime($row['trans_date'])) : '&mdash;' ?></td>
                            <td class="text-center">
                                <span class="badge <?= $gw_badge_class ?> fw-normal"><?= htmlspecialchars($row['gateway']) ?></span>
                            </td>
                            <td class="text-center text-muted fw-normal"><?= (int)$row['stid'] ?></td>
                            <td>
                                <div class="fw-normal text-dark"><?= htmlspecialchars($row['stnameeng'] ?? '') ?></div>
                                <?php if (!empty($row['stnameben'])): ?>
                                    <small class="text-muted fw-normal"><?= htmlspecialchars($row['stnameben']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-muted fw-normal"><?= htmlspecialchars($row['sessionyear']) ?></td>
                            <td class="text-center font-monospace fw-normal" style="font-size: 11px;"><?= htmlspecialchars($row['trxid']) ?></td>
                            <td class="text-center fw-normal"><?= !empty($row['payer_mobile']) ? htmlspecialchars($row['payer_mobile']) : '&mdash;' ?></td>
                            <td class="text-center text-muted fw-normal"><?= !empty($row['prno']) ? htmlspecialchars($row['prno']) : '&mdash;' ?></td>
                            <td class="text-end fw-normal text-dark"><?= number_format((float)$row['amount'], 2) ?></td>
                            <td class="text-center">
                                <span class="badge bg-label-success fw-normal"><?= htmlspecialchars($row['status']) ?></span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-normal btnDetails" 
                                    onclick="openPgwDetails(this)"
                                    data-id="<?= (int)$row['id'] ?>"
                                    data-stid="<?= htmlspecialchars($row['stid'] ?? '') ?>" 
                                    data-sccode="<?= (int)$row['sccode'] ?>"
                                    data-session="<?= htmlspecialchars($row['sessionyear'] ?? '') ?>"
                                    data-tokenid="<?= htmlspecialchars($row['token_id'] ?? '') ?>"
                                    data-prno="<?= htmlspecialchars($row['prno'] ?? '') ?>"
                                    data-source="<?= htmlspecialchars($row['source_table'] ?? '') ?>"
                                    data-trxid="<?= htmlspecialchars($row['trxid'] ?? '') ?>">
                                    Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal for Transaction Details -->
<div class="modal fade" id="detailsModal_pgw" tabindex="-1" aria-hidden="true" style="z-index: 5060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white pb-3">
                <h5 class="modal-title fw-normal text-white">Payment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="hidePgwModal()"></button>
            </div>
            <div class="modal-body p-3" id="detailsBody">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary fw-normal" data-bs-dismiss="modal" onclick="hidePgwModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    let pgwDetailsModalInstance = null;

    function getPgwModal() {
        const el = document.getElementById('detailsModal_pgw');
        if (!el) return null;
        if (!pgwDetailsModalInstance) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                pgwDetailsModalInstance = bootstrap.Modal.getOrCreateInstance(el);
            }
        }
        return pgwDetailsModalInstance;
    }

    function showPgwModal() {
        const el = document.getElementById('detailsModal_pgw');
        if (!el) return;
        try {
            const modal = getPgwModal();
            if (modal) {
                modal.show();
                return;
            }
        } catch (e) {
            console.warn('Bootstrap modal show warning:', e);
        }
        if (typeof $ !== 'undefined' && typeof $.fn.modal === 'function') {
            $('#detailsModal_pgw').modal('show');
        } else {
            el.classList.add('show');
            el.style.display = 'block';
            el.removeAttribute('aria-hidden');
        }
    }

    function hidePgwModal() {
        const el = document.getElementById('detailsModal_pgw');
        if (!el) return;
        try {
            const modal = getPgwModal();
            if (modal) {
                modal.hide();
                return;
            }
        } catch (e) {
            console.warn('Bootstrap modal hide warning:', e);
        }
        if (typeof $ !== 'undefined' && typeof $.fn.modal === 'function') {
            $('#detailsModal_pgw').modal('hide');
        } else {
            el.classList.remove('show');
            el.style.display = 'none';
            el.setAttribute('aria-hidden', 'true');
        }
    }

    function openPgwDetails(btn) {
        if (!btn) return;
        const $btn = (typeof $ !== 'undefined') ? $(btn) : null;
        const id = btn.getAttribute('data-id') || ($btn ? $btn.data('id') : '');
        const stid = btn.getAttribute('data-stid') || ($btn ? $btn.data('stid') : '');
        const sccode = btn.getAttribute('data-sccode') || ($btn ? $btn.data('sccode') : '');
        const session = btn.getAttribute('data-session') || ($btn ? $btn.data('session') : '');
        const tokenid = btn.getAttribute('data-tokenid') || ($btn ? $btn.data('tokenid') : '');
        const prno = btn.getAttribute('data-prno') || ($btn ? $btn.data('prno') : '');
        const source = btn.getAttribute('data-source') || ($btn ? $btn.data('source') : '');
        const trxid = btn.getAttribute('data-trxid') || ($btn ? $btn.data('trxid') : '');

        const bodyEl = document.getElementById('detailsBody');
        if (bodyEl) {
            bodyEl.innerHTML = '<div class="text-center p-4 text-muted fw-normal"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Loading fee details...</div>';
        }

        // 1. Immediately show modal
        showPgwModal();

        // 2. Trigger AJAX request
        const postData = {
            id: id,
            stid: stid,
            sccode: sccode,
            sessionyear: session,
            tokenid: tokenid,
            prno: prno,
            source: source,
            trxid: trxid
        };

        if (typeof $ !== 'undefined' && typeof $.ajax === 'function') {
            $.ajax({
                url: 'ajax/pgw-details-ajax.php',
                type: 'POST',
                data: postData,
                success: function (data) {
                    if (bodyEl) bodyEl.innerHTML = data;
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    if (bodyEl) bodyEl.innerHTML = '<div class="alert alert-danger p-3 fw-normal mb-0"><i class="bi bi-x-circle me-1"></i> Failed to load transaction details: ' + (error || status) + '</div>';
                }
            });
        } else {
            const params = new URLSearchParams();
            for (const k in postData) params.append(k, postData[k]);
            fetch('ajax/pgw-details-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(res => res.text())
            .then(html => {
                if (bodyEl) bodyEl.innerHTML = html;
            })
            .catch(err => {
                if (bodyEl) bodyEl.innerHTML = '<div class="alert alert-danger p-3 fw-normal mb-0"><i class="bi bi-x-circle me-1"></i> Failed to load: ' + err.message + '</div>';
            });
        }
    }

    $(document).ready(function () {
        try {
            if (typeof $.fn.DataTable !== 'undefined' && !$.fn.DataTable.isDataTable('#pgwTable')) {
                $('#pgwTable').DataTable({ 
                    pageLength: 25,
                    order: [[1, 'desc']],
                    language: {
                        search: '_INPUT_',
                        searchPlaceholder: 'Live filter results...'
                    }
                });
            }
        } catch (e) {
            console.warn('DataTables init skipped:', e);
        }

        $(document).on('click', '.btnDetails', function (e) {
            e.preventDefault();
            openPgwDetails(this);
        });
    });

    // -------------------------------------------------------------
    // Quick Date Range Setter
    // -------------------------------------------------------------
    function setDateRange(range) {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const todayStr = `${yyyy}-${mm}-${dd}`;

        if (range === 'today') {
            $('#date_from').val(todayStr);
            $('#date_to').val(todayStr);
        } else if (range === 'this_month') {
            $('#date_from').val(`${yyyy}-${mm}-01`);
            $('#date_to').val(todayStr);
        } else if (range === 'last_30') {
            const pastDate = new Date();
            pastDate.setDate(today.getDate() - 30);
            const p_yyyy = pastDate.getFullYear();
            const p_mm = String(pastDate.getMonth() + 1).padStart(2, '0');
            const p_dd = String(pastDate.getDate()).padStart(2, '0');
            $('#date_from').val(`${p_yyyy}-${p_mm}-${p_dd}`);
            $('#date_to').val(todayStr);
        } else if (range === 'this_year') {
            $('#date_from').val(`${yyyy}-01-01`);
            $('#date_to').val(`${yyyy}-12-31`);
        } else if (range === 'all') {
            $('#date_from').val('');
            $('#date_to').val('');
        }

        $('#filterForm').submit();
    }

    // -------------------------------------------------------------
    // Print Report
    // -------------------------------------------------------------
    function printReport() {
        const dateFrom = $('#date_from').val() || '';
        const dateTo = $('#date_to').val() || '';
        const gateway = $('#gateway').val() || 'all';
        const search = $('#search').val() || '';

        const url = 'pgw-collection.php?print=1'
                  + '&date_from=' + encodeURIComponent(dateFrom)
                  + '&date_to=' + encodeURIComponent(dateTo)
                  + '&gateway=' + encodeURIComponent(gateway)
                  + '&search=' + encodeURIComponent(search);

        window.open(url, '_blank');
    }
</script>
</body>
</html>