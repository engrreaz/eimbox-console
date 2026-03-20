<?php require_once 'header.php'; ?>

<?php
// ================= LOAD SETTINGS =================

$q = mysqli_query($conn, "SELECT admin_data FROM scinfo WHERE sccode='$sccode' LIMIT 1");
$row = mysqli_fetch_assoc($q);

$settings = [];
if (!empty($row['admin_data'])) {
    $settings = json_decode($row['admin_data'], true);
    if (!is_array($settings))
        $settings = [];
}



function s($key, $default = '')
{
    global $settings;
    return htmlspecialchars($settings[$key] ?? $default);
}

function sel($key, $val)
{
    global $settings;
    return (($settings[$key] ?? '') == $val) ? 'selected' : '';
}

// ================= AUTO SCAN =================
function scanFiles($dir, $ext = '', $pattern = '')
{
    $out = [];

    if (!is_dir($dir))
        return $out;

    foreach (scandir($dir) as $f) {

        if ($f == '.' || $f == '..')
            continue;

        // Extension filter
        if ($ext) {
            if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) != strtolower($ext)) {
                continue;
            }
        }

        // Wildcard pattern filter
        if ($pattern) {
            if (!fnmatch($pattern, $f)) {
                continue;
            }
        }

        $out[] = $f;
    }

    return $out;
}

$headerFiles = scanFiles(BASE_ROOT . 'templete', 'php', 'letter-head-*.php');
$textImages = scanFiles(BASE_ROOT . 'templete', 'png');
$bgFiles = scanFiles(BASE_ROOT . 'templete', 'jpg', 'progress-report-background-*.jpg');

function yesno($title, $name, $subtext = '')
{
    ?>

    <div class="row">
        <div class="col-6 pt-2">
            <label><?= $title ?></label>
            <div class=" fs-tiny  pb-2">
                <?= $subtext ?>
            </div>

        </div>
        <div class="col-6">
            <select name="<?= $name ?>" class="form-select form-select-sm setting-input">
                <option value="1" <?= sel($name, '1') ?>>Yes</option>
                <option value="0" <?= sel($name, '0') ?>>No</option>
            </select>
        </div>


    </div>



    <?php
}
?>

<div class="container-fluid">

<div class="row">
    <div class="col-12 text-center fw-bold">
        <h4>Progress Report Customize Settings</h4>
    </div>
</div>
    <div class="row p-0">

        <!-- ================= LEFT : SETTINGS ================= -->
        <div class="col-lg-6">
            <form id="settingsForm">

                <input type="hidden" name="sccode" value="<?= $sccode ?>">

                <div class="row g-3">

                    <!-- Report Header -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header ">Report Header</div>
                            <div class="card-body">

                                <div class="row">
                                    <div class="col-6 pt-2">
                                        <label>Background</label>
                                        <div class=" fs-tiny  pb-2">
                                                Select a background image for the report page
                                        </div>

                                    </div>
                                    <div class="col-6">
                                        <select name="report_background"
                                            class="form-select form-select-sm setting-input">
                                            <option value=''>No Background</option>
                                            <?php foreach ($bgFiles as $bg): ?>
                                                <option value="templete/<?= $bg ?>" <?= sel('report_background', "templete/$bg") ?>>
                                                    <?= $bg ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>


                                </div>




                                <div class="row">
                                    <div class="col-6 pt-2">
                                        <label>Header</label>
                                        <div class="fs-tiny  pb-2">
                                            Choose the institution letterhead / report header design
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <select name="report_header" class="form-select form-select-sm setting-input">
                                            <?php foreach ($headerFiles as $f): ?>
                                                <option value="templete/<?= $f ?>" <?= sel('report_header', "templete/$f") ?>> <?= $f ?> </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                </div>


                                <div class="row">
                                    <div class="col-6 pt-2">
                                        <label>Header Scale</label>
                                        <div class="fs-tiny  pb-2">
                                            Adjust header display size (1 = normal size)
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <input type="number" step="0.01" name="header_scale"
                                            value="<?= s('header_scale', '1.00') ?>"
                                            class="form-control form-control-sm setting-input">
                                    </div>

                                </div>





                            </div>
                        </div>



                    </div>


                    <div class="col-12">
                        <div class="card">
                            <div class="card-header ">Report Body</div>
                            <div class="card-body">
                                <?php
