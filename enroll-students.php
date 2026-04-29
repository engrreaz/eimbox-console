<?php require_once 'header.php'; ?>


<?php

setcookie("enroll_save", "next", time() + (86400 * 30), "/");
$enroll_action = $_COOKIE['enroll_save'] ?? "blank";
if (isset($_GET['stid']))
    $enroll_action = 'back';





// echo $enroll_action;
$sql = "SELECT st_entry_fld FROM usersapp 
        WHERE email='$usr' AND sccode='$sccode' 
        LIMIT 1";

$res = $conn->query($sql);

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $st_entry_fld = $row['st_entry_fld'];
} else {
    $st_entry_fld = "";
}

if ($st_entry_fld == '' || $st_entry_fld == null) {
    $st_entry_fld = "Student_Name_English.Student-Name-Bengali";
}

$field_name = array("stnameeng", "stnameben", "fname", "fnameben", "fprof", "fmobile", "fnid", "falive", "mname", "mnameben", "mprof", "mmobile", "mnid", "malive", "previll", "prepo", "preps", "predist", "pervill", "perpo", "perps", "perdist", "dob", "religion", "brn", "gender", "bgroup", "disables", "height", "weight", "guarname", "guarnameben", "guarrelation", "guarnid", "guarmobile", "guaremail", "guaradd", "guarmobile2", "guaremail2", "tcno", "preins", "preinsadd", "doa");
$display_name = array("Student_Name_English", "Student_Name_Bengali", "Father_Name_English", "Father_Name_Bengali", "Profession_Father", "Mobile_Father", "NID_Father", "Alive_Father", "Mother_Name_English", "Mother_Name_Bengali", "Profession_Mother", "Mobile_Mother", "NID_Mother", "Alive_Mother", "Address1_Present", "Address2_Present", "Upazila_Present", "District_Present", "Address1_Permanent", "Address2_Permanent", "Upazila_Permanent", "District_Permanent", "Date_of_Birth", "Religion", "Birth_Regd", "Gender", "Blood_Group", "Disability", "Height", "Weight", "Guardian_Name_Eng", "Guardian_name_Ben", "Relation_Gurardin", "NID_Gurardin", "Mobile_Gurardin", "Email_Gurardin", "Address_Gurardin", "Mobile2_Gurardin", "Email2_Gurardin", "TC_No", "Previous_Institute", "Ins_Address", "Date_of_Admission");

