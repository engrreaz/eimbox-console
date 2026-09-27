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

function clean_phone($phone) {
    if (!$phone) return '';
    $digits = preg_replace('/\D/', '', $phone);
    if (empty($digits)) return '';
    if (strlen($digits) == 13 && str_starts_with($digits, '8801')) {
        return substr($digits, 2);
    }
    return $digits;
}

function clean_nid($nid) {
    if (!$nid) return '';
    $digits = preg_replace('/\D/', '', $nid);
    if (strlen($digits) >= 10) {
        return $digits;
    }
    return '';
}

// Fetch active students strictly for Classes Six to Twelve with matching Session pattern
$query = "
    SELECT 
        si.id as sessioninfo_id, si.stid, si.sessionyear, si.classname, si.sectionname, si.rollno, si.voter_no, si.slot,
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
$res = $stmt->get_result();

$students = [];
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// Disjoint Set (Union-Find) clustering
$parent = [];
foreach ($students as $idx => $st) {
    $parent[$idx] = $idx;
}

function find_root_report(&$parent, $i) {
    if ($parent[$i] == $i) return $i;
    $parent[$i] = find_root_report($parent, $parent[$i]);
    return $parent[$i];
}

function union_sets_report(&$parent, $i, $j) {
    $root_i = find_root_report($parent, $i);
    $root_j = find_root_report($parent, $j);
    if ($root_i != $root_j) {
        $parent[$root_i] = $root_j;
    }
}

$nid_to_indices = [];
$mobile_to_indices = [];

foreach ($students as $idx => $st) {
    $fnid = clean_nid($st['fnid'] ?? '');
    $mnid = clean_nid($st['mnid'] ?? '');
    $fm = clean_phone($st['fmobile'] ?? '');
    $mm = clean_phone($st['mmobile'] ?? '');
    $gm = clean_phone($st['guarmobile'] ?? '');

    if (!empty($fnid)) $nid_to_indices[$fnid][] = $idx;
    if (!empty($mnid)) $nid_to_indices[$mnid][] = $idx;

    if (!empty($fm)) $mobile_to_indices[$fm][] = $idx;
    if (!empty($mm)) $mobile_to_indices[$mm][] = $idx;
    if (!empty($gm)) $mobile_to_indices[$gm][] = $idx;
}

// Connect NID matches (Priority 1)
foreach ($nid_to_indices as $nid => $indices) {
    $first = $indices[0];
    for ($k = 1; $k < count($indices); $k++) {
        union_sets_report($parent, $first, $indices[$k]);
    }
}

// Connect Mobile matches (Priority 2 - with basic sanity)
foreach ($mobile_to_indices as $phone => $indices) {
    $first = $indices[0];
    for ($k = 1; $k < count($indices); $k++) {
        $curr = $indices[$k];
        $f1 = strtolower(trim($students[$first]['fname'] ?? ''));
        $f2 = strtolower(trim($students[$curr]['fname'] ?? ''));
        $v1 = strtolower(trim($students[$first]['previll'] ?? ''));
        $v2 = strtolower(trim($students[$curr]['previll'] ?? ''));

        $name_match = (empty($f1) || empty($f2) || soundex($f1) == soundex($f2) || levenshtein($f1, $f2) <= 4 || str_contains($f1, $f2) || str_contains($f2, $f1));
        $village_match = (empty($v1) || empty($v2) || $v1 === $v2);

        if ($name_match || $village_match) {
            union_sets_report($parent, $first, $curr);
        }
    }
}

// Group into clusters
$clusters = [];
foreach ($students as $idx => $st) {
    $root = find_root_report($parent, $idx);
    $clusters[$root][] = $st;
}

// Filter only multi-child sibling clusters (count > 1)
$sibling_groups = [];
$total_sibling_students = 0;

