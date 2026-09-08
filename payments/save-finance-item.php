<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id = intval($_POST['id'] ?? 0);
$eng = trim($_POST['eng'] ?? '');
$ben = trim($_POST['ben'] ?? '');
$mon = strval($_POST['mon'] ?? '0');
$new = isset($_POST['new_only']) ? intval($_POST['new_only']) : 0;
$split = isset($_POST['splitable']) ? intval($_POST['splitable']) : 0;
$acc_head = intval($_POST['acc_head'] ?? 0);

$slot = isset($_COOKIE['slot']) ? $_COOKIE['slot'] : (isset($_GET['slot']) ? $_GET['slot'] : ($_POST['slot'] ?? ''));
$sy = isset($_COOKIE['session']) ? $_COOKIE['session'] : (isset($_GET['session']) ? $_GET['session'] : ($_POST['session'] ?? $sessionyear));

// Determine inexin / inexex if sub_head is chosen
$inin = 1;
$exex = 0;
if ($acc_head > 0) {
    $subq = $conn->query("SELECT income, expenditure FROM account_sub_head WHERE id='$acc_head' AND sccode='$sccode'");
    if ($subq && $subq->num_rows > 0) {
        $subrow = $subq->fetch_assoc();
        $inin = intval($subrow['income'] ?? 1);
        $exex = intval($subrow['expenditure'] ?? 0);
    }
}

if ($id == 0) {
    $uid = uniqid();
    
    // Get next slno
    $slno = 1;
    $slq = $conn->query("SELECT COALESCE(MAX(slno), 0) + 1 AS next_sl FROM financesetup WHERE sccode='$sccode' AND sessionyear='$sy'");
    if ($slq && ($slrow = $slq->fetch_assoc())) {
        $slno = intval($slrow['next_sl']);
    }

    $stmt = $conn->prepare("INSERT INTO financesetup (sccode, slot, sessionyear, particulareng, particularben, month, inexin, inexex, new_only, splitable, itemcode, slno, sub_head) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiiiiisii", $sccode, $slot, $sy, $eng, $ben, $mon, $inin, $exex, $new, $split, $uid, $slno, $acc_head);
    if ($stmt->execute()) {
        echo "<span class='text-success'>success: Item added successfully</span>";
    } else {
        echo "<span class='text-danger'>error: " . $conn->error . "</span>";
    }
    $stmt->close();
} else {
    // Get existing itemcode
    $icode = '';
    $iq = $conn->query("SELECT itemcode FROM financesetup WHERE id='$id' AND sccode='$sccode'");
    if ($iq && ($irow = $iq->fetch_assoc())) {
        $icode = $irow['itemcode'];
    }

    $stmt = $conn->prepare("UPDATE financesetup SET particulareng=?, particularben=?, month=?, new_only=?, splitable=?, sub_head=?, inexin=?, inexex=? WHERE id=? AND sccode=?");
    $stmt->bind_param("sssiiiiiss", $eng, $ben, $mon, $new, $split, $acc_head, $inin, $exex, $id, $sccode);
    if ($stmt->execute()) {
        if (!empty($icode)) {
            $conn->query("UPDATE financesetupvalue SET new_only='$new', splitable='$split' WHERE itemcode='$icode' AND sccode='$sccode'");
        }
        echo "<span class='text-success'>success: Item updated successfully</span>";
    } else {
        echo "<span class='text-danger'>error: " . $conn->error . "</span>";
    }
    $stmt->close();
}

