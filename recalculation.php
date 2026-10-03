<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header & Title -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold py-1 mb-1">
                <i class="bi bi-arrow-repeat text-primary me-2"></i>Marks & Grade Recalculation
            </h4>
            <span class="text-muted small">
                Recalculate student GP & GL in <code>stmark</code> table based on current <code>subsetup</code> and <code>slots</code> configuration.
            </span>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-border-shadow-primary mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">Slot</label>
                    <select id="slot" name="slot" class="form-select form-select-sm">
                        <option value="">Select Slot</option>
                        <?php
                        $sq = mysqli_query($conn, "SELECT slotname FROM slots WHERE sccode='$sccode'");
                        while ($row = mysqli_fetch_assoc($sq)) {
                            echo "<option value='{$row['slotname']}'>{$row['slotname']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">Session</label>
                    <select id="session" name="session" class="form-select form-select-sm">
                        <option value="">Select Session</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">Exam</label>
                    <select id="exam" name="exam" class="form-select form-select-sm">
                        <option value="">Select Exam</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">Class</label>
                    <select id="class" name="class" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">Section</label>
                    <select id="section" name="section" class="form-select form-select-sm">
                        <option value="">All Sections</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <label class="form-label small fw-bold">Subject</label>
                    <select id="subject" name="subject" class="form-select form-select-sm">
                        <option value="">All Subjects</option>
                    </select>
                </div>

                <div class="col-12 mt-3 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label small fw-bold mb-0 text-secondary">Batch Size:</label>
                        <select id="batchSize" class="form-select form-select-sm" style="width: 100px;">
                            <option value="15">15 records</option>
                            <option value="30" selected>30 records</option>
                            <option value="50">50 records</option>
                            <option value="100">100 records</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button id="btnStart" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-play-circle-fill me-1"></i> Start Recalculation
                        </button>
                        <button id="btnStop" class="btn btn-outline-danger btn-sm px-3" style="display:none;">
                            <i class="bi bi-stop-circle-fill me-1"></i> Stop
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Card -->
    <div class="card mb-3" id="progressCard" style="display:none;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold" id="progressStatusText">Processing...</span>
                <span class="badge bg-primary fs-6" id="progressPercentBadge">0%</span>
            </div>
            <div class="progress mb-3" style="height: 22px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                    role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    0%
                </div>
            </div>

            <!-- KPI Counters -->
            <div class="row text-center g-2 pt-2 border-top">
                <div class="col-6 col-md-3">
                    <div class="p-2 border rounded bg-light">
                        <div class="small text-muted fw-bold">TOTAL RECORDS</div>
                        <div class="fs-5 fw-bold text-dark" id="statTotal">0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2 border rounded bg-light">
                        <div class="small text-muted fw-bold">PROCESSED</div>
                        <div class="fs-5 fw-bold text-primary" id="statProcessed">0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2 border rounded bg-light">
                        <div class="small text-muted fw-bold">PASSED</div>
                        <div class="fs-5 fw-bold text-success" id="statPassed">0</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2 border rounded bg-light">
                        <div class="small text-muted fw-bold">FAILED (F)</div>
                        <div class="fs-5 fw-bold text-danger" id="statFailed">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recalculation Results Table Card -->
    <div class="card" id="resultsCard" style="display:none;">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <h6 class="m-0 fw-bold"><i class="bi bi-list-check me-2"></i>Recalculated Records Log</h6>
            <button class="btn btn-outline-secondary btn-sm" id="btnClearLog">Clear Log</button>
        </div>
        <div class="table-responsive" style="max-height: 450px;">
            <table class="table table-sm table-hover table-striped mb-0" id="logTable">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Marks / Full</th>
                        <th>Old (GP | GL)</th>
                        <th>New (GP | GL)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="logTbody">
                    <!-- Logs dynamically appended here -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>

<!-- Cascading Select Loader Script -->
<script>
    const currentPage = "<?php echo basename($_SERVER['PHP_SELF'], ".php"); ?>";

    function loadOptions(url, target, placeholder = "Select", callback = null) {
        $("#" + target).html(`<option value="">Loading...</option>`);

        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            success: function (res) {
                $("#" + target).html(`<option value="">${placeholder}</option>`);
                if (res && res.length > 0) {
                    $.each(res, function (i, item) {
                        $("#" + target).append(`<option value="${item.value}">${item.label}</option>`);
                    });
                }

                let saved = localStorage.getItem(currentPage + "_" + target);
                if (saved !== null && saved !== "") {
                    $("#" + target).val(saved);
                }

                if (typeof callback === "function") callback();
            },
            error: function () {
                $("#" + target).html(`<option value="">${placeholder}</option>`);
            }
        });
    }

    function saveValue(id) {
        let key = currentPage + "_" + id;
        let val = $("#" + id).val();
        if (val !== null && val !== "") localStorage.setItem(key, val);
    }

    $(document).ready(function () {

        $("#slot, #session, #exam, #class, #section, #subject").on("change click", function () {
            saveValue($(this).attr("id"));
        });

        $(document).on("change", "#slot", function () {
            let slot = $(this).val();
            if (!slot) return;

            loadOptions(
                "components/get-session.php?slot=" + encodeURIComponent(slot),
                "session",
                "Select Session",
                function () { $("#session").trigger("change"); }
            );
        });

        $(document).on("change", "#session", function () {
            let slot = $("#slot").val();
            let session = $(this).val();
            if (!session) return;

            loadOptions(
                "components/get-exam.php?slot=" + encodeURIComponent(slot) + "&session=" + encodeURIComponent(session),
                "exam",
                "Select Exam",
                function () { $("#exam").trigger("change"); }
            );

            loadOptions(
                "components/get-class.php?slot=" + encodeURIComponent(slot) + "&session=" + encodeURIComponent(session),
                "class",
                "All Classes",
                function () { $("#class").trigger("change"); }
            );
        });

        $(document).on("change", "#class", function () {
            let slot = $("#slot").val();
            let session = $("#session").val();
            let className = $(this).val();

            if (!className) {
                $("#section").html('<option value="">All Sections</option>');
                $("#subject").html('<option value="">All Subjects</option>');
                return;
            }

            loadOptions(
                "components/get-section.php?slot=" + encodeURIComponent(slot) + "&session=" + encodeURIComponent(session) + "&class=" + encodeURIComponent(className),
                "section",
                "All Sections",
                function () { $("#section").trigger("change"); }
            );
        });

        $(document).on("change", "#section", function () {
            let slot = $("#slot").val();
            let session = $("#session").val();
            let className = $("#class").val();
            let sectionName = $(this).val();

            if (!className) return;

            loadOptions(
                "components/get-subject.php?slot=" + encodeURIComponent(slot) + "&session=" + encodeURIComponent(session) + "&class=" + encodeURIComponent(className) + "&section=" + encodeURIComponent(sectionName),
                "subject",
                "All Subjects"
            );
        });

        // Trigger saved initial load if present
        let savedSlot = localStorage.getItem(currentPage + "_slot");
        if (savedSlot) {
            $("#slot").val(savedSlot).trigger("change");
        }

    });
