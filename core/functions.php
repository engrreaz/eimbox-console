<?php
require_once 'db.php';
require_once 'core-val.php';
require_once 'global_values.php';
require_once 'sms-var.php';


// ========================
// XSS Safe Output
// ========================
function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// ========================
// Localhost / Remote 
// ========================
function isLocalhost()
{
    $whitelist = ['127.0.0.1', '::1', 'localhost'];
    return in_array($_SERVER['SERVER_NAME'], $whitelist);
}

// ========================
// CSRF Token
// ========================
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_validate($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ========================
// Client IP
// ========================
function client_ip()
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ========================
// Audit Log
// ========================
function auth_log($conn, $action, $userId = null, $emailAttempt = null)
{
    $ip = client_ip();
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $stmt = $conn->prepare("INSERT INTO auth_logs(user_id, email_attempted, ip, user_agent, action) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $userId, $emailAttempt, $ip, $ua, $action);
    $stmt->execute();
    $stmt->close();
}

// ========================
// Password Validation
// ========================
function validate_password($pwd)
{
    if (strlen($pwd) < 8)
        return false;
    if (!preg_match('/[a-z]/', $pwd))
        return false;
    if (!preg_match('/[A-Z]/', $pwd))
        return false;
    if (!preg_match('/[0-9]/', $pwd))
        return false;
    if (!preg_match('/[\W]/', $pwd))
        return false;
    return true;
}

// ========================
// Find User by Email
// ========================
function find_user_by_email($conn, $email)
{

    // ইউজার ডেটা বের করো
    $stmt = $conn->prepare("SELECT * FROM usersapp WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $user = [];
        $school = [];
        return [
            "user" => $user,
            "school" => $school
        ];
    }

    // স্কুল ডেটা বের করো
    $stmt2 = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");

    // 's' ব্যবহার করো, কারণ sccode string হতে পারে (leading zero থাকতে পারে)
    $stmt2->bind_param('s', $user['sccode']);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $school = $res2->fetch_assoc();
    $stmt2->close();

    return [
        "user" => $user,
        "school" => $school ?? []
    ];
}


function find_user_by_stid($conn, $stid, $pin)
{
    // ইউজার ডেটা বের করো
    $stmt = $conn->prepare("SELECT * FROM students WHERE stid = ? and (guarmobile = ? OR dob = ?) LIMIT 1");
    $stmt->bind_param('sss', $stid, $pin, $pin);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();


    if (!$user) {
        $user = [];
        $school = [];
        return [
            "user" => $user,
            "school" => $school
        ];
    }

    // স্কুল ডেটা বের করো
    $stmt2 = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");

    // 's' ব্যবহার করো, কারণ sccode string হতে পারে (leading zero থাকতে পারে)
    $stmt2->bind_param('s', $user['sccode']);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $school = $res2->fetch_assoc();
    $stmt2->close();


    return [
        "user" => $user,
        "school" => $school ?? []
    ];



}

// ========================
// Store User Session
// ========================
function store_user_session($user, $school = [])
{
    $_SESSION['user_id'] = $user['id'] ?? '';
    $_SESSION['user_email'] = $user['email'] ?? '';
    $_SESSION['user_name'] = $user['username'] ?? '';
    $_SESSION['userid'] = '';
    $_SESSION['first_name'] = $user['first_name'] ?? '';
    $_SESSION['last_name'] = $user['last_name'] ?? '';
    $_SESSION['phone'] = $user['phone'] ?? '';
    $_SESSION['address'] = $user['address'] ?? '';
    $_SESSION['dob'] = $user['dob'] ?? '';
    $_SESSION['user_role'] = $user['role'] ?? 'user';

    if (($user['is_chief'] ?? '0') == 1) {
        $_SESSION['userlevel'] = 'Chief';
    } else {
        $_SESSION['userlevel'] = $user['userlevel'] ?? '';
    }

    $_SESSION['sccode'] = $user['sccode'] ?? '';
    $_SESSION['photourl'] = $user['photourl'] ?? '';
    $_SESSION['isadmin'] = $user['admin'] ?? 0;
    $_SESSION['page_status_grant'] = $user['page_status_grant'] ?? 6;
    $_SESSION['fullname'] = $user['profilename'] ?? $user['email'] ?? '';

    $_SESSION['locktime'] = 10000; //$user['admin'] ?? 0;

    // স্কুল ইনফো
    $_SESSION['scname'] = $school['scname'] ?? '';
    $_SESSION['sccategory'] = $school['sccategory'] ?? '';
    $_SESSION['admin_data'] = $school['admin_data'] ?? '';
    $_SESSION['package_id'] = $school['package_id'] ?? 2;
    $_SESSION['scaddress_top'] = $school['ps'] . ', ' . $school['dist'];
    $_SESSION['scaddress_top_full'] = str_replace(', ,', ', ', $school['scadd1'] . ', ' . $school['scadd2'] . ', ' . $school['ps'] . ', ' . $school['dist']);

    $_SESSION['rootuser'] = $school['rootuser'];
    $_SESSION['scmobile'] = $school['mobile'];
    $_SESSION['sms_gateway'] = $school['sms_gateway'] ?? '';

    $_SESSION['valid_module'] = $school['valid_module'] ?? '';
    $_SESSION['active_module'] = $school['active_module'] ?? '';

}


function store_student_session($user, $school = [])
{
    $_SESSION['user_id'] = $user['id'] ?? '';
    $_SESSION['user_email'] = $user['stid'] ?? '';
    $_SESSION['user_name'] = $user['stid'] ?? '';
    $_SESSION['userid'] = $user['stid'] ?? '';
    $_SESSION['first_name'] = $user['stnameeng'] ?? '';
    $_SESSION['last_name'] = $user['stnameben'] ?? '';
    $_SESSION['phone'] = $user['guarmobile'] ?? '';
    $_SESSION['address'] = $user['previll'] ?? '';
    $_SESSION['dob'] = $user['dob'] ?? '';
    $_SESSION['user_role'] = 'Student';
    $_SESSION['userlevel'] = 'Student';
    $_SESSION['sccode'] = $user['sccode'] ?? '';
    $_SESSION['photourl'] = dirname(__DIR__) . '/students' . '/' . $user['stid'] . 'jpg';
    $_SESSION['isadmin'] = 0;
    $_SESSION['page_status_grant'] = 6;
    $_SESSION['fullname'] = $user['stnameeng'];

    $_SESSION['locktime'] = 10000; //$user['admin'] ?? 0;

    // স্কুল ইনফো
    $_SESSION['scname'] = $school['scname'] ?? '';
    $_SESSION['sccategory'] = $school['sccategory'] ?? '';
    $_SESSION['admin_data'] = $school['admin_data'] ?? '';
    $_SESSION['package_id'] = $school['package_id'] ?? 2;
    $_SESSION['scaddress_top'] = $school['ps'] . ', ' . $school['dist'];
    $_SESSION['scaddress_top_full'] = $school['po'] . $school['ps'] . ', ' . $school['dist'];

    $_SESSION['rootuser'] = $school['rootuser'];
    $_SESSION['scmobile'] = $school['mobile'];
    $_SESSION['sms_gateway'] = $school['sms_gateway'] ?? '';
}



// ========================
// Remember-Me Token
// ========================
function create_remember_token($conn, $userId)
{
    $token = bin2hex(random_bytes(32));
    $hash = password_hash($token, PASSWORD_BCRYPT);
    $expires = date('Y-m-d H:i:s', strtotime('+' . REMEMBER_ME_EXPIRE_DAYS . ' days'));

    $stmt = $conn->prepare("UPDATE usersapp SET remember_token_hash=?, remember_token_expires=? WHERE id=?");
    $stmt->bind_param('ssi', $hash, $expires, $userId);
    $stmt->execute();
    $stmt->close();

    setcookie('remember_me', $userId . ':' . $token, time() + 60 * 60 * 24 * REMEMBER_ME_EXPIRE_DAYS, '/', '', true, true);
}

// ========================
// Verify Remember-Me Token
// ========================

function verify_remember_token($conn)
{
    if (empty($_COOKIE['remember_me'])) {
        return false;
    }

    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) !== 2) {
        return false;
    }

    [$uid, $token] = $parts;

    // Fetch user
    $stmt = $conn->prepare("SELECT * FROM usersapp WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if (!$user)
        return false;

    // Expire চেক
    if (empty($user['remember_token_expires']) || strtotime($user['remember_token_expires']) < time()) {
        return false;
    }

    // Token verify
    if (!password_verify($token, $user['remember_token_hash'])) {
        return false;
    }

    // স্কুল ডেটা ফেচ
    $stmt2 = $conn->prepare("SELECT * FROM scinfo WHERE sccode=? LIMIT 1");
    $stmt2->bind_param('i', $user['sccode']);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $school = $res2->fetch_assoc();
    $stmt2->close();

    // সেশনে স্টোর করা (এখন দুইটা আর্গুমেন্ট পাঠাই)
    store_user_session($user, $school);

    // চাইলে এখানে নতুন টোকেন issue করা যেতে পারে (replay attack রোধ করতে)
    // create_remember_token($conn, $user['id']);

    return true;
}



// ========================
// Send MFA Token (Stub)
// ========================
function send_mfa_token($user, $token, $flag = 'otp')
{

    $to = $user['email'];
    $subject = "Your MFA code";
    $message = "Your one-time code is: $token\nIt expires in 5 minutes.";
    $headers = "From: thisisengrreaz@gmail.com\r\n";

    $mail_type = 'otp';
    $mail_to = $to;
    $mail_name = 'EIMBox User';
    $mail_subject = 'EIMBox OTP for MFA';
    $mail_attach = '';
    $msg_success = "Your OTP has been send to your mail $mail_to";
    if ($flag == 'otp') {
        include('mailer/send-mail.php');
    } else {
        include('../mailer/send-mail.php');
    }


}



// Example: admin user detection (adjust your logic)
function is_admin_user()
{
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') || (isset($_SESSION['user_name']) && $_SESSION['user_name'] == 'engrreaz@gmail.com');
}

// Error logging location
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log'); // adjust path
ini_set('display_errors', 0); // never show errors to normal users

// Custom error handler
set_error_handler('custom_error_handler');
set_exception_handler('custom_exception_handler');

function custom_error_handler($errno, $errstr, $errfile, $errline)
{
    $errorMessage = "PHP ERROR [$errno]: $errstr in $errfile on line $errline";
    error_log($errorMessage); // always log

    if (is_admin_user()) {
        // Admin sees detailed error
        echo "<pre>$errorMessage</pre>";
    } else {

        // echo "<h2>Something went wrong. Please try again later.</h2>";

    }
}

// Custom exception handler
function custom_exception_handler($exception)
{
    $errorMessage = "Uncaught Exception: " . $exception->getMessage() .
        " in " . $exception->getFile() .
        " on line " . $exception->getLine();
    error_log($errorMessage);

    if (is_admin_user()) {
        echo "<pre>$errorMessage</pre>";
    } else {
        echo "<pre>$errorMessage</pre>";
        // if(!headers_sent()){
        //     header("Location: error_page.php");
        //     exit;
        // } else {
        //     echo "<h2>Something went wrong. Please try again later.</h2>";
        //     exit;
        // }
    }
}

// Optional: shutdown function to catch fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && ($error['type'] & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE))) {
        $msg = "Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}";
        error_log($msg);

        if (is_admin_user()) {
            echo "<pre>$msg</pre>";
        } else {
            if (!headers_sent()) {
                header("Location: error_page.php");
                exit;
            } else {
                echo "<h2>Something went wrong. Please try again later.</h2>";
                exit;
            }
        }
    }
});


