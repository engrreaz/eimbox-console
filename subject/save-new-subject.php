<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

// ==========================
// INPUT SANITIZE
// ==========================
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$tail = isset($_POST['tail']) ? intval($_POST['tail']) : 0;
$subcode = isset($_POST['subcode']) ? intval($_POST['subcode']) : 0;
$sccode = isset($_POST['sccode']) ? intval($_POST['sccode']) : 0;
$subsh = isset($_POST['subsh']) ? trim($_POST['subsh']) : '';


$sube = isset($_POST['sube']) ? trim($_POST['sube']) : '';
$subb = isset($_POST['subb']) ? trim($_POST['subb']) : '';

$sube = mysqli_real_escape_string($conn, $sube);
$subb = mysqli_real_escape_string($conn, $subb);

// ==========================
// DELETE
// ==========================
if ($tail == 1 && $id > 0) {

    $sql = "DELETE FROM subjects 
            WHERE id='$id' 
            AND sccode='$sccode'";

    if ($conn->query($sql)) {
        echo "<span class='text-success'>Deleted</span>";
    } else {
        echo "<span class='text-danger'>Delete Failed</span>";
    }
    exit;
}

// ==========================
// VALIDATION
// ==========================

if ($is_admin <= 4) {
    if ($subcode < 401 || $subcode > 800) {
        echo "<span class='text-danger'>Invalid Subject Code</span>";
        exit;
    }
}


if ($sube == '' || $subb == '') {
    echo "<span class='text-danger'>All fields required</span>";
    exit;
}

// ==========================
// DUPLICATE CHECK
// ==========================
$dupSql = "SELECT id FROM subjects 
           WHERE subcode='$subcode' 
           AND sccategory='$sctype'
           AND id != '$id'";

$dupRes = $conn->query($dupSql);
if ($dupRes && $dupRes->num_rows > 0) {
    echo "<span class='text-danger'>Subject Code Already Exists</span>";

    exit;
}

// ==========================
// UPDATE
// ==========================
if ($id > 0) {

    $sql = "UPDATE subjects SET
                subcode      ='$subcode',
                subject      ='$sube',
                subben       ='$subb',
                subshname    ='$subsh',
                modifieddate = NOW()
            WHERE id='$id'
            AND sccode='$sccode'";

    if ($conn->query($sql)) {
        echo "$sql <span class='text-success'>Updated Successfully</span>";
    } else {
        echo "<span class='text-danger'>Update Failed</span>";
    }

}
// ==========================
// INSERT
// ==========================
else {

    $sql = "INSERT INTO subjects
            (sccode, sccategory, subcode, subject, subben, subshname, ncode, fourth)
            VALUES
            ('$sccode', '$sctype', '$subcode', '$sube', '$subsh', '$subb', 0, 0)";

    if ($conn->query($sql)) {
        echo "<span class='text-success'>Saved Successfully</span>";
    } else {
        echo "<span class='text-danger'>Save Failed</span>";
    }
}
?>