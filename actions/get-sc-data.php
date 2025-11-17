<?php 
$stmt = $conn->prepare("SELECT * FROM scinfo WHERE sccode = ? LIMIT 1");
$stmt->bind_param("i", $sccode);
$stmt->execute();
$res = $stmt->get_result();
$datainfo = $res->fetch_assoc();
$stmt->close();

var_dump($datainfo);

if (!$datainfo) {
    setcookie('sccode', '', time() - 3600*24*30, '/');
    if (isset($_COOKIE['sccode'])) unset($_COOKIE['sccode']);
    if (isset($_SESSION['scode'])) {
        unset($_SESSION['scode']);
    }

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
