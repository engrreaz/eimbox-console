<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card">
        <div class="card-body">
            <form id="mainForm" method="POST">
                <div class="row">

                    <!-- Slot -->
                    <div class="col-md-3 mb-3">
                        <label>Slot</label>
                        <select id="slot" name="slot" class="form-select form-select-sm">
                            <option value="">Select</option>
                            <?php
                            $sq = mysqli_query($conn, "SELECT slotname FROM slots WHERE sccode='$sccode'");
                            while ($row = mysqli_fetch_assoc($sq)) {
                                echo "<option value='{$row['slotname']}'>{$row['slotname']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Session -->
                    <div class="col-md-3 mb-3">
                        <label>Session</label>
                        <select id="session" name="session" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <!-- Exam -->
                    <div class="col-md-3 mb-3">
                        <label>Exam</label>
                        <select id="exam" name="exam" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <!-- Class -->
                    <div class="col-md-3 mb-3">
                        <label>Class</label>
                        <select id="class" name="class" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <!-- Section -->
                    <div class="col-md-3 mb-3">
                        <label>Section</label>
                        <select id="section" name="section" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <!-- Subject -->
                    <div class="col-md-3 mb-3">
                        <label>Subject</label>
                        <select id="subject" name="subject" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>&nbsp;</label><br>
                        <button id="btnView" class="btn btn-primary btn-sm py-2 w-100">View</button>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>&nbsp;</label><br>
                        <button type="submit" id="btnMerge" class="btn btn-success btn-sm py-2 w-100">Merge</button>
                    </div>

                </div>
            </form>

            <div id="progressArea" style="display:none;">
                <label>Merging Progress</label>
                <div class="progress" style="height:20px;">
                    <div id="mergeProgress" class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar" style="width: 0%; height:20px;">0%</div>
                </div>
                <div id="progressText" class="mt-2 small text-secondary"></div>
            </div>

        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>



<script>
    $(document).ready(function () {

        $("#btnMerge").click(function (e) {
            e.preventDefault(); // form submit stop

            let formData = $("#mainForm").serialize();

            // Show progress UI
            $("#progressArea").show();
            updateProgress(0, "Starting merge...");

            $.ajax({
                url: "result/merge-start.php",
                method: "POST",
                data: formData,
                dataType: "json",
                success: function (res) {
                    $("#progressText").html(res.total);

                    if (res.total == 0) {
                        updateProgress(0, "No students found. "+ res.cry);
                        return;
                    }

                    // Start student processing loop
                    processStudentRecursive(0, res.total, formData);
                }
            });
        });

        function processStudentRecursive(index, total, formData) {

            $.ajax({
                url: "result/merge-process.php",
                method: "POST",
                data: formData + "&index=" + index,
                dataType: "json",
                success: function (res) {

                    let percent = Math.round(((index + 1) / total) * 100);

                    updateProgress(percent, "Processing student " + (index + 1) + " of " + total);

                    // Continue loop
                    if (index + 1 < total) {
                        processStudentRecursive(index + 1, total, formData);
                    } else {
                        updateProgress(100, "Merge completed successfully!");
                    }
                }
            });
        }

        function updateProgress(percent, text) {
            $("#mergeProgress").css("width", percent + "%").html(percent + "%");
            $("#progressText").html(text);
        }

    });
</script>

<!-- *************************************************************************************************************** -->
<!-- UNIVERSAL SELECT LOADER -->
<script>
    function loadOptions(url, target, placeholder = "Select") {

        $("#" + target).html(`<option value="">Loading...</option>`);

        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            success: function (res) {

                $("#" + target).html(`<option value="">${placeholder}</option>`);

                if (res.length > 0) {
                    $.each(res, function (i, item) {
                        $("#" + target).append(`<option value="${item.value}">${item.label}</option>`);
                    });
                }

                // Restore saved value AFTER options load
                let saved = localStorage.getItem(currentPage + "_" + target);
                if (saved !== null && saved !== "") {
                    $("#" + target).val(saved).trigger("change");
                }
            }
        });

    }
</script>


<!-- UNIVERSAL SAVE / LOAD -->
<script>
    const currentPage = "<?php echo basename($_SERVER['PHP_SELF'], ".php"); ?>";

    function saveValue(id) {
        let key = currentPage + "_" + id;
        let val = $("#" + id).val();
        if (val !== null && val !== "") {
            localStorage.setItem(key, val);
        }
    }

    function loadValue(id) {
        let key = currentPage + "_" + id;
        return localStorage.getItem(key);
    }
</script>


<!-- MAIN SCRIPT -->
<script>
    $(document).ready(function () {

        // AUTO SAVE
        $("#slot, #session, #exam, #class, #section, #subject").on("change", function () {
            saveValue($(this).attr("id"));
        });


        /* SLOT → SESSION */
        $("#slot").change(function () {
            let slot = $(this).val();
            loadOptions("components/get-session.php?slot=" + slot, "session", "Select Session");
        });

        /* SESSION → EXAM + CLASS */
        $("#session").change(function () {
            let slot = $("#slot").val();
            let session = $(this).val();

            loadOptions("components/get-exam.php?slot=" + slot + "&session=" + session, "exam", "Select Exam");
            loadOptions("components/get-class.php?slot=" + slot + "&session=" + session, "class", "Select Class");
        });

        /* CLASS → SECTION */
        $("#class").change(function () {
            let slot = $("#slot").val();
            let session = $("#session").val();
            let className = $(this).val();

            loadOptions(
                "components/get-section.php?slot=" + slot + "&session=" + session + "&class=" + className,
                "section",
                "Select Section"
            );
        });

        /* SECTION → SUBJECT */
        $("#section").change(function () {
            let slot = $("#slot").val();
            let session = $("#session").val();
            let className = $("#class").val();
            let sectionName = $(this).val();

            loadOptions(
                "components/get-subject.php?slot=" + slot + "&session=" + session + "&class=" + className + "&section=" + sectionName,
                "subject",
                "Select Subject"
            );
        });


        /* ============================
           RESTORE CHAIN
           ============================ */
        let savedSlot = loadValue("slot");
        if (savedSlot) {
            $("#slot").val(savedSlot).trigger("change");
        }

    });
</script>

</body>

</html>