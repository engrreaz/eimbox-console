<?php require_once 'header.php'; ?>

<?php
// বর্তমান প্রতিষ্ঠানের কোড
$current_sccode = $sccode ?? '';

// ✅ 1. সব প্রতিষ্ঠান লোড করা scinfo টেবিল থেকে
$schoolsQ = $conn->query("SELECT sccode, scname FROM scinfo ORDER BY scname ASC");
$schools = [];
while ($row = $schoolsQ->fetch_assoc()) {
    $schools[] = $row;
}

// ✅ 2. ফিল্টারড ইউজার লিস্ট লোড করা
$selected_sccode = $_GET['sccode'] ?? $current_sccode;

$stmt = $conn->prepare("SELECT email, profilename, userlevel, userid FROM usersapp WHERE sccode = ? ORDER BY profilename ASC");
$stmt->bind_param("s", $selected_sccode);
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php if ($is_admin >= 4) { ?>
        <div class="card mb-3">
            <div class="card-body">
                <form method="get" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label for="sccode" class="col-form-label fw-bold">প্রতিষ্ঠান নির্বাচন:</label>
                    </div>
                    <div class="col-auto">
                        <select name="sccode" id="sccode" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($schools as $school): ?>
                                <option value="<?= htmlspecialchars($school['sccode']) ?>"
                                    <?= ($school['sccode'] === $selected_sccode) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($school['scname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    <?php } ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">ইউজার তালিকা</h5>
        </div>
        <div class="card-body table-responsive">
            <table id="userTable" class="table table-bordered table-striped align-middle">
                <thead>
                    <tr class="table-light">
                        <th>#</th>
                        <th>Profile Name</th>
                        <th>Email</th>
                        <th>User Level</th>
                        <th>User ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    while ($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['profilename'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['userlevel'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['userid'] ?? '') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new DataTable('#userTable', {
            pageLength: 10,
            language: {
                search: "অনুসন্ধান:",
                lengthMenu: "প্রতি পৃষ্ঠায় _MENU_ টি রেকর্ড",
                info: "মোট _TOTAL_ রেকর্ডের মধ্যে _START_ থেকে _END_ পর্যন্ত",
                paginate: {
                    previous: "পেছনে",
                    next: "আগে"
                }
            }
        });
    });
</script>

<?php require_once 'footer.php'; ?>
</body>

</html>