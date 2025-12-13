<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$id = $_POST['id'];
$accno = $_POST['accno'];
$acctype = $_POST['acctype'];
$bankname = $_POST['bankname'];
$branch = $_POST['branch'];

if ($id == "") {
    // ADD NEW
    $sql = "INSERT INTO bankinfo (sccode, accno, acctype, bankname, branch)
            VALUES ('$sccode', '$accno', '$acctype', '$bankname', '$branch')";
    mysqli_query($conn, $sql);

    echo "<div class='alert alert-success'>New Bank Added</div>";
} else {
    // UPDATE
    $sql = "UPDATE bankinfo SET 
            accno='$accno',
            acctype='$acctype',
            bankname='$bankname',
            branch='$branch'
            WHERE id='$id'";

    mysqli_query($conn, $sql);

    echo "<div class='alert alert-primary'>Updated Successfully</div>";
}
