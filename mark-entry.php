<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card">
        <div class="card-body">

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
                    <button type="submit" id="btnMerge" class="btn btn-success btn-sm py-2 w-100">Merge
                        Grand</button>
                </div>

            </div>


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



    <div class="card mt-3" id="mark-setup" hidden>

    </div>

    <div class="card mt-3" id="get-marks" hidden>

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
                    // $("#progressText").html(res.total);

                    if (res.total == 0) {
                        updateProgress(0, "No students found. " + res.cry);
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
    const currentPage = "<?php echo basename($_SERVER['PHP_SELF'], ".php"); ?>";

    // UNIVERSAL SELECT LOADER
    function loadOptions(url, target, placeholder = "Select", callback = null) {
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

                // Restore saved value
                let saved = localStorage.getItem(currentPage + "_" + target);
                if (saved !== null && saved !== "") {
                    $("#" + target).val(saved);
                }

                // callback after load
                if (typeof callback === "function") callback();
            }
        });
    }

    // UNIVERSAL SAVE/LOAD
    function saveValue(id) {
        let key = currentPage + "_" + id;
        let val = $("#" + id).val();
        if (val !== null && val !== "") localStorage.setItem(key, val);
    }

    function loadValue(id) {
        let key = currentPage + "_" + id;
        return localStorage.getItem(key);
    }

    // MAIN CHAIN
    $(document).ready(function () {

        // Auto-save
        $("#slot, #session, #exam, #class, #section, #subject").on("change click", function () {
            saveValue($(this).attr("id"));
        });

        // Slot → Session
        $(document).on("change click", "#slot", function () {
            let slot = $(this).val();
            if (!slot) return;

            loadOptions(
                "components/get-session.php?slot=" + slot,
                "session",
                "Select Session",
                function () { $("#session").trigger("change"); }
            );
        });

        // Session → Exam + Class
        $(document).on("change click", "#session", function () {
            let slot = $("#slot").val();
            let session = $(this).val();
            if (!session) return;

            loadOptions(
                "components/get-exam.php?slot=" + slot + "&session=" + session,
                "exam",
                "Select Exam",
                function () { $("#exam").trigger("change"); }
            );

            loadOptions(
                "components/get-class.php?slot=" + slot + "&session=" + session,
                "class",
                "Select Class",
                function () { $("#class").trigger("change"); }
            );
        });

        // Class → Section
        $(document).on("change click", "#class", function () {
            let slot = $("#slot").val();
            let session = $("#session").val();
            let className = $(this).val();
            if (!className) return;

            loadOptions(
                "components/get-section.php?slot=" + slot + "&session=" + session + "&class=" + className,
                "section",
                "Select Section",
                function () { $("#section").trigger("change"); }
            );
        });

        // Section → Subject
        $(document).on("change click", "#section", function () {
            let slot = $("#slot").val();
            let session = $("#session").val();
            let className = $("#class").val();
            let sectionName = $(this).val();
            if (!sectionName) return;

            loadOptions(
                "components/get-subject.php?slot=" + slot + "&session=" + session + "&class=" + className + "&section=" + sectionName,
                "subject",
                "Select Subject",
                function () {
                    // Small delay to ensure DOM is updated
                    setTimeout(function () {
                        $("#subject").trigger("change");
                    }, 200);
                }
            );
        });

        // Subject → Fetch Marks Setup
        $(document).on("change click", "#subject", function () {
            let slot = $("#slot").val();
            let session = $("#session").val();
            let className = $("#class").val();
            let sectionName = $("#section").val();
            let examName = $("#exam").val();
            let subCode = $(this).val();
            if (!subCode) return;

            $.ajax({
                url: "result/fetch-marks-distribution.php",
                type: "POST",
                data: {
                    slot: slot,
                    session: session,
                    class: className,
                    section: sectionName,
                    exam: examName,
                    subject: subCode
                },
                success: function (response) {
                    $("#mark-setup").html(response).removeAttr("hidden");
                },
                error: function () { alert("Unable to load marks distribution."); }
            });
        });

        // Restore previous selection
        let savedSlot = loadValue("slot");
        if (savedSlot) $("#slot").val(savedSlot).trigger("change");

    });
</script>


<script>
    $(document).ready(function () {
        let savedSlot = loadValue("slot");
        if (savedSlot) {
            $("#slot").val(savedSlot).trigger("change");
        }

    });
</script>