foreach ($clusters as $root => $members) {
    if (count($members) > 1) {
        $primary = $members[0];
        $sibling_groups[] = [
            'guardian_name' => $primary['fname'] ?: ($primary['fnameben'] ?: ($primary['mname'] ?: ($primary['guarname'] ?? ''))),
            'fname' => $primary['fname'] ?: ($primary['fnameben'] ?? ''),
            'mname' => $primary['mname'] ?: ($primary['mnameben'] ?? ''),
            'fnid' => $primary['fnid'] ?? '',
            'mnid' => $primary['mnid'] ?? '',
            'mobile' => $primary['fmobile'] ?: ($primary['mmobile'] ?: ($primary['guarmobile'] ?? '')),
            'village' => $primary['previll'] ?: ($primary['pervill'] ?? ''),
            'post' => $primary['prepo'] ?: ($primary['perpo'] ?? ''),
            'ps' => $primary['preps'] ?: ($primary['perps'] ?? ''),
            'dist' => $primary['predist'] ?: ($primary['perdist'] ?? ''),
            'children' => $members
        ];
        $total_sibling_students += count($members);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sibling Students / Multi-Child Family Audit Report - <?= htmlspecialchars($sessionyear) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 8mm 12mm 8mm;
        }
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
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
        .sibling-item-row {
            padding: 4px 0;
            border-bottom: 1px dashed #cbd5e1;
        }
        .sibling-item-row:last-child {
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
            width: 200px;
        }
        .sig-line {
            border-top: 1px dashed #333;
            padding-top: 4px;
            font-weight: 600;
            font-size: 11px;
        }
        @media print {
            body {
                background: #fff;
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
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .report-table tr {
                page-break-inside: avoid !important;
            }
            thead {
                display: table-header-group !important;
            }
            .badge-voter-print {
                border: 1px solid #000 !important;
                background: transparent !important;
                color: #000 !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print-bar d-flex justify-content-between align-items-center">
    <div>
        <strong><i class="bi bi-people-fill me-1"></i> Sibling Students & Multi-Child Family Audit Report</strong>
        <span class="ms-2 text-white-50">Session: <?= htmlspecialchars($sessionyear) ?> | Families: <strong><?= count($sibling_groups) ?></strong> | Total Children: <strong><?= $total_sibling_students ?></strong></span>
    </div>
    <div class="d-flex gap-2">
        <a href="managing-voter-list.php" class="btn btn-sm btn-outline-light">
            <i class="bi bi-arrow-left me-1"></i> Control Panel
        </a>
        <a href="voter-master-list.php" class="btn btn-sm btn-outline-info">
            <i class="bi bi-file-earmark-text me-1"></i> Master Voter List
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
    <!-- Header -->
    <div class="inst-header">
        <h3 class="fw-bold mb-0 text-uppercase"><?= htmlspecialchars($institute['scname'] ?? 'EDUCATIONAL INSTITUTION') ?></h3>
        <div class="text-muted small">
            <?= htmlspecialchars($institute['scadd1'] ?? ($institute['scaddress'] ?? '')) ?>
            <?php if (!empty($institute['scadd2'])): ?>
                , <?= htmlspecialchars($institute['scadd2']) ?>
            <?php endif; ?>
        </div>
        <h5 class="fw-bold mt-2 mb-1 text-dark text-uppercase">
            <i class="bi bi-diagram-3 me-1"></i> Sibling Students / Multi-Child Family Audit Report
        </h5>
        <div class="d-flex justify-content-center align-items-center gap-3 mt-1 text-muted" style="font-size: 11px;">
            <span><strong>Target Scope:</strong> Class Six &ndash; Twelve</span>
            <span>&bull;</span>
            <span><strong>Session Year:</strong> <?= htmlspecialchars($sessionyear) ?> (<?= htmlspecialchars($session_pattern) ?>)</span>
            <span>&bull;</span>
            <span><strong>Printed Date:</strong> <?= date('d M Y, h:i A') ?></span>
        </div>
    </div>

    <?php if (!empty($sibling_groups)): ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 5%;">SL</th>
                    <th style="width: 25%; text-align: left;">Guardian / Parents Details</th>
                    <th style="width: 22%; text-align: left;">Contact & Address</th>
                    <th style="width: 40%; text-align: left;">Enrolled Sibling Students Details</th>
                    <th style="width: 8%;">Count</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sibling_groups as $idx => $group): ?>
                    <tr>
                        <td class="text-center fw-bold text-muted"><?= $idx + 1 ?></td>
                        <td>
                            <div class="fw-bold text-dark">
                                <?= htmlspecialchars($group['guardian_name'] ?: '—') ?>
                            </div>
                            <?php if (!empty($group['fname']) && $group['fname'] !== $group['guardian_name']): ?>
                                <div class="text-muted" style="font-size: 10px;">Father: <?= htmlspecialchars($group['fname']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($group['mname'])): ?>
                                <div class="text-muted" style="font-size: 10px;">Mother: <?= htmlspecialchars($group['mname']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($group['fnid'])): ?>
                                <div class="text-dark" style="font-size: 10px;"><i class="bi bi-card-heading me-1"></i>NID: <strong><?= htmlspecialchars($group['fnid']) ?></strong></div>
                            <?php elseif (!empty($group['mnid'])): ?>
                                <div class="text-dark" style="font-size: 10px;"><i class="bi bi-card-heading me-1"></i>NID: <strong><?= htmlspecialchars($group['mnid']) ?></strong></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($group['mobile'])): ?>
                                <div class="fw-semibold text-dark mb-1">
                                    <i class="bi bi-telephone-fill text-success me-1"></i><?= htmlspecialchars($group['mobile']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="text-muted" style="font-size: 10px;">
                                <i class="bi bi-geo-alt-fill text-secondary me-1"></i>
                                <?= htmlspecialchars($group['village'] ?: '—') ?>
                                <?= !empty($group['post']) ? (', ' . htmlspecialchars($group['post'])) : '' ?>
                                <?= !empty($group['ps']) ? (', ' . htmlspecialchars($group['ps'])) : '' ?>
                            </div>
                        </td>
                        <td>
                            <?php foreach ($group['children'] as $cidx => $child): ?>
                                <div class="sibling-item-row d-flex justify-content-between align-items-center flex-wrap gap-1">
                                    <div>
                                        <span class="badge bg-light text-dark border me-1 fw-bold"><?= $cidx + 1 ?></span>
                                        <strong><?= htmlspecialchars($child['stnameeng'] ?: $child['stnameben']) ?></strong>
                                        <span class="text-primary fw-semibold">[ID: <?= htmlspecialchars($child['stid']) ?>]</span>
                                        <div class="text-muted ps-3" style="font-size: 10px;">
                                            Class: <strong><?= htmlspecialchars($child['classname']) ?></strong> | 
                                            Sec: <strong><?= htmlspecialchars($child['sectionname'] ?: 'All') ?></strong> | 
                                            Roll: <strong><?= htmlspecialchars($child['rollno']) ?></strong> | 
                                            Session: <strong><?= htmlspecialchars($child['sessionyear']) ?></strong>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if (!empty($child['voter_no']) && intval($child['voter_no']) > 0): ?>
                                            <span class="badge bg-primary badge-voter-print">
                                                Voter #<?= str_pad($child['voter_no'], 3, '0', STR_PAD_LEFT) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td class="text-center fw-bold fs-6">
                            <?= count($group['children']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="sig-block">
                <div class="sig-line">Prepared / Audited By</div>
            </div>
            <div class="sig-block">
                <div class="sig-line">Convener / Member Secretary</div>
            </div>
            <div class="sig-block">
                <div class="sig-line">Head of Institution / Returning Officer</div>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-secondary text-center py-4 my-4">
            <h5>No Sibling Clusters Detected</h5>
            <p class="mb-0">No multi-student families were found matching the selected session.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
