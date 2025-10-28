<?php require_once 'header.php';
// $is_admin = 0; 
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row">
        <div class="col-12">
            <div class="mb-3 text-end float-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">Add Role</button>
            </div>

            <h4 class="mb-1">Roles List</h4>
        </div>
    </div>


    <!-- Role cards -->
    <div class="row g-6" id="roleList">
        <?php
        $roles = $conn->query("SELECT * FROM rolemanager where sccode=0 or sccode='$sccode' ORDER BY id");
        while ($row = $roles->fetch_assoc()):
            // count users assigned to this role
            $role_sccode = intval($row['sccode']);
            $userlevel = $conn->real_escape_string($row['userlevel']);
            $userCountRes = $conn->query("SELECT COUNT(*) as total FROM usersapp WHERE sccode=$sccode AND userlevel='$userlevel'");
            $userCount = $userCountRes->fetch_assoc()['total'];


            if ($is_admin < 4 && $role_sccode == 0) {
                $edit_mode = 'disabled';
                $back = 'white';
            } else {
                $edit_mode = '';
                $back = 'white';
            }
            ?>
            <div class="col-xl-4 col-lg-6 col-md-6 role-card" data-id="<?= $row['id'] ?>">
                <div class="card h-100 bg-<?php echo $back; ?> ">

                    <div class="card-header">
                        <div class="float-end">
                            <button class="btn btn-sm btn-outline-info editRoleBtn" data-id="<?= $row['id'] ?>"
                                data-bs-toggle="modal" data-bs-target="#editRoleModal" <?php echo $edit_mode; ?>><i
                                    class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger deleteRoleBtn" data-id="<?= $row['id'] ?>" <?php echo $edit_mode; ?>><i class="bi bi-trash"></i></button>
                        </div>

                        <div class="role-heading">
                            <h5 class="mb-1"><?= htmlspecialchars($row['userlevel']) ?></h5>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="role-heading">
                                <p class="mb-0"><?= htmlspecialchars($row['descrip']) ?></p>
                            </div>

                        </div>

                        <div class="card-footer">
                            <div class="">
                                Total <?= $userCount ?> users
                                <!-- Optional: avatars -->
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    <!--/ Role cards -->

</div>

<?php require_once 'footer.php'; ?>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="addRoleForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>User Level</label>
                        <input type="text" class="form-control" name="userlevel" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control" name="descrip" rows="3"></textarea>
                    </div>
                    <?php
                    if ($is_admin < 4) {
                        $role_sccode = $sccode;
                    } else {
                        $role_sccode = 0;
                    }
                    ?>

                    <div class="mb-3">
                        <label>SCCODE</label>
                        <input type="number" class="form-control" name="sccode" value="<?= $role_sccode; ?>" disabled>
                    </div>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editRoleForm">
            <input type="hidden" name="id" id="editRoleId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>User Level</label>
                        <input type="text" class="form-control" name="userlevel" id="editUserLevel" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control" name="descrip" id="editDescrip" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>SCCODE</label>
                        <input type="number" class="form-control" name="sccode" id="editSccode">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {

        // Add Role
        $('#addRoleForm').submit(function (e) {
            e.preventDefault();
            $.post('core/role-actions.php', $(this).serialize() + '&action=add', function (res) {
                if (res.status === 'success') location.reload();
                else alert(res.message);
            }, 'json');
        });

        // Edit Role - Fill modal
        $('.editRoleBtn').click(function () {
            let id = $(this).data('id');
            $.getJSON('core/role-actions.php', { id: id, action: 'get' }, function (res) {
                if (res.status === 'success') {
                    $('#editRoleId').val(res.data.id);
                    $('#editUserLevel').val(res.data.userlevel);
                    $('#editDescrip').val(res.data.descrip);
                    // $('#editSccode').val(res.data.sccode);
                    $('#editSccode').val(res.data.sccode).prop('disabled', true);
                } else alert(res.message);
            });
        });

        // Update Role
        $('#editRoleForm').submit(function (e) {
            e.preventDefault();
            $.post('core/role-actions.php', $(this).serialize() + '&action=update', function (res) {
                if (res.status === 'success') location.reload();
                else alert(res.message);
            }, 'json');
        });

        // Delete Role
        $('.deleteRoleBtn').click(function () {
            if (confirm('Are you sure you want to delete this role?')) {
                let id = $(this).data('id');
                $.post('core/role-actions.php', { id: id, action: 'delete' }, function (res) {
                    if (res.status === 'success') location.reload();
                    else alert(res.message);
                }, 'json');
            }
        });

    });
</script>

</body>

</html>