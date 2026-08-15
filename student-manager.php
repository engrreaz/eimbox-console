<?php
require_once 'header.php';

// 1. Get parameters from URL
$sessionyear = $_GET['sessionyear'] ?? null;
$slot = $_GET['slot'] ?? null;
$filter = $_GET['filter'] ?? null;

$students = [];
$page_title = "Student Manager";
$filter_description = "";

if ($sessionyear && $slot && $filter) {
    if ($filter === 'missing_gender') {
        $page_title = "Students with Missing Gender";
        $filter_description = "Showing students from session <strong>$sessionyear</strong> ({$slot}) where gender is not assigned.";

        // 2. Build and execute the query
        $sql = "
            SELECT
                s.stid,
                s.stnameeng,
                s.gender,
                s.mnumber,
                si.classname,
                si.sectionname,
                si.rollno
            FROM students s
            JOIN sessioninfo si ON s.stid = si.stid AND s.sccode = si.sccode
            WHERE
                s.sccode = ?
                AND si.sessionyear = ?
                AND si.slot = ?
                AND (s.gender IS NULL OR s.gender = '' OR s.gender NOT IN ('Male', 'Boy', 'Female', 'Girl'))
            ORDER BY
                si.classname, si.sectionname, si.rollno;
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $sccode, $sessionyear, $slot);
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
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <?= $filter_description ?>
        </div>
    <?php endif; ?>

    <!-- Bulk Actions Card -->
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center gap-3">
            <label class="form-label mb-0">Bulk Actions:</label>
            <select id="bulkGenderSelect" class="form-select form-select-sm" style="width: 150px;">
                <option value="">Select Gender</option>
                <option value="Male">Set to Male</option>
                <option value="Female">Set to Female</option>
            </select>
            <button id="bulkUpdateBtn" class="btn btn-sm btn-primary">Apply to Selected</button>
            <span id="selectedCount" class="ms-2 text-muted">0 selected</span>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th><input class="form-check-input" type="checkbox" id="selectAllCheckbox"></th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Roll</th>
                            <th>Mobile</th>
                            <th style="width: 150px;">Gender</th>
                            <th style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $index => $student): ?>
                                <tr data-stid="<?= htmlspecialchars($student['stid']) ?>">
                                    <td><input class="form-check-input student-checkbox" type="checkbox"></td>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($student['stnameeng']) ?></td>
                                    <td><?= htmlspecialchars($student['classname'] . ' - ' . $student['sectionname']) ?></td>
                                    <td><?= htmlspecialchars($student['rollno']) ?></td>
                                    <td><?= htmlspecialchars($student['mnumber']) ?></td>
                                    <td>
                                        <select class="form-select form-select-sm gender-select">
                                            <option value="" <?= empty($student['gender']) ? 'selected' : '' ?>>Select Gender</option>
                                            <option value="Male" <?= in_array($student['gender'], ['Male', 'Boy']) ? 'selected' : '' ?>>Male</option>
                                            <option value="Female" <?= in_array($student['gender'], ['Female', 'Girl']) ? 'selected' : '' ?>>Female</option>
                                        </select>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-save-gender">Save</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">No students found matching the criteria.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    // --- Bulk Update Logic ---

    // Update selected count
    function updateSelectedCount() {
        const count = $('.student-checkbox:checked').length;
        $('#selectedCount').text(count + ' selected');
    }

    // Select/Deselect all
    $('#selectAllCheckbox').on('change', function() {
        $('.student-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });

    // Single checkbox change
    $(document).on('change', '.student-checkbox', function() {
        updateSelectedCount();
        if (!this.checked) {
            $('#selectAllCheckbox').prop('checked', false);
        }
    });

    // Bulk update button click
    $('#bulkUpdateBtn').on('click', function() {
        const selectedStids = $('.student-checkbox:checked').map(function() {
            return $(this).closest('tr').data('stid');
        }).get();

        const gender = $('#bulkGenderSelect').val();

        if (selectedStids.length === 0) {
            showToast('warning', 'Please select at least one student.');
            return;
        }
        if (!gender) {
            showToast('warning', 'Please select a gender to apply.');
            return;
        }

        $.post('ajax/bulk_update_student_gender.php', { stids: selectedStids, gender: gender }, function(response) {
            if (response.status === 'success') {
                showToast('success', `${response.updated_count} student(s) updated successfully!`);
                $('.student-checkbox:checked').closest('tr').fadeOut(500, function() { $(this).remove(); });
            } else {
                showToast('danger', 'Error: ' + response.message);
            }
        }, 'json').fail(function() {
            showToast('danger', 'An unknown error occurred during bulk update.');
        });
    });

    // --- Inline Edit Logic ---
    $('.btn-save-gender').on('click', function() {
        const button = $(this);
        const row = button.closest('tr');
        const stid = row.data('stid');
        const gender = row.find('.gender-select').val();

        if (!gender) {
            showToast('warning', 'Please select a gender.');
            return;
        }

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.post('ajax/update_student_gender.php', { stid: stid, gender: gender }, function(response) {
            if (response.status === 'success') {
                showToast('success', 'Gender updated successfully!');
                row.fadeOut(500, function() { $(this).remove(); }); // Remove row on success
            } else {
                showToast('danger', 'Error: ' + response.message);
                button.prop('disabled', false).text('Save');
            }
        }, 'json').fail(function() {
            showToast('danger', 'An unknown error occurred.');
            button.prop('disabled', false).text('Save');
        });
    });
});
</script>

</body>
</html>