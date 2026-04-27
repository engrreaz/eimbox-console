<?php require_once 'header.php'; ?>

<?php
$current_sccode = $sccode ?? '';

if ($is_admin >= 4) {
    $selected_sccode = $_GET['sccode'] ?? $current_sccode;
} else {
    $selected_sccode = $current_sccode;
}

// প্রতিষ্ঠান লোড
$schoolsQ = $conn->query("SELECT sccode, scname FROM scinfo ORDER BY scname ASC");
$schools = [];
while ($row = $schoolsQ->fetch_assoc()) {
    $schools[] = $row;
}


$levels_fix = ["Administrator", "Teacher", "Accountants", "Librarian", "Staff", "Student", "Guardian", "Guest"];

// ইউজার লোড
$stmt = $conn->prepare("SELECT * FROM usersapp WHERE sccode = ? AND status < 2 ORDER BY id ASC");
$stmt->bind_param("s", $selected_sccode);
$stmt->execute();
$users = $stmt->get_result();

// user levels collect
$levels = [];
while ($u = $users->fetch_assoc()) {
    $data[] = $u;
    if (!in_array($u['userlevel'], $levels)) {
        $levels[] = $u['userlevel'];
    }
}
?>
<style>
    #userTable td:last-child,
    #userTable th:last-child {
        width: 10px;
        white-space: nowrap;
        text-align: right;
    }

    .dropdown-menu {
        min-width: 150px;
        /* default 200+ থাকে */
        font-size: 14px;
    }
</style>
<div class="container-xxl container-p-y">

    <!-- Institution -->
    <?php if ($is_admin >= 4) { ?>
        <form method="get" class="mb-3">
            <select name="sccode" class="form-select w-auto" onchange="this.form.submit()">
                <?php foreach ($schools as $school): ?>
                    <option value="<?= $school['sccode'] ?>" <?= ($school['sccode'] == $selected_sccode) ? 'selected' : '' ?>>
                        <?= $school['scname'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php } ?>

    <!-- Top Controls -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">

                <!-- Level Filter -->
                <div>
                    <button class="btn btn-sm btn-primary filter-btn active me-2 mt-2" data-level="all">All</button>
                    <?php foreach ($levels as $lvl): ?>
                        <button class="btn btn-sm btn-outline-primary filter-btn me-2 mt-2" data-level="<?= $lvl ?>">
                            <?= $lvl ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Add User -->
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    + Add User
                </button>
            </div>
        </div>

    </div>


    <!-- Table -->
    <div class="card">
        <div class="table-responsive">
            <table id="userTable" class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Level</th>
                        <th>UserID</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    foreach ($data as $row):
                        if ($row['status'] == 0) {
                            $row_class = "table-danger";
                            $ends = "Enabled Account";
                        } else {
                            $row_class = "";
                            $ends = "Disabled Account";
                        }
                        ?>
                        <tr data-level="<?= $row['userlevel'] ?>" class="<?= $row_class ?>">
                            <td class="text-center"><?= $i++ ?></td>
                            <td><?= $row['profilename'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['userlevel'] ?></td>
                            <td><?= $row['userid'] ?></td>
                            <td class="text-end pe-4">

                                <div class="dropdown ">
                                    <span role="button" data-bs-toggle="dropdown" class="px-3 text-muted user-select-none "
                                        style="cursor:pointer;">
                                        ⋮
                                    </span>
                                    <ul class="dropdown-menu dropdown-menu-end">

                                        <li><a class="dropdown-item viewUser" data-id="<?= $row['email'] ?>">View</a></li>
                                        <li><a class="dropdown-item editUser" data-id="<?= $row['email'] ?>">Edit</a></li>

                                        <li>
                                            <hr class="my-0">
                                        </li>

                                        <li><a class="dropdown-item resetPass" data-id="<?= $row['email'] ?>">Reset
                                                Password</a></li>
                                        <li><a class="dropdown-item disableUser text-danger"
                                                data-id="<?= $row['email'] ?>"><?= $ends ?></a></li>

                                        <li>
                                            <hr class="my-0">
                                        </li>


                                        <li><a class="dropdown-item permUser" data-id="<?= $row['email'] ?>">Permissions</a>
                                        </li>
                                        <li><a class="dropdown-item logUser" data-id="<?= $row['email'] ?>">User Log</a>
                                        </li>
                                    </ul>
                                </div>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addUserForm">
                <div class="modal-header">
                    <h5>Add User</h5>
                </div>

                <div class="modal-body">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                    <input type="text" name="userid" class="form-control mb-2" placeholder="User ID" required>

                    <select name="level" class="form-control">

                        <?php
                        $levels_final = array_unique(array_merge($levels_fix, $levels));
                        $levels_final = array_values($levels_final);

                        ?>

                        <?php foreach ($levels_final as $lvl): ?>
                            <option value="<?= $lvl ?>"><?= $lvl ?></option>
                        <?php endforeach; ?>


                    </select>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- VIEW USER -->
<div class="modal fade" id="viewUserModal">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>User Details</h5>
            </div>
            <div class="modal-body" id="viewUserBody"></div>
        </div>
    </div>
</div>

<!-- EDIT USER -->
<div class="modal fade" id="editUserModal">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <form id="editUserForm">
                <div class="modal-header">
                    <h5>Edit User</h5>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="userid" id="edit_userid">

                    <input type="text" name="name" id="edit_name" class="form-control mb-2" required>
                    <input type="email" name="email" id="edit_email" class="form-control mb-2" readonly>

                    <select name="level" id="edit_level" class="form-control">
                        <?php foreach ($levels_final as $lvl): ?>
                            <option value="<?= $lvl ?>"><?= $lvl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PERMISSIONS -->
<div class="modal fade" id="permModal">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Permissions</h5>
            </div>
            <div class="modal-body">
                <label><input type="checkbox"> Can Edit</label><br>
                <label><input type="checkbox"> Can Delete</label><br>
                <label><input type="checkbox"> Can View Reports</label>
            </div>
        </div>
    </div>
</div>

<!-- LOG -->
<div class="modal fade" id="logModal">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5>User Log</h5>
            </div>
            <div class="modal-body" id="logBody">
                Loading...
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        // Filter
        const table = new DataTable('#userTable', {
            autoWidth: false,
            columnDefs: [
                { targets: -1, width: "10px", orderable: false }
            ]
        });

        // Custom filter
        document.querySelectorAll(".filter-btn").forEach(btn => {
            btn.addEventListener("click", function () {

                document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
                this.classList.add("active");

                let lvl = this.dataset.level;

                if (lvl === "all") {
                    table.column(3).search('').draw(); // Level column index = 3
                } else {
                    table.column(3).search('^' + lvl + '$', true, false).draw();
                }
            });
        });

        // Add User
        document.getElementById("addUserForm").addEventListener("submit", function (e) {
            e.preventDefault();

            let formData = new FormData(this);

            fetch("users/user_action.php?action=add", {
                method: "POST",
                body: formData
            })
                .then(r => r.json())
                .then(res => {
                    if (res.status) {
                        Swal.fire("Success", "User Added", "success").then(() => location.reload());
                    }
                });
        });

        // Reset Password
        document.querySelectorAll(".resetPass").forEach(btn => {
            btn.onclick = () => {
                let id = btn.dataset.id;

                Swal.fire({
                    title: "Reset Password?",
                    showCancelButton: true
                }).then(res => {
                    if (res.isConfirmed) {
                        fetch("users/user_action.php?action=reset&id=" + id)
                            .then(() => Swal.fire("Done", "Password Reset", "success"));
                    }
                });
            }
        });

        // Disable
        document.querySelectorAll(".disableUser").forEach(btn => {
            btn.onclick = () => {
                let id = btn.dataset.id;

                Swal.fire({
                    title: "Switch User Status ?",
                    icon: "warning",
                    showCancelButton: true
                }).then(res => {
                    if (res.isConfirmed) {
                        fetch("users/user_action.php?action=disable&id=" + id)
                            .then(() => location.reload());
                    }
                });
            }
        });

    });
