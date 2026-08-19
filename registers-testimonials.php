<?php require_once 'header.php'; ?>

<?php
$slot = $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_COOKIE['chain-session'] ?? date('Y');
$cls2 = $_COOKIE['chain-class'] ?? '';
$sec2 = $_COOKIE['chain-section'] ?? '';
$exam2 = $_GET['exam'] ?? 'SSC';

// Fetch scinfo data for center_code, center_name, ed_board
$scinfo_data = [];
$scinfo_query = $conn->prepare("SELECT center_code, center_name, ed_board FROM scinfo WHERE sccode = ? LIMIT 1");
if ($scinfo_query) {
    $scinfo_query->bind_param("s", $sccode);
    $scinfo_query->execute();
    $scinfo_result = $scinfo_query->get_result();
    if ($scinfo_result && $scinfo_result->num_rows > 0) {
        $scinfo_data = $scinfo_result->fetch_assoc();
    }
    $scinfo_query->close();
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h3 class="d-print-none">Testimonial Issue Register</h3>
     <?php
                $chain_param = '-c 12 -t Choose Class & Section -u -r -b Show List';
                include 'components/slot-tree-ui.php';
                ?>

                
    <div class="card mb-4 d-print-none">
        <div class="card-body">
            <div class="row g-3 align-items-end">               
                <div class="col-md-2">
                    <label class="form-label">Exam</label>
                    <select class="form-select" id="exam">
                        <option value="SSC" <?= ($exam2 == 'SSC') ? 'selected' : '' ?>>SSC</option>
                        <option value="HSC" <?= ($exam2 == 'HSC') ? 'selected' : '' ?>>HSC</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Center Code & Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($scinfo_data['center_name'] ?? '') . ' (' . htmlspecialchars($scinfo_data['center_code'] ?? '') . ')' ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Education Board</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($scinfo_data['ed_board'] ?? '') ?>" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#centerInfoModal">Update Center Info</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Student List for Testimonial</h5>
            <div>
                <button class="btn btn-sm btn-info" onclick="printSelected()"><i class="bi bi-printer me-1"></i> Print Selected</button>
                <!-- <button class="btn btn-sm btn-warning" onclick="resultEntry(0)"><i class="bi bi-pencil-square me-1"></i> Result Entry</button> -->
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="checkAll" class="form-check-input"></th>
                        <th>Class Roll</th>
                        <th>Student Name</th>
                        <th>Parents</th>
                        <th>Board Roll / Regd No</th>
                        <th>Result (GPA/Grade)</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($cls2) && !empty($sec2)) {
                        $sql0 = "SELECT si.id as session_id, si.rollno as classroll, si.stid, s.*, s.sscpassyear FROM sessioninfo si JOIN students s ON si.stid = s.stid AND si.sccode = s.sccode WHERE si.sessionyear = '$sessionyear' AND si.sccode='$sccode' AND si.classname='$cls2' AND si.sectionname = '$sec2' ORDER BY si.rollno";
                        $result0 = $conn->query($sql0);
                        if ($result0->num_rows > 0) {
                            while ($row = $result0->fetch_assoc()) {
                                ?>
                                <?php
                                $is_issued_query = "SELECT id FROM testimonial WHERE stid='{$row['stid']}' AND sccode='$sccode' AND pubexam = '$exam2'";
                                $is_issued_res = $conn->query($is_issued_query);
                                $is_printable = $is_issued_res->num_rows > 0;

                                // Determine row color based on status
                                $is_data_updated = !empty($row['regdno']) && !empty($row['rollno']) && $row['gpa'] > 0;
                                $row_class = '';
                                if ($is_printable) {
                                    $row_class = 'table-success'; // Issued
                                } elseif ($is_data_updated) {
                                    $row_class = 'table-primary'; // Ready to be issued
                                } else {
                                    $row_class = 'table-warning'; // Data missing
                                }
                                ?>
                                <tr id="student-row-<?= $row['stid'] ?>" class="<?= $row_class ?>">
                                    <td><input type="checkbox" class="form-check-input st-check"
                                            value="<?= $row['stid'] ?>" <?= !$is_printable ? 'disabled' : '' ?>></td>
                                    <td id="class_roll_<?= $row['stid'] ?>"><?= $row['classroll'] ?></td>
                                    <td>
                                        <div><?= $row['stnameeng'] ?></div>
                                        <small class="text-muted"><?= $row['stnameben'] ?></small>
                                    </td>
                                    <td>
                                        <div>F: <span id="fname_<?= $row['stid'] ?>"><?= $row['fname'] ?></span></div>
                                        <div>M: <span id="mname_<?= $row['stid'] ?>"><?= $row['mname'] ?></span></div>
                                    </td>
                                    <td>
                                        <div>Roll : <span id="board_roll_<?= $row['stid'] ?>"><?= $row['rollno'] ?></span></div>
                                        <div>Regd : <span id="regd_no_<?= $row['stid'] ?>"><?= $row['regdno'] ?></span></div>
                                    </td>
                                    <td>
                                        <span id="gpa_<?= $row['stid'] ?>"><?= $row['gpa'] > 0 ? $row['gpa'] : '' ?></span>
                                        <span id="gla_<?= $row['stid'] ?>"><?= $row['gpa'] > 0 ? ' / ' . $row['gla'] : '' ?></span>
                                    </td>
                                     <td class="text-center" id="action-cell-<?= $row['stid'] ?>">
                                         <div class="dropdown">
                                             <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                 <i class="bi bi-three-dots-vertical"></i>
                                             </button>
                                             <div class="dropdown-menu dropdown-menu-end">
                                                 <?php
                                                 // Always show Update Info, added gender and dob
                                                 echo '<a class="dropdown-item" href="javascript:void(0);" onclick="openModifyModal(\'' . $row['stid'] . '\', \'' . addslashes($row['stnameeng']) . '\', \'' . addslashes($row['stnameben']) . '\', \'' . addslashes($row['fname']) . '\', \'' . addslashes($row['mname']) . '\', \'' . $row['rollno'] . '\', \'' . $row['regdno'] . '\', \'' . $row['gpa'] . '\', \'' . $row['sscpassyear'] . '\', \'' . $row['gender'] . '\', \'' . $row['dob'] . '\')"><i class="bi bi-pencil-square me-2"></i> Update Info</a>';
 
                                                 if ($is_printable) {
                                                     // If issued, show all three
                                                     echo '<a class="dropdown-item" href="javascript:void(0);" onclick="resultEntry(\'' . $row['rollno'] . '\')"><i class="bi bi-card-list me-2"></i> Update Result</a>';
                                                     echo '<a class="dropdown-item" href="javascript:void(0);" onclick="issue(\'' . $row['stid'] . '\')"><i class="bi bi-arrow-repeat me-2"></i> Re-issue Testimonial</a>';
                                                     echo '<a class="dropdown-item text-success" href="javascript:void(0);" onclick="printSingle(\'' . $row['stid'] . '\')"><i class="bi bi-printer me-2"></i> Print</a>';
                                                 } elseif ($is_data_updated) {
                                                     // If data is updated but not issued, show Update Result and Issue Testimonial
                                                     echo '<a class="dropdown-item" href="javascript:void(0);" onclick="resultEntry(\'' . $row['rollno'] . '\')"><i class="bi bi-card-list me-2"></i> Update Result</a>';
                                                     echo '<a class="dropdown-item" href="javascript:void(0);" onclick="issue(\'' . $row['stid'] . '\')"><i class="bi bi-file-earmark-check me-2"></i> Issue Testimonial</a>';
                                                 }
                                                 ?>
                                             </div>
                                         </div>
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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_stnameeng" class="form-label">Student Name (Eng)</label>
                            <input type="text" class="form-control" id="modal_stnameeng" name="stnameeng" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_stnameben" class="form-label">Student Name (Bn)</label>
                            <input type="text" class="form-control" id="modal_stnameben" name="stnameben">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_fname" class="form-label">Father's Name</label>
                            <input type="text" class="form-control" id="modal_fname" name="fname">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_mname" class="form-label">Mother's Name</label>
                            <input type="text" class="form-control" id="modal_mname" name="mname">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_gender" class="form-label">Gender</label>
                            <select class="form-select" id="modal_gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_dob" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="modal_dob" name="dob">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="modal_sscroll" class="form-label">Board Roll</label>
                            <input type="text" class="form-control" id="modal_sscroll" name="rollno" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="modal_regdno" class="form-label">Registration No</label>
                            <input type="text" class="form-control" id="modal_regdno" name="regdno" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="modal_gpa" class="form-label">Result (GPA)</label>
                            <input type="text" class="form-control" id="modal_gpa" name="gpa">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="modal_passing_year" class="form-label">Passing Year</label>
                            <select class="form-select" id="modal_passing_year" name="passing_year">
                                <?php
                                $current_year = date('Y');
                                for ($y = $current_year; $y >= $current_year - 10; $y--) { // Show current year and 10 years back
                                    echo '<option value="' . $y . '">' . $y . '</option>';
                                }
                                ?>
                            </select>
                        </div>
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

<!-- Center Info Modal -->
<div class="modal fade" id="centerInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Center Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="centerInfoForm">
                    <div class="mb-3">
                        <label for="modal_center_code" class="form-label">Center Code</label>
                        <input type="text" class="form-control" id="modal_center_code" name="center_code" value="<?= htmlspecialchars($scinfo_data['center_code'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="modal_center_name" class="form-label">Center Name</label>
                        <input type="text" class="form-control" id="modal_center_name" name="center_name" value="<?= htmlspecialchars($scinfo_data['center_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="modal_ed_board" class="form-label">Education Board</label>
                        <input type="text" class="form-control" id="modal_ed_board" name="ed_board" value="<?= htmlspecialchars($scinfo_data['ed_board'] ?? '') ?>">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="saveCenterInfo()">Save Changes</button>
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
        var exam = document.getElementById('exam').value;
        var infor = "stid=" + stid + "&year=" + '<?= $sessionyear ?>' + "&sec=" + '<?= $sec2 ?>' + "&exam=" + exam;
        var actionCell = $("#action-cell-" + stid);

        actionCell.html('<small>Processing...</small>');

        $.ajax({
            type: "POST",
            url: "backend/issue-testimonial.php",
            data: infor,
            dataType: 'json', // Expect a JSON response
            cache: false,
            success: function (response) {
                if (response.status === 'success') {
                    showToast('success', response.message, 'Success');
                   actionCell.html(response.action_html);
                } else {
                    showToast('error', response.message, 'Error');
                    actionCell.html('Failed'); // Or restore previous state
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // AJAX রিকোয়েস্ট ব্যর্থ হলে এই কোডটি কাজ করবে
                showToast('error', 'An unexpected error occurred. Please check the console.', 'AJAX Error');
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);

                // আগের অবস্থায় ফিরিয়ে আনার জন্য আবার action cell এর কন্টেন্ট লোড করা হচ্ছে
                $.get(`backend/get-testimonial-action-cell.php?stid=${stid}&exam=${exam}`, (actionHtml) => actionCell.html(actionHtml));
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
        document.querySelectorAll('.st-check:not(:disabled)').forEach(cb => cb.checked = this.checked);
    });

    function openModifyModal(stid, stnameeng, stnameben, fname, mname, rollno, regdno, gpa, passing_year, gender, dob) {
        document.getElementById('modal_stid').value = stid;
        document.getElementById('modal_stnameeng').value = stnameeng;
        document.getElementById('modal_stnameben').value = stnameben;
        document.getElementById('modal_fname').value = fname;
        document.getElementById('modal_mname').value = mname;
        document.getElementById('modal_sscroll').value = rollno;
        document.getElementById('modal_regdno').value = regdno;
        document.getElementById('modal_gpa').value = gpa;
        document.getElementById('modal_passing_year').value = passing_year;
        document.getElementById('modal_gender').value = gender;
        document.getElementById('modal_dob').value = dob;
        modifyModal.show();
    }

    function saveStudentRegdInfo() {
        const form = document.getElementById('modifyStudentForm');
        const formData = new FormData(form);

        if (!formData.get('rollno') || !formData.get('regdno')) {
            alert('Board Roll and Registration No are required.');
            return;
        }

        $.ajax({
            url: 'backend/update-student-regd.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json', // Expect JSON from update-student-regd.php
            success: function (response) {
                if (response.status === 'success') {
                    showToast('success', 'Information updated successfully!', 'Success');
                    modifyModal.hide();

                    const stid = formData.get('stid');
                    const stnameeng = formData.get('stnameeng');
                    const stnameben = formData.get('stnameben');
                    const fname = formData.get('fname');
                    const mname = formData.get('mname');
                    // Update table cells instantly
                    document.getElementById('board_roll_' + stid).textContent = formData.get('rollno');
                    document.getElementById('regd_no_' + stid).textContent = formData.get('regdno');
                    document.getElementById('gpa_' + stid).textContent = formData.get('gpa') > 0 ? formData.get('gpa') : '';
                    document.getElementById('gla_' + stid).textContent = formData.get('gpa') > 0 ? ' / ' + response.gla : '';
                    document.getElementById('fname_' + stid).textContent = formData.get('fname');
                    document.getElementById('mname_' + stid).textContent = formData.get('mname');

                    // Update student name in the table
                    const studentNameCell = document.getElementById('student-row-' + stid).cells[2];
                    studentNameCell.innerHTML = `<div>${stnameeng}</div><small class="text-muted">${stnameben}</small>`;


                    // Fetch and update the action cell
                    $.get(`backend/get-testimonial-action-cell.php?stid=${stid}&exam=${$('#exam').val()}`, function(actionHtml) {
                        $('#action-cell-' + stid).html(actionHtml);
                    });

                    // Update row color
                    const isDataUpdated = formData.get('rollno') && formData.get('regdno') && formData.get('gpa') > 0;
                    const row = $('#student-row-' + stid);
                    row.removeClass('table-warning table-primary table-success');
                    if (isDataUpdated) {
                        row.addClass('table-primary'); // Not issued yet, but data is complete
                    } else {
                        row.addClass('table-warning');
                    }
                } else {
                    alert(response.message || 'An error occurred.');
                }
            },
            error: function () {
                alert('An error occurred. Please try again.');
            }
        });
    }

    function saveCenterInfo() {
        const form = document.getElementById('centerInfoForm');
        const formData = new FormData(form);

        $.ajax({
            url: 'backend/update-center-info.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    showToast('success', 'Center info updated successfully!', 'Success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast('error', response.message || 'An error occurred.', 'Error');
                }
            }
        });
    }

    // More JS functions for result entry can be added here if needed.
</script>
</body>

</html>