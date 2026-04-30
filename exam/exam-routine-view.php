<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'] ?? '';
$sessionyear = $_POST['sessionyear'] ?? '';
$examname = $_POST['exam'] ?? '';
$clsname = $_POST['classname'] ?? '';
$secname = $_POST['sectionname'] ?? '';
$action = $_POST['action'] ?? '';


$subjectList = [];

$sqlSub = "SELECT s.subcode, s.subject
FROM subjects s
INNER JOIN subsetup ss ON s.subcode = ss.subject
WHERE s.sccategory = '$sctype'
AND (s.sccode = '0' OR s.sccode = '$sccode')
AND ss.slot = '$slot'
AND ss.sessionyear = '$sessionyear'
AND ss.sccode = '$sccode'
AND ss.classname = '$clsname'
AND ss.sectionname = '$secname'
ORDER BY s.subcode";

$resSub = mysqli_query($conn, $sqlSub);

while ($rowSub = mysqli_fetch_assoc($resSub)) {
    $subjectList[] = $rowSub;
}


if ($action == 'fetch') {

    $sql = "SELECT r.id, r.date, r.time, r.subcode, s.subject
            FROM examroutine r
            LEFT JOIN subjects s 
                ON r.subcode = s.subcode 
            WHERE s.sccategory = '$sctype'
            AND r.sccode='$sccode'
            AND r.sessionyear='$sessionyear'
            AND r.examname='$examname'
            AND r.clsname='$clsname'
            AND r.secname='$secname'
            ORDER BY r.date, r.time";

    $res = mysqli_query($conn, $sql);

    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }


}

?>

<div class="card-header d-flex justify-content-between">
    <div>
        <b>Exam Routine</b>
        <div id="routine-info" class="d-flex mt-2">
            <div >
                <div class="small">Slot</div>
                <div class="fs-6 fw-bold"><?= $slot ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div >
                <div class="small">Session</div>
                <div class="fs-6 fw-bold"><?= $sessionyear ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Examination</div>
                <div class="fs-6 fw-bold"><?= $examname ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Class</div>
                <div class="fs-6 fw-bold"><?= $clsname ?? '' ?></div>
            </div>
            <div class="vr mx-3"></div>
            <div>
                <div class="small">Section</div>
                <div class="fs-6 fw-bold"><?= $secname ?? '' ?></div>
            </div>
           
        </div>
    </div>

    <div>
        <button id="btnAdd" class="btn btn-sm btn-primary" onclick="openAddModalRoutine()">+ Add Subject</button>
        <button id="btnClone" class="btn btn-sm btn-warning" onclick="openCloneModal()">Import Routine</button>
    </div>
</div>

<div class="card-body">

    <!-- Empty State -->
    <div id="emptyState" style="display:none; text-align:center;">
        <h4>No Routine Found</h4>
        <button id="btnStart" class="btn btn-success">
            Let's Get Started
        </button>
    </div>

    <!-- Table -->


</div>

<div id="routineTable" class="table table-responsive">
<?php if (count($data) > 0): ?>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Subject</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr data-id="<?= $row['id'] ?>">

                    <!-- Date -->
                    <td>
                        <input type="date" class="form-control form-control-sm updateField" data-field="date"
                            value="<?= $row['date'] ?>">
                    </td>

                    <!-- Time -->
                    <td>
                        <input type="time" class="form-control form-control-sm updateField" data-field="time"
                            value="<?= $row['time'] ?>">
                    </td>

                    <!-- Subject -->
                    <td>
                        <select class="form-control form-control-sm updateField" data-field="subcode">
                            <option value="">Select</option>
                            <?php foreach ($subjectList as $sub): ?>
                                <option value="<?= $sub['subcode'] ?>" <?= ($sub['subcode'] == $row['subcode']) ? 'selected' : '' ?>>
                                    <?= $sub['subcode'] ?> - <?= $sub['subject'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <!-- Delete -->
                    <td>
                        <i class="bi bi-trash text-danger btnDelete" style="cursor:pointer;"></i>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div id="emptyState" class="alert alert-warning text-center mx-4">
        No subject assigned for this exam. Click the button below to add subjects to the routine.
    </div>
<?php endif; ?>
</div>