?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- ============================================================= -->
    <!-- CARD 1: SESSION / BASIC ENTRY SELECTION -->
    <!-- ============================================================= -->




    <div class="modal fade" id="optionsModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header d-flex align-items-center justify-content-between">

                    <h5 class="modal-title mb-0">Options : Entry Field</h5>

                    <div class="d-flex align-items-center gap-2">
                        <label for="entry_action" class="fs-tiny"> After Submit</label>

                        <select id="entry_action" class="form-select form-select-sm">
                            <option value="blank">No Action</option>
                            <option value="next">Next Roll</option>
                            <option value="back">Go Back</option>
                        </select>

                        <button type="button" class="btn-close " data-bs-dismiss="modal">
                        </button>
                    </div>

                </div>

                <div class="modal-body">

                    <div id="myDiv" class="row mb-3">
                        <div class="col-md-12 h-100">
                            <div class="card card-border-shadow-primary">
                                <div class="card-body">
                                    <h5>Available Items</h5>
                                    <ul id="leftList" class="row d-flex fs-6 m-0 p-0">
                                        <?php
                                        foreach ($display_name as $disp) {
                                            if (str_contains($st_entry_fld, $disp))
                                                continue;
                                            ?>
                                            <li class="nav col m-2 p-1 px-3 badge badge-dark">
                                                <?php echo $disp; ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3">
                            <div class="card card-border-shadow-secondary">
                                <div class="card-body">
                                    <h5 class="text-warning">Selected Items</h5>
                                    <ul id="rightList" class="row d-flex fs-6  m-0 p-0">
                                        <?php
                                        foreach ($display_name as $disp) {
                                            if (str_contains($st_entry_fld, $disp)) { ?>
                                                <li class="nav col-md-2  mb-1 pe-1 badge badge-danger"><?php echo $disp; ?>
                                                </li>
                                            <?php }
                                        } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <div class="container-fluid px-0">
                        <div class="row align-items-center g-2">

                            <div class="col-md-8">
                                <div id="output" class="mt-1 fw-semibold text-warning" style="font-size:10px;">
                                    <?php echo $st_entry_fld; ?>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <button class="btn btn-sm btn-primary w-100" onclick="showRightListAsText();">
                                    Update Sequence
                                </button>
                            </div>

                            <div class="col-md-2">
                                <button class="btn btn-secondary btn-sm w-100" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <div class="card mb-4 card-border-shadow-primary">
        <div class="card-header bg-primary text-white fw-bold p-3 ">
            <div class="row">
                <div class="col-auto">
                    Enrollment – Session Info
                </div>
                <div class="col text-end">
                    <button id="btnToggle" class="btn btn-outline-link text-white btn-sm p-0 px-2"
                        data-bs-toggle="modal" data-bs-target="#optionsModal"> Options <i
                            class="bi bi-chevron-expand fs-5 ms-3"></i> </button>
                </div>
            </div>


        </div>
        <div class="card-body row g-3 mt-2">

            <div class="col-md-10">
                <div class="row g-2">
                    <div class="col-md-2">
                        <label class="form-label">Medium</label>
                        <select id="medium" class="form-select form-select-sm">
                            <option value="">Select</option>
                            <option value="Bengali">Bengali</option>
                            <option value="English">English</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Version</label>
                        <select id="version" class="form-select form-select-sm">
                            <option value="">Select</option>
                            <option value="Bengali">Bengali</option>
                            <option value="English">English</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Slot</label>
                        <select id="slot" class="form-select form-select-sm">
                            <option value="">Select</option>
                            <?php
                            $sq = mysqli_query($conn, "SELECT slotname FROM slots WHERE sccode='$sccode'");
                            while ($r = mysqli_fetch_assoc($sq)) {
                                echo "<option value='{$r['slotname']}'>{$r['slotname']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Session</label>
                        <select id="session" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Class</label>
                        <select id="class" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Section</label>
                        <select id="section" class="form-select form-select-sm">
                            <option value="">Select</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Roll</label>
                        <input type="text" id="rollno" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Student ID</label>
                        <input type="text" id="stid" class="form-control form-control-sm" readonly>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button id="btnFetch" class="btn btn-primary  w-100 py-2 d-flex">

                            <i class="bi bi-search"></i>
                            <span class="text-end">View</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-2 text-end p-3">
                <div id="photobox p-3">
                    <img id="stphoto" src=""
                        style="height:130px; width:103px; border-radius:5px; border:1px solid gray;" />
                </div>

            </div>




        </div>
    </div>


    <!-- ============================================================= -->
    <!--  CARD 2: BASIC STUDENT INFO -->
    <!-- ============================================================= -->

    <div class="card mb-4">
        <div class="card-header  fw-bold">Basic Student Information</div>
        <div class="card-body row g-3">


            <div class="col-md-4">
                <label class="form-label">Unique ID</label>
                <input type="text" id="uniqueid" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label">RFID</label>
                <input type="text" id="rfid" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label">Mobile (Self)</label>
                <input type="text" id="mobileself" class="form-control form-control-sm">
            </div>


            <div class="col-md-2">
                <label class="form-label">Waiver (%)</label>
                <input type="number" id="waiver" class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="form-label">Quota</label>
                <input type="text" id="quota" class="form-control form-control-sm">
            </div>

            <div class="col-md-6">
                <label class="form-label">Student Name (English)</label>
                <input type="text" id="stnameeng" class="form-control form-control-sm">
            </div>

            <div class="col-md-6">
                <label class="form-label">Student Name (Bangla)</label>
                <input type="text" id="stnameben" class="form-control form-control-sm">
            </div>

        </div>
    </div>



    <!-- ============================================================= -->
    <!--  CARD 3: FATHER INFORMATION -->
    <!-- ============================================================= -->

    <div class="card mb-4">
        <div class="card-header fw-bold">Parents Information</div>
        <div class="card-body row g-3">

            <div class="col-md-6">
                <label class="form-label">Father Name (Eng)</label>
                <input type="text" id="fname" class="form-control form-control-sm">
            </div>

            <div class="col-md-6">
                <label class="form-label">Father Name (Bangla)</label>
                <input type="text" id="fnameben" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label">Profession</label>
                <input type="text" id="fprof" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label">Mobile</label>
                <input type="text" id="fmobile" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label">NID</label>
                <input type="text" id="fnid" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label">Is Alive?</label>
                <select id="falive" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="1">Alive</option>
                    <option value="0">Dead</option>
                </select>
            </div>




            <div class="col-md-6">
                <label class="form-label">Mother Name (Eng)</label>
                <input type="text" id="mname" class="form-control form-control-sm">
            </div>

            <div class="col-md-6">
                <label class="form-label">Mother Name (Bangla)</label>
                <input type="text" id="mnameben" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label">Profession</label>
                <input type="text" id="mprof" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label">Mobile</label>
                <input type="text" id="mmobile" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label">NID</label>
                <input type="text" id="mnid" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label">Is Alive?</label>
                <select id="malive" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="1">Alive</option>
                    <option value="0">Dead</option>
                </select>
            </div>

        </div>
    </div>



    <!-- ============================================================= -->
    <!--  CARD 5: PRESENT ADDRESS -->
    <!-- ============================================================= -->

    <div class="card mb-4">
        <div class="card-header fw-bold"> Address (Present)</div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label>Village</label>
                <input type="text" id="previll" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label>PO</label>
                <input type="text" id="prepo" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label>PS</label>
                <input type="text" id="preps" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label>District</label>
                <input type="text" id="predist" class="form-control form-control-sm">
            </div>
        </div>


        <div class="card-header fw-bold">
            Address (Permanent)
            <button class="btn btn-dark btn-sm float-end" onclick="sameAddress();">Same as Present</button>
        </div>
        <div class="card-body row g-3">

            <div class="col-md-3">
                <label>Village</label>
                <input type="text" id="pervill" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label>PO</label>
                <input type="text" id="perpo" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label>PS</label>
                <input type="text" id="perps" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label>District</label>
                <input type="text" id="perdist" class="form-control form-control-sm">
            </div>
        </div>
    </div>



    <!-- ============================================================= -->
    <!-- CARD 7: PERSONAL INFORMATION -->
    <!-- ============================================================= -->

    <div class="card mb-4">
        <div class="card-header fw-bold">Personal Information</div>
        <div class="card-body row g-3">

            <div class="col-md-3">
                <label>DOB</label>
                <input type="date" id="dob" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label>Religion</label>
                <select id="religion" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="Islam">Islam</option>
                    <option value="Hindu">Hindu</option>
                    <option value="Christian">Christian</option>
                    <option value="Buddist">Buddist</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>BRN</label>
                <input type="text" id="brn" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label>Gender</label>
                <select id="gender" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>Blood Group</label>
                <select id="bgroup" class="form-select form-select-sm">
                    <option value="">Select Blood Group</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>Height</label>
                <input type="text" id="height" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label>Weight</label>
                <input type="text" id="weight" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label>Disability</label>
                <select id="disables" class="form-select form-select-sm">
                    <option value="0">Disabled</option>
                    <option value="1">Not Disabled</option>
                </select>
            </div>

        </div>
    </div>



    <!-- ============================================================= -->
    <!-- CARD 8: GUARDIAN INFO -->
    <!-- ============================================================= -->

    <div class="card mb-4">
        <div class="card-header fw-bold">Guardian Information</div>
        <div class="card-body row g-3">

            <div class="col-md-6">
                <label>Name (Eng)</label>
                <input type="text" id="guarname" class="form-control form-control-sm">
            </div>

            <div class="col-md-6">
                <label>Name (Bangla)</label>
                <input type="text" id="guarnameben" class="form-control form-control-sm">
            </div>

            <div class="col-md-6">
                <label>Address</label>
                <input type="text" id="guaradd" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label>Relation</label>
                <input type="text" id="guarrelation" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label>Mobile</label>
                <input type="text" id="guarmobile" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label>Mobile 2</label>
                <input type="text" id="guarmobile2" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label>Email</label>
                <input type="text" id="guaremail" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label>Email 2</label>
                <input type="text" id="guaremail2" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label>NID</label>
                <input type="text" id="guarnid" class="form-control form-control-sm">
            </div>

        </div>
    </div>



    <!-- ============================================================= -->
    <!-- CARD 9: TC + PREVIOUS SCHOOL -->
    <!-- ============================================================= -->

    <div class="card mb-4">
        <div class="card-header  fw-bold">Transfer Certificate / Previous School</div>
        <div class="card-body row g-3">

            <div class="col-md-2">
                <label>TC No</label>
                <input type="text" id="tcno" class="form-control form-control-sm">
            </div>

            <div class="col-md-4">
                <label>Previous School</label>
                <input type="text" id="preins" class="form-control form-control-sm">
            </div>

            <div class="col-md-4">
                <label>School Address</label>
                <input type="text" id="preinsadd" class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label>Date of Admission</label>
                <input type="text" id="doa" class="form-control form-control-sm">
            </div>

        </div>
    </div>



    <!-- ============================================================= -->
    <!-- CARD 10: PHOTO UPLOAD + CROPPER -->
    <!-- ============================================================= -->

    <div class="card mb-5">
        <div class="card-header bg-dark text-white fw-bold">Photo Upload</div>
        <div class="card-body">

            <div class="row g-3 p-4">



                <div class="col-md-4">
                    <input type="file" id="photoFile" class="form-control form-control-sm">
                    <input type="hidden" id="photoid">
                </div>


                <div class="col-md-3">
                    <img id="photoPreview" src="" class="img-thumbnail"
                        style="height:40px;">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-dark btn-sm py-2  w-100" id="btnCrop">Crop & Upload</button>
                </div>

                <div class="col-md-3 text-end">
                    <button id="btnSave" class="btn btn-primary btn-sm w-100 fs-6">
                        <i class="bi bi-check-circle pe-5"></i> Save Enrollment
                    </button>
                </div>

            </div>

        </div>
    </div>



</div>

<?php require_once 'footer.php'; ?>

<!-- ============================================================= -->
<!-- JAVASCRIPT SECTION -->
<!-- ============================================================= -->



<script>
    const currentPage = "<?php echo basename($_SERVER['PHP_SELF'], '.php'); ?>";

    $('#entry_action').val('<?= $enroll_action ?>');


    function sameAddress() {
        // alert('trigger');
        $('#pervill').val($('#previll').val());
        $('#perpo').val($('#prepo').val());
        $('#perps').val($('#preps').val());
        $('#perdist').val($('#predist').val());
        $('#perdist').focus();
    }

</script>

<script>
    // 1️⃣ trigger function
    function triggerAltCtrlV() {
        const e = new KeyboardEvent('keydown', {
            key: 'v',
            code: 'KeyV',
            ctrlKey: true,
            altKey: true,
            bubbles: true
        });
        document.dispatchEvent(e);
    }

    // 2️⃣ focus / blur listeners
    document.getElementById('stnameben').addEventListener('focus', function () {
        console.log('ben focus');
        triggerAltCtrlV();
    });

    document.getElementById('stnameben').addEventListener('blur', function () {
        console.log('ben blur');
        triggerAltCtrlV();
    });

    // 3️⃣ shortcut catcher
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.altKey && e.key.toLowerCase() === 'v') {
            console.log('ALT+CTRL+V detected');

            // visible proof it worked
            document.body.style.background = '#ffe6e6';
        }
    });
