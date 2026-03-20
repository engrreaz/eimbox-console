<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Subjects List</h4>
        <?php
        if ($is_admin >= 4 || $permission >= 2) {
            ?>
            <button class="btn btn-primary btn-new" onclick="openAddModal()" data-feature="Add New Subject" data-point="10">+ Add New</button>
        <?php } ?>
    </div>

    <div class="card">
        <div class="card-bodyx">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-sm ">
                    <thead>
                        <tr>
                            <th width="100">Code</th>
                            <th>Subject (English)</th>
                            <th>Subject (Bengali)</th>
                            <th>Short</th>
                            <th width="120">Category</th>
                            <th width="140" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT id, sccode, subcode, subject, subben, subshname, sccategory
                            FROM subjects
                            WHERE (sccode='$sccode' OR sccode=0)
                            AND sccategory='$sctype'
                            ORDER BY subcode";

                        $res = $conn->query($sql);

                        if ($res && $res->num_rows):
                            while ($r = $res->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= $r['subcode'] ?></td>
                                    <td><?= htmlspecialchars($r['subject']) ?></td>
                                    <td><?= htmlspecialchars($r['subben']) ?></td>
                                    <td><?= htmlspecialchars($r['subshname']) ?></td>
                                    <td><?= $r['sccategory'] ?></td>
                                    <td class="text-end">

                                        <?php
                                        if ($is_admin >= 4 || ($r['sccode'] == $sccode && $r['subcode'] >= 401 && $r['subcode'] <= 800 && $permission == 3)) {
                                            ?>

                                            <button class="btn btn-sm btn-info btn-edit" onclick="openEditModal(this)"
                                                data-id="<?= $r['id'] ?>" data-code="<?= $r['subcode'] ?>" data-short="<?= $r['subshname'] ?>"
                                                data-eiin="<?= $r['sccode'] ?>"
                                                data-subject="<?= htmlspecialchars($r['subject'], ENT_QUOTES) ?>"
                                                data-subben="<?= htmlspecialchars($r['subben'], ENT_QUOTES) ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button class="btn btn-sm btn-danger btn-delete" data-id="<?= $r['id'] ?>" >
                                                <i class="bi bi-trash"></i>
                                            </button>

                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">No Records Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>




<div class="modal fade" id="subjectModal">
    <div class="modal-dialog modal-dialog-centered ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="sid">
<div class="row gap-3" <?php if($is_admin >= 4){echo '';} else {echo 'hidden';} ?>>
    <div class="col-12 mb-2">
        <label>Institute Code</label>
                    <input type="text" id="sccode" class="form-control form-control-sm">
    </div>
</div>
       

                <div class="row">
<div class="mb-2 col-md-6">
                    <label>Subject Code</label>
                    <input type="number" id="subcode" class="form-control">
                </div>
<div class="mb-2 col-md-6">
                    <label>Short Name</label>
                    <input type="text" id="subshname" class="form-control">
                </div>
                </div>
                

                <div class="mb-2">
                    <label>Subject Name (English)</label>
                    <input type="text" id="sube" class="form-control">
                </div>

                <div class="mb-2">
                    <label>Subject Name (Bengali)</label>
                    <input type="text" id="subb" class="form-control">
                </div>

                <div id="msg"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" onclick="saveSubject()">Save</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="deleteModal">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <p>Delete this subject?</p>
                <input type="hidden" id="deleteId">
                <button class="btn btn-danger" onclick="confirmDelete()">Yes Delete</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // মডাল ইনস্ট্যান্স
        const subjectModal = new bootstrap.Modal(document.getElementById('subjectModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        // ADD BUTTON
        const addBtn = document.querySelector('.btn-new');
        addBtn.addEventListener('click', function () {
            $('#modalTitle').text('Add Subject');
            $('#sid').val(0);
            if(<?= $is_admin >=4 ?>){
                 $('#sccode').val(0);
            } else {
                $('#sccode').val(<?= $sccode ?>);
            }
           
            $('#subcode,#sube,#subb, #subshname').val('');
            $('#msg').html('');
            subjectModal.show();
        });

        // EDIT BUTTONS
        document.querySelectorAll('.btn-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                $('#modalTitle').text('Edit Subject');
                $('#sid').val(btn.dataset.id);
                $('#sccode').val(btn.dataset.eiin);
                $('#subshname').val(btn.dataset.short);
                $('#subcode').val(btn.dataset.code);
                $('#sube').val(btn.dataset.subject);
                $('#subb').val(btn.dataset.subben);
                $('#msg').html('');
                subjectModal.show();
            });
        });

        // DELETE BUTTONS
        document.querySelectorAll('.btn-delete').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = btn.dataset.id || btn.getAttribute('data-id');
                $('#deleteId').val(id);
                deleteModal.show();
            });
        });

        // SAVE SUBJECT
        document.querySelector('#subjectModal .btn-success').addEventListener('click', function () {
            const id = $('#sid').val();
            const subcode = parseInt($('#subcode').val());
            const sube = $('#sube').val();
            const subb = $('#subb').val();
            const sccode = $('#sccode').val();
            const subsh = $('#subshname').val();





            if (<?= $is_admin ?> <= 4 && (subcode < 401 || subcode > 800)) {
                alert('Subject Code: 401–800');
                return;
            }

            $('#msg').html('Saving...');

            $.post('subject/save-new-subject.php', {
                id: id,
                subcode: subcode,
                sube: sube,
                subb: subb,
                subsh: subsh,
                sccode: sccode
            }, function (res) {
                $('#msg').html(res);
                setTimeout(() => location.reload(), 800);
            });
        });

        // CONFIRM DELETE
        document.querySelector('#deleteModal .btn-danger').addEventListener('click', function () {
            const id = $('#deleteId').val();
            $.post('subject/save-new-subject.php', { id: id, tail: 1 }, function () {
                location.reload();
            });
        });
    });
</script>