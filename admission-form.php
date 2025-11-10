<?php
include_once('core/config.php');
include_once('core/db.php');
include_once('header-plain.php');
include_once('core/core-val.php');




$sccode = 103187;
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
        <div class="col-md-4">

        </div>

        <div class="col-md-8">

            <?php include_once('actions/sc-header.php'); ?>


            <div class="row ps-3 pe-3 mb-3 p-0">
                <div class="col-12 text-center  alert alert-primary m-0">
                    <h3 class="m-0 p-0 fw-bold text-primary"> Admission Form </h3>
                </div>
            </div>

            <form id="myForm" action="core/register_process.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" value="<?= $sccode; ?>">
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
                                <div class="mb-3">
                                    <label class="form-label">Mobile Number / মোবাইল নম্বর </label>
                                    <input name="mnumber" class="form-control" required>
                                </div>
                            </div>

                            <!-- right: photo upload & crop (6 cols) -->
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
                                            id="zoom-out">Zoom
                                            -</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            id="reset-crop">Reset</button>
                                        <button type="button" class="btn btn-sm btn-primary" id="crop-save">Save
                                            Photo</button>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <div class="photo-preview" id="photoPreview">
                                        <!-- preview here -->
                                        <img id="previewImg" src="" alt="Preview"
                                            style="width:150px; height:190px; object-fit:cover; display:none;">
                                    </div>
                                </div>

                                <!-- hidden input to hold base64 cropped image -->
                                <input type="hidden" name="photo_data" id="photo_data">
                                <div class="form-text mt-2">
                                    Allowed size: up to 2MB. Final size will be 150x190 px.
                                    <br>
                                    গ্রহণযোগ্য ফাইলের আকার : সর্বোচ্চ ২এমবি। চুড়ান্ত আকার হবে ১৫০x১৯০ পিক্সেল।
                                </div>
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

<script>
    let cropper;
    const outputW = 150;
    const outputH = 190;

    $('#photoInput').on('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) { alert('Please choose an image.'); return; }
        const url = URL.createObjectURL(file);
        $('#image-to-crop').attr('src', url);
        $('#crop-area').show();

        // destroy old cropper
        if (cropper) cropper.destroy();

        cropper = new Cropper(document.getElementById('image-to-crop'), {
            aspectRatio: outputW / outputH,
            viewMode: 1,
            autoCropArea: 1,
            responsive: true,
            background: false,
        });
    });

    $('#zoom-in').on('click', function () { if (cropper) cropper.zoom(0.1); });
    $('#zoom-out').on('click', function () { if (cropper) cropper.zoom(-0.1); });
    $('#reset-crop').on('click', function () { if (cropper) cropper.reset(); });

    $('#crop-save').on('click', function () {
        if (!cropper) return alert('No image selected');
        // get cropped canvas with exact size
        const canvas = cropper.getCroppedCanvas({
            width: outputW,
            height: outputH,
            imageSmoothingQuality: 'high'
        });
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        $('#previewImg').attr('src', dataUrl).show();
        $('#photo_data').val(dataUrl);
        // hide crop area if you want
        // $('#crop-area').hide();
    });

    // handle form submit: simple client-side check to ensure photo exists
    $('#myForm').on('submit', function (e) {
        // require photo_data
        if (!$('#photo_data').val()) {
            if (!confirm('You have not saved a photo. Proceed without photo?')) {
                e.preventDefault();
                return false;
            }
        }
        // allow normal submit to actions/register_submit.php
    });
</script>



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
        $('#myForm').on('submit', function () {
            const distName = $('#dist option:selected').data('name') || '';
            const psName = $('#ps option:selected').data('name') || '';
            if (!$('#dist_name').length) $(this).append('<input type="hidden" name="dist_name" id="dist_name">');
            if (!$('#ps_name').length) $(this).append('<input type="hidden" name="ps_name" id="ps_name">');
            $('#dist_name').val(distName);
            $('#ps_name').val(psName);
            return true;
        });

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
        $('#myForm').on('submit', function () {
            const insDistName = $('#insdist option:selected').data('name') || '';
            const insPSName = $('#insps option:selected').data('name') || '';
            if (!$('#insdist_name').length) $(this).append('<input type="hidden" name="insdist_name" id="insdist_name">');
            if (!$('#insps_name').length) $(this).append('<input type="hidden" name="insps_name" id="insps_name">');
            $('#insdist_name').val(insDistName);
            $('#insps_name').val(insPSName);
            return true;
        });

    });
</script>




<script>

</script>

</body>

</html>