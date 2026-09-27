<?php
require_once 'header.php';

$slot = $_COOKIE['chain-slot'] ?? ($_GET['slot'] ?? '');
$sessionyear = $_COOKIE['chain-session'] ?? ($_GET['sessionyear'] ?? date('Y'));

// Extract 2 digit year pattern (e.g., '26' matches 2026, 2025-26, 2026-27)
$yr_digits = preg_replace('/\D/', '', $sessionyear);
$yr_last2 = substr($yr_digits, -2);
$session_pattern = !empty($yr_last2) ? ('%' . $yr_last2 . '%') : ('%' . date('y') . '%');

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

<style>
    /* Sticky Dark Toolbar Header */
    .no-print-bar {
        background: #1e293b;
        color: #f8fafc;
        padding: 12px 24px;
        position: sticky;
        top: 0;
        z-index: 1050;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .no-print-bar .badge-stat {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    /* Print & Document Styles */
    .printable-sheet {
        background: #fff;
        max-width: 1100px;
        margin: 20px auto;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .table-siblings {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }
    .table-siblings th {
        background-color: #f1f5f9;
        color: #1e293b;
        font-weight: 700;
        text-align: center;
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
    }
    .table-siblings td {
        border: 1px solid #cbd5e1;
        padding: 8px 10px;
        vertical-align: middle;
    }
    .sibling-item-row {
        padding: 6px 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    .sibling-item-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .signature-area {
        margin-top: 60px;
        display: flex;
        justify-content: space-between;
        text-align: center;
    }
    .signature-box {
        width: 200px;
        border-top: 1px dashed #475569;
        padding-top: 6px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #1e293b;
    }

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
            font-size: 11.5px;
            padding: 0 !important;
            margin: 0 !important;
        }
        .no-print-bar,
        .no-print,
        .layout-navbar,
        .layout-menu,
        .footer,
        .content-backdrop,
        nav {
            display: none !important;
        }
        .container-xxl,
        .content-wrapper {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        .printable-sheet {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        .table-siblings th,
        .table-siblings td {
            border: 1px solid #333 !important;
            padding: 5px 6px !important;
        }
        .table-siblings tr {
            page-break-inside: avoid !important;
        }
        thead {
            display: table-header-group !important;
        }
        .badge-voter {
            border: 1px solid #000 !important;
            color: #000 !important;
            background: transparent !important;
        }
    }
</style>

<!-- Sticky Dark Header / Toolbar -->
<div class="no-print-bar d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center flex-wrap gap-3">
        <h5 class="mb-0 text-white fw-bold">
            <i class="bi bi-people-fill text-warning me-2"></i> EIMBox Sibling Students & Multi-Child Families Report
        </h5>
        <div class="d-flex align-items-center gap-2">
            <span class="badge-stat">
                <i class="bi bi-house-door me-1"></i> Sibling Families: <strong><?= count($sibling_groups) ?></strong>
            </span>
            <span class="badge-stat">
                <i class="bi bi-mortarboard me-1"></i> Total Enrolled Children: <strong><?= $total_sibling_students ?></strong>
            </span>
            <span class="badge-stat">
                <i class="bi bi-calendar3 me-1"></i> Session: <strong><?= htmlspecialchars($sessionyear) ?></strong>
            </span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="managing-voter-list.php" class="btn btn-sm btn-outline-light">
            <i class="bi bi-arrow-left me-1"></i> Control Panel
        </a>
        <a href="voter-master-list.php" class="btn btn-sm btn-outline-info">
            <i class="bi bi-file-earmark-text me-1"></i> Master Voter List
        </a>
        <button class="btn btn-sm btn-warning fw-bold px-3" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Print / Save PDF
        </button>
        <button class="btn btn-sm btn-outline-secondary text-white" onclick="window.close()" title="Close Viewer">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
</div>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="printable-sheet">

        <!-- Institution & Report Header -->
        <div class="text-center mb-4 pb-3 border-bottom">
            <h3 class="fw-bold mb-1" style="color: #0f172a;"><?= htmlspecialchars($scname ?? 'Educational Institution') ?></h3>
            <p class="text-muted mb-2 small"><?= htmlspecialchars($scaddress ?? '') ?></p>
            <h5 class="fw-bold mb-1 text-dark text-uppercase letter-spacing-1">
                <i class="bi bi-diagram-3 me-1"></i> Sibling Students / Multi-Child Family Audit Report
            </h5>
            <div class="d-flex justify-content-center align-items-center gap-3 mt-2 text-muted small">
                <span><strong>Target Classes:</strong> Six &ndash; Twelve</span>
                <span>&bull;</span>
                <span><strong>Academic Session:</strong> <?= htmlspecialchars($sessionyear) ?> (<?= htmlspecialchars($session_pattern) ?>)</span>
                <span>&bull;</span>
                <span><strong>Generated On:</strong> <?= date('d M Y, h:i A') ?></span>
            </div>
        </div>

        <?php if (!empty($sibling_groups)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-siblings align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 5%;" class="text-center">SL</th>
                            <th style="width: 25%;">Guardian & Parents' Information</th>
                            <th style="width: 22%;">Contact & Residential Address</th>
                            <th style="width: 40%;">Enrolled Sibling Students Details</th>
                            <th style="width: 8%;" class="text-center">Siblings Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sibling_groups as $idx => $group): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted">
                                    <?= $idx + 1 ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-6 mb-1">
                                        <i class="bi bi-person-fill text-primary me-1"></i>
                                        <?= htmlspecialchars($group['guardian_name'] ?: '—') ?>
                                    </div>
                                    <?php if (!empty($group['fname']) && $group['fname'] !== $group['guardian_name']): ?>
                                        <div class="small text-muted">Father: <?= htmlspecialchars($group['fname']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($group['mname'])): ?>
                                        <div class="small text-muted">Mother: <?= htmlspecialchars($group['mname']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($group['fnid'])): ?>
                                        <div class="small text-dark mt-1">
                                            <i class="bi bi-card-heading text-secondary me-1"></i>F-NID: <strong><?= htmlspecialchars($group['fnid']) ?></strong>
                                        </div>
                                    <?php elseif (!empty($group['mnid'])): ?>
                                        <div class="small text-dark mt-1">
                                            <i class="bi bi-card-heading text-secondary me-1"></i>M-NID: <strong><?= htmlspecialchars($group['mnid']) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($group['mobile'])): ?>
                                        <div class="fw-semibold text-dark mb-1">
                                            <i class="bi bi-telephone-fill text-success me-1"></i><?= htmlspecialchars($group['mobile']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="small text-muted">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        <?= htmlspecialchars($group['village'] ?: '—') ?>
                                        <?= !empty($group['post']) ? (', ' . htmlspecialchars($group['post'])) : '' ?>
                                        <?= !empty($group['ps']) ? (', ' . htmlspecialchars($group['ps'])) : '' ?>
                                    </div>
                                </td>
                                <td>
                                    <?php foreach ($group['children'] as $cidx => $child): ?>
                                        <div class="sibling-item-row d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div>
                                                <span class="badge bg-light text-dark border me-1 fw-bold"><?= $cidx + 1 ?></span>
                                                <strong><?= htmlspecialchars($child['stnameeng'] ?: $child['stnameben']) ?></strong>
                                                <span class="text-primary small fw-semibold ms-1">[ID: <?= htmlspecialchars($child['stid']) ?>]</span>
                                                <div class="small text-muted ps-4">
                                                    Class: <strong><?= htmlspecialchars($child['classname']) ?></strong> | 
                                                    Sec: <strong><?= htmlspecialchars($child['sectionname'] ?: 'All') ?></strong> | 
                                                    Roll: <strong><?= htmlspecialchars($child['rollno']) ?></strong> | 
                                                    Session: <strong><?= htmlspecialchars($child['sessionyear']) ?></strong>
                                                </div>
                                            </div>
                                            <div>
                                                <?php if (!empty($child['voter_no']) && intval($child['voter_no']) > 0): ?>
                                                    <span class="badge bg-label-primary border badge-voter">
                                                        Voter: #<?= str_pad($child['voter_no'], 3, '0', STR_PAD_LEFT) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-label-secondary border small text-muted">No Voter #</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill fs-6 px-3">
                                        <?= count($group['children']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary & Signature Block -->
            <div class="row mt-4 pt-3 align-items-center">
                <div class="col-6">
                    <div class="card bg-light border-0 p-3 small text-muted">
                        <div><i class="bi bi-check-circle-fill text-success me-1"></i> Audit Scope: <strong>Class Six to Twelve (Secondary & Higher Secondary)</strong></div>
                        <div><i class="bi bi-info-circle-fill text-primary me-1"></i> Sibling identification algorithm uses verified Parent NID with secondary Mobile & Parent Name similarity matching.</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="signature-area">
                        <div class="signature-box">Prepared / Verified By</div>
                        <div class="signature-box">Head of Institution / Returning Officer</div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-info text-center py-4 my-4">
                <i class="bi bi-info-circle-fill fs-3 mb-2 d-block text-info"></i>
                <h5 class="fw-bold">No Sibling Clusters Found</h5>
                <p class="text-muted mb-0">No multi-student families were detected matching the current session criteria.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'footer.php'; ?>
