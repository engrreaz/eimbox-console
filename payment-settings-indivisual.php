<?php
require_once 'header.php';

$year = trim($_GET['year'] ?? date('Y'));
$cls = trim($_GET['cls'] ?? '');
$sec = trim($_GET['sec'] ?? '');
$roll = trim($_GET['roll'] ?? '');
$stid = trim($_GET['stid'] ?? '');
$slot = trim($_GET['slot'] ?? '');

$student = null;
$stidResolved = '';
$rate = 100;
$rollnoResolved = $roll;

// 1. Resolve Student from sessioninfo & students
if (!empty($stid)) {
    $stSql = "
        SELECT 
            si.id AS sessioninfo_id,
            si.stid, 
            si.rollno, 
            si.rate, 
            si.slot,
            si.classname,
            si.sectionname,
            COALESCE(NULLIF(s.stnameeng, ''), 'Student') AS stnameeng,
            COALESCE(NULLIF(s.stnameben, ''), '') AS stnameben,
            s.fname,
            s.mname,
            s.fmobile,
            s.photo
        FROM sessioninfo si 
        LEFT JOIN students s ON si.stid = s.stid AND (s.sccode = si.sccode OR s.sccode = 0 OR s.sccode IS NULL)
        WHERE si.sccode = ? 
          AND (si.sessionyear LIKE ? OR si.sessionyear = '' OR si.sessionyear IS NULL)
          AND si.stid = ?
        LIMIT 1
    ";
    $syParam = "%$year%";
    $stStmt = $conn->prepare($stSql);
    if ($stStmt) {
        $stStmt->bind_param("iss", $sccode, $syParam, $stid);
        $stStmt->execute();
        $stRes = $stStmt->get_result();
        if ($stRow = $stRes->fetch_assoc()) {
            $student = $stRow;
            $stidResolved = (string)$student['stid'];
            $rate = intval($student['rate'] ?? 100);
            $rollnoResolved = (string)$student['rollno'];
            $cls = $student['classname'] ?: $cls;
            $sec = $student['sectionname'] ?: $sec;
            $slot = $student['slot'] ?: $slot;
        }
        $stStmt->close();
    }
} elseif (!empty($cls) && !empty($roll)) {
    $stSql = "
        SELECT 
            si.id AS sessioninfo_id,
            si.stid, 
            si.rollno, 
            si.rate, 
            si.slot,
            si.classname,
            si.sectionname,
            COALESCE(NULLIF(s.stnameeng, ''), 'Student') AS stnameeng,
            COALESCE(NULLIF(s.stnameben, ''), '') AS stnameben,
            s.fname,
            s.mname,
            s.fmobile,
            s.photo
        FROM sessioninfo si 
        LEFT JOIN students s ON si.stid = s.stid AND (s.sccode = si.sccode OR s.sccode = 0 OR s.sccode IS NULL)
        WHERE si.sccode = ? 
          AND (si.sessionyear LIKE ? OR si.sessionyear = '' OR si.sessionyear IS NULL)
          AND si.classname = ?
    ";
    $syParam = "%$year%";
    $params = [$sccode, $syParam, $cls];
    $types = "iss";
    
    if (!empty($sec) && $sec !== 'Select Section' && $sec !== 'All') {
        $stSql .= " AND si.sectionname = ?";
        $params[] = $sec;
        $types .= "s";
    }
    
    $rollInt = intval($roll);
    $stSql .= " AND (si.rollno = ? OR CAST(si.rollno AS CHAR) = ?) LIMIT 1";
    $params[] = $rollInt;
    $params[] = (string)$roll;
    $types .= "is";
    
    $stStmt = $conn->prepare($stSql);
    if ($stStmt) {
        $stStmt->bind_param($types, ...$params);
        $stStmt->execute();
        $stRes = $stStmt->get_result();
        if ($stRow = $stRes->fetch_assoc()) {
            $student = $stRow;
            $stidResolved = (string)$student['stid'];
            $rate = intval($student['rate'] ?? 100);
            $rollnoResolved = (string)$student['rollno'];
            $cls = $student['classname'] ?: $cls;
            $sec = $student['sectionname'] ?: $sec;
            $slot = $student['slot'] ?: $slot;
        }
        $stStmt->close();
    }
}

// 2. Fetch list of all students in this section for the student dropdown & navigation
$sectionStudents = [];
if (!empty($cls)) {
    $secStSql = "
        SELECT 
            si.stid, 
            si.rollno, 
            si.rate, 
            si.sectionname,
            COALESCE(NULLIF(s.stnameeng, ''), 'Student') AS stnameeng,
            COALESCE(NULLIF(s.stnameben, ''), '') AS stnameben
        FROM sessioninfo si 
        LEFT JOIN students s ON si.stid = s.stid AND (s.sccode = si.sccode OR s.sccode = 0 OR s.sccode IS NULL)
        WHERE si.sccode = ? 
          AND (si.sessionyear LIKE ? OR si.sessionyear = '' OR si.sessionyear IS NULL)
          AND si.classname = ?
    ";
    $syParam = "%$year%";
    $params = [$sccode, $syParam, $cls];
    $types = "iss";
    
    if (!empty($sec) && $sec !== 'Select Section' && $sec !== 'All' && $sec !== 'All Sections') {
        $secStSql .= " AND (TRIM(si.sectionname) = TRIM(?) OR si.sectionname = ?)";
        $params[] = $sec;
        $params[] = $sec;
        $types .= "ss";
    }
    
    $secStSql .= " ORDER BY CAST(si.rollno AS UNSIGNED) ASC, si.rollno ASC";
    
    $secStmt = $conn->prepare($secStSql);
    if ($secStmt) {
        $secStmt->bind_param($types, ...$params);
        $secStmt->execute();
        $secRes = $secStmt->get_result();
        while ($r = $secRes->fetch_assoc()) {
            $sectionStudents[] = $r;
        }
        $secStmt->close();
    }
}

