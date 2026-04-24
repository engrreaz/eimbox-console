<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card mb-4">
        <div class="card-header bg-light fw-bold">
            Search EIIN
        </div>
        <div class="card-body ">
            <form id="checkInstitutionForm">
                <div class="row g-2 align-items-center mt-2">
                    <div class="col-md-2">
                        <input type="text" name="check_sccode" id="check_sccode" class="form-control"
                            placeholder="Enter EIIN" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary pt-3 pb-3"> Serch EIIN </button>
                    </div>
                    <div class="col-md-8">
                        <span id="checkResult" class="fw-semibold text-success"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>








    <div class="card shadow-lg">
        <div class="card-header bg-primary ">
            <h5 class="mb-0 text-white">Institution Setup Wizard (Mock Settings)</h5>
        </div>

        <div class="card-body">
            <!-- Tabs -->
            <ul class="nav nav-tabs" id="setupTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab1" data-bs-toggle="tab" data-bs-target="#step1" type="button"
                        role="tab">
                        Register New EIIN
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab2" data-bs-toggle="tab" data-bs-target="#step2" type="button"
                        role="tab" disabled>
                        Settings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab3" data-bs-toggle="tab" data-bs-target="#step3" type="button"
                        onclick="thirdSetp();" role="tab" disabled>
                        Mock Data
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab4" data-bs-toggle="tab" data-bs-target="#step4" type="button"
                        onclick="fourthSetp();" role="tab" disabled>
                        Sample Marks
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-danger" id="tab5" data-bs-toggle="tab" data-bs-target="#step5"
                        type="button" onclick="fifthSetp();" role="tab">
                        Rollback
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-3" id="setupTabsContent">
                <!-- Step 1: Institution Form -->
                <div class="tab-pane fade show active" id="step1" role="tabpanel">
                    <form id="instForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Institute Code</label>
                            <input type="text" name="sccode" class="form-control" placeholder="ex: SC001" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Institute Name</label>
                            <input type="text" name="scname" class="form-control" placeholder="Institute Name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="sccategory" class="form-select">
                                <option value="">Select Category</option>
                                <option value="School">School</option>
                                <option value="College">College</option>
                                <option value="College">School-College</option>
                                <option value="Madrasa">Madrasa</option>
                                <option value="Technical">Technical</option>
                                <option value="Technical">Non-Govt</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="scadd1" class="form-control" placeholder="Village/Road/Street">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="scadd2" class="form-control" placeholder="Post Office / Upazila">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">District</label>
                            <select name="dist" id="dist" class="form-select">
                                <option value="">Select District</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Police Station</label>
                            <select name="ps" id="ps" class="form-select">
                                <option value="">Select PS</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control" placeholder="017XXXXXXXX">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="scmail" class="form-control" placeholder="example@email.com">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Head Name</label>
                            <input type="text" name="headname" class="form-control" placeholder="Principal / Head">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Head Title</label>
                            <input type="text" name="headtitle" class="form-control" placeholder="Designation">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Root User (Username)</label>
                            <input type="text" name="rootuser" class="form-control" placeholder="admin / headteacher">
                        </div>

                        <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4">Save & Next</button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Settings -->
                <div class="tab-pane fade" id="step2" role="tabpanel">

                </div>


                <!-- Step 3: Dummy Data -->
                <div class="tab-pane fade" id="step3" role="tabpanel">
                    <p class="text-muted">Dummy data insert section will appear here...</p>
                </div>

                <div class="tab-pane fade" id="step4" role="tabpanel">
                    <p class="text-muted">Marks Here...</p>

                    <div class="mt-4">
                        <button id="generateBtn" class="btn btn-primary">Generate Mock Marks</button>
                        <div class="mt-2">
                            <div class="progress" style="height:25px;">
                                <div id="progressBar" class="progress-bar" role="progressbar" style="width:0%">0%</div>
                            </div>
                            <div id="progressText" class="mt-1"></div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="step5" role="tabpanel">
                    <p class="text-muted">This will rollback all dummy record. Just Resetting your account. </p>
                    <button onclick="rollback();" class="btn btn-danger fw-bold">Proceed?</button>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<!-- ----------------------------------- -->

<!-- ----------------------------------- -->
<script>
    function thirdSetp() {
        const sccode = $("#check_sccode").val().trim();
        const tab3Btn = document.getElementById('tab3');
        tab3Btn.removeAttribute('disabled'); // যদি disabled থাকে
        const tab3 = new bootstrap.Tab(tab3Btn);
        tab3.show();
        $("#step3").load("ajax/load_dummy.php?sccode=" + encodeURIComponent(sccode));
    }