/*
// Notificate Create
function createNotification($user_id, $title, $message, $link = null)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $message, $link);
    $stmt->execute();
}

// Fetch 
function getUnreadNotifications($user_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// read
function markAsRead($notification_id)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notification_id);
    $stmt->execute();
}

*/


// core/notifications.php
// Assumes $conn is the mysqli connection from your core/db.php
// and session is already started (header.php includes these).

/**
 * Create notification for a user
 * @param int $user_id
 * @param string $title
 * @param string $message
 * @param string|null $link
 * @param string|null $type
 * @return int|false inserted id or false
 */
function createNotification($user_id, $title, $message = '', $link = null, $type = null)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt)
        return false;
    $stmt->bind_param("issss", $user_id, $type, $title, $message, $link);
    if ($stmt->execute()) {
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return $insert_id;
    }
    $stmt->close();
    return false;
}

/**
 * Get notifications for a user
 * @param int $user_id
 * @param int $limit
 * @return array
 */
function getNotifications($user_id, $limit = 10)
{
    global $conn;
    $stmt = $conn->prepare("SELECT id, type, title, message, link, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/**
 * Count unread notifications
 * @param int $user_id
 * @return int
 */
function countUnreadNotifications($user_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) $res['cnt'];
}

/**
 * Mark a notification as read (single id)
 * @param int $notification_id
 * @param int|null $user_id - optional security check
 * @return bool
 */
function markNotificationAsRead($notification_id, $user_id = null)
{
    global $conn;
    if ($user_id) {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notification_id, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->bind_param("i", $notification_id);
    }
    if (!$stmt)
        return false;
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/**
 * Mark all notifications as read for a user
 * @param int $user_id
 * @return bool
 */
function markAllAsRead($user_id)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    if (!$stmt)
        return false;
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}



function timeAgo($datetime, $full = false)
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);
    $units = ['y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second'];
    $values = ['y' => $diff->y, 'm' => $diff->m, 'w' => $weeks, 'd' => $days, 'h' => $diff->h, 'i' => $diff->i, 's' => $diff->s];
    $strings = [];
    foreach ($units as $k => $label) {
        if ($values[$k] > 0)
            $strings[$k] = $values[$k] . ' ' . $label . ($values[$k] > 1 ? 's' : '');
    }
    if (!$full)
        $strings = array_slice($strings, 0, 1);
    return $strings ? implode(', ', $strings) . ' ago' : 'just now';
}

