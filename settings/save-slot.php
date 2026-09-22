<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id = trim($_POST['id'] ?? '');
$slotname = mysqli_real_escape_string($conn, trim($_POST['slotname'] ?? ''));
$merit = intval($_POST['merit'] ?? 0);
$decimal_mark = intval($_POST['decimal_mark'] ?? $_POST['decimal'] ?? 0);
$disp_entry_mark = intval($_POST['disp_entry_mark'] ?? 0);
$trans_name_eng = intval($_POST['trans_name_eng'] ?? 1);
$trans_name_ben = intval($_POST['trans_name_ben'] ?? 1);
$parents = mysqli_real_escape_string($conn, trim($_POST['parents'] ?? 'DOSO'));
$cus_report = mysqli_real_escape_string($conn, trim($_POST['cus_report'] ?? 'default'));

if (empty($slotname)) {
    echo "Error: Slot name is required";
    exit;
}

if ($id === "") {
    // Insert
    $q = "INSERT INTO slots (sccode, slotname, merit, decimal_mark, disp_entry_mark, trans_name_eng, trans_name_ben, parents, cus_report)
          VALUES ('$sccode', '$slotname', '$merit', '$decimal_mark', '$disp_entry_mark', '$trans_name_eng', '$trans_name_ben', '$parents', '$cus_report')";
    if (mysqli_query($conn, $q)) {
        echo "Slot Created Successfully";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    // Update
    $id = intval($id);
    $q = "UPDATE slots SET 
            slotname = '$slotname',
            merit = '$merit',
            decimal_mark = '$decimal_mark',
            disp_entry_mark = '$disp_entry_mark',
            trans_name_eng = '$trans_name_eng',
            trans_name_ben = '$trans_name_ben',
            parents = '$parents',
            cus_report = '$cus_report'
          WHERE id = '$id' AND sccode = '$sccode'";
    if (mysqli_query($conn, $q)) {
        echo "Slot Updated Successfully";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>