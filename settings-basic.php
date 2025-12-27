<?php require_once 'header.php';

$refno = '';
$refdate = date('Y-m-d');




// session year
$sylist = [];
$sql = "SELECT * FROM sessionyear WHERE sccode='$sccode' AND active=1 ORDER BY id";
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

    <h3 class="d-print-none">Basic Primary Settings</h3>

    <!-- ================== WEEKENDS ================== -->
    <?php
    $indweek = array_search('Weekends', array_column($sett, 'setting_title'));
    $weekends = $indweek !== false ? $sett[$indweek]['settings_value'] : '';
    ?>

    <div class="card mb-3 d-print-none">
        <div class="card-body">
            <h4>Weekends</h4>
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
                    <button id="week" class="btn btn-inverse-success" onclick="updateWeekends()">Update</button>

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
                    <h4 class="m-0">Medium</h4>
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
                            <button id="med" class="btn btn-inverse-success" onclick="updateMedium()">Update</button>
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

    <div class="row d-print-none">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="m-0">Version</h4>
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
                            <button id="ver" class="btn btn-inverse-success" onclick="updateVersion()">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>





    <div class="row d-print-none mt-3">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="m-0">Active Session Year</h4>
                    <small class="text-muted">Select active academic session</small>

                    <div class="row pt-2">
                        <?php
                        $years = [
                            date('Y') - 1 . '-' . date('y'),
                            date('Y'),
                            date('Y') . '-' . (date('y') + 1)
                        ];
                        if (date('m') == 12) {
                            $years[] = date('Y') + 1;
                        }

                        foreach ($years as $i => $yr) {
                            $ind = array_search($yr, array_column($sylist, 'syear'));
                            $checked = ($ind !== false) ? 'checked' : '';
                            ?>
                            <div class="col-md-2">
                                <div class="form-check form-check-primary">
                                    <input type="checkbox" class="form-check-input" id="yr<?= $i ?>" value="<?= $yr ?>"
                                        <?= $checked ?>>
                                    <label class="form-check-label"><?= $yr ?></label>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-md-12 pt-2">
                            <button class="btn btn-inverse-success" id="sy"
                                onclick="updateSessionYear()">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0">Classes</h6>
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
                    'Ten'
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
                <button class="btn btn-inverse-success btn-sm" onclick="updateClasses()">
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
        });
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
    }
</script>

<script>
    function updateSessionYear() {
        let years = [];

        $('[id^=yr]').each(function () {
            years.push({
                year: this.value,
                active: this.checked ? 1 : 0
            });
        });

        $.post('settings/save-sessionyear.php', { years: years }, function (res) {

            if (res.status === 'success') {
                $('#sy').html('Updated');
                $('#sy').css('color', 'green');
            } else {
                $('#sy').html('Error Updating');
                $('#sy').css('color', 'red');
            }

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
        });
    }
</script>

</body>

</html>