// ⚠️ header.php তে config/db আগে থেকেই ইনক্লুড করা আছে, তাই এখানে আবার লাগবে না।

function logSuspiciousActivity($conn, $user_id, $email, $event_type, $description = '')
{
    // ইউজারের IP ও User-Agent নেওয়া
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    // master টেবিল থেকে matching rule খোঁজা
    $stmt = $conn->prepare("SELECT id, risk_score, recommended_action FROM suspicious_activity_types WHERE title = ?");
    $stmt->bind_param("s", $event_type);
    $stmt->execute();
    $rule = $stmt->get_result()->fetch_assoc();

    $risk = $rule['risk_score'] ?? 10;
    $action = $rule['recommended_action'] ?? 'log_only';
    $rule_id = $rule['id'] ?? NULL;

    // ইভেন্ট লগ করা
    $stmt2 = $conn->prepare("INSERT INTO suspicious_events (user_id, email, ip_address, user_agent, event_type, description, risk_score, recommended_action, matched_rule_id)
                             VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt2->bind_param("isssssisi", $user_id, $email, $ip, $ua, $event_type, $description, $risk, $action, $rule_id);
    $stmt2->execute();

    // অটো অ্যাকশন নেওয়া
    if ($action == 'block') {
        // IP ব্লক করার উদাহরণ
        file_put_contents(__DIR__ . '/../logs/blocked_ips.txt', $ip . "\n", FILE_APPEND);
        // বিকল্প: সেশনে ফ্ল্যাগ সেট
        $_SESSION['blocked'] = true;
    } elseif ($action == 'alert') {
        // ইমেইল বা সিস্টেম অ্যাডমিনকে নোটিফাই (placeholder)
        error_log("🚨 Suspicious Activity Alert: $event_type from $ip ($email)");
    } elseif ($action == 'review') {
        // পর্যালোচনার জন্য লগ রাখা
        error_log("🔎 Review Needed: $event_type - $email - $ip");
    }
}



function taka($number)
{

    $number1 = $number;
    $no = floor($number);
    $hundred = null;
    $digits_1 = strlen($no); //to find lenght of the number
    $i = 0;
    // Numbers can stored in array format
    $str = array();

    $words = array(
        '0' => '',
        '1' => 'One',
        '2' => 'Two',
        '3' => 'Three',
        '4' => 'Four',
        '5' => 'Five',
        '6' => 'Six',
        '7' => 'Seven',
        '8' => 'Eight',
        '9' => 'Nine',
        '10' => 'Ten',
        '11' => 'Eleven',
        '12' => 'Twelve',
        '13' => 'Thirteen',
        '14' => 'Fourteen',
        '15' => 'Fifteen',
        '16' => 'Sixteen',
        '17' => 'Seventeen',
        '18' => 'Eighteen',
        '19' => 'Nineteen',
        '20' => 'Twenty',
        '30' => 'Thirty',
        '40' => 'Forty',
        '50' => 'Fifty',
        '60' => 'Sixty',
        '70' => 'Seventy',
        '80' => 'Eighty',
        '90' => 'Ninety'
    );

    $digits = array('', 'Hundred', 'Thousand', 'lakh', 'Crore');
    //Extract last digit of number and print corresponding number in words till num becomes 0
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        //Round numbers down to the nearest integer
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;

        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($number < 21) ? $words[$number] . " " .
                $digits[$counter] .
                $plural . " " .
                $hundred : $words[floor($number / 10) * 10] . " " .
                $words[$number % 10] . " " .
                $digits[$counter] . $plural . " " .
                $hundred;
        } else
            $str[] = null;
    }

    $str = array_reverse($str);
    $result = implode('', $str); //Join array elements with a string
    //echo "Given number is: ".$number1."</br>";
    return $result;
    // return 0;

}

