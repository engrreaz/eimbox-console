<?php
$permission = 0;
$email_null = '';

// $access_page_list = [
//     'forgot_password_process.php'
// ];

$access_page_list = [];
$restrict_page_list = [];
$status = 0;
$result = $conn->query("SELECT module_name, related_pages, status_name FROM modulemanager where module_name = 'Core' or  module_name = 'Backend' or related_pages = '$currentFile'");
while ($row = $result->fetch_assoc()) {

    $md = $row['module_name'];
    if ($row['related_pages'] == $currentFile) {
        $status = $row['status_name'];
    }


    if ($md == 'Core') {
        $access_page_list[] = $row['related_pages'];
    } else {
        $restrict_page_list[] = $row['related_pages'];
    }

}

// var_dump($access_page_list);
// echo '<hr>';
// var_dump($restrict_page_list);

if (in_array($currentFile, $access_page_list)) {
    $permission = 3; // Full access
    echo 'Tis Tis';
} else if (in_array($currentFile, $restrict_page_list)) {
    $permission = 0; // Full access
    echo 'PUK PUK';
} else {
    // আপনার নরমাল permission লজিক এখানে চলবে
    echo $usr . '/' . $userlevel . '/' . $currentFile;
    $stmt = $conn->prepare("SELECT * FROM permission_map WHERE (email=? OR email=? OR userlevel=? ) and page_name=?   ORDER BY (email != '') DESC LIMIT 1");
    $stmt->bind_param('ssss', $usr, $email_null, $userlevel, $currentFile);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res) {
        error_log("DB Error: " . $stmt->error);
        $permission = 0;
    }
    $permission_data = $res->fetch_assoc();
    $stmt->close();
    // var_dump($permission_data);

    $permission = $permission_data['permission'] ?? 0;
}

$permission = 3;
echo "%%" . $status;
echo ".........." . $permission;

include_once('page_load_query.php');

if ($permission < 2) {
    include_once('block-print.php');
}

if ($permission == 0) {
    $error_message = "Access Denied: You do not have permission to access this page.";
    // header('Location: access-denied.php');
    include_once('pages/access-denied.php');
    exit;
}

