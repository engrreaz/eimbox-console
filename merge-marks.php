<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <form id="mainForm" method="POST">
        <div class="card">
            <div class="card-body">

                <div class="row">

                    <!-- Slot -->
                    <div class="col-md-2 mb-3">
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
                    <div class="col-md-2 mb-3">
                        <label>Session</label>
                        <select id="session" name="session" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <!-- Exam -->
                    <div class="col-md-3 mb-3" hidden>
                        <label>Exam</label>
                        <select id="exam" name="exam" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <!-- Class -->
                    <div class="col-md-2 mb-3">
                        <label>Class</label>
                        <select id="class" name="class" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <!-- Section -->
                    <div class="col-md-2 mb-3">
                        <label>Section</label>
                        <select id="section" name="section" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <!-- Subject -->
                    <div class="col-md-2 mb-3">
                        <label>Subject</label>
                        <select id="subject" name="subject" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>


                    <div class="col-md-2 mb-3">
                        <label>&nbsp;</label><br>
                        <button type="submit" id="btnMerge" class="btn btn-warning text-dark btn-sm py-2 w-100">Merge
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


    </form>

    <div class="card mt-3">
        <div class="card-body">
            <div class="row">
                <div class="col-auto gap-2">
                    <button type="submit" class="btn btn-danger btn-sm " id="mergeEntire">Merge Entire Session</button>

                </div>
                <div class="col-auto gap-2">
                    <button type="submit" class="btn btn-dark btn-sm " id="resetMerge">Reset All</button>

                </div>

            </div>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>