function is_bengali_unicode($text)
{
    return preg_match('/\p{Bengali}/u', $text) ? true : false;
}
function sms_templete_2_text($msgText)
{
    global $sms_hint, $sms_var, $sms_sample;

    global $stnameeng, $stnameben, $guarname, $classname, $sectionname,
    $dueamount, $paymentamount, $paymentdate, $intime, $outtime, $month, $cur;

    $msgText = htmlspecialchars($msgText);
    // $sms_var অ্যারের প্রতিটি ভ্যালুকে প্রপার মানে রূপান্তর
    $values = [];
    foreach ($sms_var as $vname) {
        // $vname হলো '$stnameeng', '$classname' ইত্যাদি
        // substring(1) করে $ চিহ্ন বাদ দিয়ে variable variable ব্যবহার
        $varName = substr($vname, 1);
        if (isset($$varName)) {
            $values[] = $$varName; // ভ্যালু
        } else {
            $values[] = ""; // ডিফল্ট খালি স্ট্রিং
        }
    }

    // এখন replace করুন
    return str_replace($sms_hint, $sms_sample, $msgText);
}


function global_send_sms($mobile, $message, $campaign = 'Regular', $type = '', $stid = 0)
{
    global $sms_api_key, $sms_secret_key, $sms_username, $sms_password, $sms_url, $sms_provider, $sms_price;
    global $sccode, $y_v2, $conn, $usr, $cur;

    global $sms_hint, $sms_var;

    global $stnameeng, $stnameben, $guarname, $classname, $sectionname,
    $dueamount, $paymentamount, $paymentdate, $intime, $outtime, $month, $cur;

    $msgText = $message;
    // $msgText = htmlspecialchars($message);
    // $sms_var অ্যারের প্রতিটি ভ্যালুকে প্রপার মানে রূপান্তর
    $values = [];
    foreach ($sms_var as $vname) {
        // $vname হলো '$stnameeng', '$classname' ইত্যাদি
        // substring(1) করে $ চিহ্ন বাদ দিয়ে variable variable ব্যবহার
        $varName = substr($vname, 1);
        if (isset($$varName)) {
            $values[] = $$varName; // ভ্যালু
        } else {
            $values[] = ""; // ডিফল্ট খালি স্ট্রিং
        }
    }

    // এখন replace করুন
    $message = str_replace($sms_hint, $values, $msgText);


    $mobile = mysqli_real_escape_string($conn, $mobile);
    // $message_original = $message;              // real message for length
    $message_original = htmlspecialchars($message);              // real message for length
    $message = urlencode($message);            // encoded for API

    // -----------------------------------------
    // 2. Default response values
    // -----------------------------------------
    $response_code = '';
    $message_id = '';
    $success_message = '';
    $error_message = '';
    $status = '';

    // echo 'ABC';
    // echo $sms_url;
    // -----------------------------------------
    // 3. SMS Gateway: bulksmsbd.net
    // -----------------------------------------
    if (str_contains($sms_url, 'bulksmsbd.net')) {
        $sms_url = "http://bulksmsbd.net/api/smsapi";
        $data = [
            "api_key" => $sms_api_key,
            "senderid" => $sms_username,
            "number" => $mobile,
            "message" => $message_original
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sms_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response_raw = curl_exec($ch);
        // curl_close($ch);
        $ch = null;

        // JSON → associative array
        $response = json_decode($response_raw, true);

        $response_code = $response['response_code'] ?? '';
        $message_id = $response['message_id'] ?? '';
        $success_message = $response['success_message'] ?? '';
        $error_message = $response['error_message'] ?? '';
        $status = $response['status'] ?? '';

    }

    // -----------------------------------------
    // 4. SMS Gateway: smsvaults.work
    // -----------------------------------------
    else if (str_contains($sms_url, 'cpanel.smsvaults.work')) {

        $url = "http://cpanel.smsvaults.work/sendtext?apikey={$sms_api_key}&secretkey={$sms_secret_key}&callerID=01234567890&toUser={$mobile}&messageContent={$message}";
        // $url = $sms_url;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response_raw = curl_exec($ch);
        $ch = null;

        $response = json_decode($response_raw, true);

        $response_code = 0;
        $status = $response['Status'] ?? '';
        $message_id = $response['Message_ID'] ?? '';
        $success_message = $response['Text'] ?? '';
        $error_message = '';

    }

    // -----------------------------------------
    // 5. Unknown gateway
    // -----------------------------------------
    else {
        $response_code = '';
        $message_id = '';
        $success_message = '';
        $error_message = '';
        $status = '';
    }

    // -----------------------------------------
    // 6. SMS Count (real message)
    // -----------------------------------------


    $msg_length = mb_strlen($message_original);
    if (is_bengali_unicode($message_original)) {
        $count = ceil($msg_length / 70);
    } else {
        $count = ceil($msg_length / 160);
    }




    // -----------------------------------------
    // 7. Get session info for this student
    // -----------------------------------------
    $sql = "SELECT sessionyear, classname, sectionname, rollno 
            FROM sessioninfo 
            WHERE stid = '$stid' AND sessionyear LIKE '%$y_v2%' 
            ORDER BY id DESC LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sessionyear = $row['sessionyear'];
        $classname = $row['classname'];
        $sectionname = $row['sectionname'];
        $rollno = $row['rollno'];
    } else {
        $sessionyear = date('Y');
        $classname = '';
        $sectionname = '';
        $rollno = 0;
    }

    // -----------------------------------------
    // 8. Cost calculation
    // -----------------------------------------
    if ($sms_provider != 'self') {
        $cost = $sms_price *= $count;
    } else {
        $cost = 0;
    }

    $td = date('Y-m-d');

    // -----------------------------------------
    // 9. Insert log into `sms` table
    // -----------------------------------------
    $sqls = "INSERT INTO sms
        (sccode, sessionyear, stid, date, campaign, sms_type, mobile_number, sms_text, sms_len, count, send_by, send_time, cost,
        response_code, message_id, success_message, error_message, status, modifieddate)
        VALUES
        ('$sccode', '$sessionyear', '$stid',  '$td', '$campaign', '$type', '$mobile', '$message_original', '$msg_length', '$count',
         '$usr', '$cur', '$cost', '$response_code', '$message_id', '$success_message', '$error_message', '$status', '$cur')";
    // echo $sqls;
    $conn->query($sqls);
}


