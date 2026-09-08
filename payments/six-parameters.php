<div class="row g-2 mb-3">

    <!-- TYPE -->
    <div class="col-md-2">
        <label class="form-label small fw-semibold">Filter Type</label>
        <select class="form-select form-select-sm" name="type" id="type-main">
            <option value="">Overall (All)</option>
            <option value="item">Specific Item</option>
            <option value="student">Specific Student</option>
            <option value="class">Class</option>
            <option value="section">Section</option>
        </select>
    </div>

    <!-- PART -->
    <div class="col-md-2">
        <label class="form-label small fw-semibold">Range</label>
        <select class="form-select form-select-sm" name="part" id="part-main">
            <option value="all">Full Range (All Items)</option>
            <option value="ind">Individual Setup</option>
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
                    echo "<option value='{$row['itemcode']}'>{$itemTitle}</option>";
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
               placeholder="e.g. 2026001">
    </div>

    <!-- CLASS -->
    <div class="col-md-2">
        <label class="form-label small fw-semibold">Class</label>
        <select class="form-select form-select-sm" name="cls" id="class-main">
            <option value="">-- All Classes --</option>
            <?php
            $q = "SELECT DISTINCT areaname
                  FROM areas
                  WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%'
                  ORDER BY idno ASC, areaname ASC";
            $r = $conn->query($q);
            if ($r && $r->num_rows > 0) {
                while ($row = $r->fetch_assoc()) {
                    echo "<option value='{$row['areaname']}'>{$row['areaname']}</option>";
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