</script>

<script>

    document.getElementById('entry_action').addEventListener('change', function () {
        const action = this.value;
        console.log(action);
        setCookie('enroll_save', action, 1);
        window.location.reload();
    });

    $("#stid").on("change keyup click", function () {
        let st = $(this).val();
        if (!st) return;
        $.post('student/get-student-photo.php', { id: st }, function (imgPath) {
            $("#stphoto").attr("src", imgPath);
            $("#photoPreview").attr("src", imgPath);
        });
    });
    // ===========================
    // UNIVERSAL SAVE/LOAD (localStorage)
    // ===========================
    function saveValue(id) {
        const key = currentPage + "_" + id;
        const val = $("#" + id).val();
        if (val !== null && val !== "") localStorage.setItem(key, val);
    }

    function loadValue(id) {
        const key = currentPage + "_" + id;
        return localStorage.getItem(key);
    }

    // Save values on change/click
    $("#medium, #version, #slot, #session, #class, #section, #rollno").on("change click", function () {
        saveValue($(this).attr("id"));
    });

    // ===========================
    // UNIVERSAL DROPDOWN LOADER
    // ===========================
    function loadOptions(url, target, callback) {
        $("#" + target).html('<option>Loading...</option>');
        $.post(url, function (res) {
            $("#" + target).html('<option value="">Select</option>');
            $.each(res, function (i, item) {
                $("#" + target).append(`<option value="${item.value}">${item.label}</option>`);
            });
            if (callback) callback();
        }, 'json');
    }

    // ===========================
    // CHAINED DROPDOWN LOGIC
    // slot → session → class → section → rollno
    // ===========================

    function triggerLoadDropdowns() {
        const savedSession = loadValue("session");
        const savedClass = loadValue("class");
        const savedSection = loadValue("section");
        const savedRoll = loadValue("rollno");

        const slotVal = $("#slot").val();
        if (!slotVal) return;

        loadOptions("components/get-session.php?slot=" + slotVal, "session", function () {
            if (savedSession) $("#session").val(savedSession).trigger("change");
        });
    }

    $("#slot").on("change", function () {
        console.log('Trigger....................');
        const slot = $(this).val();
        if (!slot) return;

        loadOptions("components/get-session.php?slot=" + slot, "session", function () {
            const savedSession = loadValue("session");
            if (savedSession) $("#session").val(savedSession).trigger("change");
        });
    });


    function changeSection() {
        const savedRoll = loadValue("rollno");
        console.log('savedRoll:', savedRoll);
        if (savedRoll) $("#rollno").val(savedRoll).trigger("change");
        console.log('Section changed, trigger fetch');
        $("#btnFetch").trigger("click");
    }

    function triggerClassChange() {
        console.log('Class changed, load sections');
        const slot = $("#slot").val();
        const session = $("#session").val();
        const className = $("#class").val();
        if (!className) return;

        loadOptions(`components/get-section.php?slot=${slot}&session=${session}&class=${className}`, "section", function () {
            const savedSection = loadValue("section");
            console.log('savedSection:', savedSection);
            if (savedSection) {
                console.log('Trigger section change');
                $("#section").val(savedSection);
                changeSection();
            }

        });
    }



    $("#session").on("change", function () {
        const slot = $("#slot").val();
        const session = $(this).val();
        if (!session) return;

        loadOptions(`components/get-class.php?slot=${slot}&session=${session}`, "class", function () {
            const savedClass = loadValue("class");
            if (savedClass) {
                console.log('Trigger class change');
                $("#class").val(savedClass);
                console.log('class name set to:', savedClass);
                triggerClassChange();
            }
        });
    });



    $("#class").on("change", function () {
        triggerClassChange();
    });

    $("#section").on("change", function () {
        changeSection();
    });




    function loadSection() {
        const slot = $("#slot").val();
        const session = $("#session").val();
        const className = $("#class").val();

        if (!className) {
            console.log("Class empty, skip section load");
            return;
        }

        console.log("Calling get-section.php...");

        loadOptions(
            `components/get-section.php?slot=${slot}&session=${session}&class=${className}`,
            "section",
            function () {
                console.log("Section loaded");

                const savedSection = loadValue("section");
                if (savedSection) {
                    $("#section").val(savedSection).trigger("change");
                }
            }
        );
    }

    // $("#class").on("change", function () {
    //     loadSection();
    // });




    // ===========================
    // AUTO GENERATE STID FOR NEW STUDENT
    // ===========================
    function generateStid() {
        $.post("student/get-new-stid.php", {}, function (data) {
            showToast('info', data, 'S.Id');
            $("#stid").val(data);
        });
    }

    // ===========================
    // FETCH STUDENT RECORD
    // ===========================
    $("#btnFetch").on("click", function () {
        const slot = $("#slot").val();
        const session = $("#session").val();
        const className = $("#class").val();
        const section = $("#section").val();
        const roll = $("#rollno").val();
        const medium = $("#medium").val();
        const version = $("#version").val();

        if (!slot || !session || !className || !section || !roll) {
            showToast("danger", "Missing Required Fields", "Error");
            return;
        }

        $("#btnFetch").html("Loading...");

        $.post("student/fetch-student.php", {
            slot, session, class: className, section, rollno: roll, medium, version
        }, function (res) {
            $("#btnFetch").html(' <i class="bi bi-search"></i> View ');

            if (res.status === "found" && res.new == 0) {
                // Fill Form
                Object.keys(res.data).forEach(id => {
                    $("#" + id).val(res.data[id]);
                });
                showToast('info', 'Student Found ' + res.data['stnameeng'], 'Found');
                // console.log(JSON.stringify(res.data));
                console.log("segs" + JSON.stringify(res.data['photo_path']));

                let img = $("#stphoto");
                img.attr("src", "");
                img.attr("src", res.data['photo_path'] );
                img.attr("src", res.data['photo_path'] );
                $("#stid").trigger("click");

            } else {
                // New student
                generateStid();
                // showToast('info', 'Generate New ID ', 'New Student');
                // Fields to exclude from clearing
                const excludeFields = ["stid", "class", "section", "slot", "rollno", "medium", "version", "session"];
                $("input, select").each(function () {
                    if (!excludeFields.includes($(this).attr("id"))) {
                        $(this).val("");
                    }
                });



                $("#photoPreview").attr("src", res.data['photo_path']);



                showToast("warning", "New profile. Fill and Save.", "Student Not Found");
            }
            if (activeFields.length > 0) {
                const first = document.getElementById(activeFields[0]);
                if (first) first.focus();
            }
        }, 'json');

        $("#stid").trigger("click");

    });

    // ===========================
    // PHOTO UPLOAD WITH CROPPER
    // ===========================
    let cropper = null;

    $("#photoFile").on("change", function (e) {

        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (ev) {
            $("#photoPreview").attr("src", ev.target.result);

            if (cropper) cropper.destroy();

            cropper = new Cropper(document.getElementById("photoPreview"), {
                aspectRatio: 3 / 4,   // স্কুল ID পাসপোর্ট সাইজ
                viewMode: 1,
                autoCropArea: 1
            });
        };
        reader.readAsDataURL(file);
    });



    $("#btnCrop").on("click", function () {

        if (!cropper) {
            showToast("danger", "Please select an image first", "No Image");
            return;
        }

        cropper.getCroppedCanvas({
            width: 300,
            height: 400
        }).toBlob(function (blob) {

            let fd = new FormData();
            fd.append("file", blob);        // cropped blob
            fd.append("stid", $("#stid").val());

            $.ajax({
                url: "student/image-save.php",
                type: "POST",
                data: fd,
                contentType: false,
                processData: false,
                success: function (resp) {
                    $("#photoid").val(resp);
                    $("#photoPreview").attr("src", resp);
                    cropper.destroy();
                    cropper = null;

                    showToast("success", "Photo Uploaded", "Done");
                }
            });

        }, "image/jpeg");
    });


    // ===========================
    // SAVE ENROLLMENT
    // ===========================
    $("#btnSave").on("click", function () {
        const fd = {};
        $("input, select").each(function () {
            fd[$(this).attr("id")] = $(this).val();
        });

        $.post("student/save-enrollment.php", fd, function (res) {
            if (res === "SUCCESS") {
                showToast("success", "Saved Successfully", "Done");
                const enroll_action = "<?php echo $enroll_action; ?>";
                // alert(enroll_action);

                Swal.fire({
                    icon: 'success',
                    title: 'Profile Saved Successfully',
                    timer: 1000, // 1 second
                    timerProgressBar: true,
                    showConfirmButton: false
                });

                if (enroll_action == "next") {

                    let nextRoll = parseInt($("#rollno").val()) + 1;

                    // next roll store for after reload
                    sessionStorage.setItem("next_roll", nextRoll);
                    localStorage.setItem("enroll-students_rollno", nextRoll);

                    // reload page
                    location.reload();
                }
                else if (enroll_action === "back") {
                    window.history.back();
                } else {
                    $("input, select").each(function () {
                        const excludeFields = ["class", "section", "slot", "medium", "version", "session"];

                        if (!excludeFields.includes($(this).attr("id"))) {
                            $(this).val("");
                        }
                    });
                }


            } else {
                alert(res);
            }
        });
    });

    // ===========================
    // RESTORE SAVED LOCALSTORAGE VALUES
    // ===========================
    $(document).ready(function () {
        const savedMedium = loadValue("medium");
        const savedVersion = loadValue("version");
        const savedSlot = loadValue("slot");

        if (savedMedium) $("#medium").val(savedMedium).trigger("change");
        if (savedVersion) $("#version").val(savedVersion).trigger("change");
        if (savedSlot) $("#slot").val(savedSlot).trigger("change");
    });
