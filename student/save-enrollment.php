<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================
// RECEIVE FORM DATA
// ==========================
$sy = $_POST['session'];
$classname = $_POST['class'];
$sectionname = $_POST['section'];
$rollno = $_POST['rollno'];
$stid = $_POST['stid'];
$version = $_POST['version'];
$medium = $_POST['medium'];

$stnameeng = $_POST['stnameeng'];
$stnameben = $_POST['stnameben'];

$fname = $_POST['fname'];
$fprof = $_POST['fprof'];
$fmobile = $_POST['fmobile'];
$mname = $_POST['mname'];
$mprof = $_POST['mprof'];
$mmobile = $_POST['mmobile'];

$previll = $_POST['previll'];
$prepo = $_POST['prepo'];
$preps = $_POST['preps'];
$predist = $_POST['predist'];

$pervill = $_POST['pervill'];
$perpo = $_POST['perpo'];
$perps = $_POST['perps'];
$perdist = $_POST['perdist'];

$fnid = $_POST['fnid'];
$mnid = $_POST['mnid'];

$dob2 = str_replace('/', '-', $_POST['dob']);
$dob = date('Y-m-d', strtotime($dob2));

$religion = $_POST['religion'];
$brn = $_POST['brn'];
$gender = $_POST['gender'];

$guarname = $_POST['guarname'];
$guaradd = $_POST['guaradd'];
$guarrelation = $_POST['guarrelation'];
$guarmobile = $_POST['guarmobile'];

$tcno = $_POST['tcno'];
$preins = $_POST['preins'];
$preinsadd = $_POST['preinsadd'];

$doa = date('Y-m-d', strtotime($_POST['doa']));

$slot = $_POST['slot'];
$sessionyear =  $_POST['session'];

// $sscyear        = ($_POST['sscyear'] == '' ? 0 : $_POST['sscyear']);
// $sscregd        = $_POST['sscregd'];
// $sscroll        = $_POST['sscroll'];
// $sscresult      = $_POST['sscresult'];

$bgroup = $_POST['bgroup'];
$disables = $_POST['disables'];
$height = ($_POST['height'] == '' ? 0 : $_POST['height']);
$weight = ($_POST['weight'] == '' ? 0 : $_POST['weight']);

$guarnid = $_POST['guarnid'];
$waiver = ($_POST['waiver'] == '' ? 100 : (100 - $_POST['waiver']));
$quota = $_POST['quota'];

$mnoself = $_POST['mobileself'];
$rfid = $_POST['rfid'];
$uid = $_POST['uniqueid'];

$fnameben = $_POST['fnameben'];
$mnameben = $_POST['mnameben'];
$falive = $_POST['falive'];
$malive = $_POST['malive'];
$guarnameben = $_POST['guarnameben'];
$guaremail = $_POST['guaremail'];
$guarmobile2 = $_POST['guarmobile2'];
$guaremail2 = $_POST['guaremail2'];



// ==========================
// IMAGE HANDLING (BASE64)
// ==========================
$newBase64 = $_POST['photo'] ?? '' ; // cropped image

$photo_new_name = "";
$dopp = "2000-12-31";

// পুরাতন ছবি পাওয়া গেলে replace হবে
$oldPhoto = "";
$check = $conn->query("SELECT photo_id FROM students WHERE stid='$stid' AND sccode='$sccode'");
if ($check->num_rows > 0) {
    $r = $check->fetch_assoc();
    $oldPhoto = $r['photo_id'];
}

// যদি নতুন ইমেজ আসে
if (!empty($newBase64)) {

    // Base64 → JPEG File
    $image_parts = explode(";base64,", $newBase64);
    $image_base64 = base64_decode($image_parts[1]);
    $photo_new_name = "IMG_" . $stid . "_" . time() . ".jpg";

    $savePath = "../students/" . $photo_new_name;

    // পুরাতন ছবি থাকলে মুছে ফেলো
    if (!empty($oldPhoto) && file_exists("../students/" . $oldPhoto)) {
        unlink("../students/" . $oldPhoto);
    }

    file_put_contents($savePath, $image_base64);

} else {
    // নতুন ইমেজ না থাকলে পুরাতনটাই ব্যবহার
    $photo_new_name = $oldPhoto;
}


// ==========================
// CHECK EXISTING STUDENT
// ==========================
$sql0 = "SELECT * FROM students WHERE stid='$stid' AND sccode='$sccode'";
$result0 = $conn->query($sql0);

