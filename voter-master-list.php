<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';

$conn = db_connect();

if (empty($_SESSION['user_id']) || empty($sccode)) {
    echo "<div style='font-family:sans-serif; padding:30px; text-align:center;'><h3>Please login to view report.</h3></div>";
    exit;
}

$slot = $_COOKIE['chain-slot'] ?? ($_GET['slot'] ?? '');
$sessionyear = $_COOKIE['chain-session'] ?? ($_GET['sessionyear'] ?? date('Y'));

// Extract 2 digit year pattern (e.g., '26' matches 2026, 2025-26, 2026-27)
$yr_digits = preg_replace('/\D/', '', $sessionyear);
$yr_last2 = substr($yr_digits, -2);
$session_pattern = !empty($yr_last2) ? ('%' . $yr_last2 . '%') : ('%' . date('y') . '%');

// Fetch Institution Info
$sc_stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
$sc_stmt->bind_param("i", $sccode);
$sc_stmt->execute();
$institute = $sc_stmt->get_result()->fetch_assoc();
$sc_stmt->close();

$query = "
    SELECT 
        si.id, si.stid, si.sessionyear, si.classname, si.sectionname, si.rollno, si.voter_no,
        s.stnameeng, s.stnameben, s.fname, s.fnameben, s.mname, s.mnameben,
        s.fnid, s.mnid, s.fmobile, s.mmobile, s.guarmobile, s.guarname,
        s.previll, s.prepo, s.preps, s.predist,
        s.pervill, s.perpo, s.perps, s.perdist
    FROM sessioninfo si
    JOIN students s ON si.stid = s.stid AND si.sccode = s.sccode
    WHERE si.sccode = ? AND si.sessionyear LIKE ? AND si.status = 1
    AND (
        LOWER(TRIM(si.classname)) IN ('six', '6', 'class 6', 'class six')
        OR LOWER(TRIM(si.classname)) IN ('seven', '7', 'class 7', 'class seven')
        OR LOWER(TRIM(si.classname)) IN ('eight', '8', 'class 8', 'class eight')
        OR LOWER(TRIM(si.classname)) IN ('nine', '9', 'class 9', 'class nine')
        OR LOWER(TRIM(si.classname)) IN ('ten', '10', 'class 10', 'class ten')
        OR LOWER(TRIM(si.classname)) IN ('eleven', '11', 'class 11', 'class eleven', 'xi')
        OR LOWER(TRIM(si.classname)) IN ('twelve', '12', 'class 12', 'class twelve', 'xii')
    )
    ORDER BY 
      CASE WHEN si.voter_no IS NULL OR si.voter_no = 0 THEN 999999 ELSE si.voter_no END ASC,
      CASE 
        WHEN LOWER(TRIM(si.classname)) IN ('six', '6', 'class 6', 'class six') THEN 1
        WHEN LOWER(TRIM(si.classname)) IN ('seven', '7', 'class 7', 'class seven') THEN 2
        WHEN LOWER(TRIM(si.classname)) IN ('eight', '8', 'class 8', 'class eight') THEN 3
        WHEN LOWER(TRIM(si.classname)) IN ('nine', '9', 'class 9', 'class nine') THEN 4
        WHEN LOWER(TRIM(si.classname)) IN ('ten', '10', 'class 10', 'class ten') THEN 5
        WHEN LOWER(TRIM(si.classname)) IN ('eleven', '11', 'class 11', 'class eleven', 'xi') THEN 6
        WHEN LOWER(TRIM(si.classname)) IN ('twelve', '12', 'class 12', 'class twelve', 'xii') THEN 7
        ELSE 99
      END ASC,
      si.classname ASC,
      si.sectionname ASC,
      si.rollno ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $sccode, $session_pattern);
$stmt->execute();
$result = $stmt->get_result();

