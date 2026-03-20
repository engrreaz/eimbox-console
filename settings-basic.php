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
                    <button id="week" class="btn btn-outline-success  " onclick="updateWeekends()">Update</button>

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
                            <button id="med" class="btn btn-outline-success" onclick="updateMedium()">Update</button>
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
                            <button id="ver" class="btn btn-outline-success" onclick="updateVersion()">Update</button>
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

                        // var_dump($sylist);
                        $years = [
                            date('Y') - 1 . '-' . date('y'),
                            date('Y'),
                            date('Y') . '-' . (date('y') + 1)
                        ];
                        if (date('m') == 12) {
                            $years[] = date('Y') + 1;
                        }

                        $sylistYears = array_column($sylist, 'syear');
                        $years = array_unique(array_merge($years, $sylistYears));

                        foreach ($years as $i => $yr) {
                            $ind = array_search($yr, array_column($sylist, 'syear'));
                            $checked = ($ind !== false && $sylist[$ind]['active'] == 1) ? 'checked' : '';
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
                            <button class="btn btn-outline-success" id="sy"
                                onclick="updateSessionYear()">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card my-3">
        <div class="card-header">
            <h4 class="mb-0">Classes</h4>
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
                <button id="klass" class="btn btn-outline-success btn-sm" onclick="updateClasses()">
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