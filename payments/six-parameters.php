<div class="row g-2 mb-3">

    <!-- TYPE -->
    <div class="col-md-2">
        <label class="form-label small fw-semibold">Filter Type</label>
        <select class="form-select form-select-sm" name="type" id="type-main">
            <option value="" <?= ($type === '') ? 'selected' : '' ?>>Overall (All)</option>
            <option value="item" <?= ($type === 'item') ? 'selected' : '' ?>>Specific Item</option>
            <option value="student" <?= ($type === 'student') ? 'selected' : '' ?>>Specific Student</option>
            <option value="class" <?= ($type === 'class') ? 'selected' : '' ?>>Class</option>
            <option value="section" <?= ($type === 'section') ? 'selected' : '' ?>>Section</option>
        </select>
    </div>

    <!-- PART -->
    <div class="col-md-2">
        <label class="form-label small fw-semibold">Range</label>
        <select class="form-select form-select-sm" name="part" id="part-main">
            <option value="all" <?= ($part === 'all') ? 'selected' : '' ?>>Full Range (All Items)</option>
            <option value="ind" <?= ($part === 'ind') ? 'selected' : '' ?>>Individual Setup</option>
        </select>
    </div>

    <!-- ITEM / CODE -->
    <div class="col-md-2">
        <label class="form-label small fw-semibold">Fee Item</label>
        <select class="form-select form-select-sm" name="icode" id="icode-main">
            <option value="">-- All Items --</option>
            <?php
            $q = "SELECT itemcode, particulareng, particularben 
                  FROM financesetup
                  WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%'
                  ORDER BY slno ASC, particulareng ASC";
            $r = $conn->query($q);
            if ($r && $r->num_rows > 0) {
                while ($row = $r->fetch_assoc()) {
                    $itemTitle = htmlspecialchars($row['particulareng']);
                    if (!empty($row['particularben'])) {
                        $itemTitle .= " (" . htmlspecialchars($row['particularben']) . ")";
                    }
                    $iSel = ($icode === $row['itemcode']) ? 'selected' : '';
                    echo "<option value='{$row['itemcode']}' {$iSel}>{$itemTitle}</option>";
                }
            }
            ?>
        </select>
    </div>

    <!-- STUDENT -->
    <div class="col-md-2">
        <label class="form-label small fw-semibold">Student ID</label>
        <input type="text" class="form-control form-control-sm"
               name="stid" id="student-main"
               value="<?= htmlspecialchars($stid) ?>"
               placeholder="e.g. 2026001">
    </div>

    <!-- CLASS -->
    <div class="col-md-2">
        <label class="form-label small fw-semibold">Class</label>
        <select class="form-select form-select-sm" name="cls" id="class-main">
            <option value="">-- All Classes --</option>
            <?php
            $q = "SELECT areaname
                  FROM areas
                  WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%' AND areaname IS NOT NULL AND areaname != ''
                  GROUP BY areaname
                  ORDER BY MIN(idno) ASC, areaname ASC";
            $r = $conn->query($q);
            if ($r && $r->num_rows > 0) {
                while ($row = $r->fetch_assoc()) {
                    $cSel = (strcasecmp($cls, $row['areaname']) === 0) ? 'selected' : '';
                    echo "<option value='{$row['areaname']}' {$cSel}>{$row['areaname']}</option>";
                }
            }
            ?>
        </select>
    </div>

    <!-- SECTION -->
    <div class="col-md-2">
        <label class="form-label small fw-semibold">Section</label>
        <select class="form-select form-select-sm" name="sec" id="section-main">
            <option value="">-- All Sections --</option>
            <?php
            if (!empty($cls)) {
                $qSec = "SELECT DISTINCT subarea 
                         FROM areas 
                         WHERE sccode='$sccode' 
                           AND (sessionyear LIKE '%$sy%' OR sessionyear='' OR sessionyear IS NULL) 
                           AND areaname='$cls' 
                           AND subarea IS NOT NULL AND subarea != '' 
                         ORDER BY subarea ASC";
                $rSec = $conn->query($qSec);
                if ($rSec && $rSec->num_rows > 0) {
                    while ($rowSec = $rSec->fetch_assoc()) {
                        $sSel = (strcasecmp($sec, $rowSec['subarea']) === 0) ? 'selected' : '';
                        echo "<option value='{$rowSec['subarea']}' {$sSel}>{$rowSec['subarea']}</option>";
                    }
                }
            }
            ?>
        </select>
    </div>

</div>

<div class="col-md-12 text-end mb-3">
    <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.location.href='sync-payments.php'">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Clear Filter
    </button>
    <button type="button" class="btn btn-sm btn-primary" id="applyFilter">
        <i class="bi bi-funnel me-1"></i> Apply Filter
    </button>
</div>