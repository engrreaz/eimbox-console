<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id = intval($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$start = $_POST['start'] ?? '';
$end = $_POST['end'] ?? '';

$start = str_replace('T', ' ', $start);
$end = $end ? str_replace('T', ' ', $end) : null;

$all_day = isset($_POST['all_day']) && $_POST['all_day'] == 1 ? 1 : 0;
$color = $_POST['color'] ?? '#7367F0';

$event_type = $_POST['event_type'] ?? 'other';
$scope = $_POST['scope'] ?? 'institution';
$user_id = ($scope == 'personal') ? intval($_SESSION['user_id']) : null;

$sccode = isset($_POST['is_general']) && $_POST['is_general']==1 ? 0 : $sccode;


// ----------------------
// UPDATE
// ----------------------
if ($id > 0) {

    if ($scope == 'personal') {
        $sql = "UPDATE events SET
            title=?,
            start=?,
            end=?,
            all_day=?,
            color=?,
            event_type=?,
            scope=?,
            user_id=?
            WHERE id=? AND sccode=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "sssisssiii",
            $title,
            $start,
            $end,
            $all_day,
            $color,
            $event_type,
            $scope,
            $user_id,
            $id,
            $sccode
        );
    } else {
        $sql = "UPDATE events SET
            title=?,
            start=?,
            end=?,
            all_day=?,
            color=?,
            event_type=?,
            scope=?,
            user_id=NULL
            WHERE id=? AND sccode=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "sssisssii",
            $title,
            $start,
            $end,
            $all_day,
            $color,
            $event_type,
            $scope,
            $id,
            $sccode
        );
    }

    mysqli_stmt_execute($stmt);

    // ----------------------
// INSERT
// ----------------------
} else {

    if ($scope == 'personal') {
        $sql = "INSERT INTO events
        (title,start,end,all_day,color,sccode,event_type,scope,user_id)
        VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "sssissssi",
            $title,
            $start,
            $end,
            $all_day,
            $color,
            $sccode,
            $event_type,
            $scope,
            $user_id
        );
    } else {
        $sql = "INSERT INTO events
        (title,start,end,all_day,color,sccode,event_type,scope)
        VALUES (?,?,?,?,?,?,?,?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "sssissss",
            $title,
            $start,
            $end,
            $all_day,
            $color,
            $sccode,
            $event_type,
            $scope
        );
    }

    mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
}

echo json_encode([
    'status' => 'success',
    'id' => $id
]);
