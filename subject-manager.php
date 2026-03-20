<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    // -----------------------------
// Input & Defaults
// -----------------------------
    $slot = $_COOKIE['chain-slot'] ?? '';
    $sessionyear = $_COOKIE['chain-session'] ?? '';
    $cls = $_COOKIE['chain-class'] ?? '';
    $sec = $_COOKIE['chain-section'] ?? '';



    $years = [];
    $q = "SELECT syear FROM sessionyear Where sccode='$sccode' AND active=1 ORDER BY syear DESC";
    $r = $conn->query($q);
    while ($row = $r->fetch_assoc()) {
        $years[] = $row['syear'];
    }


    // -----------------------------
// Helpers
// -----------------------------
    function selected($a, $b)
    {
        return $a == $b ? 'selected' : '';
    }
    ?>

    <!-- ================= FILTER BAR ================= -->
    <?php
    $chain_param = '-c 10 -t Choose Values -u -r -b View List';
    include 'components/slot-tree-ui.php';
    ?>


    <?php if (!$slot || !$sessionyear || !$cls || !$sec): ?>
        <div class="alert alert-warning">Select Slot, Year, Class & Section</div>
    <?php else: ?>

        <!-- ================= ACTION BAR ================= -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Subjects — <?php echo "$cls ($sec)"; ?></h5>
            <div class="btn-group">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#subjectModal" onclick="openAdd()">+
                    Add Subject</button>
                <button class="btn btn-warning" onclick="reorder_row()">Update Re-order</button>
                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#defaultModal">Default
                    Settings</button>
            </div>
        </div>

        <!-- ================= TABLE ================= -->
        <div class="card">
            <div class=" table-responsive">
                <table class="table table-hover table-sm" id="myTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:30px; padding:5px;"></th>
                            <th>#</th>
                            <th hidden>ID</th>
                            <th>Code</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $sl = 1;
                        $unique_ids = []; // ডুপ্লিকেট ট্র্যাক করার জন্য
                        $q = "SELECT * FROM subsetup
                            WHERE slot='$slot' AND sccode='$sccode' AND sessionyear='$sessionyear'
                            AND classname='$cls' AND sectionname='$sec'
                            ORDER BY slno, subject";
                        $r = $conn->query($q);

                        if ($r->num_rows):
                            while ($row = $r->fetch_assoc()):
                                // এখানে ধরো subcode unique
                                if (in_array($row['subject'], $unique_ids)) {
                                    $hx = $row['id'];
                                    $conn->query("DELETE FROM subsetup WHERE sccode='$sccode' AND id='$hx'");
                                    continue; // ডুপ্লিকেট হলে স্কিপ
                                }
                                $unique_ids[] = $row['subject'];

                                $id = $row['id'];
                                $sub = $row['subject'];
                                $tid = $row['tid'];

                                $sq = "SELECT subject FROM subjects
                                    WHERE (sccode='$sccode' OR sccode=0) AND subcode='$sub' AND sccategory='$sctype'
                                    ORDER BY sccode DESC LIMIT 1";
                                $sr = $conn->query($sq);
                                $subname = $sr->num_rows ? $sr->fetch_assoc()['subject'] : '';

                                $tq = "SELECT tname FROM teacher WHERE sccode='$sccode' AND tid='$tid'";
                                $tr = $conn->query($tq);
                                $tname = $tr->num_rows ? $tr->fetch_assoc()['tname'] : '';

                                ?>
                                <tr data-id="<?php echo $id; ?>">
                                    <td><i class="bi bi-grip-vertical"></i></td>
                                    <td><?php echo $sl++; ?></td>
                                    <td hidden><?php echo $id; ?></td>
                                    <td><?php echo $sub; ?></td>
                                    <td><?php echo $subname; ?></td>
                                    <td><?php echo $tname; ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-info" onclick="openEdit(<?php echo $id; ?>)">Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="openDelete(<?php echo $id; ?>)">Del</button>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    No subjects found<br><br>
                                    <button class="btn btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#defaultModal">
                                        Apply Default List
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</div>



<!-- Delete Modal -->
<div class="modal fade" id="deleteModal">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <input type="hidden" id="delid">
                <h6 class="mb-3">Delete this subject?</h6>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- ================= SUBJECT MODAL ================= -->
<div class="modal fade" id="subjectModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subjectModalTitle">Add Subject</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="mid">

                <div class="mb-2">
                    <label>Subject</label>
                    <select id="msub" class="form-control"></select>
                </div>

                <div class="mb-2">
                    <label>Teacher</label>
                    <select id="mtid" class="form-control"></select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveSubject()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= DEFAULT SETTINGS MODAL ================= -->
