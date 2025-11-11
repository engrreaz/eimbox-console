<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-4 fw-bold"><i class="bi bi-copy"></i> Data Duplicator</h4>

    <div id="accordionCustomIcon" id="duplicatorAccordion" class="accordion mt-4 accordion-custom-button">
        <div class="accordion-item">
            <h2 class="accordion-header bg-dark text-body d-flex justify-content-between" id="accordionCustomIconOne">
                <button type="button" class=" accordion-button collapsed" data-bs-toggle="collapse"
                    data-bs-target="#accordionCustomIcon-1" aria-controls="accordionCustomIcon-1">

                    <span class="pe-4 fw-bold text-primary"><i class="icon-base bi bi-diagram-2-fill me-2"></i>
                        Class/Section</span> Sessionyear → Class → Section
                </button>
            </h2>

            <div id="accordionCustomIcon-1" class="accordion-collapse collapse" aria-labelledby="accordionCustomIconOne"
                data-bs-parent="#accordionCustomIcon">
                <div class="accordion-body">

                    <div class="card-body">
                        <div class="row">

                            <!-- ====== Column 1: Source Tree ====== -->
                            <div class="col-4 border-end">
                                <h6 class="text-primary mb-2">Source Year / Class / Section</h6>
                                <div id="sourceTree" class="border rounded p-2"
                                    style="max-height:420px; overflow:auto; list-style:none;">
                                    <style>
                                        ul {
                                            list-style: none;
                                            padding-left: 15px;
                                        }

                                        .tree-toggle {
                                            cursor: pointer;
                                            user-select: none;
                                        }

                                        label {
                                            cursor: pointer;
                                        }
                                    </style>
                                    <?php
                                    $query = "SELECT sessionyear, areaname, subarea 
                                          FROM areas WHERE sccode='$sccode' 
                                          ORDER BY sessionyear DESC, areaname, subarea";
                                    $res = $conn->query($query);
                                    $tree = [];
                                    while ($r = $res->fetch_assoc()) {
                                        $tree[$r['sessionyear']][$r['areaname']][] = $r['subarea'];
                                    }

                                    foreach ($tree as $year => $areas) {
                                        echo "<div class='tree-node mb-1'>
                                            <div class='tree-toggle' onclick='toggleNode(this)'>
                                                <i class='bx bx-chevron-right'></i> 
                                                <label>
                                                    <input type='radio' name='sourceClass' value='$year' onchange='updateSelection()'> <b>$year</b>
                                                </label>
                                            </div>
                                            <ul class='ms-3' style='display:none;'>";
                                        foreach ($areas as $area => $subs) {
                                            echo "<li>
                                                <div class='tree-toggle' onclick='toggleNode(this)'>
                                                    <i class='bx bx-chevron-right'></i>
                                                    <label>
                                                        <input type='radio' name='sourceClass' value='$year|$area' onchange='updateSelection()'> $area
                                                    </label>
                                                </div>
                                                <ul class='ms-3' style='display:none;'>";
                                            foreach ($subs as $sub) {
                                                $val = "$year|$area|$sub";
                                                echo "<li>
                                                    <label>
                                                        <input type='radio' name='sourceClass' value='$val' onchange='updateSelection()'> 
                                                        <i class='bx bx-book'></i> $sub
                                                    </label>
                                                </li>";
                                            }
                                            echo "</ul></li>";
                                        }
                                        echo "</ul></div>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- ====== Column 2: Target Tree ====== -->
                            <div class="col-4 border-end">
                                <h6 class="text-success mb-2">Target Year / Class / Section</h6>
                                <div id="targetTree" class="border rounded p-2"
                                    style="max-height:420px; overflow:auto;">
                                    <?php
                                    $syQuery = "SELECT syear FROM sessionyear WHERE sccode='$sccode' and active=1 ORDER BY syear DESC";
                                    $syRes = $conn->query($syQuery);
                                    if ($syRes->num_rows > 0) {
                                        while ($syRow = $syRes->fetch_assoc()) {
                                            $year = $syRow['syear'];
                                            $arQuery = "SELECT areaname, subarea 
                                                    FROM areas 
                                                    WHERE sccode='$sccode' 
                                                      AND sessionyear='$year'
                                                    ORDER BY areaname, subarea";
                                            $arRes = $conn->query($arQuery);

                                            echo "<div class='tree-node mb-1'>
                                                <div class='tree-toggle' onclick='toggleNode(this)'>
                                                    <i class='bx bx-chevron-right'></i> 
                                                    <label>
                                                        <input type='radio' name='targetClass' value='$year' onchange='updateSelection()'> <b>$year</b>
                                                    </label>
                                                </div>";

                                            if ($arRes->num_rows > 0) {
                                                echo "<ul class='ms-3' style='display:none;'>";
                                                $currentArea = '';
                                                while ($r = $arRes->fetch_assoc()) {
                                                    if ($currentArea != $r['areaname']) {
                                                        if ($currentArea != '')
                                                            echo "</ul></li>";
                                                        $currentArea = $r['areaname'];
                                                        echo "<li>
                                                            <div class='tree-toggle' onclick='toggleNode(this)'>
                                                                <i class='bx bx-chevron-right'></i>
                                                                <label>
                                                                    <input type='radio' name='targetClass' value='$year|{$r['areaname']}' onchange='updateSelection()'> {$r['areaname']}
                                                                </label>
                                                            </div>
                                                            <ul class='ms-3' style='display:none;'>";
                                                    }
                                                    $val = "$year|{$r['areaname']}|{$r['subarea']}";
                                                    echo "<li>
                                                        <label>
                                                            <input type='radio' name='targetClass' value='$val' onchange='updateSelection()'>
                                                            <i class='bx bx-book-open'></i> {$r['subarea']}
                                                        </label>
                                                    </li>";
                                                }
                                                echo "</ul></li></ul>";
                                            } else {
                                                echo "<div class='ms-4 text-muted small fst-italic'>No classes found.</div>";
                                            }
                                            echo "</div>";
                                        }
                                    } else {
                                        echo "<div class='text-muted small'>No session years found.</div>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- ====== Column 3: Action ====== -->
                            <div class="col-4">
                                <h6 class="text-secondary mb-2">Action</h6>
                                <div class="p-3 border rounded">
                                    <p class="small mb-1">Source → Target → <b>Clone Now</b> প্রেস করো।</p>
                                    <div class="mb-2 small text-muted">
                                        <div>🟦 Source: <span id="selSource" class="fw-semibold text-primary">—</span>
                                        </div>
                                        <div>🟩 Target: <span id="selTarget" class="fw-semibold text-success">—</span>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary w-100 mb-2" onclick="cloneClassSection()">🚀 Clone
                                        Now</button>
                                    <div id="cloneResult" class="text-success small"></div>
                                </div>
                            </div>

                        </div>
                    </div>


                </div>
            </div>
        </div>

        <div class="accordion-item border">
            <h2 class="accordion-header" id="headingSubjects">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseSubjects" aria-expanded="false">
                    <span class="pe-4 fw-bold text-primary"><i class="icon-base bi bi-book-half me-2"></i>
                        Clone Subjects</span>
                </button>
            </h2>
            <div id="collapseSubjects" class="accordion-collapse collapse" data-bs-parent="#cloneAccordion">
                <div class="accordion-body">

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-2 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-diagram-3-fill me-1"></i> Select Source(s)</span>
                                <div class="btn-group" role="group">
                                    <!-- Expand / Collapse -->
                                    <button type="button" class="btn btn-sm btn-outline-info" id="btnExpandAll"
                                        data-bs-toggle="tooltip" data-bs-title="Expand All">
                                        <i class="bi bi-arrows-expand"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCollapseAll"
                                        data-bs-toggle="tooltip" data-bs-title="Collapse All">
                                        <i class="bi bi-arrows-collapse"></i>
                                    </button>

                                    <!-- Select / Deselect -->
                                    <button type="button" class="btn btn-sm btn-outline-success" id="btnSelectAll"
                                        data-bs-toggle="tooltip" data-bs-title="Select All">
                                        <i class="bi bi-check2-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnDeselectAll"
                                        data-bs-toggle="tooltip" data-bs-title="Deselect All">
                                        <i class="bi bi-x-square"></i>
                                    </button>
                                </div>
                            </h6>

                            <div id="subjectTree" class="border p-2 rounded shadow-sm bg-white"
                                style="max-height:300px;overflow:auto;">
                                <?php
                                $sql = "SELECT DISTINCT sessionyear, areaname, subarea 
                                    FROM areas WHERE sccode='$sccode' 
                                    ORDER BY sessionyear DESC, areaname, subarea";

                                $data = [];
                                $q = $conn->query($sql);
                                while ($r = $q->fetch_assoc()) {
                                    $data[$r['sessionyear']][$r['areaname']][] = $r['subarea'];
                                }

                                $icony = '<i class="bi bi-calendar3 text-primary"></i>';
                                $iconc = '<i class="bi bi-building text-success"></i>';
                                $icons = '<i class="bi bi-card-list text-secondary"></i>';

                                foreach ($data as $year => $areas) {
                                    echo "<div class='mb-3'>";
                                    echo "<div class='btn-sm pb-1 mb-2 fw-semibold text-dark border-bottom d-block' 
                                        data-bs-toggle='collapse' data-bs-target='#y$year' 
                                        style='cursor:pointer;'>$icony $year</div>";
                                    echo "<div id='y$year' class='collapse ms-4 mt-1'>";
                                    foreach ($areas as $area => $subs) {
                                        $aid = md5($year . $area);
                                        echo "<div class='btn-sm pb-1 mb-2 text-dark border-bottom d-block collapse' 
                        data-bs-toggle='collapse' data-bs-target='#a$aid' 
                        style='cursor:pointer;'>$iconc $area</div>";
                                        echo "<div id='a$aid' class='collapse ms-4 mt-1'>";
                                        foreach ($subs as $sub) {
                                            $val = "$year|$area|$sub";
                                            echo "<div class='form-check mb-1'>
                            <input type='checkbox' id='$val' class='form-check-input chkSource' value='$val'>
                            <label class='form-check-label' for='$val'>$icons $sub</label>
                          </div>";
                                        }
                                        echo "</div>";
                                    }
                                    echo "</div></div>";
                                }
                                ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-success mb-2">Select Target</h6>
                            <select id="subjectTarget" class="form-select">
                                <option value="">-- Select Target --</option>
                                <?php
                                $res = $conn->query("SELECT DISTINCT sessionyear FROM areas WHERE sccode='$sccode' ORDER BY sessionyear DESC");
                                while ($r = $res->fetch_assoc()) {
                                    echo "<option value='{$r['sessionyear']}'>{$r['sessionyear']}</option>";
                                }
                                ?>
                            </select>

                            <div class="mt-3">
                                <button class="btn btn-primary w-100" id="btnCloneSubjects">
                                    <i class="bx bx-copy-alt me-1"></i> Clone Selected Subjects
                                </button>

                                <div id="cloneSubjectResult" class="mt-2 small text-success"></div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>



        <!-- ===== Accordion Item 3: Payment Clone ===== -->
        <div class="accordion-item border">
            <h2 class="accordion-header" id="headingPayments">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapsePayments" aria-expanded="false">
                    <span class="pe-4 fw-bold text-primary"><i class="icon-base bi bi-coin me-2"></i>
                        Clone Payments</span> 
                </button>
            </h2>

            <div id="collapsePayments" class="accordion-collapse collapse" data-bs-parent="#duplicatorAccordion">
                <div class="accordion-body">

                    <div class="row">

                        <!-- ===== Column 1: Source Year ===== -->
                        <div class="col-md-4 border-end">
                            <h6 class="text-primary mb-2">Select Source Session</h6>
                            <select id="paySource" class="form-select">
                                <option value="">-- Select Source Year --</option>
                                <?php
                                $res = $conn->query("SELECT DISTINCT syear FROM sessionyear WHERE sccode='$sccode' ORDER BY syear DESC");
                                while ($r = $res->fetch_assoc()) {
                                    echo "<option value='{$r['syear']}'>{$r['syear']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- ===== Column 2: Target Year ===== -->
                        <div class="col-md-4 border-end">
                            <h6 class="text-success mb-2">Select Target Session</h6>
                            <select id="payTarget" class="form-select">
                                <option value="">-- Select Target Year --</option>
                                <?php
                                $res2 = $conn->query("SELECT DISTINCT syear FROM sessionyear WHERE sccode='$sccode' ORDER BY syear DESC");
                                while ($r2 = $res2->fetch_assoc()) {
                                    echo "<option value='{$r2['syear']}'>{$r2['syear']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- ===== Column 3: Action ===== -->
                        <div class="col-md-4">
                            <h6 class="text-secondary mb-2">Action</h6>
                            <div class="p-3 border rounded">
                                <p class="small mb-2">Source → Target → <b>Clone Payments</b></p>
                                <div class="small text-muted mb-2">
                                    🟦 Source: <span id="paySelSource" class="fw-semibold text-primary">—</span><br>
                                    🟩 Target: <span id="paySelTarget" class="fw-semibold text-success">—</span>
                                </div>
                                <button id="btnClonePayments" class="btn btn-primary w-100">
                                    <i class="bx bx-transfer me-1"></i> Clone Payments
                                </button>
                                <div id="payCloneResult" class="small mt-2 text-info"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<?php require_once 'footer.php'; ?>

<script>
    function toggleNode(el) {
        const icon = el.querySelector('i');
        const nextUl = el.nextElementSibling;
        if (!nextUl) return;
        const isHidden = nextUl.style.display === "none" || nextUl.style.display === "";
        nextUl.style.display = isHidden ? "block" : "none";
        icon.classList.toggle('bx-chevron-down', isHidden);
        icon.classList.toggle('bx-chevron-right', !isHidden);
    }

    // Accordion header chevron
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(header => {
        header.addEventListener('click', () => {
            const icon = header.querySelector('.toggle-icon');
            const expanded = header.getAttribute('aria-expanded') === 'true';
            icon.classList.toggle('bx-chevron-down', !expanded);
            icon.classList.toggle('bx-chevron-up', expanded);
        });
    });

    // Update selected source/target live
    function updateSelection() {
        const src = document.querySelector('input[name="sourceClass"]:checked');
        const tgt = document.querySelector('input[name="targetClass"]:checked');
        document.getElementById("selSource").textContent = src ? src.value : "—";
        document.getElementById("selTarget").textContent = tgt ? tgt.value : "—";
    }

    function cloneClassSection() {
        const src = document.querySelector('input[name="sourceClass"]:checked');
        const tgt = document.querySelector('input[name="targetClass"]:checked');
        if (!src || !tgt) return alert("⚠️ Please select both Source and Target first!");
        $("#cloneResult").html("<span class='text-info'>Processing...</span>");
        $.post('ajax/clone_class_section.php', { src: src.value, tgt: tgt.value }, function (res) {
            $("#cloneResult").html(res);
        });
    }
</script>




<script>
    $("#btnCloneSubjects").on("click", function () {
        const target = $("#subjectTarget").val();
        const selected = $(".chkSource:checked").map(function () {
            return $(this).val();
        }).get();

        if (selected.length === 0) return alert("Please select at least one source!");
        if (!target) return alert("Select a target session/year!");

        $.post("ajax/clone_subjects.php", { sources: selected, target: target }, function (res) {
            $("#cloneSubjectResult").html(res);
        });
    });

</script>




<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tooltip initialization (already working)
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Expand All (native bootstrap collapse)
        document.getElementById('btnExpandAll').addEventListener('click', function () {
            document.querySelectorAll('#subjectTree .collapse').forEach(function (el) {
                // get existing instance or create one without toggling
                let instance = bootstrap.Collapse.getInstance(el);
                if (!instance) instance = new bootstrap.Collapse(el, { toggle: false });
                instance.show();
            });
        });

        // Collapse All
        document.getElementById('btnCollapseAll').addEventListener('click', function () {
            document.querySelectorAll('#subjectTree .collapse').forEach(function (el) {
                let instance = bootstrap.Collapse.getInstance(el);
                if (!instance) instance = new bootstrap.Collapse(el, { toggle: false });
                instance.hide();
            });
        });

        // Select All / Deselect All (still using jQuery for convenience)
        document.getElementById('btnSelectAll').addEventListener('click', function () {
            document.querySelectorAll('.chkSource').forEach(cb => cb.checked = true);
        });
        document.getElementById('btnDeselectAll').addEventListener('click', function () {
            document.querySelectorAll('.chkSource').forEach(cb => cb.checked = false);
        });
    });
</script>


<!-- JS for Payment Clone -->
<script>
    $("#paySource, #payTarget").on("change", function () {
        $("#paySelSource").text($("#paySource").val() || "—");
        $("#paySelTarget").text($("#payTarget").val() || "—");
    });

    $("#btnClonePayments").on("click", function () {
        const src = $("#paySource").val();
        const tgt = $("#payTarget").val();

        if (!src || !tgt) return alert("⚠️ Please select both Source and Target sessions first!");

        $("#payCloneResult").html("<span class='text-info'>Processing clone...</span>");

        // AJAX POST call
        $.post("ajax/clone_payment.php", { source: src, target: tgt }, function (res) {
            $("#payCloneResult").html(res);
        }).fail(function (xhr, status, error) {
            $("#payCloneResult").html(`<span class='text-danger'>❌ Error: ${error}</span>`);
        });
    });



</script>

</body>

</html>