// Find Prev and Next student
$prevStid = null;
$nextStid = null;
if (!empty($stidResolved) && !empty($sectionStudents)) {
    $totalSt = count($sectionStudents);
    for ($i = 0; $i < $totalSt; $i++) {
        if ($sectionStudents[$i]['stid'] == $stidResolved) {
            if ($i > 0) $prevStid = $sectionStudents[$i - 1]['stid'];
            if ($i < $totalSt - 1) $nextStid = $sectionStudents[$i + 1]['stid'];
            break;
        }
    }
}

/* ======================================================
   3. LOAD MASTER ITEM LIST (financesetup)
====================================================== */
$finItems = [];
$syParam = "%$year%";
$fStmt = $conn->prepare("SELECT * FROM financesetup WHERE sccode = ? AND sessionyear LIKE ? ORDER BY CAST(slno AS UNSIGNED) ASC, id ASC");
if ($fStmt) {
    $fStmt->bind_param("is", $sccode, $syParam);
    $fStmt->execute();
    $fRes = $fStmt->get_result();
    while ($row = $fRes->fetch_assoc()) {
        $finItems[] = $row;
    }
    $fStmt->close();
}

/* ======================================================
   4. LOAD DEFAULT AMOUNT (financesetupvalue)
====================================================== */
$defaultAmt = [];
$defStmt = $conn->prepare("
    SELECT id, itemcode, amount, classname, sectionname
    FROM financesetupvalue
    WHERE sccode = ?
      AND sessionyear LIKE ?
      AND ((sectionname = ? OR sectionname IS NULL OR sectionname = '')
           AND (classname = ? OR classname IS NULL OR classname = ''))
    ORDER BY 
        CASE WHEN sectionname = ? THEN 3 WHEN sectionname IS NULL OR sectionname = '' THEN 2 ELSE 1 END ASC,
        CASE WHEN classname = ? THEN 3 WHEN classname IS NULL OR classname = '' THEN 2 ELSE 1 END ASC,
        id ASC
");
if ($defStmt) {
    $defStmt->bind_param("isssss", $sccode, $syParam, $sec, $cls, $sec, $cls);
    $defStmt->execute();
    $defRes = $defStmt->get_result();
    while ($row = $defRes->fetch_assoc()) {
        $defaultAmt[$row['itemcode']] = $row;
    }
    $defStmt->close();
}

/* ======================================================
   5. LOAD INDIVIDUAL AMOUNT (financesetupind)
====================================================== */
$indAmt = [];
if (!empty($stidResolved)) {
    $indStmt = $conn->prepare("SELECT id, stid, itemcode, amount, modifieddate FROM financesetupind WHERE sccode = ? AND sessionyear LIKE ? AND stid = ?");
    if ($indStmt) {
        $indStmt->bind_param("iss", $sccode, $syParam, $stidResolved);
        $indStmt->execute();
        $indRes = $indStmt->get_result();
        while ($row = $indRes->fetch_assoc()) {
            $indAmt[$row['itemcode']] = $row;
        }
        $indStmt->close();
    }
}

// Calculate summary totals
$totalDefault = 0;
$totalIndividual = 0;
$customizedItemsCount = 0;

foreach ($finItems as $it) {
    $c = $it['itemcode'];
    $defV = isset($defaultAmt[$c]) ? (int)$defaultAmt[$c]['amount'] : 0;
    $totalDefault += $defV;
    
    if (isset($indAmt[$c])) {
        $totalIndividual += (int)$indAmt[$c]['amount'];
        $customizedItemsCount++;
    } else {
        $totalIndividual += $defV;
    }
}
$totalWaiverBenefit = max(0, $totalDefault - $totalIndividual);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-person-gear text-primary"></i> Individual Payment & Concession Setup
            </h4>
            <p class="text-muted small mb-0">শিক্ষার্থীর ব্যক্তিগত ফি মওকুফ (Concession) ও স্পেশাল ফি নির্ধারণ</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="payment-settings.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-gear-wide-connected me-1"></i> Class Payment Setup
            </a>
            <a href="sync-payments.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-repeat me-1"></i> Sync Payments
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- LEFT COLUMN: SELECTION & STUDENT PROFILE -->
        <div class="col-lg-4 col-md-5">
            
            <!-- Filter & Selection Card -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold small text-dark d-flex align-items-center gap-1">
                        <i class="bi bi-funnel"></i> Select Student
                    </span>
                    <span class="badge bg-label-primary fs-tiny">Single Setup</span>
                </div>
                <div class="card-body p-3">
                    <form id="filterForm" onsubmit="event.preventDefault(); loadSelectedStudent();">
                        
                        <!-- Slot / Unit -->
                        <div class="mb-2">
                            <label class="form-label small fw-bold mb-1">Slot / Unit</label>
                            <select id="slot-main" class="form-select form-select-sm" onchange="onSlotChange();">
                                <option value="">All Slots</option>
                                <?php
                                $q = $conn->query("SELECT slotname FROM slots WHERE sccode='$sccode' AND slotname IS NOT NULL AND slotname != '' ORDER BY id ASC");
                                if ($q) {
                                    while ($r = $q->fetch_assoc()) {
                                        $sel = ($slot === $r['slotname']) ? 'selected' : '';
                                        echo "<option value='".htmlspecialchars($r['slotname'])."' $sel>".htmlspecialchars($r['slotname'])."</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Session Year -->
                        <div class="mb-2">
                            <label class="form-label small fw-bold mb-1">Session Year</label>
                            <select id="session-main" class="form-select form-select-sm" onchange="onSessionChange();">
                                <?php
                                $q = $conn->query("SELECT DISTINCT syear FROM sessionyear WHERE sccode='$sccode' AND active=1 ORDER BY syear DESC");
                                if ($q && $q->num_rows > 0) {
                                    while ($r = $q->fetch_assoc()) {
                                        $sel = ($r['syear'] == $year) ? 'selected' : '';
                                        echo "<option value='".htmlspecialchars($r['syear'])."' $sel>".htmlspecialchars($r['syear'])."</option>";
                                    }
                                } else {
                                    echo "<option value='".date('Y')."' selected>".date('Y')."</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Class -->
                        <div class="mb-2">
                            <label class="form-label small fw-bold mb-1">Class</label>
                            <select class="form-select form-select-sm" id="class-main" onchange="onClassChange();">
                                <option value="">Select Class</option>
                                <?php
                                $foundCls = false;
                                $q = "SELECT DISTINCT areaname FROM areas WHERE sccode='$sccode' AND (sessionyear LIKE '%$year%' OR sessionyear = '' OR sessionyear IS NULL) AND areaname IS NOT NULL AND areaname != '' ORDER BY areaname ASC";
                                $r = $conn->query($q);
                                if ($r) {
                                    while ($row = $r->fetch_assoc()) {
                                        $sel = '';
                                        if (!empty($cls) && strcasecmp($cls, $row['areaname']) === 0) {
                                            $sel = 'selected';
                                            $foundCls = true;
                                        }
                                        echo "<option value='".htmlspecialchars($row['areaname'])."' $sel>".htmlspecialchars($row['areaname'])."</option>";
                                    }
                                }
                                if (!empty($cls) && !$foundCls) {
                                    $clsSafe = htmlspecialchars($cls);
                                    echo "<option value='{$clsSafe}' selected>{$clsSafe}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Section -->
                        <div class="mb-2">
                            <label class="form-label small fw-bold mb-1">Section</label>
                            <select class="form-select form-select-sm" id="section-main" onchange="onSectionChange();">
                                <option value="">Select Section</option>
                                <?php
                                $foundSec = false;
                                if (!empty($cls)) {
                                    $q = "SELECT DISTINCT subarea FROM areas WHERE sccode='$sccode' AND (sessionyear LIKE '%$year%' OR sessionyear = '' OR sessionyear IS NULL) AND areaname='$cls' AND subarea IS NOT NULL AND subarea != '' ORDER BY subarea ASC";
                                    $r = $conn->query($q);
                                    if ($r) {
                                        while ($row = $r->fetch_assoc()) {
                                            $sel = '';
                                            if (!empty($sec) && strcasecmp($sec, $row['subarea']) === 0) {
                                                $sel = 'selected';
                                                $foundSec = true;
                                            }
                                            echo "<option value='".htmlspecialchars($row['subarea'])."' $sel>".htmlspecialchars($row['subarea'])."</option>";
                                        }
                                    }
                                }
                                if (!empty($sec) && !$foundSec) {
                                    $secSafe = htmlspecialchars($sec);
                                    echo "<option value='{$secSafe}' selected>{$secSafe}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Student List (Roll - Name Dropdown) -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold mb-1 d-flex justify-content-between align-items-center">
                                <span>Student (Roll & Name)</span>
                                <span class="badge bg-label-info fs-tiny" id="studentCountBadge"><?= count($sectionStudents) ?> Students</span>
                            </label>
                            <select class="form-select form-select-sm" id="student-main" onchange="onStudentSelectChange();">
                                <option value="">-- Choose Student --</option>
                                <?php
                                foreach ($sectionStudents as $st) {
                                    $isSel = ($st['stid'] === $stidResolved || (!empty($rollnoResolved) && (string)$st['rollno'] === (string)$rollnoResolved));
                                    $sel = $isSel ? 'selected' : '';
                                    $stLabel = "Roll: " . $st['rollno'] . " - " . $st['stnameeng'];
                                    if (!empty($st['stnameben'])) {
                                        $stLabel .= " (" . $st['stnameben'] . ")";
                                    }
                                    if (!empty($st['sectionname']) && empty($sec)) {
                                        $stLabel .= " [" . $st['sectionname'] . "]";
                                    }
                                    echo "<option value='".htmlspecialchars($st['stid'])."' data-roll='{$st['rollno']}' data-rate='{$st['rate']}' data-section='".htmlspecialchars($st['sectionname'] ?? '')."' $sel>".htmlspecialchars($stLabel)."</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Roll Direct Input & Quick Action -->
                        <div class="row g-2 mb-3">
                            <div class="col-5">
                                <input type="number" id="roll" class="form-control form-control-sm text-center" placeholder="Roll No" value="<?= htmlspecialchars($rollnoResolved ?: $roll) ?>" onkeydown="if(event.key==='Enter'){event.preventDefault();findStudentByRoll();}">
                            </div>
                            <div class="col-7">
                                <button type="button" onclick="loadSelectedStudent();" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-search me-1"></i> Load Setup
                                </button>
                            </div>
                        </div>

                        <!-- Quick Next / Prev Navigation -->
                        <?php if (!empty($stidResolved)): ?>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2" <?= empty($prevStid) ? 'disabled' : '' ?> onclick="navigateToStudent('<?= $prevStid ?>');" title="Previous Student">
                                <i class="bi bi-chevron-left me-1"></i> Prev Roll
                            </button>
                            <span class="fs-tiny text-muted fw-semibold">
                                Roll <?= htmlspecialchars($rollnoResolved) ?>
                            </span>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2" <?= empty($nextStid) ? 'disabled' : '' ?> onclick="navigateToStudent('<?= $nextStid ?>');" title="Next Student">
                                Next Roll <i class="bi bi-chevron-right ms-1"></i>
                            </button>
                        </div>
                        <?php endif; ?>

                    </form>
                </div>
            </div>

            <!-- Student Profile & Concession Rate Card -->
            <?php if ($student): ?>
            <div class="card shadow-sm border-0 mb-3 border-start border-primary border-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar avatar-md bg-label-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                            <?php if (!empty($student['photo']) && file_exists('uploads/students/' . $student['photo'])): ?>
                                <img src="uploads/students/<?= htmlspecialchars($student['photo']) ?>" alt="Photo" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-person-fill fs-4"></i>
                            <?php endif; ?>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="mb-0 text-dark fw-bold text-truncate"><?= htmlspecialchars($student['stnameeng']) ?></h6>
                            <?php if (!empty($student['stnameben'])): ?>
                                <small class="text-muted d-block text-truncate"><?= htmlspecialchars($student['stnameben']) ?></small>
                            <?php endif; ?>
                            <span class="badge bg-label-dark fs-tiny mt-1">ID: <?= htmlspecialchars($student['stid']) ?></span>
                        </div>
                    </div>

                    <div class="row g-2 small text-muted mb-3 bg-light p-2 rounded">
                        <div class="col-6"><strong>Class:</strong> <?= htmlspecialchars($student['classname']) ?></div>
                        <div class="col-6"><strong>Section:</strong> <?= htmlspecialchars($student['sectionname']) ?></div>
                        <div class="col-6"><strong>Roll:</strong> <span class="badge bg-primary"><?= htmlspecialchars($student['rollno']) ?></span></div>
                        <div class="col-6"><strong>Slot:</strong> <?= htmlspecialchars($student['slot'] ?: 'School') ?></div>
                    </div>

                    <!-- Overall Concession Rate (sessioninfo.rate) -->
                    <div class="border rounded p-2 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-bold text-dark">
                                <i class="bi bi-percent text-info me-1"></i> Concession / Rate (%):
                            </span>
                            <span class="badge <?= $rate < 100 ? 'bg-warning text-dark' : 'bg-success' ?> fw-bold" id="rateDisplayBadge">
                                <?= $rate ?>% <?= $rate < 100 ? '(Waiver Active)' : '(Regular)' ?>
                            </span>
                        </div>
                        <div class="input-group input-group-sm mb-2">
                            <input type="number" id="concession_rate" class="form-control text-center fw-bold" min="0" max="100" value="<?= $rate ?>" placeholder="Rate %">
                            <button type="button" class="btn btn-outline-primary" onclick="saveStudentRate();" title="Update Rate">
                                <i class="bi bi-check-lg"></i> Update Rate
                            </button>
                        </div>
                        
                        <!-- Quick Rate Presets -->
                        <div class="d-flex gap-1 justify-content-between">
                            <button type="button" class="btn btn-xs btn-outline-success flex-fill py-1" onclick="setQuickRate(100);" style="font-size: 11px;">
                                100% (Full)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-warning flex-fill py-1" onclick="setQuickRate(50);" style="font-size: 11px;">
                                50% (Half)
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-danger flex-fill py-1" onclick="setQuickRate(0);" style="font-size: 11px;">
                                0% (Waiver)
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Concession KPI Summary -->
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body p-3">
                    <h6 class="text-white fw-bold mb-2 d-flex align-items-center gap-1">
                        <i class="bi bi-calculator"></i> Fee Breakdown Summary
                    </h6>
                    <div class="d-flex justify-content-between align-items-center mb-1 small text-white-50">
                        <span>Class Standard Total:</span>
                        <strong class="text-white">৳ <?= number_format($totalDefault, 2) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1 small text-white-50">
                        <span>Student Custom Total:</span>
                        <strong class="text-white">৳ <span id="summaryIndividualTotal"><?= number_format($totalIndividual, 2) ?></span></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top border-white-50">
                        <span class="fw-bold">Total Concession / Waiver:</span>
                        <span class="badge bg-warning text-dark fw-bold fs-6">৳ <span id="summaryWaiverTotal"><?= number_format($totalWaiverBenefit, 2) ?></span></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- RIGHT COLUMN: PAYMENT & CONCESSION ITEMS -->
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm border-0 h-100">
                
                <div class="card-header bg-light py-2 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-receipt text-primary"></i> Payment & Concession Fee Items
                        </h5>
                        <?php if ($student): ?>
                            <small class="text-muted">
                                Configuring for: <strong class="text-primary"><?= htmlspecialchars($student['stnameeng']) ?></strong> 
                                (Roll: <?= htmlspecialchars($student['rollno']) ?>, Session: <?= htmlspecialchars($year) ?>)
                            </small>
                        <?php else: ?>
                            <small class="text-muted">Please select a student from the left panel to manage fee concessions.</small>
                        <?php endif; ?>
                    </div>

                    <?php if ($student): ?>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-label-primary"><?= count($finItems) ?> Items</span>
                        <span class="badge bg-label-success" id="customItemsCountBadge"><?= $customizedItemsCount ?> Customized</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-body p-3">

                    <?php if (!$student): ?>
                        <div class="text-center py-5">
                            <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3 d-flex align-items-center justify-content-center">
                                <i class="bi bi-person-bounding-box fs-1"></i>
                            </div>
                            <h5 class="text-dark fw-bold">No Student Selected</h5>
                            <p class="text-muted small mb-3">বাম পাশের প্যানেল থেকে শ্রেণি, শাখা এবং শিক্ষার্থী নির্বাচন করে <br><strong>"Load Setup"</strong> বাটনে ক্লিক করুন।</p>
                        </div>
                    <?php elseif (empty($finItems)): ?>
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                            <div>
                                No finance items found for session <strong><?= htmlspecialchars($year) ?></strong> in <code>financesetup</code>. 
                                Please configure items in <a href="payment-settings.php" class="alert-link">Payment Settings</a>.
                            </div>
                        </div>
                    <?php else: ?>

                        <!-- Fee Items Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="feeItemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Item Name & Particulars</th>
                                        <th class="text-center" style="width: 120px;">Class Default</th>
                                        <th class="text-end" style="width: 150px;">Student Fee (৳)</th>
                                        <th class="text-center" style="width: 90px;">Status</th>
                                        <th class="text-center" style="width: 60px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $idx = 0;
                                    foreach ($finItems as $item):
                                        $idx++;
                                        $code = $item['itemcode'];
                                        $partEng = htmlspecialchars($item['particulareng']);
                                        $partBen = htmlspecialchars($item['particularben'] ?? '');
                                        $itemSlot = htmlspecialchars($item['slot'] ?? ($slot ?: 'School'));

                                        $defaultVal = isset($defaultAmt[$code]) ? (int)$defaultAmt[$code]['amount'] : 0;

                                        /* -------- amount resolve priority -------- */
                                        if (isset($indAmt[$code])) {
                                            $amount = (int)$indAmt[$code]['amount'];
                                            $rowid = (int)$indAmt[$code]['id'];
                                            $tag = 'IND';
                                            $tagClass = 'bg-label-success';
                                            $tagText = 'Custom';
                                        } elseif (isset($defaultAmt[$code])) {
                                            $amount = $defaultVal;
                                            $rowid = 0;
                                            $tag = 'DEF';
                                            $tagClass = 'bg-label-secondary';
                                            $tagText = 'Default';
                                        } else {
                                            $amount = 0;
                                            $rowid = 0;
                                            $tag = 'NEW';
                                            $tagClass = 'bg-label-warning';
                                            $tagText = 'New';
                                        }
                                    ?>
                                    <tr id="row_<?= $code ?>" class="fee-item-row" data-itemcode="<?= $code ?>" data-default="<?= $defaultVal ?>">
                                        
                                        <!-- Index -->
                                        <td class="text-muted small"><?= $idx ?></td>

                                        <!-- Item Info -->
                                        <td>
                                            <div class="fw-bold text-dark"><?= $partEng ?></div>
                                            <?php if (!empty($partBen)): ?>
                                                <small class="text-muted d-block"><?= $partBen ?></small>
                                            <?php endif; ?>
                                            <span class="fs-tiny text-secondary">Code: <code><?= $code ?></code></span>
                                        </td>

                                        <!-- Class Default -->
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">
                                                ৳ <?= number_format($defaultVal, 2) ?>
                                            </span>
                                        </td>

                                        <!-- Individual Amount Input -->
                                        <td class="text-end">
                                            <input type="hidden" id="rowid_<?= $code ?>" value="<?= $rowid ?>">
                                            <input type="hidden" id="tag_<?= $code ?>" value="<?= $tag ?>">

                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">৳</span>
                                                <input type="number" 
                                                       class="form-control form-control-sm text-end fw-bold fee-amt-input <?= $tag === 'IND' ? 'border-success text-success' : '' ?>" 
                                                       id="amt_<?= $code ?>" 
                                                       value="<?= $amount ?>" 
                                                       min="0"
                                                       data-orig="<?= $amount ?>"
                                                       onchange="updateFinanceAmount('<?= $itemSlot ?>', '<?= $year ?>', '<?= $code ?>');"
                                                       onkeydown="if(event.key==='Enter'){event.preventDefault();this.blur();}">
                                            </div>
                                        </td>

                                        <!-- Tag Status -->
                                        <td class="text-center">
                                            <span class="badge <?= $tagClass ?> fs-tiny" id="tag_badge_<?= $code ?>">
                                                <?= $tagText ?>
                                            </span>
                                        </td>

                                        <!-- Reset / Delete Action -->
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="btn btn-icon btn-sm btn-outline-danger reset-btn" 
                                                    id="btn_del_<?= $code ?>"
                                                    <?= ($rowid > 0 && $tag === 'IND') ? '' : 'disabled' ?>
                                                    onclick="deleteFinanceRow('<?= $code ?>', <?= $rowid ?>);"
                                                    title="Reset to Class Default">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </td>

                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Instructions alert -->
                        <div class="mt-3 p-2 bg-light rounded border text-muted small d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle text-primary fs-5"></i>
                            <div>
                                <strong>টিপস:</strong> শিক্ষার্থীর জন্য যেকোনো নির্দিষ্ট ফি পরিবর্তন করতে ইনপুট বক্সে টাকার পরিমাণ লিখে বাইরে ক্লিক করুন বা <kbd>Enter</kbd> চাপুন। স্বয়ংক্রিয়ভাবে সেভ হবে। পূর্বে করা কনসেসন বাদ দিয়ে ক্লাস ডিফল্ট ফি-তে ফিরিয়ে নিতে <i class="bi bi-arrow-counterclockwise text-danger"></i> রিসেট বাটনে ক্লিক করুন।
                            </div>
                        </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>

<script>
    const currentSccode = <?= (int)$sccode ?>;
    const currentStid = '<?= addslashes($stidResolved) ?>';
    const currentYear = '<?= addslashes($year) ?>';
    const currentCls = '<?= addslashes($cls) ?>';
    const currentSec = '<?= addslashes($sec) ?>';
    const currentSlot = '<?= addslashes($slot) ?>';
    const currentRoll = '<?= addslashes($rollnoResolved ?: $roll) ?>';

    // 1. Session & Slot change handlers
    function onSessionChange() {
        let sy = $('#session-main').val();
        setCookie('chain-session', sy);
        reloadClasses();
    }

    function onSlotChange() {
        reloadClasses();
    }

    function reloadClasses() {
        let sy = $('#session-main').val();
        let slot = $('#slot-main').val();
        
        $.post('payments/get-class.php', { session: sy, slot: slot }, function(res) {
            $('#class-main').html(res);
            $('#section-main').html('<option value="">Select Section</option>');
            $('#student-main').html('<option value="">-- Choose Student --</option>');
            $('#studentCountBadge').text('0 Students');
        });
    }

    // 2. Class change handler
    function onClassChange() {
        let cls = $('#class-main').val();
        let sy = $('#session-main').val();
        
        $('#section-main').html('<option value="">Loading sections...</option>');
        $('#student-main').html('<option value="">Loading students...</option>');
        $('#studentCountBadge').text('0 Students');

        if (!cls) {
            $('#section-main').html('<option value="">Select Section</option>');
            $('#student-main').html('<option value="">-- Choose Student --</option>');
            return;
        }

        $.post('payments/get-sections.php', { cls: cls, session: sy }, function(res) {
            $('#section-main').html(res);
            loadStudentsList();
        });
    }

    // 3. Section change handler
    function onSectionChange() {
        loadStudentsList();
    }

    // 4. Load students list via AJAX
    function loadStudentsList(callback) {
        let sy = $('#session-main').val() || currentYear;
        let cls = $('#class-main').val() || currentCls;
        let sec = $('#section-main').val() || '';
        let slot = $('#slot-main').val() || '';

        if (!cls) {
            $('#student-main').html('<option value="">-- Choose Student --</option>');
            $('#studentCountBadge').text('0 Students');
            if (typeof callback === 'function') callback();
            return;
        }

        $('#student-main').html('<option value="">Loading students...</option>');

        $.post('payments/get-students.php', {
            session: sy,
            cls: cls,
            sec: sec,
            slot: slot
        }, function(res) {
            try {
                let data = typeof res === 'object' ? res : JSON.parse(res);
                if (data.status === 'success' && data.students && data.students.length > 0) {
                    let list = data.students || [];
                    $('#studentCountBadge').text(list.length + ' Students');
                    
                    let html = '<option value="">-- Choose Student --</option>';
                    let targetStid = currentStid || $('#student-main').val();
                    let targetRoll = currentRoll || $('#roll').val();

                    list.forEach(function(st) {
                        let isSel = (targetStid && String(st.stid) === String(targetStid)) || 
                                    (!targetStid && targetRoll && String(st.rollno) === String(targetRoll));
                        let sel = isSel ? 'selected' : '';
                        let nameLabel = 'Roll: ' + st.rollno + ' - ' + st.stnameeng;
                        if (st.stnameben) nameLabel += ' (' + st.stnameben + ')';
                        if (st.sectionname && !sec) nameLabel += ' [' + st.sectionname + ']';
                        html += `<option value="${st.stid}" data-roll="${st.rollno}" data-rate="${st.rate}" data-section="${st.sectionname || ''}" ${sel}>${nameLabel}</option>`;
                    });
                    $('#student-main').html(html);

                    let selOpt = $('#student-main option:selected');
                    if (selOpt.length && selOpt.attr('data-roll')) {
                        $('#roll').val(selOpt.attr('data-roll'));
                    }
                } else {
                    $('#studentCountBadge').text('0 Students');
                    $('#student-main').html('<option value="">-- No Students Found --</option>');
                }
                if (typeof callback === 'function') callback();
            } catch(e) {
                console.error("Parse error loading students:", e, res);
                $('#student-main').html('<option value="">Error loading students</option>');
                if (typeof callback === 'function') callback();
            }
        }).fail(function(xhr) {
            console.error("AJAX error loading students:", xhr.responseText);
            $('#student-main').html('<option value="">Error loading students</option>');
            if (typeof callback === 'function') callback();
        });
    }

    // 5. On student select change
    function onStudentSelectChange() {
        let opt = $('#student-main option:selected');
        let roll = opt.attr('data-roll') || '';
        let sec = opt.attr('data-section') || '';
        if (roll) {
            $('#roll').val(roll);
        }
        if (sec && !$('#section-main').val()) {
            $('#section-main').val(sec);
        }
    }

    // 6. Find student in dropdown by roll number input
    function findStudentByRoll() {
        let rollInput = $('#roll').val().trim();
        if (!rollInput) return;

        let matchedStid = '';
        $('#student-main option').each(function() {
            if ($(this).attr('data-roll') == rollInput) {
                matchedStid = $(this).val();
                $(this).prop('selected', true);
                let sec = $(this).attr('data-section') || '';
                if (sec && !$('#section-main').val()) {
                    $('#section-main').val(sec);
                }
            }
        });

        loadSelectedStudent();
    }

    // 7. Load Selected Student (Navigates to URL)
    function loadSelectedStudent() {
        let aslot = $('#slot-main').val() || '';
        let ayear = $('#session-main').val() || '';
        let acls = $('#class-main').val() || '';
        let asec = $('#section-main').val() || '';
        let astid = $('#student-main').val() || '';
        let aroll = $('#roll').val() ? $('#roll').val().trim() : '';

        // If section is empty but selected student has section, grab it
        if (!asec && astid) {
            let opt = $('#student-main option:selected');
            if (opt.attr('data-section')) {
                asec = opt.attr('data-section');
                $('#section-main').val(asec);
            }
        }

        if (!acls) {
            Swal.fire('Required', 'Please select a Class first', 'warning');
            return;
        }

        let p = new URLSearchParams();
        p.set('year', ayear);
        p.set('cls', acls);
        p.set('sec', asec);

        if (aslot) p.set('slot', aslot);
        if (astid) p.set('stid', astid);
        if (aroll) p.set('roll', aroll);

        location.href = 'payment-settings-indivisual.php?' + p.toString();
    }

    // 8. Direct Navigation by StID
    function navigateToStudent(targetStid) {
        if (!targetStid) return;
        let p = new URLSearchParams({
            year: currentYear,
            cls: currentCls,
            sec: currentSec,
            stid: targetStid
        });
        if (currentSlot) p.set('slot', currentSlot);
        location.href = 'payment-settings-indivisual.php?' + p.toString();
    }

    // 9. Update Individual Concession Amount via AJAX
    function updateFinanceAmount(slot, session, itemcode) {
        if (!currentStid) {
            Swal.fire('Error', 'No student is currently selected', 'warning');
            return;
        }

        let amtInput = $('#amt_' + itemcode);
        let amt = amtInput.val().trim();
        let rowid = $('#rowid_' + itemcode).val();
        let tag = $('#tag_' + itemcode).val();
        let defaultAmt = parseFloat($('#row_' + itemcode).attr('data-default')) || 0;

        if (amt === '' || isNaN(amt) || parseFloat(amt) < 0) {
            Swal.fire('Invalid Amount', 'Please enter a valid amount (>= 0)', 'warning');
            amtInput.val(amtInput.attr('data-orig'));
            return;
        }

        $.post('payments/crud-set-financed-ind.php', {
            slot: slot || currentSlot,
            session: session || currentYear,
            itemcode: itemcode,
            amount: amt,
            rowid: rowid,
            stid: currentStid,
            cls: currentCls,
            sec: currentSec,
            tag: tag
        }, function(res) {
            try {
                let data = typeof res === 'object' ? res : JSON.parse(res);
                if (data.status === 'success') {
                    // Update row states
                    let newRowId = data.rowid || rowid;
                    $('#rowid_' + itemcode).val(newRowId);
                    $('#tag_' + itemcode).val('IND');
                    
                    // Update badge
                    $('#tag_badge_' + itemcode)
                        .removeClass('bg-label-secondary bg-label-warning')
                        .addClass('bg-label-success')
                        .text('Custom');

                    // Style input
                    amtInput
                        .addClass('border-success text-success')
                        .attr('data-orig', amt);

                    // Enable reset button
                    $('#btn_del_' + itemcode)
                        .prop('disabled', false)
                        .attr('onclick', `deleteFinanceRow('${itemcode}', ${newRowId})`);

                    recalculateTotals();

                    Swal.fire({
                        title: 'Saved!',
                        text: 'Individual fee updated to ৳ ' + amt,
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to update amount', 'error');
                    amtInput.val(amtInput.attr('data-orig'));
                }
            } catch(e) {
                console.error("Save error:", e, res);
            }
        }).fail(function() {
            Swal.fire('Network Error', 'Could not connect to server', 'error');
        });
    }

    // 10. Delete / Reset Individual Concession Row
    function deleteFinanceRow(itemcode, rowid) {
        if (!rowid || rowid <= 0) {
            rowid = $('#rowid_' + itemcode).val();
        }

        if (!rowid || rowid <= 0) return;

        Swal.fire({
            title: 'Reset to Class Default?',
            html: `Do you want to remove individual custom amount for <strong>${itemcode}</strong> and revert to class default?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff9800',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Yes, Reset Default',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('payments/delete-finance-ind.php', {
                    rowid: rowid,
                    stid: currentStid,
                    itemcode: itemcode,
                    cls: currentCls,
                    sec: currentSec,
                    session: currentYear
                }, function(res) {
                    try {
                        let data = typeof res === 'object' ? res : JSON.parse(res);
                        if (data.status === 'success') {
                            let defAmt = data.default_amount !== undefined ? data.default_amount : (parseFloat($('#row_' + itemcode).attr('data-default')) || 0);
                            
                            // Revert input value & properties
                            $('#amt_' + itemcode)
                                .val(defAmt)
                                .attr('data-orig', defAmt)
                                .removeClass('border-success text-success');

                            $('#rowid_' + itemcode).val(0);
                            $('#tag_' + itemcode).val('DEF');

                            // Revert badge
                            $('#tag_badge_' + itemcode)
                                .removeClass('bg-label-success bg-label-warning')
                                .addClass('bg-label-secondary')
                                .text('Default');

                            // Disable reset button
                            $('#btn_del_' + itemcode).prop('disabled', true);

                            recalculateTotals();

                            Swal.fire({
                                title: 'Reset Complete',
                                text: data.message || 'Reverted to class default',
                                icon: 'success',
                                timer: 1200,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
                            });
                        } else {
                            Swal.fire('Error', data.message || 'Failed to reset item', 'error');
                        }
                    } catch(e) {
                        Swal.fire('Error', 'Response parsing error: ' + res, 'error');
                    }
                });
            }
        });
    }

    // 11. Recalculate summary KPI totals on the client
    function recalculateTotals() {
        let totalDef = 0;
        let totalInd = 0;
        let customCount = 0;

        $('.fee-item-row').each(function() {
            let code = $(this).attr('data-itemcode');
            let defVal = parseFloat($(this).attr('data-default')) || 0;
            let curVal = parseFloat($('#amt_' + code).val()) || 0;
            let tag = $('#tag_' + code).val();

            totalDef += defVal;
            totalInd += curVal;
            if (tag === 'IND') {
                customCount++;
            }
        });

        let waiver = Math.max(0, totalDef - totalInd);
        $('#summaryIndividualTotal').text(totalInd.toFixed(2));
        $('#summaryWaiverTotal').text(waiver.toFixed(2));
        $('#customItemsCountBadge').text(customCount + ' Customized');
    }

    // 12. Save Student Concession Rate (sessioninfo.rate)
    function saveStudentRate() {
        if (!currentStid) return;

        let rateVal = parseInt($('#concession_rate').val());
        if (isNaN(rateVal) || rateVal < 0 || rateVal > 100) {
            Swal.fire('Invalid Rate', 'Concession rate must be between 0% and 100%', 'warning');
            return;
        }

        $.post('payments/save-student-rate.php', {
            stid: currentStid,
            rate: rateVal,
            session: currentYear,
            cls: currentCls,
            sec: currentSec
        }, function(res) {
            try {
                let data = typeof res === 'object' ? res : JSON.parse(res);
                if (data.status === 'success') {
                    $('#rateDisplayBadge')
                        .removeClass('bg-success bg-warning text-dark')
                        .addClass(rateVal < 100 ? 'bg-warning text-dark' : 'bg-success')
                        .text(rateVal + '% ' + (rateVal < 100 ? '(Waiver Active)' : '(Regular)'));

                    Swal.fire({
                        title: 'Rate Updated!',
                        text: data.message,
                        icon: 'success',
                        timer: 1200,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to update rate', 'error');
                }
            } catch(e) {
                Swal.fire('Error', 'Server response: ' + res, 'error');
            }
        });
    }

    // 13. Preset quick rate buttons
    function setQuickRate(val) {
        $('#concession_rate').val(val);
        saveStudentRate();
    }

    // 14. Synchronize & Lock Dropdowns to URL parameters
    $(document).ready(function() {
        if (currentYear) {
            $('#session-main').val(currentYear);
        }
        if (currentSlot) {
            $('#slot-main').val(currentSlot);
        }
        if (currentCls) {
            $('#class-main').val(currentCls);
        }
        if (currentSec) {
            $('#section-main').val(currentSec);
        }
        if (currentStid) {
            $('#student-main').val(currentStid);
        }
        if (currentRoll) {
            $('#roll').val(currentRoll);
        }

        let selOpt = $('#student-main option:selected');
        if (selOpt.length && selOpt.attr('data-roll')) {
            $('#roll').val(selOpt.attr('data-roll'));
        }

        // If class is selected but student dropdown has no loaded student options, load them
        if (currentCls && $('#student-main option').length <= 1) {
            loadStudentsList();
        }

        // Re-enforce after any potential footer script auto-load
        setTimeout(function() {
            if (currentCls && $('#class-main').val() !== currentCls) {
                $('#class-main').val(currentCls);
            }
            if (currentSec && $('#section-main').val() !== currentSec) {
                $('#section-main').val(currentSec);
            }
            if (currentStid && $('#student-main').val() !== currentStid) {
                $('#student-main').val(currentStid);
            }
            if (currentRoll && $('#roll').val() !== currentRoll) {
                $('#roll').val(currentRoll);
            }
        }, 300);
    });
</script>
</body>
</html>