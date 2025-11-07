<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../core/config.php';
require_once '../core/db.php';


header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);



$response = ['status' => 'error', 'msg' => 'Unknown error'];



$sccode = $_POST['sccode'] ?? '';
$sccode = (int) $sccode;

if (empty($sccode)) {
    echo json_encode(['status' => 'error', 'msg' => 'Missing sccode']);
    exit;
}



$sy = date('Y');
$cur = date('Y-m-d H:i:s');
// --- Active Session Update ---
$active_syear = $_POST['active_syear'] ?? '';
if (empty($active_syear)) {
    $conn->query("INSERT INTO sessionyear (id, sccode, syear, active, entryby, entrytime)
              VALUES (NULL, '$sccode', '$sy', 1, 'System Auto Settings', '$cur')");
}




$globalsetting1 = $_POST['globalsetting1'] ?? '';
if (empty($globalsetting1)) {
    $conn->query("INSERT INTO globalsettings (id, sccode, stattnd_sort, stattnd_multi, tattnd, collection, tattndradius, tattndout)
              VALUES (NULL, '$sccode', 'rollno', 1, 0, 0, 50, 0)");

}




if (!empty($_POST['settings']) && is_array($_POST['settings'])) {
    // Step 1: existing settings
    $existing = [];
    $res = $conn->query("SELECT setting_title FROM settings WHERE sccode='" . $conn->real_escape_string($sccode) . "'");
    while ($row = $res->fetch_assoc()) {
        $existing[] = trim($row['setting_title']);
    }



    // Step 2: insert new settings only
    foreach ($_POST['settings'] as $title => $value) {
        $title = trim($title);
        $value = trim($value);
        if ($title === '' || in_array($title, $existing))
            continue;

        $sql = "INSERT INTO settings (sccode, setting_title, settings_value) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed: " . $conn->error);
            continue;
        }
        $stmt->bind_param("iss", $sccode, $title, $value);
        if (!$stmt->execute()) {
            error_log("Execute failed for $title: " . $stmt->error);
        }
        $stmt->close();
    }
}



$response['status'] = 'success';
if (empty($response['msg']))
    $response['msg'] = 'Settings updated successfully';


echo json_encode($response);
exit;