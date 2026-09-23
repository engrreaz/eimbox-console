<?php require_once 'header.php';

$refno = '';
$refdate = date('Y-m-d');




// session year
$sylist = [];
$sql = "SELECT syear,active FROM sessionyear WHERE sccode='$sccode'  ORDER BY id";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $sylist[] = $row;
}


// settings


// admin permission
$panel = $admin_data['panel'] ?? [];
$module = $admin_data['module'] ?? [];
?>

<!-- ================== CONTENT START ================== -->
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- <h3 class="d-print-none">Basic Primary Settings</h3> -->

    <!-- ================== WEEKENDS ================== -->
    <?php
    $indweek = array_search('Weekends', array_column($sett, 'setting_title'));
    $weekends = $indweek !== false ? $sett[$indweek]['settings_value'] : '';
    ?>

    <div class="card mb-3 d-print-none">
        <div class="card-body">
            <h4 class="tour" id="weekend-title">Weekends</h4>
            <small class="text-muted">Mark your weekly holidays</small>

            <div class="row mt-2">
                <?php
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                foreach ($days as $day) {
                    ?>
                    <div class="col-md-2">
                        <div class="form-check form-check-primary">
                            <input type="checkbox" id="<?= $day ?>" class="form-check-input" <?= str_contains($weekends, $day) ? 'checked' : '' ?>>
                            <label class="form-check-label"><?= $day ?></label>
                        </div>
                    </div>
                <?php } ?>

                <div class="col-md-12 mt-2">
                    <button id="week" class="btn btn-outline-success  " data-feature="update-weekend" data-points="5" onclick="updateWeekends()">Update</button>

                </div>
            </div>
        </div>
    </div>



    <?php
    $indMedium = array_search('Medium', array_column($sett, 'setting_title'));
    $mediums = $indMedium !== false ? $sett[$indMedium]['settings_value'] : '';
    ?>

    <div class="row d-print-none">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="m-0 tour" id="medium-title">Medium</h4>
                    <small class="text-muted">Active teaching medium</small>

                    <div class="row pt-2">
                        <div class="col-md-2">
                            <div class="form-check form-check-primary">
                                <input type="checkbox" id="ben" class="form-check-input" <?= str_contains($mediums, 'Bengali') ? 'checked' : '' ?>>
                                <label class="form-check-label">Bengali</label>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-check form-check-primary">
                                <input type="checkbox" id="eng" class="form-check-input" <?= str_contains($mediums, 'English') ? 'checked' : '' ?>>
                                <label class="form-check-label">English</label>
                            </div>
                        </div>

                        <div class="col-md-12 pt-2">
                            <button id="med" class="btn btn-outline-success"  data-feature="update-medium" data-points="5"  onclick="updateMedium()">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <?php
    $indVersion = array_search('Version', array_column($sett, 'setting_title'));
    $versions = $indVersion !== false ? $sett[$indVersion]['settings_value'] : '';
    ?>

    <div class="row d-print-none mt-3">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="m-0 tour" id="version-title">Version</h4>
                    <small class="text-muted">Curriculum / version selection</small>

                    <div class="row pt-2">
                        <div class="col-md-2">
                            <div class="form-check form-check-primary">
                                <input type="checkbox" id="ben2" class="form-check-input" <?= str_contains($versions, 'Bengali') ? 'checked' : '' ?>>
                                <label class="form-check-label">Bengali</label>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-check form-check-primary">
                                <input type="checkbox" id="eng2" class="form-check-input" <?= str_contains($versions, 'English') ? 'checked' : '' ?>>
                                <label class="form-check-label">English</label>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-check form-check-primary">
                                <input type="checkbox" id="ara2" class="form-check-input" <?= str_contains($versions, 'Arabic') ? 'checked' : '' ?>>
                                <label class="form-check-label">Arabic</label>
                            </div>
                        </div>

                        <div class="col-md-12 pt-2">
                            <button id="ver" class="btn btn-outline-success"  data-feature="update-version" data-points="5"  onclick="updateVersion()">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>





    <style>
        .session-item-box {
            transition: all 0.2s ease-in-out;
        }
        .session-item-box:hover {
            border-color: #d11111ff !important; 
            box-shadow: 0 2px 8px rgba(241, 14, 25, 0.95);
            transform: translateY(-1px);
        }
        .session-item-box .btn-delete-sy {
            opacity: 0.6;
            transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .session-item-box:hover .btn-delete-sy {
            opacity: 1;
        }
        .session-item-box .btn-delete-sy:hover {
            transform: scale(1.15);
        }
    </style>

    <div class="row d-print-none mt-3">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h4 class="m-0 tour" id="session-year-title">Active Session Year</h4>
                            <small class="text-muted">Select active academic session</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSessionYearModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Session Year
                        </button>
                    </div>

                    <div class="row pt-2" id="session-year-container">
                        <?php
                        $sylistYears = array_column($sylist, 'syear');
                        if (!empty($sylistYears)) {
                            $years = $sylistYears;
                        } else {
                            $years = [
                                date('Y') - 1 . '-' . date('y'),
                                date('Y'),
                                date('Y') . '-' . (date('y') + 1)
                            ];
                            if (date('m') == 12) {
                                $years[] = date('Y') + 1;
                            }
                        }

                        foreach ($years as $i => $yr) {
                            $ind = array_search($yr, array_column($sylist, 'syear'));
                            $checked = ($ind !== false && $sylist[$ind]['active'] == 1) ? 'checked' : '';
                            $item_id = 'sy_item_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $yr);
                            ?>
                            <div class="col-md-3 col-sm-4 col-6 mb-2" id="<?= $item_id ?>">
                                <div class="session-item-box d-flex align-items-center justify-content-between p-2 rounded">
                                    <div class="form-check form-check-primary m-0">
                                        <input type="checkbox" class="form-check-input yr-checkbox" id="yr<?= $i ?>" value="<?= htmlspecialchars($yr) ?>" <?= $checked ?>>
                                        <label class="form-check-label ms-1" for="yr<?= $i ?>"><?= htmlspecialchars($yr) ?></label>
                                    </div>
                                    <button type="button" class="btn btn-sm text-danger p-0 border-0 bg-transparent ms-2 btn-delete-sy" onclick="deleteSessionYear('<?= htmlspecialchars($yr, ENT_QUOTES) ?>', '<?= $item_id ?>')" title="Delete">
                                        <i class="bi bi-trash fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="col-md-12 pt-2">
                        <button class="btn btn-outline-success" id="sy"
                            onclick="updateSessionYear()" data-feature="update-session-year" data-points="5">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Adding Session Year -->
    <div class="modal fade" id="addSessionYearModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Session Year</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label for="new_syear" class="form-label">Session Year</label>
                        <input type="text" class="form-control" id="new_syear" placeholder="e.g. 2026 or 2026-27">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addSessionYear()">Save</button>
                </div>
            </div>
        </div>
    </div>


    <div class="card my-3">
        <div class="card-header">
            <h4 class="mb-0 tour" id="class-title">Classes</h4>
        </div>

        <div class="card-body">
            <div class="row g-2">

                <?php
                foreach ($sett as $row) {
                    if ($row['setting_title'] === 'Classes') {
                        $classesValue = $row['settings_value']; // "Two,Six"
                        break;
                    }
                }
                $selectedClasses = $classesValue
                    ? explode(',', $classesValue)
                    : [];

                $classes = [
                    'Play',
                    'Nursery',
                    'KG',
                    'Junior One',
                    'One',
                    'Two',
                    'Three',
                    'Four',
                    'Five',
                    'Six',
                    'Seven',
                    'Eight',
                    'Nine',
                    'Ten',
                    'SSC',
                    'Eleven',
                    'Twelve'
                ];

                echo "<div class='row'>";
                foreach ($classes as $cls) {

                    $id = 'cls_' . preg_replace('/\s+/', '_', strtolower($cls));
                    $checked = in_array($cls, $selectedClasses) ? 'checked' : '';
                    ?>
                    <div class="form-check col-md-2">
                        <input class="form-check-input class-item" type="checkbox" id="<?= $id ?>" value="<?= $cls ?>"
                            <?= $checked ?>>
                        <label class="form-check-label" for="<?= $id ?>">
                            <?= $cls ?>
                        </label>
                    </div>
                <?php }
                echo "</div>";
                ?>


            </div>

            <div class="mt-3">
                <button id="klass" class="btn btn-outline-success btn-sm"  data-feature="update-class-list" data-points="5"  onclick="updateClasses()">
                    Update
                </button>
                <span id="clsmsg" class="ms-2"></span>
            </div>
        </div>
    </div>





    <!-- 🔁 এখানে একইভাবে Modules / Panels / Medium / Session block থাকবে -->
    <!-- (তোমার কোড অপরিবর্তিত রেখে শুধু container এর ভেতরে বসানো হয়েছে) -->

</div>
<!-- ================== CONTENT END ================== -->

<?php require_once 'footer.php'; ?>

<script>
    function updateWeekends() {
        let days = [];
        ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
            .forEach(d => {
                if (document.getElementById(d).checked) {
                    days.push(d);
                }
            });

        $.post('settings/save-weekends.php', {
            weekends: days.join(',')
        }, function (res) {
            $('#defbtn').html(res);
            $('#week').css('background', 'green');
            $('#week').css('color', 'white');
            $('#week').html('Weekend Information Updated');
        });

        setTimeout(function () {
            $('#week').css({
                background: '',
                color: ''
            }).html('Update');
        }, 2000); // 2 seconds
    }
</script>

<script>
    function updateMedium() {
        let data = [];
        if ($('#ben').is(':checked')) data.push('Bengali');
        if ($('#eng').is(':checked')) data.push('English');

        $.post('settings/save-medium.php', {
            medium: data.join(',')
        }, res => $('#defbtn').html(res));
        $('#med').css('background', 'green');
        $('#med').css('color', 'white');
        $('#med').html('Medium Information Updated');

        setTimeout(function () {
            $('#med').css({
                background: '',
                color: ''
            }).html('Update');
        }, 2000); // 2 seconds
    }
</script>

<script>
    function updateVersion() {
        let data = [];
        if ($('#ben2').is(':checked')) data.push('Bengali');
        if ($('#eng2').is(':checked')) data.push('English');
        if ($('#ara2').is(':checked')) data.push('Arabic');

        $.post('settings/save-version.php', {
            version: data.join(',')
        }, res => $('#defbtn').html(res));
        $('#ver').css('background', 'green');
        $('#ver').css('color', 'white');
        $('#ver').html('Version Information Updated');

        setTimeout(function () {
            $('#ver').css({
                background: '',
                color: ''
            }).html('Update');
        }, 2000); // 2 seconds
    }
</script>

<script>
    function addSessionYear() {
        let yr = $('#new_syear').val().trim();
        if (!yr) {
            alert('Please enter a valid session year.');
            return;
        }

        $.post('settings/add-sessionyear.php', { year: yr }, function (res) {
            if (res.status === 'success') {
                var modalEl = document.getElementById('addSessionYearModal');
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                $('#new_syear').val('');
                location.reload();
            } else {
                alert(res.message || 'Error adding session year.');
            }
        }, 'json');
    }

    function deleteSessionYear(year, elementId) {
        const doDelete = function () {
            $.post('settings/delete-sessionyear.php', { year: year }, function (res) {
                if (res.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Session year has been deleted.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                    $('#' + elementId).fadeOut(300, function () {
                        $(this).remove();
                    });
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', res.message || 'Error deleting session year.', 'error');
                    } else {
                        alert(res.message || 'Error deleting session year.');
                    }
                }
            }, 'json');
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Session Year?',
                text: 'Are you sure you want to delete session year "' + year + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    doDelete();
                }
            });
        } else {
            if (confirm('Are you sure you want to delete session year "' + year + '"?')) {
                doDelete();
            }
        }
    }

    function updateSessionYear() {
        let years = [];

        $('.yr-checkbox').each(function () {
            years.push({
                year: this.value,
                active: this.checked ? 1 : 0
            });
        });

        $.post('settings/save-sessionyear.php', { years: years }, function (res) {

            if (res.status === 'success') {
                $('#sy').html('Session Year Updated');
                $('#sy').css('background', 'green');
                $('#sy').css('color', 'white');

            } else {
                $('#sy').html('Error Updating');
                $('#sy').css('color', 'red');
            }
            setTimeout(function () {
                $('#sy').css({
                    background: '',
                    color: ''
                }).html('Update');
            }, 2000); // 2 seconds

        }, 'json'); // response JSON হলে
    }

</script>

<script>
    function updateClasses() {

        let classes = [];

        $('.class-item').each(function () {
            classes.push({
                name: this.value,
                active: this.checked ? 1 : 0
            });
        });

        $.post('settings/save-classes.php', {
            classes: classes
        }, function (res) {
            $('#clsmsg').html(res);
            $('#klass').css('background', 'green');
            $('#klass').css('color', 'white');
            $('#klass').html('Class Information Updated');
            setTimeout(function () {
                $('#klass').css({
                    background: '',
                    color: ''
                }).html('Update');$('#clsmsg').html('');
            }, 2000); // 2 seconds

        });
    }
</script>

</body>

</html>