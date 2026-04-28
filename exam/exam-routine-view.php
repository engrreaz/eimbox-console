<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$sccode = $_POST['sccode'] ?? '';
$sessionyear = $_POST['sessionyear'] ?? '';
$examname = $_POST['examname'] ?? '';
$clsname = $_POST['clsname'] ?? '';
$secname = $_POST['secname'] ?? '';
$action = $_POST['action'] ?? '';



if ($action == 'fetch') {

    $sql = "SELECT r.id, r.date, r.time, r.subcode, s.subj
            FROM examroutine r
            LEFT JOIN subjects s 
                ON r.subcode = s.subcode 
                AND r.sccode = s.sccode
            WHERE r.sccode='$sccode'
            AND r.sessionyear='$sessionyear'
            AND r.examname='$examname'
            AND r.clsname='$clsname'
            AND r.secname='$secname'
            ORDER BY r.date, r.time";
echo $sql;
    $res = mysqli_query($conn, $sql);

    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }


}



var_dump($data);
?>

<div class="card-header d-flex justify-content-between">
    <div>
        <b>Exam Routine</b>
        <div id="routine-info"></div>
    </div>

    <div>
        <button id="btnAdd" class="btn btn-sm btn-primary">+ Add Subject</button>
        <button id="btnClone" class="btn btn-sm btn-warning">Clone From</button>
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

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Subject</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr data-id="<?= $row['id'] ?>">
                    <td contenteditable="true" class="edit" data-field="date"><?= $row['date'] ?></td>
                    <td contenteditable="true" class="edit" data-field="time"><?= $row['time'] ?></td>
                    <td contenteditable="true" class="edit" data-field="subcode"><?= $row['subcode'] ?> -
                        <?= $row['subj'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>