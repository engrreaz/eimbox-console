<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

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

    $res = mysqli_query($conn, $sql);

    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }


}


if ($action == 'update') {

    $id = $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    $allowed = ['date', 'time', 'subcode'];

    if (!in_array($field, $allowed)) {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid field']);
        exit;
    }

    $sql = "UPDATE examroutine 
            SET $field='$value' 
            WHERE id='$id'";

    mysqli_query($conn, $sql);

    echo json_encode(['status' => 'success']);
    exit;
}


if ($action == 'insert') {

    $date = $_POST['date'];
    $time = $_POST['time'];
    $subcode = $_POST['subcode'];

    $sql = "INSERT INTO examroutine
            (sccode, sessionyear, examname, clsname, secname, date, time, subcode)
            VALUES
            ('$sccode','$sessionyear','$examname','$clsname','$secname','$date','$time','$subcode')";

    mysqli_query($conn, $sql);

    echo json_encode(['status' => 'success']);
    exit;
}


if ($action == 'clone') {

    $from_session = $_POST['from_session'];
    $from_exam = $_POST['from_exam'];
    $from_class = $_POST['from_class'];
    $from_section = $_POST['from_section'];

    $sql = "INSERT INTO examroutine
            (sccode, sessionyear, examname, clsname, secname, date, time, subcode)
            SELECT 
                '$sccode','$sessionyear','$examname','$clsname','$secname',
                date, time, subcode
            FROM examroutine
            WHERE sccode='$sccode'
            AND sessionyear='$from_session'
            AND examname='$from_exam'
            AND clsname='$from_class'
            AND secname='$from_section'";

    mysqli_query($conn, $sql);

    echo json_encode(['status' => 'success']);
    exit;
}


if ($action == 'init') {

    $sql = "SELECT subcode FROM subsetup
            WHERE sccode='$sccode'
            AND clsname='$clsname'";

    $res = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($res)) {

        $subcode = $row['subcode'];

        $insert = "INSERT INTO examroutine
                   (sccode, sessionyear, examname, clsname, secname, subcode)
                   VALUES
                   ('$sccode','$sessionyear','$examname','$clsname','$secname','$subcode')";

        mysqli_query($conn, $insert);
    }

    echo json_encode(['status' => 'success']);
    exit;
}


if ($action == 'check') {

    $sql = "SELECT id FROM examroutine
            WHERE sccode='$sccode'
            AND sessionyear='$sessionyear'
            AND examname='$examname'
            AND clsname='$clsname'
            AND secname='$secname'
            LIMIT 1";

    $res = mysqli_query($conn, $sql);

    if (mysqli_num_rows($res) > 0) {
        echo json_encode(['status' => 'exists']);
    } else {
        echo json_encode(['status' => 'empty']);
    }

    exit;
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