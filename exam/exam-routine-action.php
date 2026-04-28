<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

$sessionyear = $_POST['sessionyear'] ?? '';
$examname = $_POST['examname'] ?? '';
$clsname = $_POST['clsname'] ?? '';
$secname = $_POST['secname'] ?? '';
$action = $_POST['action'] ?? '';


if ($action == 'update') {

    $id = $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];

    $allowed = ['date', 'time', 'subcode'];

    if (!in_array($field, $allowed)) {
        echo json_encode(['status' => 'error']);
        exit;
    }

    $sql = "UPDATE examroutine SET $field='$value' WHERE id='$id'";
    mysqli_query($conn, $sql);

    echo json_encode(['status' => 'success']);
    exit;
}

if ($action == 'delete') {

    $id = $_POST['id'];

    $sql = "DELETE FROM examroutine WHERE id='$id'";
    mysqli_query($conn, $sql);

    echo json_encode(['status' => 'success']);
    exit;
}


if ($action == 'insert') {

    $sccode = $_POST['sccode'];
    $sessionyear = $_POST['sessionyear'];
    $examname = $_POST['examname'];
    $clsname = $_POST['clsname'];
    $secname = $_POST['secname'];

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


if ($action == 'preview_clone') {

    $sccode = $_POST['sccode'];
    $sessionyear = $_POST['sessionyear'];
    $examname = $_POST['examname'];
    $clsname = $_POST['clsname'];
    $secname = $_POST['secname'];

    $sql = "SELECT r.*, s.subject
            FROM examroutine r
            LEFT JOIN subjects s ON r.subcode=s.subcode
            WHERE r.sccode='$sccode'
            AND r.sessionyear='$sessionyear'
            AND r.examname='$examname'
            AND r.clsname='$clsname'
            AND r.secname='$secname'
            ORDER BY r.date,time";

    $res = mysqli_query($conn, $sql);

    $data = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
    exit;
}


if ($action == 'clone') {

    $sccode = $_POST['sccode'];

    $from_session = $_POST['from_session'];
    $from_exam = $_POST['from_exam'];
    $from_class = $_POST['from_class'];
    $from_section = $_POST['from_section'];

    $sessionyear = $_POST['sessionyear'];
    $examname = $_POST['examname'];
    $clsname = $_POST['clsname'];
    $secname = $_POST['secname'];

    $sql = "INSERT INTO examroutine
            (sccode,sessionyear,examname,clsname,secname,date,time,subcode)

            SELECT
            '$sccode',
            '$sessionyear',
            '$examname',
            '$clsname',
            '$secname',
            date,
            time,
            subcode

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
