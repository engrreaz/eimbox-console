<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-3">

        <div class="card-header">
            <h5 class="fw-bold">Promotion Verification Tool</h5>
        </div>

        <div class="card-body">
            <!-- Tabulatingsheet Filter Section -->
            <h5>Filter From <b>Tabulating Sheet</b></h5>
            <div class="row g-2 mb-3">
                <div class="col-md-2">
                    <label for="slotFilter" class="fs-small ps-1">Slot / Unit</label>
                    <select id="slotFilter" class="form-select form-select-sm">
                        <option value="">Select Slot</option>
                        <?php
                        $slots = mysqli_query($conn, "SELECT DISTINCT slot FROM tabulatingsheet WHERE sccode='$sccode' ORDER BY slot");
                        while ($row = mysqli_fetch_assoc($slots)) {
                            echo "<option value='{$row['slot']}'>{$row['slot']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sessionFilter" class="fs-small ps-1">Session</label>
                    <select id="sessionFilter" class="form-select form-select-sm">
                        <option value="">Select Session</option>
                        <?php
                        $sessions = mysqli_query($conn, "SELECT DISTINCT sessionyear FROM tabulatingsheet where sccode='$sccode' and (sessionyear !='' OR sessionyear IS NOT NULL) ORDER BY sessionyear");
                        while ($row = mysqli_fetch_assoc($sessions)) {
                            echo "<option value='{$row['sessionyear']}'>{$row['sessionyear']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="examFilter" class="fs-small ps-1">Examination</label>
                    <select id="examFilter" class="form-select form-select-sm">
                        <option value="">Select Exam</option>
                        <?php
                        $exams = mysqli_query($conn, "SELECT DISTINCT exam FROM tabulatingsheet  Where   sccode='$sccode' ORDER BY exam");
                        while ($row = mysqli_fetch_assoc($exams)) {
                            echo "<option value='{$row['exam']}'>{$row['exam']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="classFilter" class="fs-small ps-1">Class</label>
                    <select id="classFilter" class="form-select form-select-sm">
                        <option value="">Select Class</option>
                        <?php
                        $classes = mysqli_query($conn, "SELECT DISTINCT classname FROM tabulatingsheet Where  sccode='$sccode'  ORDER BY classname");
                        while ($row = mysqli_fetch_assoc($classes)) {
                            echo "<option value='{$row['classname']}'>{$row['classname']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sectionFilter" class="fs-small ps-1">Section</label>
                    <select id="sectionFilter" class="form-select form-select-sm">
                        <option value="">Select Section</option>
                        <?php
                        $sections = mysqli_query($conn, "SELECT DISTINCT sectionname FROM tabulatingsheet  Where   sccode='$sccode'  ORDER BY sectionname");
                        while ($row = mysqli_fetch_assoc($sections)) {
                            echo "<option value='{$row['sectionname']}'>{$row['sectionname']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- SessionInfo Filter Section -->
            <h5>Maching With <b>Session Information</b></h5>
            <div class="row g-2 mb-3">
                <div class="col-md-2">
                    <label for="sessioninfoSlot" class="fs-small ps-1">Slot/Unit</label>
                    <select id="sessioninfoSlot" class="form-select form-select-sm">
                        <option value="">Select Slot</option>
                        <?php
                        $siSlots = mysqli_query($conn, "SELECT DISTINCT slot FROM sessioninfo  Where   sccode='$sccode'  ORDER BY slot");
                        while ($row = mysqli_fetch_assoc($siSlots)) {
                            echo "<option value='{$row['slot']}'>{$row['slot']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="sessioninfoSession" class="fs-small ps-1">Session</label>
                    <select id="sessioninfoSession" class="form-select form-select-sm">
                        <option value="">Select Session</option>
                        <?php
                        $siSessions = mysqli_query($conn, "SELECT DISTINCT sessionyear FROM sessioninfo  Where   sccode='$sccode' ORDER BY sessionyear");
                        while ($row = mysqli_fetch_assoc($siSessions)) {
                            echo "<option value='{$row['sessionyear']}'>{$row['sessionyear']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2 mb-0 d-flex align-items-end ">
                    <button id="filterBtn" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>

                <div class="col-md-2  mb-0 d-flex align-items-end " id="errCount">

                </div>

            </div>
        </div>


        <!-- Result Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="resultTable">
                <thead>
                    <tr>
                        <th colspan="4">Result Data</th>
                        <th colspan="3">Session Data</th>
                    </tr>
                    <tr>
                        <th>Merit (Combined)</th>
                        <th>Merit</th>
                        <th>ID</th>
                        <th>Name of Student</th>
                        <th>RollNo</th>
                        <th>ClassName</th>
                        <th>SectionName</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>


    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    $(document).ready(function () {
        function loadResults() {
            let data = {
                // Tabulatingsheet filters
                slot: $('#slotFilter').val(),
                session: $('#sessionFilter').val(),
                exam: $('#examFilter').val(),
                classname: $('#classFilter').val(),
                sectionname: $('#sectionFilter').val(),
                // SessionInfo filters
                sessioninfoSlot: $('#sessioninfoSlot').val(),
                sessioninfoSession: $('#sessioninfoSession').val()
            };

            $.post('promotion/getMeritData.php', data, function (res) {
                $('#resultTable tbody').html(res);
                $('#errCount').text($('#cnt').text());
                $('#delrow').remove();
            });
        }

        $('#filterBtn').on('click', loadResults);
    });
</script>

<script>
    $(function () {

        /* ---------- helpers ---------- */
        function nextSession(val, list) {
            let i = list.indexOf(val);
            return (i >= 0 && list[i + 1]) ? list[i + 1] : val;
        }

        function syncSessionInfo() {
            if (!$('#sessioninfoSlot').val()) {
                $('#sessioninfoSlot').val($('#slotFilter').val());
            }

            if (!$('#sessioninfoSession').val()) {
                let sessions = $('#sessionFilter option').map(function () {
                    return $(this).val();
                }).get();

                let next = nextSession($('#sessionFilter').val(), sessions);
                $('#sessioninfoSession').val(next);
            }
        }

        /* ---------- dependent loaders ---------- */

        function loadExamClass() {
            $.post('promotion/get-exam-class.php', {
                slot: $('#slotFilter').val(),
                session: $('#sessionFilter').val()
            }, function (r) {
                $('#examFilter').html(r.exam);
                $('#classFilter').html(r.classname);
                loadSection();
            }, 'json');
        }

        function loadSection() {
            $.post('promotion/get-section.php', {
                slot: $('#slotFilter').val(),
                session: $('#sessionFilter').val(),
                exam: $('#examFilter').val(),
                classname: $('#classFilter').val()
            }, function (r) {
                $('#sectionFilter').html(r);
                loadResults();
            });
        }

        function loadResults() {
            syncSessionInfo();

            $.post('promotion/getMeritData.php', {
                slot: $('#slotFilter').val(),
                session: $('#sessionFilter').val(),
                exam: $('#examFilter').val(),
                classname: $('#classFilter').val(),
                sectionname: $('#sectionFilter').val(),
                sessioninfoSlot: $('#sessioninfoSlot').val(),
                sessioninfoSession: $('#sessioninfoSession').val()
            }, function (res) {
                $('#resultTable tbody').html(res);
                $('#errCount').text($('#cnt').text());
                $('#delrow').remove();
            });
        }

        /* ---------- events ---------- */

        $('#slotFilter,#sessionFilter').on('change', loadExamClass);
        $('#examFilter,#classFilter').on('change', loadSection);
        $('#sectionFilter,#sessioninfoSlot,#sessioninfoSession').on('change', loadResults);

        $('#filterBtn').on('click', loadResults);

    });

    function fixnow(id, rollno) {

    if (!id || !rollno) {
        showToast('danger', 'Invalid data');
        return;
    }

    $.post('promotion/fixnow.php', {
        id: id,
        rollno: rollno
    }, function (r) {

        if (r.status === 'ok') {
            showToast('success', 'Fixed successfully');
            loadResults(); // table auto refresh
        } else {
            showToast('danger', r.msg || 'Fix failed');
        }

    }, 'json')
    .fail(function () {
        showToast('danger', 'Server error');
    });
}

</script>

</body>

</html>