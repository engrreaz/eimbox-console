<?php
require_once '../core/config.php';
require_once '../core/db.php'; // $conn = new mysqli(...);

// Get sccode
$new_sccode = $_GET['new_sccode'] ?? '';
if (!$new_sccode)
    exit('Invalid code');

// Logo
$logo_path = dirname(dirname(__DIR__)) . "/logo/{$new_sccode}.png";
// echo $logo_path;
// $logo_exists = file_exists($logo_path);

// Administrators
$admins = [];
$stmt = $conn->prepare("SELECT email, profilename FROM usersapp WHERE sccode=? AND userlevel='Administrator'");
$stmt->bind_param("s", $new_sccode);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}
$stmt->close();

// Active session year
$currentYear = date('y');
$stmt2 = $conn->prepare("SELECT syear FROM sessionyear WHERE sccode=? AND active=1 AND syear LIKE ?");
$like = "%$currentYear%";
$stmt2->bind_param("ss", $new_sccode, $like);
$stmt2->execute();
$res2 = $stmt2->get_result();
$activeSession = $res2->fetch_assoc();
$stmt2->close();

// Global settings
$stmt3 = $conn->prepare("SELECT * FROM globalsettings WHERE sccode=? LIMIT 1");
$stmt3->bind_param("s", $new_sccode);
$stmt3->execute();
$res3 = $stmt3->get_result();
$globalSettings = $res3->fetch_assoc();
$stmt3->close();

// Settings
$settingsArr = [];
$stmt4 = $conn->prepare("SELECT setting_title, settings_value FROM settings WHERE sccode=?");
$stmt4->bind_param("s", $new_sccode);
$stmt4->execute();
$res4 = $stmt4->get_result();
while ($row = $res4->fetch_assoc()) {
    $settingsArr[$row['setting_title']] = $row['settings_value'];
}
$stmt4->close();


// Include the HTML template
include 'settings_form_template.php';