<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

$slot = isset($_COOKIE['slot']) ? $_COOKIE['slot'] : (isset($_GET['slot']) ? $_GET['slot'] : ($_POST['slot'] ?? ''));
$session = isset($_COOKIE['session']) ? $_COOKIE['session'] : (isset($_GET['session']) ? $_GET['session'] : ($_POST['session'] ?? ''));

if (empty($session)) {
    echo json_encode(['status' => 'error', 'msg' => 'Session year is missing']);
    exit;
}

// Check if items already exist
$check = $conn->query("SELECT COUNT(*) AS cnt FROM financesetup WHERE sccode='$sccode' AND sessionyear='$session'");
$existingCount = 0;
if ($check && $r = $check->fetch_assoc()) {
    $existingCount = intval($r['cnt']);
}

// Fetch default finance items from sccode = '0' or active template
$defList = [];
$defSql = "SELECT * FROM financesetup WHERE sccode = '0' ORDER BY slno ASC";
$defRs = $conn->query($defSql);
if ($defRs && $defRs->num_rows > 0) {
    while ($row = $defRs->fetch_assoc()) {
        $defList[] = $row;
    }
}

// Fallback hardcoded defaults if sccode=0 has no records
if (empty($defList)) {
    $defList = [
        ['particulareng' => 'Admission Fee', 'particularben' => 'ভর্তি ফি', 'month' => '1', 'new_only' => 1, 'splitable' => 0, 'inexin' => 1, 'inexex' => 0],
        ['particulareng' => 'Monthly Tuition Fee', 'particularben' => 'মাসিক বেতন', 'month' => '0', 'new_only' => 0, 'splitable' => 0, 'inexin' => 1, 'inexex' => 0],
        ['particulareng' => 'Session Charge', 'particularben' => 'সেশন চার্জ', 'month' => '1', 'new_only' => 0, 'splitable' => 0, 'inexin' => 1, 'inexex' => 0],
        ['particulareng' => 'Exam Fee (Half-Yearly)', 'particularben' => 'অর্ধবার্ষিক পরীক্ষা ফি', 'month' => '6', 'new_only' => 0, 'splitable' => 0, 'inexin' => 1, 'inexex' => 0],
        ['particulareng' => 'Exam Fee (Annual)', 'particularben' => 'বার্ষিক পরীক্ষা ফি', 'month' => '11', 'new_only' => 0, 'splitable' => 0, 'inexin' => 1, 'inexex' => 0],
        ['particulareng' => 'Identity Card Fee', 'particularben' => 'পরিচয়পত্র ফি', 'month' => '1', 'new_only' => 1, 'splitable' => 0, 'inexin' => 1, 'inexex' => 0]
    ];
}

$inserted = 0;
$startSl = $existingCount + 1;

foreach ($defList as $item) {
    $icode = uniqid();
    $eng = $conn->real_escape_string($item['particulareng'] ?? '');
    $ben = $conn->real_escape_string($item['particularben'] ?? '');
    $mon = strval($item['month'] ?? '0');
    $new_only = intval($item['new_only'] ?? 0);
    $splitable = intval($item['splitable'] ?? 0);
    $inexin = intval($item['inexin'] ?? 1);
    $inexex = intval($item['inexex'] ?? 0);
    $sub_head = intval($item['sub_head'] ?? 0);

    $sql = "INSERT INTO financesetup (sccode, slot, sessionyear, slno, itemcode, particulareng, particularben, month, inexin, inexex, new_only, splitable, sub_head)
            VALUES ('$sccode', '$slot', '$session', '$startSl', '$icode', '$eng', '$ben', '$mon', '$inexin', '$inexex', '$new_only', '$splitable', '$sub_head')";
    if ($conn->query($sql)) {
        $inserted++;
        $startSl++;
    }
}

echo json_encode(['status' => 'success', 'msg' => "Successfully imported {$inserted} default payment items."]);