</script>


<script>

    const search = <?php echo json_encode($display_name); ?>;
    const replace = <?php echo json_encode($field_name); ?>;

    // Safe replace (escape regex)
    function escapeRegExp(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
    }

    // Convert display_name → field_name
    function strReplaceArray(search, replace, subject) {
        let result = subject;

        search.forEach((item, index) => {
            const regex = new RegExp(escapeRegExp(item), "g");
            result = result.replace(regex, replace[index]);
        });

        return result;
    }

    // Original comma/pipe/dot separated string
    let raw = document.getElementById("output").innerHTML.trim();

    // Convert display values → input IDs
    let converted = strReplaceArray(search, replace, raw);

    // Convert to array (clean)
    const activeFields = converted
        .split(".")
        .map(v => v.trim())
        .filter(v => v !== "");

    // Debug
    console.log("Converted =", converted);
    console.log("Active Fields =", activeFields);

    // Apply ENTER navigation
    activeFields.forEach((id, index) => {
        const input = document.getElementById(id);
        if (!input) return;

        input.addEventListener("keydown", e => {
            if (e.key === "Enter") {
                e.preventDefault();

                const nextId = activeFields[index + 1];
                const nextInput = document.getElementById(nextId);

                if (nextInput) {
                    nextInput.focus();
                } else {
                    console.log("submit");
                    $("#btnSave").trigger("click");
                }
            }
        });
    });

    // Auto focus first field
    if (activeFields.length > 0) {
        const first = document.getElementById(activeFields[0]);
        if (first) first.focus();
    }