if ($result0->num_rows > 0) {

    // ======================
    // UPDATE MODE
    // ======================
    $query3 = "UPDATE students SET 
        stnameeng='$stnameeng',
        stnameben='$stnameben',
        fname='$fname',
        fprof='$fprof',
        fmobile='$fmobile',
        fnid='$fnid',
        mname='$mname',
        mprof='$mprof',
        mmobile='$mmobile',
        mnid='$mnid',

        previll='$previll',
        prepo='$prepo',
        preps='$preps',
        predist='$predist',

        pervill='$pervill',
        perpo='$perpo',
        perps='$perps',
        perdist='$perdist',

        dob='$dob',
        religion='$religion',
        brn='$brn',
        gender='$gender',

        guarname='$guarname',
        guaradd='$guaradd',
        guarrelation='$guarrelation',
        guarmobile='$guarmobile',

        tcno='$tcno',
        preins='$preins',
        preinsadd='$preinsadd',
        doa='$doa',
        modify='$td',

        photo_id='$photo_new_name',
        photo_pick_date='$dopp',



        bgroup='$bgroup',
        height='$height',
        weight='$weight',
        disables='$disables',
        guarnid='$guarnid',
        icardno='$rfid',
        mobileself='$mnoself',
        uniqueid='$uid',

        fnameben='$fnameben',
        mnameben='$mnameben',
        falive='$falive',
        malive='$malive',
        guarnameben='$guarnameben',
        guaremail='$guaremail',
        guaremail2='$guaremail2',
        guarmobile2='$guarmobile2',

        mobileself='$mnoself',
        uniqueid = '$uid',

        modifieddate='$cur'
    WHERE stid='$stid' AND sccode='$sccode'";

    // sessioninfo update
    $conn->query("UPDATE sessioninfo SET 
        rate='$waiver', 
        sector='$quota',
        rfidtag='$rfid',
        validate=0,
        slot='$slot',
        modifieddate='$cur'
    WHERE stid='$stid' AND sccode='$sccode' AND sessionyear LIKE '%$sessionyear%'");


    $action = "Update Profile";

} else {

    // ======================
    // INSERT MODE
    // ======================
    $conn->query("INSERT INTO sessioninfo 
        (id, stid, sessionyear, classname, sectionname, rollno, sccode, rate, sector, rfidtag, slot)
        VALUES (NULL, '$stid', '$sessionyear', '$classname', '$sectionname', '$rollno', '$sccode',
                '$waiver', '$quota', '$rfid', '$slot')");

    $query3 = "INSERT INTO students 
        (id, sccode, stid, stnameeng, stnameben, fname, fprof, fmobile, mname, mprof, mmobile,
        previll, prepo, preps, predist,
        pervill, perpo, perps, perdist,
        dob, religion, brn, gender,
        guarname, guaradd, guarrelation, guarmobile, tcno, preins, preinsadd,
        doa, modify, photo_id, photo_pick_date, fnid, mnid,
        bgroup, height, weight, disables, guarnid, icardno, mobileself, uniqueid,
        fnameben, mnameben, falive, malive, guarnameben, guaremail, guarmobile2, guaremail2)
        
        VALUES (NULL, '$sccode', '$stid', '$stnameeng', '$stnameben', '$fname', '$fprof', '$fmobile',
                '$mname', '$mprof', '$mmobile',
                '$previll', '$prepo', '$preps', '$predist',
                '$pervill', '$perpo', '$perps', '$perdist',
                '$dob', '$religion', '$brn', '$gender',
                '$guarname', '$guaradd', '$guarrelation', '$guarmobile',
                '$tcno', '$preins', '$preinsadd',
                '$doa', '$td', '$photo_new_name', '$dopp', '$fnid', '$mnid',
                '$bgroup', '$height', '$weight', '$disables', '$guarnid',
                '$rfid', '$mnoself', '$uid',
                '$fnameben', '$mnameben', '$falive', '$malive', '$guarnameben', '$guaremail',
                '$guarmobile2', '$guaremail2')";

    $action = "New Profile";
}


// ==========================
// EXECUTE QUERY
// ==========================
if ($conn->query($query3) === TRUE) {
    echo "SUCCESS";
} else {
    echo "ERROR";
}


// ==========================
// ACTIVITY LOG
// ==========================
$module = "Student";
$notes = "$action for $stnameeng ($stid)";
// include "save-track-book.php";

?>