</script>
<script>
    function fourthSetp() {
        const sccode = $("#check_sccode").val().trim();
        const tab4Btn = document.getElementById('tab4');
        tab4Btn.removeAttribute('disabled'); // যদি disabled থাকে
        const tab4 = new bootstrap.Tab(tab4Btn);
        tab4.show();
        $("#step4").html("Executing...");
        $("#step4").load("ajax/load_dummy_2.php?sccode=" + encodeURIComponent(sccode));
    }
</script>

<script>
    function fifthSetp() {
        const sccode = $("#check_sccode").val().trim();
        const tab4Btn = document.getElementById('tab5');
        tab4Btn.removeAttribute('disabled'); // যদি disabled থাকে
        const tab4 = new bootstrap.Tab(tab5Btn);
        tab4.show();
    }
    function rollback() {
        alert('Rolling Back');
        const sccode = $("#check_sccode").val().trim();
        const tab5Btn = document.getElementById('tab5');
        const tab5 = new bootstrap.Tab(tab5Btn);
        tab5.show();
        $("#step5").load("ajax/rollback.php?sccode=" + encodeURIComponent(sccode));
    }
</script>




<script>
    $(document).on("click", ".btn-insert", function () {

        const btn = $(this);

        const sccode = btn.data("sccode");
        const slot = btn.data("slot");
        const sessionyear = btn.data("sessionyear");
        const classname = btn.data("classname");
        const sectionname = btn.data("sectionname");
        const exam = btn.data("exam");
        const exam_id = btn.data("exam_id");

        if (!confirm("Insert Mock Data")) return;

        let countdown = 100; // 100 seconds
        btn.text(`Processing... (${countdown}s)`);

        // Countdown interval
        const timer = setInterval(() => {
            countdown--;
            if (countdown >= 0) {
                btn.text(`Processing... (${countdown}s)`);
            } else {
                clearInterval(timer);
            }
        }, 1000);

        $.ajax({
            url: "ajax/dummy-mark.php",
            type: "POST",
            data: {
                sccode: sccode,
                sl: slot,
                sy: sessionyear,
                cc: classname,
                ss: sectionname,
                ex: exam,
                id: exam_id
            },
            beforeSend: function () {
                $(".btn-insert").prop("disabled", true);
            },
            success: function (res) {
                alert(res);
            },
            complete: function () {
                clearInterval(timer); // Ajax শেষ হলে countdown বন্ধ
                $(".btn-insert").prop("disabled", false);
                 btn.prop("disabled", true);
                btn.text("Data Inserted");
            },
            error: function (xhr) {
                clearInterval(timer);
                alert("Error: " + xhr.statusText);
                $(".btn-insert").prop("disabled", false);
               
            }
        });

    });
</script>



<script>
    $(document).ready(function () {
        $("#checkInstitutionForm").on("submit", function (e) {
            e.preventDefault();

            const sccode = $("#check_sccode").val().trim();
            const $result = $("#checkResult");

            if (!sccode) {
                $result.removeClass().addClass("text-danger").text("Please Provide 6 Digits EIIN");
                return;
            }

            // প্রক্রিয়ার বার্তা দেখানো
            $result.removeClass().addClass("text-muted").text("Verifying...");

            // AJAX request
            $.ajax({
                url: "ajax/check_institution.php",
                type: "POST",
                dataType: "json",
                data: { sccode: sccode },
                success: function (res) {
                    if (!res || typeof res.status === 'undefined') {
                        $result.removeClass().addClass("text-warning").text("Unsuspected Error");
                        console.log("Unexpected response:", res);
                        return;
                    }

                    if (res.status === "found") {

                        $result.removeClass().addClass("text-success").html("EIIN Found : <b>" + res.scname + "</b>");
                        // এখানে চাইলে পরবর্তী ট্যাব সক্রিয় করতে পারো
                        // ===== পরবর্তী ট্যাব সক্রিয় =====
                        const tab2Btn = document.getElementById('tab2');
                        tab2Btn.removeAttribute('disabled'); // যদি disabled থাকে
                        const tab2 = new bootstrap.Tab(tab2Btn);
                        tab2.show();
                        $("#step2").load("ajax/load_settings.php?new_sccode=" + encodeURIComponent(sccode));

                    } else if (res.status === "notfound") {

                        $result.removeClass().addClass("text-danger").text("No Record Found");
                        $('#tab2').prop('disabled', true);
                        $('#tab3').prop('disabled', true);
                        $('#tab4').prop('disabled', true);
                        $('#tab1').trigger('click');

                    } else if (res.status === "error") {
                        $result.removeClass().addClass("text-danger").text("Error : " + res.msg);
                    } else {
                        $result.removeClass().addClass("text-warning").text("Unknown Response");
                        console.log("Unknown response:", res);
                    }
                },
                error: function (xhr, status, error) {
                    $result.removeClass().addClass("text-danger").text("Server Error");
                    console.error("AJAX Error:", status, error);
                    console.log("Server Response:", xhr.responseText);
                }
            });
        });
    });