</script>




<script>
    const leftList = document.getElementById("leftList");
    const rightList = document.getElementById("rightList");

    // বাম থেকে ডানে নেয়ার জন্য
    leftList.addEventListener("click", function (e) {
        if (e.target.tagName === "LI") {
            let item = e.target;
            leftList.removeChild(item);
            rightList.appendChild(item);
        }
    });

    // ডান থেকে বামে ফেরত নেয়ার জন্য
    rightList.addEventListener("click", function (e) {
        if (e.target.tagName === "LI") {
            let item = e.target;
            rightList.removeChild(item);
            leftList.appendChild(item);
        }
    });

    function showRightListAsText() {
        let items = Array.from(rightList.children).map(li => li.textContent);
        let text = items.join(".");
        document.getElementById("output").textContent = text || "(Empty)";

        let infor = "email=<?php echo $usr; ?>&data=" + text;
        // alert(infor);
        $("#output").html("");
        $.ajax({
            type: "POST",
            url: "student/update-st-entry-fld.php",
            data: infor,
            cache: false,
            beforeSend: function () {
                $('#output').html('<span class="mif-spinner3 mif-ani-pulse"></span>');
            },
            success: function (html) {
                setCookie("enroll_save", "next");
                $("#output").html(html);
                window.location.reload();
            }
        });
    }
