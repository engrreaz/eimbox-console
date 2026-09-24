<?php
require_once 'header.php';

// Filter parameters
$search_query = trim($_GET['search'] ?? '');
$filter_year = trim($_GET['year'] ?? '');
$filter_class = trim($_GET['class'] ?? '');

// Fetch distinct session years for filter
$years_list = [];
$y_stmt = $conn->prepare("SELECT DISTINCT sessionyear FROM sessioninfo WHERE sccode = ? AND status = 0 ORDER BY sessionyear DESC");
if ($y_stmt) {
    $y_stmt->bind_param("i", $sccode);
    $y_stmt->execute();
    $y_res = $y_stmt->get_result();
    while ($yr = $y_res->fetch_assoc()) {
        $years_list[] = $yr['sessionyear'];
    }
    $y_stmt->close();
}

// Build query for archived students (status = 0 in sessioninfo)
$params = [$sccode];
$types = "i";

$sql = "SELECT 
            si.id AS session_id, si.stid, si.sessionyear, si.classname, si.sectionname, si.rollno, si.slot, si.modifieddate AS archive_date,
            s.stnameeng, s.stnameben, s.fname, s.mname, s.previll, s.prepo, s.preps, s.predist, s.fmobile
        FROM sessioninfo AS si
        JOIN students AS s ON si.stid = s.stid AND si.sccode = s.sccode
        WHERE si.sccode = ? AND si.status = 0";

if (!empty($filter_year)) {
    $sql .= " AND si.sessionyear = ?";
    $params[] = $filter_year;
    $types .= "s";
}

if (!empty($filter_class)) {
    $sql .= " AND si.classname = ?";
    $params[] = $filter_class;
    $types .= "s";
}

if (!empty($search_query)) {
    $sql .= " AND (s.stid LIKE ? OR s.stnameeng LIKE ? OR s.stnameben LIKE ? OR s.fname LIKE ? OR s.fmobile LIKE ?)";
    $search_like = "%{$search_query}%";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "sssss";
}

$sql .= " ORDER BY si.sessionyear DESC, si.classname ASC, si.rollno ASC LIMIT 300";

$archived_students = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $archived_students[] = $row;
    }
    $stmt->close();
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Top Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold m-0 d-flex align-items-center gap-2">
                <i class="bi bi-archive-fill text-warning fs-3"></i>
                Archived Students Directory
            </h4>
            <p class="text-muted small mb-0">নিষ্ক্রিয় ও আর্কাইভকৃত শিক্ষার্থীদের তালিকা ও ডেটা সংরক্ষণাগার</p>
        </div>
        <div class="d-flex gap-2">
            <a href="students-list.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-people me-1"></i> Active Students List
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Search by Name / ID / Mobile</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search archived students..." value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Session Year</label>
                    <select name="year" class="form-select form-select-sm">
                        <option value="">All Sessions</option>
                        <?php foreach ($years_list as $yr): ?>
                            <option value="<?= htmlspecialchars($yr) ?>" <?= $filter_year == $yr ? 'selected' : '' ?>>
                                <?= htmlspecialchars($yr) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Class</label>
                    <input type="text" name="class" class="form-control form-control-sm" placeholder="e.g. Six, Ten" value="<?= htmlspecialchars($filter_class) ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark btn-sm flex-grow-1">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <?php if (!empty($search_query) || !empty($filter_year) || !empty($filter_class)): ?>
                        <a href="students-archived.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Archived Students Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-file-earmark-lock me-2 text-warning"></i>
                Archived Students Record
            </h5>
            <span class="badge bg-label-warning rounded-pill">Total Archived: <?= count($archived_students) ?></span>
        </div>

        <?php if (empty($archived_students)): ?>
            <div class="card-body text-center py-5">
                <i class="bi bi-archive text-muted fs-1 d-block mb-3 opacity-50"></i>
                <h6 class="fw-bold text-muted">কোন আর্কাইভকৃত শিক্ষার্থী পাওয়া যায়নি।</h6>
                <p class="text-muted small">No archived students found matching the selected filter criteria.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">SL</th>
                            <th>Student ID &amp; Name</th>
                            <th>Class / Section</th>
                            <th>Session &amp; Roll</th>
                            <th>Parents &amp; Address</th>
                            <th>Archived Date</th>
                            <th class="text-center" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archived_students as $idx => $st): ?>
                            <tr>
                                <td><?= $idx + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= student_profile_image_path($st['stid']) ?>" alt="Avatar"
                                             class="rounded-circle me-3"
                                             style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #eee;"
                                             onerror="this.src='assets/images/user.png'">
                                        <div>
                                            <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($st['stnameeng'] ?? '') ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($st['stnameben'] ?? '') ?></div>
                                            <small class="badge bg-light text-muted border-0 px-2 mt-1 fs-tiny">
                                                ID: <?= htmlspecialchars($st['stid']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($st['classname']) ?></span>
                                    <div class="text-muted small">Sec: <?= htmlspecialchars($st['sectionname']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary"><?= htmlspecialchars($st['sessionyear']) ?></span>
                                    <div class="small fw-bold text-primary mt-1">Roll: <?= htmlspecialchars($st['rollno']) ?></div>
                                </td>
                                <td>
                                    <div class="small">
                                        <span class="text-muted">F:</span> <?= htmlspecialchars($st['fname'] ?: '—') ?>
                                    </div>
                                    <div class="small text-muted text-truncate" style="max-width: 180px;">
                                        <?= htmlspecialchars(implode(', ', array_filter([$st['previll'] ?? '', $st['predist'] ?? '']))) ?: '—' ?>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= !empty($st['archive_date']) ? date('d M, Y', strtotime($st['archive_date'])) : '—' ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="student-view-profile.php?stid=<?= $st['stid'] ?>&year=<?= $st['sessionyear'] ?>" 
                                           target="_blank" class="btn btn-icon btn-sm btn-outline-primary" title="View Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="student-overall-report.php?stid=<?= $st['stid'] ?>&year=<?= $st['sessionyear'] ?>" 
                                           target="_blank" class="btn btn-icon btn-sm btn-outline-secondary" title="Overall Report">
                                            <i class="bi bi-file-earmark-person"></i>
                                        </a>
                                        <button type="button" class="btn btn-icon btn-sm btn-outline-success" 
                                                onclick="restoreStudent('<?= $st['stid'] ?>', '<?= htmlspecialchars(addslashes($st['stnameeng'])) ?>')" 
                                                title="Restore / Unarchive Student">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'footer.php'; ?>

<script>
    function restoreStudent(stid, name) {
        Swal.fire({
            title: 'Restore Student?',
            text: `Are you sure you want to restore "${name}" (ID: ${stid}) back to active status?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i> Yes, Restore',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("student/restore-student.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "stid=" + encodeURIComponent(stid)
                })
                .then(res => res.text())
                .then(msg => {
                    Swal.fire({
                        title: 'Restored!',
                        text: msg,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 1200);
                })
                .catch(err => {
                    Swal.fire('Error', 'Failed to restore student: ' + err, 'error');
                });
            }
        });
    }
</script>
</body>
</html>
