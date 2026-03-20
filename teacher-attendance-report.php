<?php
require_once 'header.php';

// প্যারামিটার গ্রহণ
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

$weekendDays = [];
foreach ($sett as $row) {
    if ($row['setting_title'] == 'Weekends') {
        $weekendDays = explode(',', trim($row['settings_value']));
    }
}


// ১. শিক্ষক ডাটা
$teachers = [];
$t_res = $conn->query("SELECT tid, tname, position FROM teacher WHERE sccode = '$sccode' ORDER BY sl ASC");
while ($row = $t_res->fetch_assoc()) {
    $teachers[$row['tid']] = $row;
}

// ২. হাজিরা ডাটা
$attnd_data = [];
$a_res = $conn->query("SELECT tid, adate, realin, realout FROM teacherattnd 
                      WHERE sccode = '$sccode' AND MONTH(adate) = '$month' AND YEAR(adate) = '$year'");
while ($row = $a_res->fetch_assoc()) {
    $attnd_data[$row['tid']][$row['adate']] = $row;
}

// ৩. হলিডে (Holidays) ফেচ করা
// মাসের শুরু এবং শেষ তারিখ
$start = "$year-$month-01";
$end = "$year-$month-$days_in_month";


// --- ক্যালেন্ডার থেকে হলিডে ফেচ করা ---
$holidays = [];
$cq = $conn->prepare("SELECT date, dateto, category, work FROM calendar WHERE (sccode=? OR sccode=0) AND (date BETWEEN ? AND ? OR dateto BETWEEN ? AND ?)");
$cq->bind_param("issss", $sccode, $start, $end, $start, $end);
$cq->execute();
$cr = $cq->get_result();
while ($r = $cr->fetch_assoc()) {
    if (strtolower($r['work']) == '0') {
        $to = $r['dateto'] ?: $r['date'];
        $cur = $r['date'];
        while ($cur <= $to) {
            $holidays[$cur] = $r['work'];
            $cur = date("Y-m-d", strtotime("+1 day", strtotime($cur)));
        }
    }
}



// --- লিভ অ্যাপ্লিকেশন থেকে লিভ ফেচ করা (আপনার দেওয়া কোড) ---
$leave = [];
$lq = $conn->prepare("SELECT tid, date_from, date_to, leave_type FROM teacher_leave_app WHERE sccode=? AND status=1 AND date_from <= ? AND date_to >= ?");
$lq->bind_param("iss", $sccode, $end, $start);
$lq->execute();
$lr = $lq->get_result();
while ($r = $lr->fetch_assoc()) {
    $cur = $r['date_from'];
    while ($cur <= $r['date_to']) {
        $leave[$r['tid']][$cur] = strtolower($r['leave_type']);
        $cur = date("Y-m-d", strtotime("+1 day", strtotime($cur)));
    }
}

?>

<style>
    /* সাধারণ স্ক্রিন স্টাইল */
    .att-cell {
        font-size: 9px !important;
        line-height: 1.1;
        min-width: 42px;
        text-align: center;
        vertical-align: middle;
        height: 35px;
    }

    .time-in {
        color: #2e7d32;
        display: block;
        border-bottom: 1px dotted #ddd;
    }

    .time-out {
        color: #1565c0;
        display: block;
    }

    .status-text {
        font-size: 10px;
        font-weight: bold;
    }

    @media print {

        /* ১. সাদা ব্যাকগ্রাউন্ড নিশ্চিত করা এবং মার্জিন সেট করা */
        @page {
            size: A4 landscape;
            /* ল্যান্ডস্কেপ মোড বাধ্যতামূলক */
            margin: 10mm;
        }

        body {
            background: #fff !important;
            margin: 0;
            padding: 0;
        }

        /* ২. ড্যাশবোর্ডের সব কিছু লুকিয়ে ফেলা */
        .footer,
        .no-print,
        .layout-navbar,
        .layout-menu,
        .content-footer,nav {
            display: none !important;
        }

        /* ৩. প্রিন্ট ব্লককে দৃশ্যমান এবং পজিশন ঠিক করা */
        .container-xxl {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .d-print-block {
            display: block !important;
            position: static !important;
            /* Absolute সরিয়ে Static করা হয়েছে যাতে ওভারল্যাপ না হয় */
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* ৪. টেবিলের স্ক্রলবার রিমুভ করা */
        .table-responsive {
            display: block !important;
            overflow: visible !important;
            /* এতে স্ক্রলবার আসবে না, সব কলাম কাগজের সাইজে ফিট হবে */
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            table-layout: auto !important;
            /* কন্টেন্ট অনুযায়ী সেল বড়-ছোট হবে */
            font-size: 8px !important;
            /* প্রিন্টের জন্য ফন্ট সাইজ আরও ছোট করা হলো */
        }

        th,
        td {
            border: 1px solid #000 !important;
            /* কালো বর্ডার */
            padding: 1px !important;
            color: #000 !important;
            print-color-adjust: exact;
        }

        /* ৫. ব্যাকগ্রাউন্ড কালার (ছুটির দিন) প্রিন্ট নিশ্চিত করা */
        .bg-danger-subtle {
            background-color: #fce4ec !important;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        /* ওভারল্যাপ বন্ধ করতে সিগনেচার এরিয়া */
        .signature-area {
            margin-top: 50px !important;
            page-break-inside: avoid;
            /* সিগনেচার যেন ভেঙে অন্য পাতায় না যায় */
        }
    }
</style>


<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card mb-4 no-print shadow-none border">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Select Month</label>
                    <select name="month" class="form-select form-select-sm">
                        <?php for ($m = 1; $m <= 12; $m++):
                            $m_str = str_pad($m, 2, '0', STR_PAD_LEFT); ?>
                            <option value="<?= $m_str ?>" <?= $month == $m_str ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Year</label>
                    <select name="year" class="form-select form-select-sm">
                        <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-filter me-1"></i> Filter</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()"><i
                            class="bi bi-printer me-1"></i> Print</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportPDF()"><i
                            class="bi bi-file-pdf me-1"></i> PDF</button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-print-block card border-0 bg-white">

        <div class="d-none d-print-block text-center mb-4">
            <h3 class="mb-1 fw-bold" style="color:#000;">EIMBox Model School & College</h3>
            <p class="mb-0" style="font-size:12px;">প্রতিষ্ঠানের ঠিকানা, থানা, জেলা।</p>
            <p class="mb-1" style="font-size:12px;">Teacher Attendance & Time Register -
                <?= date('F, Y', mktime(0, 0, 0, $month, 1, $year)) ?>
            </p>
            <div style="border-top: 2px solid #000; margin-top: 5px;"></div>
        </div>


        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0">
                <thead style="background: #f2f2f2 !important;">
                    <tr>
                        <th class="ps-2 text-start" style="width: 120px; color:#000;">Teacher Name</th>
                        <?php for ($d = 1; $d <= $days_in_month; $d++):
                            $date_str = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);
                            $day_name = date('l', strtotime($date_str));
                            $is_weekend = in_array($day_name, $weekendDays);
                            $is_holiday = isset($holidays[$date_str]);
                            $bg = ($is_weekend || $is_holiday) ? 'bg-danger-subtle' : '';
                            ?>
                            <th class="<?= $bg ?> p-1" style="font-size: 8px; color:#000;">
                                <?= $d ?><br><small><?= substr(date('D', strtotime($date_str)), 0, 1) ?></small>
                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($teachers as $tid => $t): ?>
                        <tr>
                            <td class="ps-2 text-start">
                                <div class="fw-bold" style="font-size: 10px; color:#000;"><?= $t['tname'] ?></div>
                                <div style="font-size: 8px; color:#555;"><?= $t['position'] ?></div>
                            </td>

                            <?php for ($d = 1; $d <= $days_in_month; $d++):
                                $date_str = "$year-$month-" . str_pad($d, 2, '0', STR_PAD_LEFT);

                                // ডাটা চেক
                                $has_leave = $leave[$tid][$date_str] ?? null;
                                $holiday_name = $holidays[$date_str] ?? null;
                                $day_name = date('l', strtotime($date_str));
                                $is_weekend = in_array($day_name, $weekendDays);
                                $att = $attnd_data[$tid][$date_str] ?? null;
                                $is_holiday = isset($holidays[$date_str]);
                                // ব্যাকগ্রাউন্ড কালার নির্ধারণ
                                $bg = ($is_weekend || $is_holiday) ? 'bg-light' : '';
                                ?>
                                <td class="att-cell <?= $bg ?>">
                                    <?php
                                    if ($has_leave) {
                                        // লিভ থাকলে (যেমন: cl, sl) সেটি দেখাবে
                                        echo '<span class="text-primary fw-bold" title="Leave">' . strtoupper($has_leave) . '</span>';
                                    } elseif ($is_holiday) {
                                        // হলিডে থাকলে 'H'
                                        echo '<span class="text-danger fw-bold" title="' . $holiday_name . '">H</span>';
                                    } elseif ($is_weekend) {
                                        // সাপ্তাহিক ছুটি থাকলে 'W'
                                        echo '<span class="text-muted">W</span>';
                                    } elseif ($att) {
                                        // উপস্থিতি থাকলে ইন/আউট টাইম
                                        echo '<span class="time-in">' . date('h:i', strtotime($att['realin'])) . '</span>';
                                        echo '<span class="time-out">' . ($att['realout'] ? date('h:i', strtotime($att['realout'])) : '--') . '</span>';
                                    } elseif ($date_str <= date('Y-m-d')) {
                                        // কোনো ডাটা না থাকলে এবং তারিখটি অতীত হলে 'A' (Absent)
                                        echo '<span class="text-danger">A</span>';
                                    }
                                    ?>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <div class="d-none d-print-block signature-area mt-5">
            <table style="width: 100%; border: none !important;">
                <tr>
                    <td style="width: 33%; border: none !important; text-align: center;">
                        <div style="border-top: 1px solid #000; display: inline-block; padding: 5px 30px;">Prepared By
                        </div>
                    </td>
                    <td style="width: 34%; border: none !important;"></td>
                    <td style="width: 33%; border: none !important; text-align: center;">
                        <div style="border-top: 1px solid #000; display: inline-block; padding: 5px 30px;">Principal
                            Signature</div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</div>

<?php require_once 'footer.php'; ?>