<script>
    $(document).on("click", "#btnView", function () {

        let savedSection = loadValue("section");
        let savedSubject = loadValue("subject");

        function doFetch() {
            let slot = $("#slot").val();
            let session = $("#session").val();
            let className = $("#class").val();
            let sectionName = $("#section").val();
            let examName = $("#exam").val();
            let subCode = $("#subject").val();

            if (subCode === "") {
                alert("Please select subject.");
                return;
            }

            $("#get-marks").html('Loading...');
            $.ajax({
                url: "result/fetch-marks.php",
                type: "POST",
                data: {
                    slot: slot,
                    session: session,
                    class: className,
                    section: sectionName,
                    exam: examName,
                    subject: subCode
                },
                success: function (response) {
                    $("#get-marks").html(response);
                    $("#get-marks").removeAttr('hidden');
                },
                error: function () {
                    alert("Unable to load marks data from server.");
                }
            });
        }

        // যদি mark-setup hidden থাকে → restore section + subject
        if ($("#mark-setup").is(":hidden")) {
            if (savedSection) {
                $("#section").val(savedSection).trigger("click");

                // Section trigger হলে subject trigger
                setTimeout(function () {
                    if (savedSubject) {
                        $("#subject").val(savedSubject).trigger("click");
                    }
                    // AJAX call এখন চালাবে
                    doFetch();
                }, 500); // subject load delay
            } else {
                // Section না থাকলে সরাসরি subject restore
                if (savedSubject) {
                    $("#subject").val(savedSubject).trigger("click");
                }
                doFetch();
            }
        } else {
            // hidden না হলে সরাসরি AJAX
            doFetch();
        }
    });

</script>

<script>
    $(document).on("keydown", ".mark", function (e) {
        if (e.key === "Enter") {
            e.preventDefault(); // form submit না হওয়ার জন্য
            let inputs = $(".mark");
            let idx = inputs.index(this); // current input index
            if (idx >= 0 && idx < inputs.length - 1) {
                inputs.eq(idx + 1).focus(); // next input এ ফোকাস
            }
        }
    });

    $(document).on("blur", ".mark", function () {

        let stid = $(this).data("stid");

        let row = $(this).closest("tr");

        // Enabled inputs collect
        let ct = row.find(".ct:enabled").val();
        let mt = row.find(".mt:enabled").val();
        let sub = row.find(".sub:enabled").val();
        let obj = row.find(".obj:enabled").val();
        let pra = row.find(".pra:enabled").val();
        let ca = row.find(".ca:enabled").val();

        let ctmax = parseFloat(document.getElementById('ctmax')?.innerText) || 0;
        let mtmax = parseFloat(document.getElementById('mtmax')?.innerText) || 0;
        let submax = parseFloat(document.getElementById('submax')?.innerText) || 0;
        let objmax = parseFloat(document.getElementById('objmax')?.innerText) || 0;
        let pramax = parseFloat(document.getElementById('pramax')?.innerText) || 0;
        let camax = parseFloat(document.getElementById('camax')?.innerText) || 0;
        let alg = parseFloat(document.getElementById('alg')?.innerText) || 0;


        if (ct > ctmax || mt > mtmax || sub > submax || obj > objmax || pra > pramax || ca > camax) {
            alert(ct + 'Invalid Marks' + ctmax);
            let input = $(this);
            setTimeout(function () {
                input.focus();
            }, 0);
            return;
        }


        // যদি কোনো enabled খাত ফাঁকা থাকে → return
        let allFilled = true;
        row.find(".mark:enabled").each(function () {
            if ($(this).val() === "") allFilled = false;
        });
        if (!allFilled) return;

        // মোট নম্বর হিসাব
        let total =
            (parseFloat(ct || 0) +
                parseFloat(mt || 0) +
                parseFloat(sub || 0) +
                parseFloat(obj || 0) +
                parseFloat(pra || 0) +
                parseFloat(ca || 0));

        row.find(".total").val(total);

        // AJAX Save
        $.ajax({
            url: "result/save-stmark.php",
            type: "POST",
            data: {
                stid: stid,
                slot: $("#slot").val(),
                session: $("#session").val(),
                class: $("#class").val(),
                section: $("#section").val(),
                exam: $("#exam").val(),
                subject: $("#subject").val(),
                ct: ct, mt: mt, sub: sub, obj: obj, pra: pra, ca: ca, alg: alg,
                total: total
            },
            success: function (response) {
                // response = "4.50/A" এর মতো আসবে
                $("#gpgl_" + stid).text(response);
            },
            error: function () {
                alert("Unable to save marks!");
            }
        });

    });

</script>

</body>

</html>