</script>

<script>
    function fourthStepx() {
        const sccode = $("#check_sccode").val().trim();
        const tab4Btn = document.getElementById('tab4');
        tab4Btn.removeAttribute('disabled');
        const tab4 = new bootstrap.Tab(tab4Btn);
        tab4.show();

        const output = document.getElementById("step4");
        output.innerHTML = "<b>Processing...</b><br>";

        const evtSource = new EventSource("ajax/load_dummy_2.php?sccode=" + encodeURIComponent(sccode));

        evtSource.onmessage = function (e) {
            output.innerHTML += e.data + "<br>";
            output.scrollTop = output.scrollHeight; // অটো স্ক্রল
        };

        evtSource.onerror = function () {
            output.innerHTML += "<b style='color:red'>Connection closed or error occurred.</b><br>";
            evtSource.close();
        };
    }
</script>


<script>
    $(document).ready(function () {

        // Settings Form Submit
        $(document).on('submit', '#settingsForm', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = $(this).find('button[type="submit"]');
            const nextBtn = $('#Step3');

            submitBtn.prop('disabled', true).text('Updating...');
            nextBtn.prop('disabled', true);

            let output = '';
            for (let [key, value] of formData.entries()) {
                output += `${key}: ${value}\n`;
            }
            // alert(output);


            fetch('ajax/update_settings.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        submitBtn.text('✅ Updated');
                        nextBtn.prop('disabled', false);
                        alert('✅ Settings updated successfully!');
                        thirdSetp();
                    } else {
                        alert('⚠️ Error: ' + (data.msg || 'Unknown error'));
                        submitBtn.text('Update Settings');
                    }
                })
                .catch(err => {
                    alert('❌ AJAX Error: ' + err.message);
                    submitBtn.text('Update Settings');
                })
                .finally(() => {
                    submitBtn.prop('disabled', false);
                });
        });

        // Next Step Button
        $('#Step3').on('click', function () {
            $('#tab3').removeAttr('disabled');
            const tab = new bootstrap.Tab(document.getElementById('tab3'));
            tab.show();
        });

    });
</script>



<script>

    document.addEventListener('DOMContentLoaded', function () {
        const distSelect = document.getElementById('dist');
        const psSelect = document.getElementById('ps');
        let districts = [];
        let upazilas = [];

        // জেলা লোড করা
        fetch('assets/json/districts.json')
            .then(res => res.json())
            .then(json => {
                // phpMyAdmin JSON থেকে শুধুমাত্র "data" অংশ বের করা
                const tableObj = json.find(obj => obj.type === "table" && obj.name === "districts");
                if (!tableObj || !tableObj.data) throw new Error("Invalid districts.json structure");
                districts = tableObj.data;

                districts.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = `${d.bn_name} (${d.name})`;
                    distSelect.appendChild(opt);
                });
            })
            .catch(err => console.error('District load error:', err));

        // উপজেলা লোড করা
        fetch('assets/json/upazilas.json')
            .then(res => res.json())
            .then(json => {
                const tableObj = json.find(obj => obj.type === "table" && obj.name === "upazilas");
                if (!tableObj || !tableObj.data) throw new Error("Invalid upazilas.json structure");
                upazilas = tableObj.data;
            })
            .catch(err => console.error('Upazila load error:', err));

        // জেলা পরিবর্তন করলে উপজেলা ফিল্টার করা
        distSelect.addEventListener('change', function () {
            const distId = this.value;
            psSelect.innerHTML = '<option value="">Select PS</option>';
            if (!distId) return;

            const filtered = upazilas.filter(u => u.district_id == distId);
            filtered.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.name;
                opt.textContent = `${u.bn_name} (${u.name})`;
                psSelect.appendChild(opt);
            });
        });

        // ফরম সাবমিটাাাা---------
        document.getElementById('instForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('ajax/insert_institution.php', { 
                method: 'POST',
                body: formData   
            })
                .then(res => res.text())
                .then(data => {
                    alert(data || 'Institution saved!');
                    // দ্বিতীয় ট্যাব সক্রিয় করা
                    document.getElementById('tab2').removeAttribute('disabled');
                    const tab2 = new bootstrap.Tab(document.getElementById('tab2'));
                    tab2.show();
                })
                .catch(err => alert('Error: ' + err.message));
        });
    });
</script>


<!-- ----------------------------------- -->



<!-- ----------------------------------- -->

<!-- ----------------------------------- -->
</body>

</html>