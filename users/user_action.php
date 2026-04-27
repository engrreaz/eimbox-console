<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

// ALTER TABLE `usersapp` CHANGE `photourl` `photourl` VARCHAR(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL;
// ALTER TABLE `usersapp` CHANGE `token` `token` VARCHAR(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL;




$action = $_GET['action'] ?? '';

if ($action == "add") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $userid = $_POST['userid'];
    $level = $_POST['level'];

    $token = '--';
      $pass = password_hash("123456", PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // KB (64 MB)
        'time_cost' => 4,     // iteration
        'threads' => 2
    ]);

    $hardpass='123456';

    $stmt = $conn->prepare("INSERT INTO usersapp (profilename,email,userid,userlevel,sccode, token, password_hash, password_salt) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssssss", $name, $email, $userid, $level, $sccode, $token, $pass, $hardpass);
    $stmt->execute();

    echo json_encode(["status" => true]);
}

// reset password
if ($action == "reset") {
    $id = $_GET['id'];

    $pass = password_hash("123456", PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // KB (64 MB)
        'time_cost' => 4,     // iteration
        'threads' => 2
    ]);

    $hardpass='123456';
    $stmt = $conn->prepare("UPDATE usersapp SET password_hash=?, password_salt=? WHERE email=?");
    $stmt->bind_param("ss", $pass, $hardpass, $id);
    $stmt->execute();
}

// disable
if ($action == "disable") {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("UPDATE usersapp SET status=1-status WHERE email=?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
}



// GET USER
if ($action == "get") {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM usersapp WHERE email=?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    echo json_encode($res);
}

// UPDATE USER
if ($action == "update") {
    $id = $_POST['userid'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $level = $_POST['level'];

    $stmt = $conn->prepare("UPDATE usersapp SET profilename=?,  userlevel=? WHERE email=?");
    $stmt->bind_param("sss", $name, $level, $email);
    $stmt->execute();

    echo json_encode(["status" => true]);
}

// LOG (dummy)
if ($action == "log") {
    echo "<ul>
        <li>Login সফল</li>
        <li>Password change</li>
        <li>Profile update</li>
    </ul>";
}