<?php
include 'header.php'; // already includes config & db
$slotQ = mysqli_query($conn, "SELECT * FROM slots WHERE sccode='$sccode' ORDER BY id DESC");
$slotCount = mysqli_num_rows($slotQ);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between mb-2">

        <?php if ($slotCount == 0) { ?>
            <button class="btn btn-warning btn-sm" onclick="setDefault()">Set Default</button>
        <?php } else { ?>
            <button class="btn btn-primary btn-sm" onclick="openCreate()">+ Add Slot</button>
        <?php } ?>
    </div>
    <?php if ($slotCount == 0) { ?>
        <div class="alert alert-danger">No Slot/Unit Found</div>
    <?php } ?>

    <div class="card">
        <table class="table table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Slot Name</th>
                    <th>Merit</th>
                    <th>Parents</th>
                    <th width="100">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                while ($row = mysqli_fetch_assoc($slotQ)) {
                    ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $row['slotname'] ?></td>
                        <td><?= $row['merit'] == 1 ? 'GPA' : 'Total' ?></td>
                        <td><?= $row['parents'] ?></td>
                        <td class="d-flex ">
                            <button class="btn btn-sm btn-info me-2" onclick='openEdit(<?= json_encode($row) ?>)'><i
                                    class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="deleteSlot(<?= $row['id'] ?>)"><i
                                    class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>


</div>


<!-- MODAL -->
<div class="modal fade" id="slotModal">
    <div class="modal-dialog modal-dialog-centered ">
        <div class="modal-content  ">
            <form id="slotForm">
                <div class="modal-header">
                    <h5 class="modal-title">Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="sccode" value="<?= $sccode ?>">

                    <div class="row">
                        <div class="col-md-6 mb-2"><label>Slot Name</label>
                            <input type="text" class="form-control form-control-sm" name="slotname" id="slotname"
                                required>
                        </div>
                        <div class="col-md-6 mb-2"><label>Merit</label>
                            <select class="form-select form-select-sm" name="merit" id="merit">
                                <option value="0">Total Marks</option>
                                <option value="1">GPA</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2"> <label>Decimal</label>
                            <select class="form-select form-select-sm" name="decimal" id="decimal">
                                <option value="0">Nearest Top Integer</option>
                                <option value="1">Deciaml</option>
                                <option value="2">Round</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2"><label>Parents</label>
                            <select class="form-select form-select-sm" name="parents" id="parents">
                                <option value="DOSO">DOSO (Doughter Of / Son of)</option>
                                <option value="FM">FM (Father / Mother)</option>
                            </select>
                        </div>
                    </div>




                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    // Create
    function openCreate() {
        document.getElementById("slotForm").reset();
        document.getElementById("id").value = "";
        new bootstrap.Modal(document.getElementById('slotModal')).show();
    }

    // Edit
    function openEdit(row) {
        document.getElementById("id").value = row.id;
        document.getElementById("slotname").value = row.slotname;
        document.getElementById("merit").value = row.merit;
        document.getElementById("parents").value = row.parents;
        document.getElementById("decimal").value = row.decimal_mark;
        new bootstrap.Modal(document.getElementById('slotModal')).show();
    }

    // Submit Form
    document.getElementById("slotForm").addEventListener("submit", function (e) {
        e.preventDefault();
        let fd = new FormData(this);

        fetch("settings/save-slot.php", {
            method: "POST",
            body: fd
        })
            .then(res => res.text())
            .then(res => {
                showToast("Success", res, "Saved");
                location.reload();
            });
    });

    // Delete
    function deleteSlot(id) {
        if (!confirm("Delete Slot?")) return;

        fetch("settings/slot-delete.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "id=" + id
        })
            .then(res => res.text())
            .then(data => {
                showToast("danger", data, "Slot Deleted");
                location.reload();
            });
    }
</script>

<script>
    function setDefault() {
        if (!confirm("Create default slot?")) return;

        fetch("settings/save-slot.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "slotname=<?= $sctype ?>"
        })
            .then(res => res.text())
            .then(res => {

                showToast("success", "Default Slot Implemented", "Include Default");
                location.reload();
            });
    }

</script>