yesno('Watermark', 'watermark', 'Show a watermark in the background');
yesno('Grading System', 'grading_system', 'Display grades along with marks');
yesno('Student Photo', 'student_photo', 'Include student photograph');
yesno('Student Name English', 'student_name_en', 'Show student name in English');
yesno('Student Name Bengali', 'student_name_bn', 'Show student name in Bengali');
yesno('Parents Info', 'parents_info', 'Display parent/guardian information');
yesno('Attendance', 'attendance_info', 'Show attendance summary');
yesno('Highest Mark', 'highest_mark', 'Display highest marks per subject');
yesno('Annotate', 'annotate', 'Include teacher remarks section');
yesno('Publish Date', 'publish_date', 'Show result publication date');
yesno('Guardian Signature', 'guardian_signature', 'Add guardian signature area');
yesno('QR Code', 'qr_code', 'Include QR code for verification');
yesno('Class Teacher', 'class_teacher', 'Show class teacher signature');
yesno('Head Signature', 'head_signature', 'Show head of institution signature');
?>
                            </div>
                        </div>
                    </div>


                    <div class="col-12">
                        <div class="card">
                            <div class="card-header fw-bold">Report Footer</div>
                            <div class="card-body">



                                <div class="row">
                                    <div class="col-6 pt-2">
                                        <label>Report Text</label>
                                        <div class="fs-tiny  pb-2">
                                            Select footer text / notice image for the report
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <select name="report_text_image" class="form-select form-select-sm setting-input">
                                            <?php foreach ($textImages as $f):
                                                if (strpos($f, 'progress-report-text') !== false): ?>
                                                    <option value="templete/<?= $f ?>" <?= sel('report_text_image', "templete/$f") ?>>
                                                        <?= $f ?>
                                                    </option>
                                                <?php endif; endforeach; ?>
                                        </select>
                                    </div>

                                </div>




                                <div class="row">
                                    <div class="col-6 pt-2">
                                        <label>Parents Title</label>
                                        <div class="fs-tiny  pb-2">
                                            Choose how parent names will be displayed
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <select name="parents_text_style" class="form-select form-select-sm setting-input">
                                            <option value="son_daughter" <?= sel('parents_text_style', 'son_daughter') ?>>
                                                Son of / Daughter of
                                            </option>
                                            <option value="father_mother" <?= sel('parents_text_style', 'father_mother') ?>>
                                                Father : ___ & Mother : ___
                                            </option>
                                        </select>
                                    </div>

                                </div>


                                <div class="row">
                                    <div class="col-6 pt-2">
                                        <label>Result Position</label>
                                        <div class="fs-tiny  pb-2">
                                            Select where the final result summary will appear
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <select name="result_position" class="form-select  form-select-sm setting-input">
                                            <option value="right" <?= sel('result_position', 'right') ?>>Right</option>
                                            <option value="bottom" <?= sel('result_position', 'bottom') ?>>Bottom
                                            </option>
                                        </select>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>







                    <!-- Save -->
                   

                </div>
            </form>
        </div>

        <!-- ================= RIGHT : LIVE PREVIEW ================= -->
        <div class="col-lg-6">
            <div class="card sticky-top" style="top:60px">
                <div class="card-header bg-dark text-white d-flex ">
                    <div class="flex-grow-1 my-1"> Live Preview</div>
                   
                     <div class="text-end my-0">
                        <button type="button" id="saveBtn" class="btn btn-primary btn-sm">
                            Save Settings
                        </button>
                    </div>
                </div>
                <div class="card-body p-2">

                    <iframe style="width:100%;height:80vh;border:1px solid #ccc;"
                        src="progress-report.php?partial=1&cls=Six&sec=Padma&sy=2026&exam=Model%20Poriksha&slot=School&preview=preview">
                    </iframe>

                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    // ========= LIVE PREVIEW AUTO REFRESH =========
    function refreshPreview() {
        const form = document.getElementById('settingsForm');
        const data = new FormData(form);
        const params = new URLSearchParams(data).toString();
        document.getElementById('previewFrame').src =
            'report/progress-report.php?partial=1&cls=Six&sec=Padma&sy=2026&exam=Model%20Poriksha&slot=School&preview=preview';
    }

    document.querySelectorAll('.setting-input').forEach(el => {
        // el.addEventListener('change', refreshPreview);
        document.getElementById('saveBtn').click();
    });

    // ========= SAVE SETTINGS =========
    document.getElementById('saveBtn').onclick = function () {
        const form = document.getElementById('settingsForm');
        const data = new FormData(form);

        fetch('report/save-progress-report-settings.php', {
            method: 'POST',
            body: data
        })
            .then(r => r.text())
            .then(res => {
                alert('Saved Successfully');
                window.location.reload();
                // refreshPreview();
            });
    }
</script>



</body>

</html>