</script>

<script>

    // ===========================
    // AUTO LOAD NEXT ROLL AFTER RELOAD
    // ===========================
    let autoRoll = sessionStorage.getItem("auto_next_roll");

    if (autoRoll) {

        $("#rollno").val(autoRoll);
        sessionStorage.removeItem("auto_next_roll");

        // wait dropdown restore then fetch
        setTimeout(function () {
            $("#btnFetch").trigger("click");
        }, 500);
    }

    $roll = sessionStorage.getItem("next_roll") || 0;
    $('#rollno').val($roll);
    setTimeout(function () {
        $("#btnFetch").trigger("click");
        sessionStorage.setItem("next_roll", '');
    }, 500);





</script>


<script>
    document.addEventListener("DOMContentLoaded", async () => {

        const params = new URLSearchParams(window.location.search);
        const stid = params.get("stid");
        const sy = params.get("sy");
        if (!stid) return;

        const res = await fetch("student/fetch-single.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ stid, sy })
        });

        const data = await res.json();

        // basic
        $("#version").val(data.version);
        $("#medium").val(data.medium);
        $("#rollno").val(data.rollno);
        $("#stid").val(data.stid);

        // 🔥 STEP 1: SLOT → SESSION
        $("#slot").val(data.slot);

        loadOptions(
            "components/get-session.php?slot=" + data.slot,
            "session",
            function () {

                $("#session").val(data.sessionyear);

                // 🔥 STEP 2: SESSION → CLASS
                loadOptions(
                    `components/get-class.php?slot=${data.slot}&session=${data.sessionyear}`,
                    "class",
                    function () {

                        $("#class").val(data.classname);

                        // 🔥 STEP 3: CLASS → SECTION
                        loadOptions(
                            `components/get-section.php?slot=${data.slot}&session=${data.sessionyear}&class=${data.classname}`,
                            "section",
                            function () {

                                $("#section").val(data.sectionname);

                                // FINAL
                                $("#btnFetch").trigger("click");

                            }
                        );

                    }
                );

            }
        );

    });
</script>


<script>
    function toTitleCase(str) {
        return str.toLowerCase().replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
    }

    document.querySelectorAll('input[type="text"]').forEach(input => {

        // 👉 focus হলে পুরো text select
        input.addEventListener('focus', function () {
            this.select();
        });

        // 👉 blur হলে Title Case
        input.addEventListener('blur', function () {
            if (this.value.trim() !== '') {
                this.value = toTitleCase(this.value);
            }
        });

    });

    $("#rollno").on("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            $("#btnFetch").trigger("click");
        }
    });
</script>



</body>

</html>