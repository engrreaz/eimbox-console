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
    <div class="col-12 ">


    </div>
</div>

<div class="col-xxl">
    <div class="row">
        <div class="col-md-4 " style="
        background-image: url('assets/images/core/regd-form.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        height: 98vh;
        border-radius: 5px;
        ">
        </div>


        <div class="col-md-8">

            <?php include_once('actions/sc-header.php'); ?>


            <div class="row ps-3 pe-3 mb-3 p-0">
                <div class="col-12 text-center  alert alert-primary m-0">
                    <h3 class="m-0 p-0 fw-bold text-primary"> Admission Form </h3>
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
                                    <label class="form-label">Name (English) / নাম (ইংরেজীতে)</label>
                                    <input name="stnameeng" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Name (Bangla) / নাম (বাংলায়)</label>
                                    <input name="stnameben" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Father's Name / পিতার নাম</label>
                                    <input name="fname" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mother's Name / মাতার নাম</label>
                                    <input name="mname" class="form-control">
                                </div>

                            </div>

                            <!-- right: photo upload & crop (6 cols) -->
                            <!-- ✳️ Student Photo Section -->
                            <div class="col-md-6">
                                <label class="form-label">Student Photo (150x190) / শিক্ষার্থীর ছবি</label>
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
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            id="zoom-out">Zoom -</button>
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

                                <!-- ❌ Hidden Base64 input বাদ -->
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

                                <label class="form-label">Mobile Number / মোবাইল নম্বর </label>
                                <input id="mnumber" name="mnumber" class="form-control" required>

                            </div>
                            <div class="col-md-3">
                                <label for="ps" class="form-label">Date of Birth / জন্ম তারিখ </label>
                                <input type="date" id="dob" name="dob" class="form-control" value="" required>

                            </div>
                            <div class="col-md-6">
                                <label for="po" class="form-label">Birth Registration Number / জন্ম নিবন্ধন
                                    নম্বর</label>
                                <input name="brnno" class="form-control" placeholder="Birth Registration Number">
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
                                <label for="dist" class="form-label">District / জেলা</label>
                                <select id="dist" name="dist" class="form-control" required>
                                    <option value="">-- Select District --</option>
                                    <!-- JS will populate -->
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="ps" class="form-label">Upzila / উপজেলা</label>
                                <select id="ps" name="ps" class="form-control" disabled>
                                    <option value="">-- Select PS / Upazila --</option>
                                    <!-- JS will populate based on district -->
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="po" class="form-label">Post Office / ডাকঘর</label>
                                <input name="po" class="form-control" placeholder="PO">
                            </div>
                            <div class="col-md-3">
                                <label for="village" class="form-label">Village / গ্রাম</label>
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
                                <label for="testno" class="form-label">TC. No. /<br> টিসি নম্বর</label>
                                <input name="testno" class="form-control" placeholder="TC Number">
                            </div>
                            <div class="col-md-3">
                                <label for="insdist" class="form-label"> Institute District /<br> প্রতিষ্ঠানের জেলা
                                </label>
                                <select id="insdist" name="insdist" class="form-control" required>
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
                                <input name="inspo" class="form-control" placeholder="Institute Post">
                            </div>
                            <div class="col-12 mt-4">
                                <label for="insname" class="form-label"> Institute Name / প্রতিষ্ঠানের নাম </label>
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

    });


</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

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

            // RegEx: 0 দিয়ে শুরু, মোট 11 সংখ্যা
            const regex = /^0\d{10}$/;

            if (!regex.test(mobile)) {
                alert('দয়া করে একটি সঠিক 11 ডিজিটের মোবাইল নম্বর লিখুন, যা 0 দিয়ে শুরু হবে।');
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

                    alert('Raw response: ' + JSON.stringify(res.status));
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
                            alert('Registration successful!');
                        }
                    } else {
                        alert('Registration failed! Please try again.');
                    }
                },
                error: function (xhr, status, error) {
                    $('#submitBtn').prop('disabled', false).text('Submit');
                    alert('Upload failed: ' + error);
                }
            });
        });
    });
</script>




</body>

</html>