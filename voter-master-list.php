<?php
require_once 'header.php';

$slot = $_COOKIE['chain-slot'] ?? ($_GET['slot'] ?? '');
$sessionyear = $_COOKIE['chain-session'] ?? ($_GET['sessionyear'] ?? date('Y'));

$query = "
    SELECT 
        si.id, si.stid, si.classname, si.sectionname, si.rollno, si.voter_no,
        s.stnameeng, s.stnameben, s.fname, s.fnameben, s.mname, s.mnameben,
        s.fnid, s.mnid, s.fmobile, s.mmobile, s.guarmobile, s.guarname,
        s.previll, s.prepo, s.preps, s.predist
    FROM sessioninfo si
    JOIN students s ON si.stid = s.stid AND si.sccode = s.sccode
    WHERE si.sccode = ? AND si.sessionyear = ? AND si.status = 1
    ORDER BY 
      CASE WHEN si.voter_no IS NULL OR si.voter_no = 0 THEN 999999 ELSE si.voter_no END ASC,
      CASE 
        WHEN LOWER(TRIM(si.classname)) = 'play' THEN 1
        WHEN LOWER(TRIM(si.classname)) = 'nursery' THEN 2
        WHEN LOWER(TRIM(si.classname)) = 'kg' THEN 3
        WHEN LOWER(TRIM(si.classname)) IN ('one', '1', 'class 1', 'class one') THEN 4
        WHEN LOWER(TRIM(si.classname)) IN ('two', '2', 'class 2', 'class two') THEN 5
        WHEN LOWER(TRIM(si.classname)) IN ('three', '3', 'class 3', 'class three') THEN 6
        WHEN LOWER(TRIM(si.classname)) IN ('four', '4', 'class 4', 'class four') THEN 7
        WHEN LOWER(TRIM(si.classname)) IN ('five', '5', 'class 5', 'class five') THEN 8
        WHEN LOWER(TRIM(si.classname)) IN ('six', '6', 'class 6', 'class six') THEN 9
        WHEN LOWER(TRIM(si.classname)) IN ('seven', '7', 'class 7', 'class seven') THEN 10
        WHEN LOWER(TRIM(si.classname)) IN ('eight', '8', 'class 8', 'class eight') THEN 11
        WHEN LOWER(TRIM(si.classname)) IN ('nine', '9', 'class 9', 'class nine') THEN 12
        WHEN LOWER(TRIM(si.classname)) IN ('ten', '10', 'class 10', 'class ten') THEN 13
        WHEN LOWER(TRIM(si.classname)) IN ('eleven', '11', 'class 11', 'class eleven') THEN 14
        WHEN LOWER(TRIM(si.classname)) IN ('twelve', '12', 'class 12', 'class twelve') THEN 15
        ELSE 99
      END ASC,
      si.classname ASC,
      si.sectionname ASC,
      si.rollno ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $sccode, $sessionyear);
$stmt->execute();
$result = $stmt->get_result();

$voter_clusters = [];
$unassigned_students = [];

while ($row = $result->fetch_assoc()) {
    $vno = intval($row['voter_no'] ?? 0);
    if ($vno > 0) {
        if (!isset($voter_clusters[$vno])) {
            $voter_clusters[$vno] = [
                'voter_no' => $vno,
                'fname' => $row['fname'] ?: $row['fnameben'],
                'mname' => $row['mname'] ?: $row['mnameben'],
                'guarname' => $row['guarname'],
                'fnid' => $row['fnid'],
                'mnid' => $row['mnid'],
                'mobile' => $row['fmobile'] ?: ($row['mmobile'] ?: $row['guarmobile']),
                'village' => $row['previll'],
                'post' => $row['prepo'],
                'children' => []
            ];
        }
        $voter_clusters[$vno]['children'][] = [
            'stid' => $row['stid'],
            'name' => $row['stnameeng'] ?: $row['stnameben'],
            'classname' => $row['classname'],
            'sectionname' => $row['sectionname'],
            'rollno' => $row['rollno']
        ];
    } else {
        $unassigned_students[] = $row;
    }
}
$stmt->close();
?>

