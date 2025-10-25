<?php
require_once 'config.php';
require_once 'db.php';
require_once 'functions.php';
require_once 'core-val.php';
require_once 'global_values.php';

header('Content-Type: application/json');

try {
    $eiin = trim($_POST['eiin'] ?? '');
    $sctype = trim($_POST['sctype'] ?? '');
    $scname = trim($_POST['area'] ?? '');
    $address1 = trim($_POST['address1'] ?? '');
    $address2 = trim($_POST['address2'] ?? '');
    $city = trim($_POST['ps'] ?? '');
    $state = trim($_POST['dist'] ?? '');
    $scmobile = str_replace(' ', '', trim($_POST['mobile'] ?? ''));
    $scmail = trim($_POST['pincode'] ?? '');
    $rootuser = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($eiin === '' || $sctype === '' || $scname === '' || $email === '' || $password === '') {
        throw new Exception('Required fields are missing.');
    }

    // Generate token for activation
    $token = bin2hex(random_bytes(16)); // 32-char hex
    // $hash = password_hash($token, PASSWORD_DEFAULT);
    $hash = $token;
    $expires_school = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $token_school = bin2hex(random_bytes(16));
    $token_user = bin2hex(random_bytes(32));
    $expires_school = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $hash_school = password_hash($token_school, PASSWORD_DEFAULT);
    $hash_user = password_hash($token_user, PASSWORD_DEFAULT);
    $expires_user = date('Y-m-d H:i:s', strtotime('+1 hour'));


    // EIIN already exists check
    $stmt = $conn->prepare("SELECT id FROM scinfo WHERE sccode = ?");
    $stmt->bind_param("s", $eiin);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        throw new Exception('EIIN already registered!');
    }
    $stmt->close();

    // Generate rootuser if not provided
    if ($rootuser === '') {
        $words = preg_split('/\s+/', $scname);
        $acronym = '';
        foreach ($words as $w) {
            if ($w !== '' && $w !== '&')
                $acronym .= strtoupper($w[0]);
        }
        $rootuser = strtolower($acronym) . $eiin;
    }


    // Ensure rootuser is unique
    $stmt = $conn->prepare("SELECT rootuser FROM scinfo WHERE rootuser = ?");
    $stmt->bind_param("s", $rootuser);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $rootuser .= rand(10, 99); // Append random digits
    }
    $stmt->close();

    // EIIN already exists check
    $stmt = $conn->prepare("SELECT id FROM usersapp WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        throw new Exception('User already registered!');
    }
    $stmt->close();













    // Insert into scinfo
    $naw = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO scinfo 
        (sccode, sccategory, scname, scadd1, scadd2, ps, dist, mobile, scmail, rootuser, modifieddate, reg_hash, hash_expire)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssssssssssss",
        $eiin,
        $sctype,
        $scname,
        $address1,
        $address2,
        $city,
        $state,
        $scmobile,
        $scmail,
        $rootuser,
        $naw,
        $token_school,
        $expires_school
    );
    if (!$stmt->execute()) {
        throw new Exception('Institution registration failed.');
    }
    $stmt->close();

    // Insert into usersapp
    $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
    $ulv = 'Administrator';
    $uid = $eiin . '9999';
    $pid = '';
    $pin = rand(100000, 999999);
    $actv = 0;



    // Prepare user data for insertion
    // ভ্যারিয়েবলগুলো ডিফাইন করি
    $panel = ['admin', 'teacher', 'student'];
    $module = ['student', 'result', 'attendance'];
    $account = [
        'freq' => 'monthly',
        'unit' => 'fixed',
        'amount' => 0
    ];
    $extra = ['library']; // এই "" key টাকে ধরছি extra নামে
    $settings = [
        'theme' => 'dark',
        'sms' => [
            'api' => [
                'api_key' => 'sf;sfskldfjs',
                'api_secret' => '123456000'
            ],
            'in_time' => [
                'active' => '1',
                'prio_1' => 'manual',
                'prio_2' => 'after_1st',
                'prio_3' => 'fixed_time',
                'time' => '11:00'
            ]
        ]
    ];

    $package = [
        'id' => '2',
        'name' => 'Trial'
    ];

    // সবকিছু একত্র করে অ্যারে তৈরি করা
    $data = [
        'panel' => $panel,
        'module' => $module,
        'payment' => $account,
        'extra' => $extra,
        'settings' => $settings,
        'package' => $package
    ];

    // JSON এ রূপান্তর
    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);






    $now = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO usersapp 
        (sccode, email, password_hash, created_at, userlevel, userid, photourl, status, fixedpin, reset_token_hash, reset_token_expires, admin_data)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssssssisss",
        $eiin,
        $email,
        $hashedPassword,
        $now,
        $ulv,
        $uid,
        $pid,
        $actv,
        $pin,
        $hash_user,
        $expires_user,
        $json_data
    );
    if (!$stmt->execute()) {
        throw new Exception('Administrator registration failed.');
    }
    $stmt->close();


    // Prepare activation link
    $resetLink = APP_PATH . "register-new-ins.php?eiin=" . urlencode($eiin) . "&token=$token_school";

    // Mail variables
    $mail_type = 'new-ins';
    $mail_to = $scmail;
    $mail_name = $scname;
    $mail_subject = 'Register New Institute';
    $mail_attach = '';
    $msg_success = "Account activation mail has been sent to $mail_to";

    include(dirname(__DIR__) . '/mailer/send-mail.php');





    // Prepare user activation link
    $resetLink = APP_PATH . "register-new-user.php?email=" . urlencode($email) . "&token=$token_user";

    $mail_type = 'new-user';
    $mail_to = $email;
    $mail_name = $scname;
    $mail_subject = 'Register New User';
    $mail_attach = '';
    $msg_success = "Account activation mail has been sent to $mail_to";

    include(dirname(__DIR__) . '/mailer/send-mail.php');

    // Return success

    echo json_encode([
        'success' => true,
        'message' => 'Institute and Administrator registered successfully. Activation links sent.'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}