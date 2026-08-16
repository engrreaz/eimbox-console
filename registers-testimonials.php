<?php require_once 'header.php'; ?>

<?php
$slot = $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_COOKIE['chain-session'] ?? date('Y');
$cls2 = $_COOKIE['chain-class'] ?? '';
$sec2 = $_COOKIE['chain-section'] ?? '';
$exam2 = $_GET['exam'] ?? 'SSC';
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h3 class="d-print-none">Testimonial Issue Register</h3>
    <div class="card mb-4 d-print-none">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select class="form-select" id="year">
                        <?php
                        for ($y = date('Y'); $y >= 2020; $y--) {
                            $selected = ($sessionyear == $y) ? 'selected' : '';
                            echo '<option value="' . $y . '"' . $selected . '>' . $y . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <?php
                $chain_param = '-c 12 -t Choose Class & Section -u -r -b Show List';
                include 'components/slot-tree-ui.php';
                ?>
                <div class="col-md-2">
                    <label class="form-label">Exam</label>
                    <select class="form-select" id="exam">
                        <option value="SSC" <?= ($exam2 == 'SSC') ? 'selected' : '' ?>>SSC</option>
                        <option value="HSC" <?= ($exam2 == 'HSC') ? 'selected' : '' ?>>HSC</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Student List for Testimonial</h5>
            <div>
                <button class="btn btn-sm btn-info" onclick="printSelected()"><i class="bi bi-printer me-1"></i> Print Selected</button>
                <button class="btn btn-sm btn-warning" onclick="resultEntry(0)"><i class="bi bi-pencil-square me-1"></i> Result Entry</button>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="checkAll" class="form-check-input"></th>
                        <th>Roll</th>
                        <th>Student Name</th>
                        <th>Parents</th>
                        <th>Roll/Regd</th>
                        <th>Result</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($cls2) && !empty($sec2)) {
                        $sql0 = "SELECT si.id as session_id, si.rollno, si.stid, s.* FROM sessioninfo si JOIN students s ON si.stid = s.stid AND si.sccode = s.sccode WHERE si.sessionyear = '$sessionyear' AND si.sccode='$sccode' AND si.classname='$cls2' AND si.sectionname = '$sec2' ORDER BY si.rollno";
                        $result0 = $conn->query($sql0);
                        if ($result0->num_rows > 0) {
                            while ($row = $result0->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><input type="checkbox" class="form-check-input st-check" value="<?= $row['stid'] ?>"></td>
                                    <td><?= $row['rollno'] ?></td>
                                    <td>
                                        <div><?= $row['stnameeng'] ?></div>
                                        <small class="text-muted"><?= $row['stnameben'] ?></small>
                                    </td>
                                    <td>
                                        <div>F: <?= $row['fname'] ?></div>
                                        <div>M: <?= $row['mname'] ?></div>
                                    </td>
                                    <td>
                                        <div>Roll: <?= $row['sscroll'] ?></div>
                                        <div>Regd: <?= $row['regdno'] ?></div>
                                    </td>
                                    <td>
                                        <?php if ($row['gpa'] > 0) echo $row['gpa'] . ' / ' . $row['gla']; ?>
                                    </td>
                                    <td class="text-center" id="action-cell-<?= $row['stid'] ?>">
                                        <?php
                                        $test_sql = "SELECT id FROM testimonial WHERE stid='{$row['stid']}' AND sccode='$sccode' AND pubexam = '$exam2'";
                                        $test_res = $conn->query($test_sql);
                                        if ($test_res->num_rows > 0) {
                                            echo '<button class="btn btn-sm btn-success" onclick="printSingle(' . $row['stid'] . ')">Print</button>';
                                        } else if (empty($row['regdno']) || empty($row['sscroll'])) {
                                            echo '<button class="btn btn-sm btn-danger" onclick="issue(' . $row['stid'] . ')">Modify</button>';
                                        } else if ($row['gpa'] < 1) {
                                            echo '<button class="btn btn-sm btn-warning" onclick="resultEntry(' . $row['sscroll'] . ')">Result</button>';
                                        } else {
                                            echo '<button class="btn btn-sm btn-primary" onclick="issue(' . $row['stid'] . ')">Issue</button>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="7" class="text-center">No students found.</td></tr>';
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center">Please select Class and Section.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    function chainBtnFunc() {
        location.reload();
    }

    function issue(stid) {
        var infor = "stid=" + stid + "&year=" + '<?= $sessionyear ?>' + "&sec=" + '<?= $sec2 ?>';
        var actionCell = $("#action-cell-" + stid);

        actionCell.html('<small>Processing...</small>');

        $.ajax({
            type: "POST",
            url: "backend/issue-testimonial.php",
            data: infor,
            cache: false,
            success: function (html) {
                actionCell.html(html);
            }
        });
    }

    function printSingle(stid) {
        var year = '<?= $sessionyear ?>';
        var sec = '<?= $sec2 ?>';
        var exam = document.getElementById('exam')?.value || 'SSC';
        window.open(`testimonial-print.php?sec=${sec}&exam=${exam}&year=${year}&stid=${stid}`, '_blank');
    }

    function printSelected() {
        let ids = Array.from(document.querySelectorAll('.st-check:checked')).map(cb => cb.value);
        if (ids.length === 0) {
            alert("Please select at least one student.");
            return;
        }
        var year = '<?= $sessionyear ?>';
        var sec = '<?= $sec2 ?>';
        var exam = document.getElementById('exam')?.value || 'SSC';
        window.open(`testimonial-print.php?sec=${sec}&exam=${exam}&year=${year}&stids=${ids.join(',')}`, '_blank');
    }

    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('.st-check').forEach(cb => cb.checked = this.checked);
    });

    // More JS functions for result entry can be added here if needed.
</script>
</body>

</html>