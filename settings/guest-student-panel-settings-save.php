<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid Request');
}

/* ---------------------------------
   Load existing admin_data
----------------------------------*/
$sql = "SELECT admin_data FROM scinfo WHERE sccode='$sccode' LIMIT 1";
$q   = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($q);

$adminData = json_decode($row['admin_data'] ?? '{}', true);

/* ---------------------------------
   Prepare Guest Student Settings
----------------------------------*/
$guestSettings = [
    'panel_active'       => $_POST['panel_active'] ?? 'no',
    'access_times'       => (int)($_POST['access_times'] ?? 0),
    'max_stay_time'      => (int)($_POST['max_stay_time'] ?? 0),
    'login_security'     => $_POST['login_security'] ?? [],

    'result'             => isset($_POST['result']),
    'result_details'     => isset($_POST['result_details']),
    'result_pdf'         => isset($_POST['result_pdf']),
    'result_archive'     => isset($_POST['result_archive']),

    'attendance'         => isset($_POST['attendance']),
    'attendance_details' => isset($_POST['attendance_details']),

    'payment'            => isset($_POST['payment']),
    'payment_details'    => isset($_POST['payment_details']),
    'payment_history'    => isset($_POST['payment_history']),
    'online_payment'     => isset($_POST['online_payment']),

    'download_profile'   => isset($_POST['download_profile']),
    'notice'             => isset($_POST['notice']),
    'notification'       => isset($_POST['notification']),
];

/* ---------------------------------
   Merge without losing old data
----------------------------------*/
$adminData['Panel Settings']['Guest Student'] = $guestSettings;

/* ---------------------------------
   Save JSON
----------------------------------*/
$json = mysqli_real_escape_string(
    $conn,
    json_encode($adminData, JSON_UNESCAPED_UNICODE)
);

$update = mysqli_query(
    $conn,
    "UPDATE scinfo SET admin_data='$json' WHERE sccode='$sccode'"
);

if ($update) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Guest Student settings saved successfully'
    ]);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database update failed'
    ]);
}