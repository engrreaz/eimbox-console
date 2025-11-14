<?php
session_start();
include_once('core/config.php');
include_once('core/db.php');
include_once('core/core-val.php');
include_once('core/global_values.php');

// require_once 'core/init.php';
session_destroy();
setcookie('remember_me', '', time() - 3600, '/', '', true, true);


$sccode = $_COOKIE['sccode'];
if ($sccode == '') {
    header("Location: admission-login.php");
    exit;
}

$reg = $_SESSION['student_reg'] ?? null;
// $sccode = $_SESSION['scode'] ?? null;
if ($reg) {
    header("Location: admission-dashboard.php");
    exit;
}

include_once('header-plain.php');

include_once('actions/get-sc-data.php');

?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />

<style>
    /* preview box size fixed to required 150x190 */
    .photo-preview {
        width: 150px;
        height: 190px;
        border: 1px solid #ccc;
        overflow: hidden;
        display: inline-block;
        background: #f8f9fa;
        border-radius: 5px;
    }

    .cropper-container img {
        max-width: 100%;
    }

    table,
    td {
        border: 0;
    }
</style>






<div class="row">
    <div class="col-md-4 " style="
            background-image: url('assets/images/core/regd-form.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 98vh;
            border-radius: 5px;
            text-align:center;
            ">

        <button class="btn btn-white top-50 text-center " style="position:relative;  " onclick="back();">
            Back to Login</button>
    </div>


    <div class="col-md-8">

        <?php include_once('actions/sc-header.php'); ?>


        <div class="row ps-3 pe-3 mb-1 ">
            <div class="col-12 text-center  alert alert-primary m-0">
                <h3 class="m-0 p-0 fw-bold text-primary "> Admission Form </h3>
                <div class="m-0 p-0 text-danger">
                    (All fields must be completed. | সব ফিল্ড সম্পূর্ণভাবে পূরণ করতে হবে।)
                    <br>
                    শুধুমাত্র বাংলায় শিক্ষার্থীর নাম বাংলায় পূরণ করবে। বাকী সকল তথ্য ইংরেজীতে হবে।
                </div>
            </div>
        </div>

        <form id="myForm" action="core/register_process.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="sccode" value="<?= $sccode; ?>">
            <!-- Card 1: Basic Information -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-info fw-bold">Student's Information / শিক্ষার্থীর তথ্য</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- left: inputs (6 cols) -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name (English) / নাম (ইংরেজীতে) <span
                                        class="text-danger">*</span></label>
                                <input name="stnameeng" class="form-control" placeholder="Name of Student in English">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name (Bangla) / নাম (বাংলায়) <span
                                        class="text-danger">*</span></label>
                                <input name="stnameben" class="form-control" placeholder="বাংলায় শিক্ষার্থীর নাম">
                            </div>
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Religion / ধর্ম <span
                                                class="text-danger">*</span></label>
                                        <select name="religion" class="form-select">
                                            <option value=""> -- Choose One --</option>
                                            <option value="Islam">Islam</option>
                                            <option value="Hindu">Hindu</option>
                                            <option value="Christian">Christian</option>
                                            <option value="Buddist">Buddist</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Gender / লিঙ্গ <span
                                                class="text-danger">*</span></label>
                                        <select name="gender" class="form-select">
                                            <option value=""> -- Choose One --</option>
                                            <option value="Boy">Boy</option>
                                            <option value="Girl">Girl</option>
                                        </select>
                                    </div>
                                </div>



                            </div>
                        </div>
                        <!-- right: photo upload & crop (6 cols) -->
                        <!-- ✳️ Student Photo Section -->
                        <div class="col-md-6">
                            <label class="form-label">Student Photo (150x190) / শিক্ষার্থীর ছবি <span
                                    class="text-danger">*</span></label>
                            <div class="mb-2">
                                <input id="photoInput" type="file" accept="image/*" class="form-control">
                            </div>

                            <div id="crop-area" style="display:none;">
                                <div style="max-width:320px;">
                                    <img id="image-to-crop" src="" style="max-width:100%; display:block;">
                                </div>

                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="zoom-in">Zoom
                                        +</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="zoom-out">Zoom
                                        -</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        id="reset-crop">Reset</button>
                                    <button type="button" class="btn btn-sm btn-primary" id="crop-save">Save
                                        Photo</button>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="photo-preview" id="photoPreview">
                                    <img id="previewImg" src="" alt="Preview"
                                        style="width:150px; height:190px; object-fit:cover; display:none;">
                                </div>
                            </div>

                        </div>



                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <!-- <h5 class="mb-0 text-info fw-bold">Address / ঠিকানা </h5> -->
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">

                            <label class="form-label">Blood Group/ রক্তের গ্রুপ</label>
                            <select name="bgroup" class="form-select">
                                <option value=""> -- Choose One --</option>
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
                            <label for="ps" class="form-label">Date of Birth / জন্ম তারিখ <span
                                    class="text-danger">*</span></label>
                            <input type="date" id="dob" name="dob" class="form-control" value=""
                                placeholder="Date of Birth">

                        </div>
                        <div class="col-md-6">
                            <label for="po" class="form-label">Birth Registration Number / জন্ম নিবন্ধন
                                নম্বর</label>
                            <input name="brnno" class="form-control" placeholder="Birth Registration Number">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 text-info fw-bold">Parents Information / পিতা/মাতার তথ্যাবলী </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">

                            <label class="form-label">Father's Name / পিতার নাম <span
                                    class="text-danger">*</span></label>
                            <input name="fname" class="form-control" placeholder="Father's Name">

                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Alive / জীবিত <span class="text-danger">*</span></label>
                            <select name="falive" class="form-select">
                                <option value=""> -- Choose One --</option>
                                <option value="Yes">Alive / জীবিত</option>
                                <option value="No">Died / মৃত</option>
                            </select>

                        </div>
                        <div class="col-md-4">
                            <label for="fmobile" class="form-label">Mobile Number / মোবাইল নম্বর
                                নম্বর</label>
                            <input name="fmobile" class="form-control" placeholder="Father's Mobile Number">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">

                            <label class="form-label">Mother's Name / মাতার নাম <span
                                    class="text-danger">*</span></label>
                            <input name="mname" class="form-control" placeholder="Mother's Name">

                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Alive / জীবিত <span class="text-danger">*</span></label>
                            <select name="malive" class="form-select">
                                <option value=""> -- Choose One --</option>
                                <option value="Yes">Alive / জীবিত</option>
                                <option value="No">Died / মৃত</option>
                            </select>

                        </div>
                        <div class="col-md-4">
                            <label for="mmobile" class="form-label">Mobile Number / মোবাইল নম্বর
                                নম্বর</label>
                            <input name="mmobile" class="form-control" placeholder="Mother's Mobile Number">
                        </div>
                    </div>
                </div>
            </div>


            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 text-info fw-bold"> Guardian's Information / অভিভাবকের তথ্যাবলী </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label">Guardian / অভিভাবক <span class="text-danger">*</span></label>
                            <select id="guar" name="guar" class="form-select">
                                <option value=""> -- Choose One --</option>
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Other">Other</option>
                            </select>

                        </div>

                        <div class="col-md-5">
                            <label for="po" class="form-label">Guardian name / অভিভাবকের নাম
                                <span class="text-danger">*</span> </label>
                            <input name="guarname" class="form-control" placeholder="Guardian's Name">
                        </div>

                        <div class="col-md-4">

                            <label class="form-label">Guardian Mobile Number / মোবাইল নম্বর <span
                                    class="text-danger">*</span></label>
                            <input id="mnumber" name="mnumber" class="form-control" placeholder="Mobile Number">

                        </div>


                    </div>
                </div>
            </div>


            <!-- Card 2: Address -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 text-info fw-bold">Address / ঠিকানা </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="dist" class="form-label">District / জেলা <span
                                    class="text-danger">*</span></label>
                            <select id="dist" name="dist" class="form-control">
                                <option value="">-- Select District --</option>
                                <!-- JS will populate -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="ps" class="form-label">Upzila / উপজেলা <span
                                    class="text-danger">*</span></label>
                            <select id="ps" name="ps" class="form-control" disabled>
                                <option value="">-- Select PS / Upazila --</option>
                                <!-- JS will populate based on district -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="po" class="form-label">Post Office / ডাকঘর <span
                                    class="text-danger">*</span></label>
                            <input name="po" class="form-control" placeholder="Post Office">
                        </div>
                        <div class="col-md-3">
                            <label for="village" class="form-label">Village | Area / গ্রাম | মহল্লা <span
                                    class="text-danger">*</span></label>
                            <input name="village" class="form-control" placeholder="Village">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Previous Institute -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 text-info fw-bold">Previous Institute / পূর্বের প্রতিষ্ঠান</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="testno" class="form-label">Testimonial | TC. No. / প্রশংসা পত্র | টিসি
                                নম্বর </label>
                            <input name="testno" class="form-control" placeholder="Testimonial / TC Number">
                        </div>
                        <div class="col-md-3">
                            <label for="insdist" class="form-label"> Institute District /<br> প্রতিষ্ঠানের জেলা
                            </label>
                            <select id="insdist" name="insdist" class="form-control">
                                <option value="">-- Select Institute District --</option>
                                <!-- JS populate -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="insps" class="form-label"> Institute Upazila /<br> প্রতিষ্ঠানের উপজেলা
                            </label>
                            <select id="insps" name="insps" class="form-control" disabled>
                                <option value="">-- Select Institute PS / Upazila --</option>
                                <!-- JS populate based on insdist -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="inspo" class="form-label"> Institute Post Office /<br> প্রতিষ্ঠানের ডাকঘর
                            </label>
                            <input name="inspo" class="form-control" placeholder="Institute Post"
                                aria-placeholder="Post Office">
                        </div>
                        <div class="col-12 mt-4">
                            <label for="insname" class="form-label"> Institute Name / প্রতিষ্ঠানের নাম <span
                                    class="text-danger">*</span></label>
                            <input name="insname" class="form-control" placeholder="Institute Name">
                        </div>
                    </div>
                </div>
            </div>

            <!-- submit/reset buttons -->
            <div class="d-flex justify-content-start gap-2 mb-5">
                <button type="submit" id="submitBtn" class="btn btn-primary">Submit</button>
                <button type="reset" id="resetBtn" class="btn btn-outline-danger">Reset</button>

            </div>
        </form>
    </div>
</div>






<?php
include_once('footer-plain.php');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>


<!-- 

<script>
    $(function () {
        const districtsUrl = 'assets/json/districts.json';
        const upazilasUrl = 'assets/json/upazilas.json';
        var preselectedDistrict = window.preselectedDistrict || '';
        var preselectedPS = window.preselectedPS || '';

        $.getJSON(districtsUrl, function (data) {
            let districts = data;
            if (Array.isArray(data) && data.length && data[0].type) {
                const tableObj = data.find(x => x.type === 'table' && x.name === 'districts');
                if (tableObj && Array.isArray(tableObj.data)) districts = tableObj.data;
            }
            districts.forEach(function (d) {
                const label = (d.bn_name && d.bn_name.trim() !== '') ? d.bn_name + ' (' + d.name + ')' : d.name;
                $('#dist').append($('<option>', { value: d.id, text: label, 'data-name': d.name }));
            });
            if (preselectedDistrict) $('#dist').val(preselectedDistrict).trigger('change');
        });

        $('#dist').on('change', function () {
            const did = $(this).val();
            $('#ps').prop('disabled', true).html('<option>Loading...</option>');
            if (!did) { $('#ps').prop('disabled', true).html('<option value="">-- Select PS / Upazila --</option>'); return; }

            $.getJSON(upazilasUrl, function (data) {
                let upazilas = data;
                if (Array.isArray(data) && data.length && data[0].type) {
                    const tableObj = data.find(x => x.type === 'table' && x.name === 'upazilas');
                    if (tableObj && Array.isArray(tableObj.data)) upazilas = tableObj.data;
                }
                const filtered = upazilas.filter(u => String(u.district_id) === String(did));
                if (!filtered.length) { $('#ps').prop('disabled', true).html('<option value="">No PS found</option>'); return; }
                let html = '<option value="">-- Select PS / Upazila --</option>';
                filtered.forEach(function (u) {
                    const label = (u.bn_name && u.bn_name.trim() !== '') ? u.bn_name + ' (' + u.name + ')' : u.name;
                    html += `<option value="${u.id}" data-name="${u.name}">${label}</option>`;
                });
                $('#ps').prop('disabled', false).html(html);
                if (preselectedPS) $('#ps').val(preselectedPS);
            }).fail(function () { $('#ps').prop('disabled', true).html('<option value="">Failed to load</option>'); });
        });

        // copy selected names to hidden inputs before submit (optional but recommended)
       

    });
</script>



<script>
    $(function () {

        const districtsUrl = 'assets/json/districts.json';
        const upazilasUrl = 'assets/json/upazilas.json';

        // যদি edit/ preselected value থাকে, সার্ভার-সাইড থেকে echo করে JS ভ্যারিয়েবল রাখো
        var preselectedInsDistrict = window.preselectedInsDistrict || '';
        var preselectedInsPS = window.preselectedInsPS || '';

        // Load Institute Districts
        $.getJSON(districtsUrl, function (data) {
            let districts = data;
            if (Array.isArray(data) && data.length && data[0].type) {
                const tableObj = data.find(x => x.type === 'table' && x.name === 'districts');
                if (tableObj && Array.isArray(tableObj.data)) districts = tableObj.data;
            }

            districts.forEach(function (d) {
                const label = (d.bn_name && d.bn_name.trim() !== '') ? d.bn_name + ' (' + d.name + ')' : d.name;
                $('#insdist').append($('<option>', { value: d.id, text: label, 'data-name': d.name }));
            });

            if (preselectedInsDistrict) $('#insdist').val(preselectedInsDistrict).trigger('change');
        });

        // On Institute District change -> populate Institute PS
        $('#insdist').on('change', function () {
            const did = $(this).val();
            $('#insps').prop('disabled', true).html('<option>Loading...</option>');

            if (!did) {
                $('#insps').prop('disabled', true).html('<option value="">-- Select Institute PS / Upazila --</option>');
                return;
            }

            $.getJSON(upazilasUrl, function (data) {
                let upazilas = data;
                if (Array.isArray(data) && data.length && data[0].type) {
                    const tableObj = data.find(x => x.type === 'table' && x.name === 'upazilas');
                    if (tableObj && Array.isArray(tableObj.data)) upazilas = tableObj.data;
                }

                const filtered = upazilas.filter(u => String(u.district_id) === String(did));
                if (!filtered.length) {
                    $('#insps').prop('disabled', true).html('<option value="">No PS found</option>');
                    return;
                }

                let html = '<option value="">-- Select Institute PS / Upazila --</option>';
                filtered.forEach(function (u) {
                    const label = (u.bn_name && u.bn_name.trim() !== '') ? u.bn_name + ' (' + u.name + ')' : u.name;
                    html += `<option value="${u.id}" data-name="${u.name}">${label}</option>`;
                });

                $('#insps').prop('disabled', false).html(html);
                if (preselectedInsPS) $('#insps').val(preselectedInsPS);

            }).fail(function () {
                $('#insps').prop('disabled', true).html('<option value="">Failed to load</option>');
            });
        });

        // copy selected names to hidden inputs before submit (optional)
        /*
         $('#myForm').on('submit', function () {
             const insDistName = $('#insdist option:selected').data('name') || '';
             const insPSName = $('#insps option:selected').data('name') || '';
             if (!$('#insdist_name').length) $(this).append('<input type="hidden" name="insdist_name" id="insdist_name">');
             if (!$('#insps_name').length) $(this).append('<input type="hidden" name="insps_name" id="insps_name">');
             $('#insdist_name').val(insDistName);
             $('#insps_name').val(insPSName);
             return true;
         });
         */

    });
</script>
 -->

<script>
    $(function () {




        const districtsUrl = 'assets/json/districts.json';
        const upazilasUrl = 'assets/json/upazilas.json';

        // --- Student District/PS ---
        var preselectedDistrict = window.preselectedDistrict || '';
        var preselectedPS = window.preselectedPS || '';

        // Load Districts
        $.getJSON(districtsUrl, function (data) {
            let districts = [];
            const tableObj = data.find(x => x.type === 'table' && x.name === 'districts');
            if (tableObj && Array.isArray(tableObj.data)) districts = tableObj.data;

            // Sort by English name
            districts.sort((a, b) => a.name.localeCompare(b.name));

            districts.forEach(d => {
                const label = `(${d.bn_name}) ${d.name}`;
                $('#dist').append($('<option>', { value: d.id, text: label, 'data-name': d.name, 'data-address': d.name }));
            });

            if (preselectedDistrict) $('#dist').val(preselectedDistrict).trigger('change');
        });




        $('#dist').on('change', function () {
            const districtId = $(this).val();
            $('#ps').prop('disabled', true).html('<option>Loading...</option>');

            if (!districtId) {
                $('#ps').prop('disabled', true).html('<option value="">-- Select PS / Upazila --</option>');
                return;
            }

            $.getJSON(upazilasUrl, function (data) {
                let upazilas = [];
                const tableObj = data.find(x => x.type === 'table' && x.name === 'upazilas');
                if (tableObj && Array.isArray(tableObj.data)) upazilas = tableObj.data;

                // Filter by district_id
                const filtered = upazilas.filter(u => String(u.district_id) === String(districtId));

                // Sort by English name
                filtered.sort((a, b) => a.name.localeCompare(b.name));

                let html = '<option value="">-- Select PS / Upazila --</option>';
                filtered.forEach(u => {
                    const label = `(${u.bn_name}) ${u.name}`;
                    html += `<option value="${u.name}" data-name="${u.name}">${label}</option>`;
                });

                $('#ps').prop('disabled', false).html(html);
                if (preselectedPS) $('#ps').val(preselectedPS);
            }).fail(function () {
                $('#ps').prop('disabled', true).html('<option value="">Failed to load</option>');
            });
        });


        // --- Institute District/PS ---
        var preselectedInsDistrict = window.preselectedInsDistrict || '';
        var preselectedInsPS = window.preselectedInsPS || '';

        $.getJSON(districtsUrl, function (data) {
            let districts = [];
            const tableObj = data.find(x => x.type === 'table' && x.name === 'districts');
            if (tableObj && Array.isArray(tableObj.data)) districts = tableObj.data;

            districts.sort((a, b) => a.name.localeCompare(b.name));

            districts.forEach(d => {
                const label = ` ${d.name}(${d.bn_name})`;
                $('#insdist').append($('<option>', { value: d.id, text: label, 'data-name': d.name, 'data-ins': d.name }));
            });

            if (preselectedInsDistrict) $('#insdist').val(preselectedInsDistrict).trigger('change');
        });

        $('#insdist').on('change', function () {
            const districtId = $(this).val();
            $('#insps').prop('disabled', true).html('<option>Loading...</option>');

            if (!districtId) {
                $('#insps').prop('disabled', true).html('<option value="">-- Select Institute PS / Upazila --</option>');
                return;
            }

            $.getJSON(upazilasUrl, function (data) {
                let upazilas = [];
                const tableObj = data.find(x => x.type === 'table' && x.name === 'upazilas');
                if (tableObj && Array.isArray(tableObj.data)) upazilas = tableObj.data;

                const filtered = upazilas.filter(u => String(u.district_id) === String(districtId));
                filtered.sort((a, b) => a.name.localeCompare(b.name));

                let html = '<option value="">-- Select Institute PS / Upazila --</option>';
                filtered.forEach(u => {
                    const label = `(${u.bn_name}) ${u.name}`;
                    html += `<option value="${u.name}" data-name="${u.name}">${label}</option>`;
                });

                $('#insps').prop('disabled', false).html(html);
                if (preselectedInsPS) $('#insps').val(preselectedInsPS);
            }).fail(function () {
                $('#insps').prop('disabled', true).html('<option value="">Failed to load</option>');
            });
        });



        $('#guar').on('change', function () {
            const guar = $(this).val();
            const getVal = name => ($(`[name="${name}"]`).length ? $(`[name="${name}"]`).val() : '');
            const setVal = (name, value) => {
                const el = $(`[name="${name}"]`);
                if (el.length) el.val(value);
            };

            if (guar === 'Father') {
                setVal('guarname', getVal('fname'));
                setVal('mnumber', getVal('fmobile')); // তুমি যদি mnumber বদলে guardian-mobile রাখতে চাও, এখানে ঠিক করে নাও
            } else if (guar === 'Mother') {
                setVal('guarname', getVal('mname'));
                setVal('mnumber', getVal('mmobile'));
            } else {
                // অন্য কিছু হলে ক্লিয়ার করে দিতে চাইলে
                setVal('guarname', '');
                setVal('mnumber', '');
            }
        });

    });


</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    function validateFormFields() {
        const fields = [
            { name: 'stnameeng', label: 'Student Name (English)' },
            { name: 'stnameben', label: 'Student Name (Bangla)' },
            { name: 'fname', label: "Father's Name" },
            { name: 'mname', label: "Mother's Name" },
            { name: 'mnumber', label: 'Mobile Number' },
            { name: 'dob', label: 'Date of Birth' },
            { name: 'brnno', label: 'Birth Reg. No.' },
            { name: 'dist', label: 'District' },
            { name: 'ps', label: 'Upazila / Thana' },
            { name: 'po', label: 'Post Office' },
            { name: 'village', label: 'Village' },
            { name: 'testno', label: 'Test Number' },
            { name: 'insdist', label: 'Institute District' },
            { name: 'insps', label: 'Institute Upazila' },
            { name: 'inspo', label: 'Institute Post Office' },
            { name: 'insname', label: 'Institute Name' },

            { name: 'religion', label: 'Religion' },
            { name: 'gender', label: 'Gender' },

            { name: 'falive', label: 'Father Alive / Died' },
            { name: 'malive', label: 'Mother Alive / Died' },
            { name: 'guar', label: 'Guardian' },
            { name: 'guarname', label: 'Guardian Name' }
        ]

        const ignoreField = ['bgroup', 'brnno', 'fmobile', 'mmobile', 'testno', 'insdist', 'insps', 'inspo']

        let hasEmpty = false;

        fields.forEach(f => {
            if (ignoreField.includes(f.name)) return;

            const value = document.getElementsByName(f.name)[0]?.value.trim() || '';
            if (!value) {
                hasEmpty = true;
                showToast("danger", `${f.label} is required!`);
            }
        });

        if (hasEmpty) return false; // ❌ Stop form submit
        return true; // ✅ All good
    }
</script>



<script>
    $(function () {
        let cropper;
        let croppedBlob = null;
        const outputW = 150;
        const outputH = 190;

        $('#photoInput').on('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                alert('Please choose a valid image file.');
                return;
            }

            const url = URL.createObjectURL(file);
            $('#image-to-crop').attr('src', url);
            $('#crop-area').show();

            if (cropper) cropper.destroy();

            cropper = new Cropper(document.getElementById('image-to-crop'), {
                aspectRatio: outputW / outputH,
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                background: false
            });
        });

        $('#zoom-in').on('click', () => cropper && cropper.zoom(0.1));
        $('#zoom-out').on('click', () => cropper && cropper.zoom(-0.1));
        $('#reset-crop').on('click', () => cropper && cropper.reset());

        $('#crop-save').on('click', function () {
            if (!cropper) return alert('No image selected.');

            cropper.getCroppedCanvas({
                width: outputW,
                height: outputH,
                imageSmoothingQuality: 'high'
            }).toBlob(function (blob) {
                croppedBlob = blob;
                const previewURL = URL.createObjectURL(blob);
                $('#previewImg').attr('src', previewURL).show();
                $('#crop-area').hide();
            }, 'image/jpeg', 0.95);
        });

        $('#myForm').on('submit', function (e) {
            e.preventDefault(); // prevent normal form submission



            const mobile = document.getElementById('mnumber').value.trim();

            const stnameeng = document.getElementsByName('stnameeng')[0]?.value.trim() || '';
            const stnameben = document.getElementsByName('stnameben')[0]?.value.trim() || '';
            const fname = document.getElementsByName('fname')[0]?.value.trim() || '';
            const mname = document.getElementsByName('mname')[0]?.value.trim() || '';
            const mnumber = document.getElementsByName('mnumber')[0]?.value.trim() || '';
            const dob = document.getElementsByName('dob')[0]?.value.trim() || '';
            const brnno = document.getElementsByName('brnno')[0]?.value.trim() || '';

            const dist = document.getElementsByName('dist')[0]?.value.trim() || '';
            const ps = document.getElementsByName('ps')[0]?.value.trim() || '';
            const po = document.getElementsByName('po')[0]?.value.trim() || '';
            const village = document.getElementsByName('village')[0]?.value.trim() || '';

            const testno = document.getElementsByName('testno')[0]?.value.trim() || '';
            const insdist = document.getElementsByName('insdist')[0]?.value.trim() || '';
            const insps = document.getElementsByName('insps')[0]?.value.trim() || '';
            const inspo = document.getElementsByName('inspo')[0]?.value.trim() || '';
            const insname = document.getElementsByName('insname')[0]?.value.trim() || '';


            validateFormFields();



            // RegEx: 0 দিয়ে শুরু, মোট 11 সংখ্যা
            const regex = /^0\d{10}$/;

            if (!regex.test(mobile)) {
                showToast('danger', 'দয়া করে একটি সঠিক 11 ডিজিটের মোবাইল নম্বর লিখুন, যা 0 দিয়ে শুরু হবে।');
                // showToast('info', 'Mobile Sample : 01919629672');
                return false;
            }


            // Build FormData
            const formData = new FormData(this);
            // console.log(formData);
            for (const [key, value] of formData.entries()) {
                console.log(key, value);
            }


            if (croppedBlob) {
                formData.append('photo', croppedBlob, 'photo.jpg');
            } else {
                showToast('danger', "No Photo Selected! Please Choose Your Photo.");
                return;
            }

            // formData.append('photo', croppedBlob, 'photo.jpg');

            var dd = $('#dist option:selected').data('address');
            var dd2 = $('#insdist option:selected').data('ins');
            formData.set('dist', dd);
            formData.set('insdist', dd2);

            $('#submitBtn').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: 'core/register_process.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (res) {
                    $('#submitBtn').prop('disabled', false).text('Submit');
                    console.log('Server Response:', res);


                    // ✅ যদি PHP থেকে JSON response আসে

                    if (res.status === 'success') {

                        // const p = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } }); p.success("Admission Form Submit Successfully.");

                        // ফর্ম রিসেট
                        $('#myForm')[0].reset();
                        $('#previewImg').hide();
                        croppedBlob = null;

                        // ✅ Redirect
                        if (res.redirect) {
                            window.location.href = res.redirect;
                        } else {
                            showToast('success', 'Registration Form Submit successfully!');
                        }
                    } else {
                        showToast('danger', 'Registration failed! Please try again.');
                    }
                },
                error: function (xhr, status, error) {
                    $('#submitBtn').prop('disabled', false).text('Submit');
                    showToast('danger', 'Upload failed: ' + error);
                    // alert('Upload failed: ' + error);
                }
            });
        });
    });
</script>

<script>
    function back() {
        document.location.href = 'admission-login.php';
    }
</script>



</body>

</html>