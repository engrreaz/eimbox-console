<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-3">
        <div class="card-body">
            <!-- Tabulatingsheet Filter Section -->
            <h5>Result Filter (Tabulatingsheet)</h5>
            <div class="row g-2 mb-3">
                <div class="col-md-2">
                    <select id="slotFilter" class="form-select">
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
                    <select id="sessionFilter" class="form-select">
                        <option value="">Select Session</option>
                        <?php
                        $sessions = mysqli_query($conn, "SELECT DISTINCT sessionyear FROM tabulatingsheet where sccode='$sccode' ORDER BY sessionyear");
                        while ($row = mysqli_fetch_assoc($sessions)) {
                            echo "<option value='{$row['sessionyear']}'>{$row['sessionyear']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="examFilter" class="form-select">
                        <option value="">Select Exam</option>
                        <?php
                        $exams = mysqli_query($conn, "SELECT DISTINCT exam FROM tabulatingsheet  sccode='$sccode' ORDER BY exam");
                        while ($row = mysqli_fetch_assoc($exams)) {
                            echo "<option value='{$row['exam']}'>{$row['exam']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="classFilter" class="form-select">
                        <option value="">Select Class</option>
                        <?php
                        $classes = mysqli_query($conn, "SELECT DISTINCT classname FROM tabulatingsheet  sccode='$sccode'  ORDER BY classname");
                        while ($row = mysqli_fetch_assoc($classes)) {
                            echo "<option value='{$row['classname']}'>{$row['classname']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="sectionFilter" class="form-select">
                        <option value="">Select Section</option>
                        <?php
                        $sections = mysqli_query($conn, "SELECT DISTINCT sectionname FROM tabulatingsheet  sccode='$sccode'  ORDER BY sectionname");
                        while ($row = mysqli_fetch_assoc($sections)) {
                            echo "<option value='{$row['sectionname']}'>{$row['sectionname']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- SessionInfo Filter Section -->
            <h5>Session Filter (SessionInfo)</h5>
            <div class="row g-2 mb-3">
                <div class="col-md-2">
                    <select id="sessioninfoSlot" class="form-select">
                        <option value="">Select Slot</option>
                        <?php
                        $siSlots = mysqli_query($conn, "SELECT DISTINCT slot FROM sessioninfo  sccode='$sccode'  ORDER BY slot");
                        while ($row = mysqli_fetch_assoc($siSlots)) {
                            echo "<option value='{$row['slot']}'>{$row['slot']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="sessioninfoSession" class="form-select">
                        <option value="">Select Session</option>
                        <?php
                        $siSessions = mysqli_query($conn, "SELECT DISTINCT sessionyear FROM sessioninfo  sccode='$sccode' ORDER BY sessionyear");
                        while ($row = mysqli_fetch_assoc($siSessions)) {
                            echo "<option value='{$row['sessionyear']}'>{$row['sessionyear']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="col-md-2 mb-3">
                <button id="filterBtn" class="btn btn-primary w-100">Filter</button>
            </div>

            <!-- Result Table -->
            <div class="table-responsive">
                <table class="table table-bordered" id="resultTable">
                    <thead>
                        <tr>
                            <th>MeritNumComb</th>
                            <th>MeritNum</th>
                            <th>STID</th>
                            <th>RollNo</th>
                            <th>ClassName</th>
                            <th>SectionName</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

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
            });
        }

        $('#filterBtn').on('click', loadResults);
    });
</script>