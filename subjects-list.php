<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Subjects List</h4>
        <?php
        if ($is_admin > 2 || $permission >= 2) {
            ?>
            <button class="btn btn-primary btn-new">+ Add New</button>
        <?php } ?>
    </div>

    <div class="card">
        <div class="card-bodyx">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-sm">
                    <thead>
                        <tr>
                            <th width="100">Code</th>
                            <th>Subject (English)</th>
                            <th>Subject (Bengali)</th>
                            <th>Short</th>
                            <th width="120">Category</th>
                            <th width="80" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $admin_cond = ($is_admin > 0) ? "" : " AND subcode <= 1000 ";
                        $sql = "SELECT id, sccode, subcode, subject, subben, subshname, sccategory
                            FROM subjects
                            WHERE (sccode='$sccode' OR sccode=0)
                            AND sccategory='$sctype'
                            $admin_cond
                            ORDER BY subcode";

                        $res = $conn->query($sql);

                        if ($res && $res->num_rows):
                            while ($r = $res->fetch_assoc()):
                                $can_edit = ($is_admin > 0) || ($r['sccode'] == $sccode && $r['subcode'] >= 401 && $r['subcode'] <= 800);
                                ?>
                                <tr>
                                    <td><?= $r['subcode'] ?></td>
                                    <td><?= htmlspecialchars($r['subject']) ?></td>
                                    <td><?= htmlspecialchars($r['subben']) ?></td>
                                    <td><?= htmlspecialchars($r['subshname']) ?></td>
                                    <td><?= $r['sccategory'] ?></td>
                                    <td class="text-end">
                                        <?php if ($can_edit): ?>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-icon btn-light rounded-circle dropdown-toggle hide-arrow shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical fs-6"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li>
                                                        <a class="dropdown-item py-2 btn-edit" href="javascript:void(0)"
                                                            data-id="<?= $r['id'] ?>" data-code="<?= $r['subcode'] ?>" data-short="<?= htmlspecialchars($r['subshname'], ENT_QUOTES) ?>"
                                                            data-eiin="<?= $r['sccode'] ?>"
                                                            data-subject="<?= htmlspecialchars($r['subject'], ENT_QUOTES) ?>"
                                                            data-subben="<?= htmlspecialchars($r['subben'], ENT_QUOTES) ?>">
                                                            <i class="bi bi-pencil-square text-info me-2"></i> Edit
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider my-1"></li>
                                                    <li>
                                                        <a class="dropdown-item py-2 text-danger btn-delete" href="javascript:void(0)" data-id="<?= $r['id'] ?>">
                                                            <i class="bi bi-trash text-danger me-2"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            endwhile;
                        else:
                            ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No Records Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= SUBJECT MODAL ================= -->
<div class="modal fade" id="subjectModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="sid">
                <div class="row gap-3" <?php if($is_admin > 0){echo '';} else {echo 'hidden';} ?>>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-save">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= DELETE MODAL ================= -->
<div class="modal fade" id="deleteModal">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <p class="mb-3">Delete this subject?</p>
                <input type="hidden" id="deleteId">
                <button class="btn btn-danger btn-confirm-delete">Yes Delete</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const subjectModalEl = document.getElementById('subjectModal');
        const deleteModalEl = document.getElementById('deleteModal');
        const subjectModal = subjectModalEl ? new bootstrap.Modal(subjectModalEl) : null;
        const deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;

        const isAdmin = <?= ($is_admin > 0) ? 'true' : 'false' ?>;
        const currentSccode = '<?= $sccode ?>';

        // ADD BUTTON
        const addBtn = document.querySelector('.btn-new');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                $('#modalTitle').text('Add Subject');
                $('#sid').val(0);
                if (isAdmin) {
                    $('#sccode').val(0);
                } else {
                    $('#sccode').val(currentSccode);
                }
                $('#subcode, #sube, #subb, #subshname').val('');
                $('#msg').html('');
                subjectModal?.show();
            });
        }

        // EDIT BUTTONS
        $(document).on('click', '.btn-edit', function () {
            const btn = this;
            $('#modalTitle').text('Edit Subject');
            $('#sid').val(btn.dataset.id);
            $('#sccode').val(btn.dataset.eiin);
            $('#subshname').val(btn.dataset.short);
            $('#subcode').val(btn.dataset.code);
            $('#sube').val(btn.dataset.subject);
            $('#subb').val(btn.dataset.subben);
            $('#msg').html('');
            subjectModal?.show();
        });

        // DELETE BUTTONS
        $(document).on('click', '.btn-delete', function () {
            const id = this.dataset.id || this.getAttribute('data-id');
            $('#deleteId').val(id);
            deleteModal?.show();
        });

        // SAVE SUBJECT
        document.querySelector('.btn-save')?.addEventListener('click', function () {
            const id = $('#sid').val();
            const subcode = parseInt($('#subcode').val());
            const sube = $('#sube').val().trim();
            const subb = $('#subb').val().trim();
            const sccode = $('#sccode').val() || currentSccode;
            const subsh = $('#subshname').val().trim();

            if (!isAdmin && (isNaN(subcode) || subcode < 401 || subcode > 800)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Subject Code',
                    text: 'Custom subject code must be in the range 401–800.'
                });
                return;
            }

            if (!subcode || !sube || !subb) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required Fields',
                    text: 'Please fill in Subject Code, English Name, and Bengali Name.'
                });
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
                if (res.includes('text-success')) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved Successfully',
                        timer: 1200,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    const cleanMsg = res.replace(/<[^>]*>?/gm, '');
                    Swal.fire({
                        icon: 'error',
                        title: 'Save Failed',
                        text: cleanMsg || 'Could not save subject.'
                    });
                    $('#msg').html(res);
                }
            });
        });

        // CONFIRM DELETE
        document.querySelector('.btn-confirm-delete')?.addEventListener('click', function () {
            const id = $('#deleteId').val();
            const sccode = $('#sccode').val() || currentSccode;
            $.post('subject/save-new-subject.php', { id: id, sccode: sccode, tail: 1 }, function (res) {
                if (res.includes('text-success')) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted Successfully',
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    const cleanMsg = res.replace(/<[^>]*>?/gm, '');
                    Swal.fire({
                        icon: 'error',
                        title: 'Delete Failed',
                        text: cleanMsg || 'Could not delete subject.'
                    });
                }
            });
        });
    });
</script>





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