</script>

<!-- Recalculation Engine Script -->
<script>
    let isProcessing = false;
    let stopRequested = false;
    let totalRecords = 0;
    let processedRecords = 0;
    let passedCount = 0;
    let failedCount = 0;
    let logIndex = 1;

    $("#btnStart").click(function () {
        let slot = $("#slot").val();
        let session = $("#session").val();
        let exam = $("#exam").val();
        let className = $("#class").val();
        let sectionName = $("#section").val();
        let subject = $("#subject").val();
        let batchSize = parseInt($("#batchSize").val()) || 30;

        if (!slot || !session || !exam) {
            alert("Please select Slot, Session and Exam first.");
            return;
        }

        if (!confirm("Are you sure you want to start recalculating GP and GL for the selected criteria?")) {
            return;
        }

        // Reset state
        isProcessing = true;
        stopRequested = false;
        totalRecords = -1;
        processedRecords = 0;
        passedCount = 0;
        failedCount = 0;
        logIndex = 1;

        $("#btnStart").prop("disabled", true);
        $("#btnStop").show();
        $("#progressCard").show();
        $("#resultsCard").show();
        $("#logTbody").html("");

        updateProgressUI(0, 0, "Initializing recalculation...");

        runBatch(0, slot, session, exam, className, sectionName, subject, batchSize);
    });

    $("#btnStop").click(function () {
        if (confirm("Do you want to stop the recalculation process?")) {
            stopRequested = true;
            $("#btnStop").prop("disabled", true).text("Stopping...");
        }
    });

    $("#btnClearLog").click(function () {
        $("#logTbody").html("");
        logIndex = 1;
    });

    function runBatch(offset, slot, session, exam, className, sectionName, subject, batchSize) {
        if (stopRequested) {
            finishRecalculation("Process stopped by user.");
            return;
        }

        $.ajax({
            url: "result/process-recalculation.php",
            type: "POST",
            data: {
                slot: slot,
                session: session,
                exam: exam,
                class: className,
                section: sectionName,
                subject: subject,
                offset: offset,
                batchSize: batchSize,
                totalCount: totalRecords
            },
            dataType: "json",
            success: function (res) {
                if (!res.success) {
                    alert("Error: " + (res.message || "Failed to process batch"));
                    finishRecalculation("Failed with errors.");
                    return;
                }

                if (totalRecords < 0) {
                    totalRecords = res.total;
                    $("#statTotal").text(totalRecords);
                }

                processedRecords = res.processed;
                passedCount += (res.passed || 0);
                failedCount += (res.failed || 0);

                let percent = totalRecords > 0 ? Math.min(100, Math.round((processedRecords / totalRecords) * 100)) : 100;
                updateProgressUI(percent, processedRecords, `Recalculated ${processedRecords} of ${totalRecords} records...`);

                // Append logs
                if (res.items && res.items.length > 0) {
                    let rowsHtml = "";
                    $.each(res.items, function (i, item) {
                        let statusBadge = item.status === 'Pass' 
                            ? `<span class="badge bg-success">Pass</span>` 
                            : `<span class="badge bg-danger">Fail</span>`;
                        
                        let changedClass = (item.old_gp != item.new_gp || item.old_gl != item.new_gl) ? "fw-bold text-primary" : "text-muted";

                        rowsHtml += `<tr>
                            <td>${logIndex++}</td>
                            <td><code>${item.stid}</code></td>
                            <td>${item.class}</td>
                            <td>${item.section}</td>
                            <td>${item.subject}</td>
                            <td>${item.total} / ${item.fullmarks}</td>
                            <td class="text-secondary">${item.old_gp} | ${item.old_gl}</td>
                            <td class="${changedClass}">${item.new_gp} | ${item.new_gl}</td>
                            <td>${statusBadge}</td>
                        </tr>`;
                    });
                    $("#logTbody").append(rowsHtml);
                }

                if (res.nextOffset !== null && !stopRequested) {
                    runBatch(res.nextOffset, slot, session, exam, className, sectionName, subject, batchSize);
                } else {
                    finishRecalculation("All records recalculated successfully!");
                }
            },
            error: function () {
                alert("Network / Server error occurred during processing.");
                finishRecalculation("Encountered connection error.");
            }
        });
    }

    function updateProgressUI(percent, processed, statusMsg) {
        $("#progressBar").css("width", percent + "%").text(percent + "%");
        $("#progressPercentBadge").text(percent + "%");
        $("#progressStatusText").text(statusMsg);
        $("#statProcessed").text(processed);
        $("#statPassed").text(passedCount);
        $("#statFailed").text(failedCount);
    }

    function finishRecalculation(finalMsg) {
        isProcessing = false;
        $("#btnStart").prop("disabled", false);
        $("#btnStop").hide().prop("disabled", false).html('<i class="bi bi-stop-circle-fill me-1"></i> Stop');
        $("#progressStatusText").html(`<strong class="text-success"><i class="bi bi-check-circle-fill me-1"></i> ${finalMsg}</strong>`);
        $("#progressBar").removeClass("progress-bar-animated");
    }
</script>
