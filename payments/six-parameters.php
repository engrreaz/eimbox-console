<div class="row g-2 mb-3">

    <!-- TYPE -->
    <div class="col-md-2">
        <label class="form-label small">Type</label>
        <select class="form-select form-select-sm" name="type" id="type-main" disabled>
            <option value="">Overall</option>
            <option value="item">Item</option>
            <option value="student">Student</option>
            <option value="class">Class</option>
            <option value="section">Section</option>
        </select>
    </div>

    <!-- PART -->
    <div class="col-md-2">
        <label class="form-label small">Range</label>
        <select class="form-select form-select-sm" name="part" id="part-main" disabled>
            <option value="">Full Range</option>
            <option value="ind">Individual</option>
        </select>
    </div>

    <!-- ITEM / CODE -->
    <div class="col-md-2">
        <label class="form-label small">Item / Code</label>
        <select class="form-select form-select-sm" name="icode" id="icode-main" disabled>
            <option value=""></option>
            <?php
            $q = "SELECT itemcode, particulareng 
                  FROM financesetup
                  WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%'
                  ORDER BY particulareng";
            $r = $conn->query($q);
            while ($row = $r->fetch_assoc()) {
                echo "<option value='{$row['itemcode']}'>{$row['particulareng']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- STUDENT -->
    <div class="col-md-2">
        <label class="form-label small">Student ID</label>
        <input type="text" class="form-control form-control-sm"
               name="stid" id="student-main"
               placeholder="Student ID" disabled>
    </div>

    <!-- CLASS -->
    <div class="col-md-2">
        <label class="form-label small">Class</label>
        <select class="form-select form-select-sm" name="cls" id="class-main" disabled>
            <option value=""></option>
            <?php
            $q = "SELECT DISTINCT areaname
                  FROM areas
                  WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%'
                  ORDER BY areaname";
            $r = $conn->query($q);
            while ($row = $r->fetch_assoc()) {
                echo "<option value='{$row['areaname']}'>{$row['areaname']}</option>";
            }
            ?>
        </select>
    </div>

    <!-- SECTION -->
    <div class="col-md-2">
        <label class="form-label small">Section</label>
        <select class="form-select form-select-sm" name="sec" id="section-main" disabled>
            <option value=""></option>
        </select>
    </div>

</div>

<div class="col-md-12 text-end mt-2">
    <button type="button" class="btn btn-sm btn-primary" id="applyFilter">
        <i class="bi bi-funnel"></i>&nbsp; Apply Filter
    </button>
</div>