function pass_validation(
    $ct = 0,
    $mt = 0,
    $sub = 0,
    $obj = 0,
    $pra = 0,
    $ca = 0,
    $fm_sub = 0,
    $fm_obj = 0,
    $fm_pra = 0,
    $fm = 0,
    $alg = 0,
    $min = 33,
    $decimal = 0
) {

    // Full mark zero হলে অনিয়মিত (Fail)
    if ($fm <= 0) {

        return false;
    }

    // helper function: percentage calculation
    $calc = function ($got, $full, $decimal) {
        if ($full <= 0)
            return 0;

        $p = ((float) $got * 100) / $full;

        if ($decimal == 0)
            return ceil($p);
        if ($decimal == 2)
            return round($p);
        return $p; // default float
    };

    // Total Marks
    $total = ((float) $ct + (float) $mt + (float) $sub + (float) $obj + (float) $pra + (float) $ca);
    $rate = $calc($total, $fm, $decimal);

    // ---------------- Algorithm 0 ----------------
    // Only total percentage check
    if ($alg == 0) {
        return ($rate >= $min);
    }

    // ---------------- Algorithm 1 ----------------
    // Individual (sub/obj/pra) mandatory pass + total pass

    $sub_pass = $calc($sub, $fm_sub, $decimal);
    $obj_pass = $calc($obj, $fm_obj, $decimal);
    $pra_pass = $calc($pra, $fm_pra, $decimal);

    // echo '//' . $fm_sub . '/' . $fm_obj . '/' . $fm_pra . '/' . $decimal . '//';
    // echo '//' . $sub . '/' . $obj . '/' . $pra .  '//';
    // echo '//' . $sub_pass . '/' . $obj_pass . '/' . $pra_pass . '/' . $min . '//';
    // One fail = total fail




    if (
        ($sub_pass != 0 && $sub_pass < $min) ||
        ($obj_pass != 0 && $obj_pass < $min) ||
        ($pra_pass != 0 && $pra_pass < $min)
    ) {

        return false;
    }

    // Check total percentage after individual pass
    return ($rate >= $min);
}


function get_GP_GL($mark, $fullmark, $decimal = 0)
{
    global $conn, $sccode;
    if ($decimal == 0) {
        $mark = ceil(($mark) * 100 / $fullmark);
    } else if ($decimal == 2) {
        $mark = round(($mark) * 100 / $fullmark);
    } else {
        $mark = floatval($mark) * 100 / $fullmark;
    }



    // Query priority:
    // 1) sccode = current school
    // 2) sccode = 0 (default)
    $q = "
        SELECT * FROM gpa 
        WHERE minvalues <= $mark 
        AND maxvalues >= $mark 
        AND (sccode = '$sccode' OR sccode = 0)
        ORDER BY 
            CASE WHEN sccode = '$sccode' THEN 1 ELSE 2 END ASC,
            id ASC
        LIMIT 1
    ";
echo $q;
    $res = mysqli_query($conn, $q);

    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        return [
            "gp" => $row['gp'],
            "gl" => $row['gl'],
            "remark" => $row['remark'],
            "color" => $row['colorcode']
        ];
    }

    // কোনো ম্যাচ না পেলে Default F
    return [
        "gp" => 0.00,
        "gl" => "F",
        "remark" => "Failed",
        "color" => "000000"
    ];
}