<!-- Default Settings Modal -->
<div class="modal fade" id="defaultModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clone Default Subject List</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-md-3">
                        <label>Source Year</label>
                        <select id="d_year" class="form-select form-select-sm"></select>
                    </div>

                    <div class="col-md-3">
                        <label>Source Class</label>
                        <select id="d_cls" class="form-select form-select-sm"></select>
                    </div>

                    <div class="col-md-3">
                        <label>Source Section</label>
                        <select id="d_sec" class="form-select form-select-sm"></select>
                    </div>

                    <div class="col-md-3">
                        <label>Or Global Default</label>
                        <select id="d_global" class="form-select form-select-sm">
                            <option value="">No</option>
                            <option value="1">Default Subject List</option>
                        </select>
                    </div>

                </div>

 

                <div class="border rounded p-2 mt-2" style="max-height:300px; overflow-y:auto;">
                    <div id="previewList" class="small text-muted">
                        Select source to preview subjects...
                    </div>
                </div>

                <button class="btn btn-primary mt-3" onclick="applyDefault()">
                    Clone Subject List
                </button>
                <div id="defaultMsg" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>

<!-- ----------------------------------- -->




<script>


    const years = <?= json_encode($years) ?>;
    console.log(years);

    function chainBtnFunc() {
        window.location.reload();
    }

    function go() {
        const u = slot.value, y = sessionyear.value, c = cls.value, s = sec.value;
        location.href = `subjects-manager.php?u=${u}&y=${y}&c=${c}&s=${s}`;
    }







    function deleteRow(id) {
        if (!confirm('Delete subject?')) return;
        const data = new URLSearchParams({ id });
        fetch('subject/delete-subject.php', { method: 'POST', body: data })
            .then(r => r.text()).then(() => location.reload());
    }

    function applyDefault() {
        const data = new URLSearchParams({
            year: sessionyear.value, cls: cls.value, sec: sec.value, u: slot.value,
            src_year: d_year.value, src_cls: d_cls.value, src_sec: d_sec.value,
            global: d_global.value
        });
        fetch('subject/clone-subjects.php', { method: 'POST', body: data })
            .then(r => r.text()).then(t => { defaultMsg.innerHTML = t; 
            // setTimeout(() => location.reload(), 800);
         });
    }

    // Drag Drop
    const tbody = document.querySelector('#myTable tbody');
    let drag;

    tbody?.querySelectorAll('tr').forEach(tr => {
        tr.draggable = true;
        tr.ondragstart = () => drag = tr;
        tr.ondragover = e => e.preventDefault();
        tr.ondrop = e => {
            e.preventDefault();
            if (drag !== tr) tbody.insertBefore(drag, tr);
        };
    });


</script>

<script>
    function reorder_row() {
        const rows = document.querySelectorAll('#myTable tbody tr');
        const ids = [];

        rows.forEach(tr => {
            ids.push(tr.dataset.id);
        });

        const data = new URLSearchParams({
            ids: ids.join(',')
        });

        fetch('subject/reorder-subjects.php', {
            method: 'POST',
            body: data
        })
            .then(r => r.text())
            .then(msg => {
                if (msg === 'OK') {
                    location.reload();
                } else {
                    alert(msg);
                }
            });
    }
</script>

<script>
    function saveSubject() {

        $.post('subject/save-subject.php', {
            id: $('#mid').val(),
            sub: $('#msub').val(),
            tid: $('#mtid').val(),
            year: '<?= $sessionyear ?>',
            cls: '<?= $cls ?>',
            sec: '<?= $sec ?>',
            u: '<?= $slot ?>'
        }, function (msg) {
            if (msg === 'OK') {
                location.reload();
            } else {
                alert(msg);
            }
        });

    }
</script>

<script>
    function openDelete(id) {
        delid.value = id;
        new bootstrap.Modal(deleteModal).show();
    }

    function confirmDelete() {
        const data = new URLSearchParams({ id: delid.value });

        fetch('subject/delete-subject.php', {
            method: 'POST',
            body: data
        })
            .then(r => r.text())
            .then(msg => {
                if (msg === 'OK') {
                    location.reload();
                } else {
                    alert(msg);
                }
            });
    }
</script>


