<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

header('Content-Type: application/json');

$itemcode = trim($_POST['itemcode'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);
$slot = isset($_COOKIE['slot']) ? $_COOKIE['slot'] : (isset($_GET['slot']) ? $_GET['slot'] : ($_POST['slot'] ?? ''));
$session = isset($_COOKIE['session']) ? $_COOKIE['session'] : (isset($_GET['session']) ? $_GET['session'] : ($_POST['session'] ?? ''));

if (empty($itemcode)) {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid item code']);
    exit;
}

// 1. Set global item amount (classname = '', sectionname = '')
$checkGlobal = $conn->query("SELECT id FROM financesetupvalue WHERE sccode='$sccode' AND sessionyear='$session' AND slot='$slot' AND itemcode='$itemcode' AND classname='' AND sectionname=''");
if ($checkGlobal && $checkGlobal->num_rows > 0) {
    $row = $checkGlobal->fetch_assoc();
    $conn->query("UPDATE financesetupvalue SET amount='$amount', modifieddate='$cur' WHERE id='{$row['id']}'");
} else {
    $conn->query("INSERT INTO financesetupvalue (sccode, sessionyear, slot, itemcode, classname, sectionname, amount, modifieddate) VALUES ('$sccode', '$session', '$slot', '$itemcode', '', '', '$amount', '$cur')");
}

// 2. Fetch all classes and sections from areas for this school, session, and slot
$areas = $conn->query("SELECT areaname, subarea FROM areas WHERE sccode='$sccode' AND sessionyear='$session' AND (slot='$slot' OR slot='' OR slot IS NULL)");
$count = 0;

if ($areas && $areas->num_rows > 0) {
    while ($ar = $areas->fetch_assoc()) {
        $cls = $ar['areaname'];
        $sec = $ar['subarea'];

        // Update or insert for class + section
        $chk = $conn->query("SELECT id FROM financesetupvalue WHERE sccode='$sccode' AND sessionyear='$session' AND slot='$slot' AND itemcode='$itemcode' AND classname='$cls' AND sectionname='$sec'");
        if ($chk && $chk->num_rows > 0) {
            $r = $chk->fetch_assoc();
            $conn->query("UPDATE financesetupvalue SET amount='$amount', modifieddate='$cur' WHERE id='{$r['id']}'");
        } else {
            $conn->query("INSERT INTO financesetupvalue (sccode, sessionyear, slot, itemcode, classname, sectionname, amount, modifieddate) VALUES ('$sccode', '$session', '$slot', '$itemcode', '$cls', '$sec', '$amount', '$cur')");
        }
        $count++;
    }
}

echo json_encode(['status' => 'success', 'msg' => "Amount (৳ {$amount}) applied to all {$count} class sections successfully."]);
