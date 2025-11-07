<?php
session_start();
require_once 'config.php';
require_once 'db.php';
require_once 'core-val.php';
require_once 'global_values.php';
require_once 'functions.php';

if (isset($_POST['sccode']) && !empty($usr)) {
    $sccode = $_POST['sccode'];

    $stmt = $conn->prepare("UPDATE usersapp SET sccode = ? WHERE email = ?");
    $stmt->bind_param("ss", $sccode, $usr);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);

        $data = find_user_by_email($conn, $usr);
        $user = $data['user'];
        $school = $data['school'];
        store_user_session($user, $school);

        // var_dump($_SESSION);

    } else {
        echo json_encode(["status" => "error", "message" => "Database update failed"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
}

?>