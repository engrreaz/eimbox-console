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
                                <?php
                                $is_issued_query = "SELECT id FROM testimonial WHERE stid='{$row['stid']}' AND sccode='$sccode' AND pubexam = '$exam2'";
                                $is_issued_res = $conn->query($is_issued_query);
                                $is_printable = $is_issued_res->num_rows > 0;
                                ?>
                                <tr>
                                    <td><input type="checkbox" class="form-check-input st-check"
                                            value="<?= $row['stid'] ?>" <?= !$is_printable ? 'disabled' : '' ?>></td>
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
                                        if ($is_printable) {
                                            echo '<button class="btn btn-sm btn-success" onclick="printSingle(' . $row['stid'] . ')">Print</button>';
                                        } else if (empty($row['regdno']) || empty($row['sscroll'])) {
                                            // Modify বাটন, যা মডাল খুলবে
                                            echo '<button class="btn btn-sm btn-danger" onclick="openModifyModal(\'' . $row['stid'] . '\', \'' . $row['sscroll'] . '\', \'' . $row['regdno'] . '\')">Modify</button>';
                                        } else if ($row['gpa'] < 1) {
                                            // রেজাল্ট এন্ট্রি বাটন
                                            echo '<button class="btn btn-sm btn-warning" onclick="resultEntry(' . $row['sscroll'] . ')">Result</button>';
                                        } else {
                                            // ইস্যু বাটন
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

<!-- Modify Student Info Modal -->
<div class="modal fade" id="modifyStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Student Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="modifyStudentForm">
                    <input type="hidden" id="modal_stid" name="stid">
                    <div class="mb-3">
                        <label for="modal_sscroll" class="form-label">Board Roll</label>
                        <input type="text" class="form-control" id="modal_sscroll" name="sscroll" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_regdno" class="form-label">Registration No</label>
                        <input type="text" class="form-control" id="modal_regdno" name="regdno" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveStudentRegdInfo()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    function chainBtnFunc() {
        location.reload();
    }

    const modifyModal = new bootstrap.Modal(document.getElementById('modifyStudentModal'));

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

    function openModifyModal(stid, sscroll, regdno) {
        document.getElementById('modal_stid').value = stid;
        document.getElementById('modal_sscroll').value = sscroll;
        document.getElementById('modal_regdno').value = regdno;
        modifyModal.show();
    }

    function saveStudentRegdInfo() {
        const stid = document.getElementById('modal_stid').value;
        const sscroll = document.getElementById('modal_sscroll').value;
        const regdno = document.getElementById('modal_regdno').value;

        if (!sscroll || !regdno) {
            alert('Board Roll and Registration No cannot be empty.');
            return;
        }

        $.ajax({
            url: 'backend/update-student-regd.php',
            type: 'POST',
            data: { stid: stid, sscroll: sscroll, regdno: regdno },
            success: function (response) {
                alert('Information updated successfully!');
                modifyModal.hide();
                location.reload(); // রিলোড করে নতুন তথ্য ও বাটন দেখানো
            },
            error: function () {
                alert('An error occurred. Please try again.');
            }
        });
    }

    // More JS functions for result entry can be added here if needed.
</script>
</body>

</html>