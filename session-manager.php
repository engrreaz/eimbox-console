<?php
require_once 'header.php';

// 1. Get parameters from URL
$sessionyear = $_GET['sessionyear'] ?? null;
$slot = $_GET['slot'] ?? null;
$filter = $_GET['filter'] ?? null;

$students = [];
$page_title = "Session Manager";
$filter_description = "";

if ($sessionyear && $slot && $filter) {
    if ($filter === 'orphan_students') {
        $page_title = "Orphan Students";
        $filter_description = "Showing students from session <strong>$sessionyear</strong> ({$slot}) who have marks but are not assigned to any class/section.";

        // 2. Build and execute the query to find students in stmark but not in sessioninfo for the given session
        $sql = "
            SELECT DISTINCT
                sm.stid,
                s.stnameeng,
                s.stnameben,
                s.guarmobile,
                COUNT(sm.subject) as subject_count
            FROM stmark sm
            JOIN students s ON sm.stid = s.stid AND sm.sccode = s.sccode
            LEFT JOIN sessioninfo si ON sm.stid = si.stid AND sm.sccode = si.sccode AND sm.sessionyear = ? AND sm.slot = ?
            WHERE
                sm.sccode = ?
                AND sm.sessionyear = ?
                AND sm.slot = ?
                AND si.stid IS NULL
            GROUP BY
                sm.stid, s.stnameeng, s.stnameben, s.guarmobile
            ORDER BY
                sm.stid;
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiss", $sessionyear, $slot, $sccode, $sessionyear, $slot);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        $stmt->close();
    }
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-2"><span class="text-muted fw-light">Tools /</span> <?= htmlspecialchars($page_title) ?></h4>

    <?php if ($filter_description): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $filter_description ?>
            <p class="small mb-0 mt-2">These students need to be assigned to a class and section to be included in reports and further processing.</p>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Guardian Mobile</th>
                            <th>Marked Subjects</th>
                            <th style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $index => $student): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($student['stid']) ?></td>
                                    <td><?= htmlspecialchars($student['stnameeng']) ?> <br><small class="text-muted"><?= htmlspecialchars($student['stnameben']) ?></small></td>
                                    <td><?= htmlspecialchars($student['guarmobile']) ?></td>
                                    <td><span class="badge bg-label-info"><?= $student['subject_count'] ?></span></td>
                                    <td>
                                        <a href="enroll-students.php?stid=<?= htmlspecialchars($student['stid']) ?>&sy=<?= htmlspecialchars($sessionyear) ?>" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="bi bi-person-plus-fill me-1"></i> Assign Class
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No orphan students found matching the criteria.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

</body>
</html>