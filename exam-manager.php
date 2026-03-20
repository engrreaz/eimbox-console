<?php include 'header.php';

$slot = $_GET['slot'] ?? 'School';          // default প্রথম অপশন
$session = $_GET['session'] ?? date('Y');  // default চলমান বছর


?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-end mb-2 gap-2">



        <div class="flex-grow-1">Examination List</div>



        <!-- Session Filter -->
        <!-- Slot Filter -->
        <div>
            <label class="form-label mb-0">Slot</label>
            <select id="slotFilter" class="form-select form-select-sm">
                <?php
                $slotRes = $conn->query("SELECT slotname FROM slots where sccode='$sccode' ORDER BY id ASC");
                while ($s = $slotRes->fetch_assoc()) {
                    echo "<option value=\"" . htmlspecialchars($s['slotname']) . "\">"
                        . htmlspecialchars($s['slotname']) . "</option>";
                }
                ?>
            </select>
        </div>

        <!-- Session Filter -->
        <div>
            <label class="form-label mb-0">Session</label>
            <select id="sessionFilter" class="form-select form-select-sm">
                <?php
                $sesRes = $conn->query("SELECT syear FROM sessionyear WHERE active=1 and sccode='$sccode' ORDER BY syear DESC");
                while ($y = $sesRes->fetch_assoc()) {
                    echo "<option value=\"" . htmlspecialchars($y['syear']) . "\">"
                        . htmlspecialchars($y['syear']) . "</option>";
                }
                ?>
            </select>
        </div>
        <button class="btn btn-primary btn-sm" id="addNewBtn">+ Add Exam</button>



    </div>

    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>SC Code</th>
                <th>Session</th>
                <th>Title</th>
                <th>Class</th>
                <th>Start Date</th>
                <th>Status</th>
                <th width="120">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = $conn->query("SELECT * FROM examlist where sccode='$sccode' and sessionyear='$session' and slot='$slot'ORDER BY id DESC");
            while ($row = $res->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['sccode'] ?></td>
                    <td><?= $row['sessionyear'] ?></td>
                    <td><?= $row['examtitle'] ?></td>
                    <td><?= $row['classname'] ?></td>
                    <td><?= $row['datestart'] ?></td>
                    <td><?= $row['status'] ? 'Active' : 'Inactive' ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm editBtn" data-id='<?= json_encode($row) ?>'>Edit</button>
                        <button class="btn btn-danger btn-sm delBtn" data-id="<?= $row['id'] ?>">Del</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>



<div class="modal fade" id="examModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="examForm">
                <div class="modal-header">
                    <h5 class="modal-title">Exam Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row g-2">

                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="mode" id="mode">

                    <div class="col-md-4">
                        <label>SC Code</label>
                        <input type="number" name="sccode" id="sccode" value="<?= $sccode ?>"
                            class="form-control form-control-sm">
                    </div>

                    <div class="col-md-4">
                        <label>Session Year</label>
                        <input type="text" name="sessionyear" id="sessionyear" value="<?= $session ?>"
                            class="form-control form-control-sm">
                    </div>

                    <div class="col-md-4">
                        <label>Slot</label>
                        <input type="text" name="slot" id="slot" value="<?= $slot ?>"
                            class="form-control form-control-sm">
                    </div>

                    <div class="col-md-6">
                        <label>Exam Title</label>
                        <input type="text" name="examtitle" id="examtitle" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-6">
                        <label>Exam Code</label>
                        <input type="text" name="examcode" id="examcode" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-4">
                        <label>Class Name</label>
                        <input type="text" name="classname" id="classname" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-4">
                        <label>Section</label>
                        <input type="text" name="sectionname" id="sectionname" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-4">
                        <label>Start Date</label>
                        <input type="date" name="datestart" id="datestart" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-6">
                        <label>Result Publish</label>
                        <input type="datetime-local" name="result_publish" id="result_publish"
                            class="form-control form-control-sm">
                    </div>

                    <div class="col-md-6">
                        <label>Status</label>
                        <select name="status" id="status" class="form-select form-select-sm">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success btn-sm">Save</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>


<script>
    const modal = new bootstrap.Modal(document.getElementById('examModal'));

    document.getElementById('addNewBtn').onclick = () => {
        examForm.reset();
        id.value = '';
        mode.value = 'add';
        modal.show();
    };

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.onclick = () => {
            let d = JSON.parse(btn.dataset.id);
            mode.value = 'edit';
            id.value = d.id;
            sccode.value = d.sccode;
            sessionyear.value = d.sessionyear;
            slot.value = d.slot;
            examtitle.value = d.examtitle;
            examcode.value = d.examcode;
            classname.value = d.classname;
            sectionname.value = d.sectionname;
            datestart.value = d.datestart;
            result_publish.value = d.result_publish?.replace(' ', 'T');
            status.value = d.status;
            modal.show();
        }
    });

    examForm.onsubmit = e => {
        e.preventDefault();
        fetch('exam/exam_save.php', {
            method: 'POST',
            body: new FormData(examForm)
        })
            .then(r => r.text())
            .then(res => {
                if (res == 'ok') location.reload();
                else alert(res);
            });
    };

    document.querySelectorAll('.delBtn').forEach(btn => {
        btn.onclick = () => {
            if (!confirm('Delete this exam?')) return;
            fetch('exam/exam_delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + btn.dataset.id
            })
                .then(r => r.text())
                .then(res => {
                    if (res == 'ok') location.reload();
                    else alert(res);
                });
        }
    });


</script>


<script>
    const urlParams = new URLSearchParams(window.location.search);

    let slotVal = urlParams.get('slot') || 'School';
    let sessionVal = urlParams.get('session') || '<?= date('Y') ?>';

    document.getElementById('slotFilter').value = slotVal;
    document.getElementById('sessionFilter').value = sessionVal;
</script>

<script>
    function applyFilter() {
        const slot = document.getElementById('slotFilter').value;
        const session = document.getElementById('sessionFilter').value;
        window.location.href = `?slot=${encodeURIComponent(slot)}&session=${encodeURIComponent(session)}`;
    }

    document.getElementById('slotFilter').onchange = applyFilter;
    document.getElementById('sessionFilter').onchange = applyFilter;
</script>

</body>

</html>