</script>

<script>
    // VIEW USER
    document.querySelectorAll(".viewUser").forEach(btn => {
        btn.onclick = () => {
            let id = btn.dataset.id;

            fetch("users/user_action.php?action=get&id=" + id)
                .then(r => r.json())
                .then(d => {
                    document.getElementById("viewUserBody").innerHTML = `
                <p><b>Name:</b> ${d.profilename}</p>
                <p><b>Email:</b> ${d.email}</p>
                <p><b>Level:</b> ${d.userlevel}</p>
                <p><b>UserID:</b> ${d.userid}</p>
            `;
                    new bootstrap.Modal("#viewUserModal").show();
                });
        }
    });

    // EDIT USER LOAD
    document.querySelectorAll(".editUser").forEach(btn => {
        btn.onclick = () => {
            let id = btn.dataset.id;

            fetch("users/user_action.php?action=get&id=" + id)
                .then(r => r.json())
                .then(d => {
                    edit_userid.value = d.userid;
                    edit_name.value = d.profilename;
                    edit_email.value = d.email;
                    edit_level.value = d.userlevel;

                    new bootstrap.Modal("#editUserModal").show();
                });
        }
    });

    // UPDATE USER
    document.getElementById("editUserForm").onsubmit = function (e) {
        e.preventDefault();

        fetch("users/user_action.php?action=update", {
            method: "POST",
            body: new FormData(this)
        })
            .then(r => r.json())
            .then(res => {
                if (res.status) {
                    Swal.fire("Updated!", "", "success").then(() => location.reload());
                }
            });
    };

    // PERMISSIONS
    document.querySelectorAll(".permUser").forEach(btn => {
        btn.onclick = () => {
            new bootstrap.Modal("#permModal").show();
        }
    });

    // LOG
    document.querySelectorAll(".logUser").forEach(btn => {
        btn.onclick = () => {
            let id = btn.dataset.id;

            document.getElementById("logBody").innerHTML = "Loading...";

            fetch("users/user_action.php?action=log&id=" + id)
                .then(r => r.text())
                .then(d => {
                    document.getElementById("logBody").innerHTML = d;
                });

            new bootstrap.Modal("#logModal").show();
        }
    });
</script>