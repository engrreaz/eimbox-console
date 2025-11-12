<?php 
$stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
$stmt->bind_param("i", $sccode);
$stmt->execute();
$res = $stmt->get_result();
$datainfo = $res->fetch_assoc();
$stmt->close();

// যদি রেকর্ড না মেলে -> কুকি মুছে redirect
if (!$datainfo) {
    // কুকি মুছে ফেলা (path='/') — নিশ্চিতভাবে ব্রাউজার থেকে পোপ করবে
    setcookie('sccode', '', time() - 3600, '/');
    // অপশনালি সার্ভার সাইডে $_COOKIE থেকে আনসেট করাও
    if (isset($_COOKIE['sccode'])) unset($_COOKIE['sccode']);

    // যদি session-এ রাখে থাকো, তা ও আনসেট করো
    if (isset($_SESSION['scode'])) {
        unset($_SESSION['scode']);
    }

    // Redirect to login page
    header('Location: admission-login.php');
    exit;
}

// record পাওয়া গেলে সাধারণ ক্ষেত্রে ভেরিয়েবল সেট করো
$scname = $datainfo['scname'] ?? '';
// address: যদি scadd2 থাকে, সেটা ব্যবহার করো; নাহলে ফিল্ড দুবার ব্যবহার না করে সঠিকভাবে বানাও
$address = trim(
    ($datainfo['scadd1'] ?? '') .
    (!empty($datainfo['scadd2'] ?? '') ? ', ' . $datainfo['scadd2'] : '') .
    (!empty($datainfo['ps'] ?? '') ? ', ' . $datainfo['ps'] : '') .
    (!empty($datainfo['dist'] ?? '') ? ', ' . $datainfo['dist'] : '') 

);
$htname = $datainfo['headname'] ?? '';
$httitle = $datainfo['headtitle'] ?? '';
