<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'] ?? '';
$session = $_POST['session'] ?? '';
$stid = $_POST['stid'] ?? '';
$roll = $_POST['roll'] ?? '';
$cls = $_POST['cls'] ?? '';
$sec = $_POST['sec'] ?? '';
$amount = (float)($_POST['amount'] ?? 0);
$note = $_POST['note'] ?? '';

if (!$stid || $amount <= 0) {
    echo 'Invalid data';
    exit;
}

// Get fine particular
$finSet = "SELECT particulareng, particularben, itemcode FROM financesetup 
           WHERE sccode=? AND sessionyear LIKE ? AND particulareng LIKE '%Fine%' 
           ORDER BY id LIMIT 1";

$stmt = $conn->prepare($finSet);
$sessionWildcard = "%$session%";
$stmt->bind_param('ss', $sccode, $sessionWildcard);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo 'Fine Item not found';
    exit;
}

$finrow = $result->fetch_assoc();

// Insert fine record
$cur_mon = date('m');
$sql = "INSERT INTO stfinance 
        (sccode, sessionyear, classname, sectionname, stid, rollno, partid,
         itemcode, particulareng, particularben, amount, month, idmon,
         setupdate, setupby, payableamt, modifieddate, paid, dues,
         last_update, validate, validationtime)
        VALUES (?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, 0, ?, ?, 1, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param('issssisssiissisiss', 
    $sccode, $session, $cls, $sec, $stid, $roll, 
    $finrow['itemcode'], $finrow['particulareng'], $finrow['particularben'],
    $amount, $cur_mon, $cur, $usr, $amount, $cur, $amount, $cur, $cur);

if (!$stmt->execute()) {
    echo 'Insert Error: ' . $stmt->error;
    exit;
}

echo 'Success';
?>