<style>
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 15mm 10mm;
        }
        body {
            background: #fff !important;
            color: #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-size: 12px;
        }
        .no-print, .layout-navbar, .layout-menu, .footer, .content-backdrop {
            display: none !important;
        }
        .container-xxl {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        .table-print {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .table-print th, .table-print td {
            border: 1px solid #333 !important;
            padding: 4px 6px !important;
        }
        .page-break {
            page-break-after: always;
        }
    }

    .table-voter th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: 600;
        text-align: center;
        border: 1px solid #dee2e6;
    }
    .table-voter td {
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    .child-badge {
        display: inline-block;
        background: #f1f3f5;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 2px 6px;
        margin: 2px 0;
        font-size: 11.5px;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Top Action Card -->
    <div class="card no-print mb-4 shadow-sm border-0">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1 text-primary fw-bold">
                        <i class="bi bi-person-lines-fill me-2"></i>মাস্টার ভোটার তালিকা (Electoral Roll)
                    </h4>
                    <p class="text-muted mb-0 small">
                        সেশন: <strong><?= htmlspecialchars($sessionyear) ?></strong> | মোট ভোটার: <strong><?= count($voter_clusters) ?> জন</strong>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="managing-voter-list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> ভোটার কন্ট্রোল প্যানেল
                    </a>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> প্রিন্ট ভোটার তালিকা
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Electoral Roll Printable Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            
            <!-- School Header -->
            <div class="text-center mb-4 pb-2 border-bottom">
                <h3 class="fw-bold mb-1" style="color: #1a237e;"><?= htmlspecialchars($scname ?? 'Educational Institution') ?></h3>
                <p class="text-muted mb-1"><?= htmlspecialchars($scaddress ?? '') ?></p>
                <h5 class="fw-bold mt-2 mb-1 text-dark">
                    ম্যানেজিং কমিটি নির্বাচন — চূড়ান্ত অভিভাবক ভোটার তালিকা
                </h5>
                <span class="badge bg-label-primary px-3 py-1">শিক্ষাবর্ষ: <?= htmlspecialchars($sessionyear) ?></span>
            </div>

            <?php if (!empty($voter_clusters)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-voter table-print align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 8%;" class="text-center">ভোটার নং</th>
                                <th style="width: 26%;">অভিভাবকের নাম ও NID</th>
                                <th style="width: 20%;">মোবাইল ও ঠিকানা</th>
                                <th style="width: 32%;">সন্তান/সিবলিংসের বিবরণ</th>
                                <th style="width: 14%;" class="text-center">স্বাক্ষর</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($voter_clusters as $vno => $voter): ?>
                                <tr>
                                    <td class="text-center fw-bold fs-6 text-primary">
                                        <?= str_pad($vno, 3, '0', STR_PAD_LEFT) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <?= htmlspecialchars($voter['fname'] ?: ($voter['mname'] ?: $voter['guarname'])) ?>
                                        </div>
                                        <?php if (!empty($voter['mname']) && $voter['mname'] !== $voter['fname']): ?>
                                            <div class="text-muted small">মাতা: <?= htmlspecialchars($voter['mname']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($voter['fnid'])): ?>
                                            <div class="small text-secondary"><i class="bi bi-card-text me-1"></i>NID (পিতা): <?= htmlspecialchars($voter['fnid']) ?></div>
                                        <?php elseif (!empty($voter['mnid'])): ?>
                                            <div class="small text-secondary"><i class="bi bi-card-text me-1"></i>NID (মাতা): <?= htmlspecialchars($voter['mnid']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($voter['mobile'])): ?>
                                            <div class="fw-semibold text-dark"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($voter['mobile']) ?></div>
                                        <?php endif; ?>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars(trim($voter['village'] . ($voter['post'] ? ', পো: ' . $voter['post'] : ''), ', ')) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php foreach ($voter['children'] as $idx => $child): ?>
                                            <div class="child-badge w-100 mb-1">
                                                <strong><?= ($idx + 1) ?>. <?= htmlspecialchars($child['name']) ?></strong> &mdash; 
                                                <span class="text-muted">শ্রেণি:</span> <strong><?= htmlspecialchars($child['classname']) ?></strong>, 
                                                <span class="text-muted">শাখা:</span> <?= htmlspecialchars($child['sectionname']) ?>, 
                                                <span class="text-muted">রোল:</span> <strong><?= htmlspecialchars($child['rollno']) ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                    </td>
                                    <td style="height: 48px;" class="text-center"></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary / Signature section for print -->
                <div class="mt-5 pt-4 d-flex justify-content-between text-center d-none d-print-flex">
                    <div style="width: 200px; border-top: 1px dashed #333; padding-top: 5px;">
                        সদস্য সচিব / প্রধান শিক্ষক
                    </div>
                    <div style="width: 200px; border-top: 1px dashed #333; padding-top: 5px;">
                        প্রিজাইডিং অফিসার / রিটার্নিং কর্মকর্তা
                    </div>
                </div>

            <?php else: ?>
                <div class="alert alert-warning text-center my-4">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    কোনো ভোটার নম্বর জেনারেট করা হয়নি। অনুগ্রহ করে <a href="managing-voter-list.php" class="alert-link fw-bold">ভোটার কন্ট্রোল প্যানেলে</a> গিয়ে <strong>"Generate Voter List"</strong> বাটনে ক্লিক করে ভোটার নম্বর তৈরি করুন।
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>