<script>
    function loadSubjects(selected = '') {
        fetch('subject/get-subject-options.php')
            .then(r => r.text())
            .then(html => {
                msub.innerHTML = html;
                if (selected) msub.value = selected;
            });
    }

    function loadTeachers(selected = '') {
        fetch('subject/get-teacher-options.php')
            .then(r => r.text())
            .then(html => {
                mtid.innerHTML = html;
                if (selected) mtid.value = selected;
            });
    }

    function openAdd() {
        mid.value = '0';
        subjectModalTitle.innerText = 'Add Subject';
        loadSubjects();
        loadTeachers();
    }

    function openEdit(id) {
        subjectModalTitle.innerText = 'Edit Subject';
        fetch('subject/get-subject-row.php?id=' + id)
            .then(r => r.json())
            .then(d => {
                mid.value = d.id;
                loadSubjects(d.subject);
                loadTeachers(d.tid);
                new bootstrap.Modal(subjectModal).show();
            });
    }
</script>

<script>
    document.getElementById('defaultModal')
        .addEventListener('show.bs.modal', function () {

            fetch('subject/get-clone-tree.php')
                .then(r => r.json())
                .then(data => {
                    // years এর ক্ষেত্রে খালি অপশন
                    let yearOptions = '<option value="">Select Year</option>'; // খালি option
                    yearOptions += data.years; // সার্ভার থেকে আসা সব year option
                    // yearOptions += '<option value="' + data.years + '">' + data.years + '</option>';
                    d_year.innerHTML = yearOptions;
                    d_cls.innerHTML = '<option value="">Select Class</option>';
                    d_sec.innerHTML = '<option value="">Select Section</option>';
                });
        });

    d_year.onchange = function () {
        fetch('subject/get-clone-tree.php?year=' + this.value)
            .then(r => r.json())
            .then(data => {
                d_cls.innerHTML = '<option value="">Select Class</option>' + data.classes;
                d_sec.innerHTML = '<option value="">Select Section</option>';
            });
    };

    d_cls.onchange = function () {
        fetch('subject/get-clone-tree.php?year=' + d_year.value + '&cls=' + this.value)
            .then(r => r.json())
            .then(data => {
                d_sec.innerHTML = '<option value="">Select Section</option>' + data.sections;
            });
    };
</script>


<script>
    function applyDefault() {
        const act = document.querySelector('input[name="act"]:checked')?.value || '';

        const data = new URLSearchParams({
            tgt_year: '<?= $sessionyear ?>',
            tgt_cls: '<?= $cls ?>',
            tgt_sec: '<?= $sec ?>',
            tgt_slot: '<?= $slot ?>',

            src_year: document.getElementById('d_year').value,
            src_cls: document.getElementById('d_cls').value,
            src_sec: document.getElementById('d_sec').value,
            global: document.getElementById('d_global').value,

            ids: document.getElementById('ids').value,
            ids_ex: document.getElementById('ids_ex').value,
            act: act
        });


        const defaultMsg = document.getElementById('defaultMsg');
        defaultMsg.innerHTML = 'Cloning...';

        fetch('subject/clone-subjects.php', {
            method: 'POST',
            body: data
        })
            .then(r => r.text())
            .then(msg => {
                if (msg.trim() === 'OK') {
                    defaultMsg.innerHTML = '<span class="text-success">Done</span>';
                    setTimeout(() => location.reload(), 800);
                } else {
                    defaultMsg.innerHTML = '<span class="text-danger">' + msg + '</span>';
                }
            })
            .catch(err => {
                defaultMsg.innerHTML = '<span class="text-danger">Error: ' + err + '</span>';
            });
    }
</script>

<script>
    function loadPreview() {

        const year = $('#d_year').val();
        const cls = $('#d_cls').val();
        const sec = $('#d_sec').val();
        const global = $('#d_global').val();

        const sl = $('#slot_main').val();
        const yr = $('#session_main').val();
        const cll = '<?php echo $cls; ?>';
        const see = '<?php echo $sec; ?>';




        if (!global && (!year || !cls || !sec)) {
            $('#previewList').html('Select source to preview subjects...');
            return;
        }

        $('#previewList').html('Loading...');

        $.post('subject/preview-subjects.php', {
            year: year,
            cls: cls,
            sec: sec,
            global: global,
            sl: sl,
            yr: yr,
            cl: cll,
            se: see
        }, function (html) {
            $('#previewList').html(html);
        });
    }

    // change events
    $('#d_year, #d_cls, #d_sec, #d_global').on('change', loadPreview);
</script>

<!-- ----------------------------------- -->
</body>

</html>