$voter_clusters = [];
$unassigned_students = [];
$total_enrolled_children = 0;

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
                'village' => $row['previll'] ?: ($row['pervill'] ?? ''),
                'post' => $row['prepo'] ?: ($row['perpo'] ?? ''),
                'ps' => $row['preps'] ?: ($row['perps'] ?? ''),
                'dist' => $row['predist'] ?: ($row['perdist'] ?? ''),
                'children' => []
            ];
        }
        $voter_clusters[$vno]['children'][] = [
            'stid' => $row['stid'],
            'name' => $row['stnameeng'] ?: $row['stnameben'],
            'classname' => $row['classname'],
            'sectionname' => $row['sectionname'],
            'rollno' => $row['rollno'],
            'sessionyear' => $row['sessionyear']
        ];
        $total_enrolled_children++;
    } else {
        $unassigned_students[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Electoral Roll (Guardian Voter List) - <?= htmlspecialchars($sessionyear) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 8mm 12mm 8mm;
        }
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #111;
            font-size: 11.5px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .report-wrapper {
            background: #fff;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
        }
        .inst-header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .report-table th, .report-table td {
            border: 1px solid #666;
            padding: 5px 6px;
            vertical-align: middle;
        }
        .report-table th {
            background-color: #f1f5f9 !important;
            font-weight: 700;
            text-align: center;
            color: #0f172a;
        }
        .child-item-row {
            padding: 3px 0;
            border-bottom: 1px dashed #cbd5e1;
        }
        .child-item-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .no-print-bar {
            background: #1e293b;
            padding: 10px 20px;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .signature-section {
            margin-top: 45px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .sig-block {
            text-align: center;
            width: 220px;
        }
        .sig-line {
            border-top: 1px dashed #333;
            padding-top: 4px;
            font-weight: 600;
            font-size: 11px;
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
                padding: 0;
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
            .voter-num-print {
                font-size: 12px !important;
                font-weight: 800 !important;
            }
        }
    </style>
</head>
<body>

<!-- STICKY TOP PRINT ENGINE / ACTION BAR -->
<div class="no-print-bar d-flex justify-content-between align-items-center">
    <div>
        <strong><i class="bi bi-file-earmark-text me-1"></i> Master Guardian Electoral Roll (Voter List) Report</strong>
        <span class="ms-2 text-white-50">Session: <?= htmlspecialchars($sessionyear) ?> | Total Voters: <strong><?= count($voter_clusters) ?></strong> | Total Students: <strong><?= $total_enrolled_children ?></strong></span>
    </div>
    <div class="d-flex gap-2">
        <a href="managing-voter-list.php" class="btn btn-sm btn-outline-light">
            <i class="bi bi-arrow-left me-1"></i> Control Panel
        </a>
        <a href="voter-siblings-report.php" target="_blank" class="btn btn-sm btn-outline-warning">
            <i class="bi bi-people-fill me-1"></i> Sibling Report
        </a>
        <button class="btn btn-sm btn-light fw-bold" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Print / Save PDF
        </button>
        <button class="btn btn-sm btn-outline-light" onclick="window.close()">
            <i class="bi bi-x-lg me-1"></i> Close
        </button>
    </div>
</div>

<div class="report-wrapper">
    <!-- Institutional Header -->
    <div class="inst-header">
        <h3 class="fw-bold mb-0 text-uppercase"><?= htmlspecialchars($institute['scname'] ?? 'EDUCATIONAL INSTITUTION') ?></h3>
        <div class="text-muted small">
            <?= htmlspecialchars($institute['scadd1'] ?? ($institute['scaddress'] ?? '')) ?>
            <?php if (!empty($institute['scadd2'])): ?>
                , <?= htmlspecialchars($institute['scadd2']) ?>
            <?php endif; ?>
        </div>
        <h5 class="fw-bold mt-2 mb-1 text-dark text-uppercase">
            <i class="bi bi-person-lines-fill me-1"></i> Managing Committee Election &mdash; Master Guardian Electoral Roll
        </h5>
        <div class="d-flex justify-content-center align-items-center gap-3 mt-1 text-muted" style="font-size: 11px;">
            <span><strong>Target Scope:</strong> Class Six &ndash; Twelve</span>
            <span>&bull;</span>
            <span><strong>Academic Session:</strong> <?= htmlspecialchars($sessionyear) ?> (<?= htmlspecialchars($session_pattern) ?>)</span>
            <span>&bull;</span>
            <span><strong>Total Unique Voters:</strong> <?= count($voter_clusters) ?></span>
            <span>&bull;</span>
            <span><strong>Printed Date:</strong> <?= date('d M Y, h:i A') ?></span>
        </div>
    </div>

    <?php if (!empty($voter_clusters)): ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 7%;" class="text-center">Voter No.</th>
                    <th style="width: 26%; text-align: left;">Guardian / Parent Details & NID</th>
                    <th style="width: 20%; text-align: left;">Contact & Address</th>
                    <th style="width: 33%; text-align: left;">Enrolled Children / Student Details</th>
                    <th style="width: 14%; text-align: center;">Signature / Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($voter_clusters as $vno => $voter): ?>
                    <tr>
                        <td class="text-center fw-bold voter-num-print text-primary" style="font-size: 13px;">
                            <?= str_pad($vno, 3, '0', STR_PAD_LEFT) ?>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="font-size: 11.5px;">
                                <?= htmlspecialchars($voter['fname'] ?: ($voter['mname'] ?: $voter['guarname'])) ?>
                            </div>
                            <?php if (!empty($voter['mname']) && $voter['mname'] !== $voter['fname']): ?>
                                <div class="text-muted" style="font-size: 10px;">Mother: <?= htmlspecialchars($voter['mname']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($voter['fnid'])): ?>
                                <div class="text-dark" style="font-size: 10px;">
                                    <i class="bi bi-card-heading me-1 text-secondary"></i>Father NID: <strong><?= htmlspecialchars($voter['fnid']) ?></strong>
                                </div>
                            <?php elseif (!empty($voter['mnid'])): ?>
                                <div class="text-dark" style="font-size: 10px;">
                                    <i class="bi bi-card-heading me-1 text-secondary"></i>Mother NID: <strong><?= htmlspecialchars($voter['mnid']) ?></strong>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($voter['mobile'])): ?>
                                <div class="fw-semibold text-dark mb-1">
                                    <i class="bi bi-telephone-fill text-success me-1"></i><?= htmlspecialchars($voter['mobile']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="text-muted" style="font-size: 10px;">
                                <i class="bi bi-geo-alt-fill text-secondary me-1"></i>
                                <?= htmlspecialchars($voter['village'] ?: '—') ?>
                                <?= !empty($voter['post']) ? (', PO: ' . htmlspecialchars($voter['post'])) : '' ?>
                                <?= !empty($voter['ps']) ? (', ' . htmlspecialchars($voter['ps'])) : '' ?>
                            </div>
                        </td>
                        <td>
                            <?php foreach ($voter['children'] as $cidx => $child): ?>
                                <div class="child-item-row">
                                    <?php if (count($voter['children']) > 1): ?>
                                        <span class="badge bg-secondary me-1 py-0 px-1" style="font-size: 9px;"><?= $cidx + 1 ?></span>
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($child['name']) ?></strong>
                                    <span class="text-primary fw-semibold">[ID: <?= htmlspecialchars($child['stid']) ?>]</span>
                                    <div class="text-muted ps-2" style="font-size: 10px;">
                                        Class: <strong><?= htmlspecialchars($child['classname']) ?></strong> | 
                                        Sec: <strong><?= htmlspecialchars($child['sectionname'] ?: 'All') ?></strong> | 
                                        Roll: <strong><?= htmlspecialchars($child['rollno']) ?></strong> | 
                                        Session: <strong><?= htmlspecialchars($child['sessionyear']) ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td style="height: 38px;" class="text-center"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Official Signatures Section -->
        <div class="signature-section">
            <div class="sig-block">
                <div class="sig-line">Prepared & Verified By</div>
            </div>
            <div class="sig-block">
                <div class="sig-line">Member Secretary / Convener</div>
            </div>
            <div class="sig-block">
                <div class="sig-line">Head of Institution / Returning Officer</div>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-warning text-center py-4 my-4">
            <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>No Voter Numbers Assigned Yet</h5>
            <p class="mb-0">Please open the <a href="managing-voter-list.php" class="alert-link fw-bold">Voter Control Panel</a> and click <strong>"Generate Voter Numbers"</strong> first.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