<script>
    function updateProgress(percent, text) {
        $("#mergeProgress").css("width", percent + "%").html(percent + "%");
        $("#progressText").html(text);
    }

    $(document).ready(function () {
        $("#btnMerge").click(function (e) {
            e.preventDefault(); // form submit stop

            let formData = $("#mainForm").serialize();
            // h5 এর মানগুলো পড়া
            let ct = $("#ctmax_final").text();
            let mt = $("#mtmax_final").text();
            let sub = $("#submax_final").text();
            let obj = $("#objmax_final").text();
            let pra = $("#pramax_final").text();
            let ca = $("#camax_final").text();
            let total = $("#totalmax_final").text();
            let alg = $("#alg_final").text();
            let fourth = $("#fourth_final").text();

            // formData তে যুক্ত করা
            formData += `&ctmax_final=${encodeURIComponent(ct)}`;
            formData += `&mtmax_final=${encodeURIComponent(mt)}`;
            formData += `&submax_final=${encodeURIComponent(sub)}`;
            formData += `&objmax_final=${encodeURIComponent(obj)}`;
            formData += `&pramax_final=${encodeURIComponent(pra)}`;
            formData += `&camax_final=${encodeURIComponent(ca)}`;
            formData += `&totalmax_final=${encodeURIComponent(total)}`;
            formData += `&alg_final=${encodeURIComponent(alg)}`;
            formData += `&fourth_final=${encodeURIComponent(fourth)}`;
            // alert(formData);
            // Show progress UI
            $("#progressArea").show();
            updateProgress(0, "Starting merge..." + formData);


            $.ajax({
                url: "result/merge-start.php",
                method: "POST",
                data: formData,
                dataType: "json",
                success: function (res) {
                    $("#progressText").html(res.cry);

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
                url: "result/fetch-exam-list.php",
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
                    updateFinalMarks();
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

    // Cookie সেট করার ফাংশন  
    function setCookie(name, value, days = 30) {
        let expires = "";
        if (days) {
            let d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + d.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    // Cookie পড়ার ফাংশন
    function getCookie(name) {
        let cname = name + "=";
        let ca = document.cookie.split(';');
        for (let i of ca) {
            while (i.charAt(0) === ' ') i = i.substring(1);
            if (i.indexOf(cname) === 0) return i.substring(cname.length, i.length);
        }
        return "";
    }


    // -----------------------------
    //  Checkbox Selection Handler
    // -----------------------------
    $(document).on("change", ".examItem", function () {

        let selected = [];
        $(".examItem:checked").each(function () {
            selected.push($(this).val());
        });

        // Cookie save
        setCookie("examitems", selected.join(","), 30);

        // Update final area
        updateFinalMarks();

    });


    // -----------------------------------------
    //  Final Calculation Function
    // -----------------------------------------
    function updateFinalMarks() {

        // কতগুলো exam select হয়েছে
        let count = $(".examItem:checked").length;
        if (count == 0) count = 0;

        // উপরের ব্লক থেকে মান পড়া
        let ct = parseFloat($("#ctmax").text()) || 0;
        let mt = parseFloat($("#mtmax").text()) || 0;
        let sub = parseFloat($("#submax").text()) || 0;
        let obj = parseFloat($("#objmax").text()) || 0;
        let pra = parseFloat($("#pramax").text()) || 0;
        let ca = parseFloat($("#camax").text()) || 0;
        let total = parseFloat($("#totalmax").text()) || 0;

        // গুণ করে নিচের ব্লকে বসানো
        $("#ctmax_final").text(ct * count);
        $("#mtmax_final").text(mt * count);
        $("#submax_final").text(sub * count);
        $("#objmax_final").text(obj * count);
        $("#pramax_final").text(pra * count);
        $("#camax_final").text(ca * count);
        $("#totalmax_final").text(total * count);

        // অন্যান্য ফিল্ড যদি দরকার হয় → নিচে বসাতে পারো
    }

    $(document).ready(function () {
        // পেজ লোড হলে পুরোটাই আপডেট হবে
        updateFinalMarks();
    });

</script>

<script>
    $(document).ready(function () {

        $("#resetMerge").click(function (e) {
            e.preventDefault();

            let slot = $("#slot").val();
            let session = $("#session").val();

            if (!slot || !session) {
                alert("Please select Slot and Session first.");
                return;
            }

            if (!confirm("Do you want to reset all merged marks for this session?")) return;

            // AJAX call to reset
            $.ajax({
                url: "result/reset-merge.php",
                method: "POST",
                data: { slot: slot, session: session },
                success: function (res) {
                    // Assume res = { success: true/false, message: "..." }
                    try { res = JSON.parse(res); } catch (e) { res = { success: false, message: "Invalid response" }; }

                    if (res.success) {
                        alert("Reset completed. Starting merge again...");
                        // Trigger mergeEntire click
                        $("#mergeEntire").click();
                    } else {
                        alert("Reset failed: " + res.message);
                    }
                },
                error: function () {
                    alert("AJAX error during reset.");
                }
            });
        });

    });

</script>

<script>
    // Merge Entire Session
    // Batch-wise Merge Entire
    $("#mergeEntire").click(function (e) {
        e.preventDefault();
        if (!confirm("Do you want to merge all students in this session?")) return;

        let slot = $("#slot").val();
        let session = $("#session").val();

        if (!slot || !session) {
            alert("Please select Slot and Session first.");
            return;
        }

        $("#progressArea").show();
        updateProgress(0, "Starting merge of entire session...");

        // Start batch processing
        mergeBatch(0, slot, session);
    });

    function mergeBatch(offset, slot, session, batchSize = 1) {
        let ct = $("#ctmax_final").text();
        let mt = $("#mtmax_final").text();
        let sub = $("#submax_final").text();
        let obj = $("#objmax_final").text();
        let pra = $("#pramax_final").text();
        let ca = $("#camax_final").text();
        let total = $("#totalmax_final").text();
        let alg = $("#alg_final").text();
        let fourth = $("#fourth_final").text();
        $.ajax({
            url: "result/merge-entire-batch.php",
            method: "POST",
            data: { slot: slot, session: session, offset: offset, batchSize: batchSize, ct: ct, mt:mt, sub:sub, obj:obj, pra:pra, ca:ca, total:total, alg:alg, fourth:fourth },
            dataType: "json",
            success: function (res) {
                if (res.done) {
                    // update progress
                    let percent = Math.min(100, Math.round(((offset + res.count) / res.total) * 100));
                    let up = Math.round(res.total) + 1;
                    updateProgress(percent, `Merged ${offset + res.count} of ${up} students`);
                    showToast('success', `Merged ${offset + res.count} of ${up} students`, 'On progress...');
                    if (res.nextOffset !== null) {
                        mergeBatch(res.nextOffset, slot, session, batchSize);
                    } else {
                        updateProgress(100, "All students merged successfully!");
                        alert("Merge completed for entire session.");
                    }
                } else {
                    alert("Error during merging.");
                }
            },
            error: function () {
                alert("AJAX error during merging.");
            }
        });
    }


</script>

</body>

</html>