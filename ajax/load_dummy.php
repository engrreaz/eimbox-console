<?php
require_once '../core/config.php';
require_once '../core/db.php'; // $conn = new mysqli(...);

// Get sccode
$sccode = $_GET['sccode'] ?? '';
if (!$sccode)
    exit('Invalid code');

$rootuser = '';
$sy = date('Y');
$td = date('Y-m-d');
$cur = date('Y-m-d H:i:s');



$sql_check = "SELECT rootuser FROM scinfo WHERE sccode = '$sccode' LIMIT 1";
$result = $conn->query($sql_check);
if ($result && $result->num_rows >= 1) {
    $row = $result->fetch_assoc();
    $rootuser = $row['rootuser'];  // এখন $rootuser-এ ভ্যালু আছে
    echo "Rootuser: " . htmlspecialchars($rootuser) . '<br>';
}






// ‍slots check
$sql_check = "SELECT id FROM slots WHERE sccode = $sccode LIMIT 1";
// echo $sql_check;
$result = $conn->query($sql_check);

if ($result && $result->num_rows >= 1) {
    echo 'Slots Found';
} else {
    $conn->query("INSERT INTO slots(sccode, slotname) VALUES ('$sccode', 'School')");
    echo 'New Inserrted';
}



// Class/Section check
$sql_check = "SELECT id FROM areas WHERE sccode = $sccode LIMIT 1";
// echo $sql_check;
$result = $conn->query($sql_check);

if ($result && $result->num_rows >= 1) {
    echo 'Class/Section Found';
} else {


    $cls = ["Six", "Seven", "Eight", "Nine", "Ten"];
    $sec = ["Shapla", "Joba", "Beli", "Tagor", "Padma", "Meghna", "Gomoti", "Jamuna", "Doel", "Koel", "Mayna"];


    foreach ($cls as $c) {
        // $sec থেকে র্যান্ডম এলিমেন্ট

        if ($c == 'Six' || $c == 'Seven' || $c == 'Eight') {
            $s = $sec[array_rand($sec)];
        } else {
            $s = 'Science';
        }

        $sql = "INSERT INTO areas(user, slot, medium, version, areaname, subarea, sessionyear, yesno, entrytime, sccode, modifieddate) 
            VALUES ('$rootuser', 'School', 'Bengali', 'Bengali', '$c', '$s', '$sy', 1, '$cur', '$sccode', '$cur')";

        if ($conn->query($sql)) {
            echo "Inserted $c -> $s<br>";
        } else {
            echo "Error inserting $c -> $s: " . $conn->error . "<br>";
        }
    }


}




// ***************************************************************
// ***************************************************************
// ***************************************************************

echo '<div>';
echo '<hr class="m-0 p-0 mb-1" />';

// Areas থেকে সব রো ফেচ
$sql_checkx = "SELECT slot, areaname, subarea FROM areas WHERE sccode = '$sccode' AND user='$rootuser' AND sessionyear='$sy'";
$result = $conn->query($sql_checkx);

if ($result && $result->num_rows > 0) {
    while ($r = $result->fetch_assoc()) {
        $sl = $r['slot'];
        $cc = $r['areaname'];
        $ss = $r['subarea'];

        echo '<span style="display:inline-block; width:150px;">' . $cc . '</span>';
        echo '<span style="display:inline-block; width:150px;">' . $ss . '</span>';


        // Check if subsetup exists
        $sql_check_sub = "SELECT id FROM subsetup WHERE sccode = '$sccode' AND slot='$sl' AND sessionyear='$sy' AND classname='$cc' AND sectionname='$ss'";
        $res_sub = $conn->query($sql_check_sub);

        if ($res_sub && $res_sub->num_rows > 0) {
            ?>
            Subject Already Found
            <div class="float-end"><i class="bi bi-check-circle-fill text-secondary"></i></div>
            <hr class="m-1 p-0 " />
            <?php
        } else {
            // sccode=0 দিয়ে ডাটা ফেচ
            $sql_default = "SELECT * FROM subsetup WHERE sccode=0 AND sessionyear='$sy' AND classname='$cc'";
            $res_default = $conn->query($sql_default);

            if ($res_default && $res_default->num_rows > 0) {
                while ($row_def = $res_default->fetch_assoc()) {
                    // সব কলাম নিয়ে ইনসার্ট
                    $cols = [];
                    $vals = [];
                    foreach ($row_def as $col => $val) {
                        if ($col == 'id')
                            continue; // Auto-increment হলে বাদ
                        if ($col == 'sccode')
                            $val = $sccode;
                        if ($col == 'sectionname')
                            $val = $ss;

                        $cols[] = "`$col`";
                        $vals[] = "'" . $conn->real_escape_string($val) . "'";
                    }

                    $sql_insert = "INSERT INTO subsetup (" . implode(",", $cols) . ") VALUES (" . implode(",", $vals) . ")";
                    $conn->query($sql_insert);
                }
                ?>
                Default Subjects Inserted
                <div class="float-end"><i class="bi bi-floppy-fill text-success"></i></div>
                <hr class="m-1 p-0" />
                <?php
            }
        }
    }
}

echo '</div>';



// ‍slots check

$examcode = uniqid();
$sql_check = "SELECT id FROM examlist WHERE sccode = $sccode and sessionyear = '$sy' LIMIT 1";
$result = $conn->query($sql_check);

if ($result && $result->num_rows > 0) {
    echo 'Exam Found';
} else {
    $qu = "INSERT INTO examlist(sccode, sessionyear, slot, examtitle, examcode, exam_type, datestart, result_publish, status) 
                                    VALUES ('$sccode', '$sy', 'School', 'Model Test', '$examcode', 'PE', '$td', '$cur', 1 )";
    // echo $qu;
    $conn->query($qu);
    echo 'New Exam Inserrted';
}



$sql_check = "SELECT id FROM financesetup WHERE sccode = $sccode and sessionyear = '$sy' and slot='School' LIMIT 1";
$result = $conn->query($sql_check);

if ($result && $result->num_rows > 0) {
    echo 'Payment Settings Found';
} else {

    $en = ["Admission Fee", "Development Fee", "PF", "ICT Fee", "Tution Fee", "Exam Fee"];
    $bn = ["ভর্তি ফি", "উন্নয়ন ফি", "পি এফ", "আই.সি.টি. ফি", "মাসিক বেতন", "পরীক্ষা ফি"];
    $mon = [1, 1, 66, 1, 0, 66];
    $val = [600, 300, 100, 240, 180, 350];


    for ($i = 0; $i < 6; $i++) {
        $itemcode = uniqid();
        $qu = "INSERT INTO financesetup(sccode, sessionyear, slot, itemcode, particulareng, particularben, month) 
                    VALUES ('$sccode', '$sy', 'School', '$itemcode', '$en[$i]', '$bn[$i]', '$mon[$i]')";
        $conn->query($qu);

        $qu2 = "INSERT INTO financesetupvalue(sccode, sessionyear, slot, itemcode, amount, month) 
                    VALUES ('$sccode', '$sy', 'School', '$itemcode', '$val[$i]', '$mon[$i]')";
        $conn->query($qu2);
    }

    echo 'New Payment Item Inserrted';
}




echo '<div>';
echo '<hr class="m-0 p-0 mb-1" />';
echo '<span class="text-info fw-bold">Students Profile Entry</span>';
echo '<hr class="m-0 p-0 mb-1" />';

// Areas থেকে সব রো ফেচ
$sql_checkx = "SELECT slot, areaname, subarea FROM areas WHERE sccode = '$sccode' AND user='$rootuser' AND sessionyear='$sy'";
$result = $conn->query($sql_checkx);

if ($result && $result->num_rows > 0) {
    while ($r = $result->fetch_assoc()) {
        $sl = $r['slot'];
        $cc = $r['areaname'];
        $ss = $r['subarea'];

        echo '<span style="display:inline-block; width:150px;">' . $cc . '</span>';
        echo '<span style="display:inline-block; width:150px;">' . $ss . '</span>';

        // Check if subsetup exists
        $sql_check_sub = "SELECT id FROM sessioninfo WHERE sccode = '$sccode' AND slot='$sl' AND sessionyear='$sy' AND classname='$cc' AND sectionname='$ss'";
        $res_sub = $conn->query($sql_check_sub);

        if ($res_sub && $res_sub->num_rows > 0) {
            ?>
            Students Found
            <div class="float-end"><i class="bi bi-check-circle-fill text-secondary"></i></div>
            <hr class="m-1 p-0 " />
            <?php
        } else {
            // নতুন ছাত্র বানানোর সংখ্যা
            $count = rand(10, 30);

            // সর্বশেষ stid বের করা
            $stid = $sccode * 10000;
            $sql_check = "SELECT stid FROM sessioninfo WHERE sccode = '$sccode' ORDER BY stid DESC LIMIT 1";
            $result2 = $conn->query($sql_check);
            if ($result2 && $result2->num_rows >= 1) {
                $row = $result2->fetch_assoc();
                $stid = $row['stid'];
            }

            $stid++;

            // 🔹 র‍্যান্ডম পুরনো স্টুডেন্ট রেকর্ড ফেচ (একবারই)
            $sql_rand_stu = "SELECT stnameeng, stnameben, fname, mname, previll, prepo, preps, predist 
                             FROM students ORDER BY RAND() LIMIT 1";
            $rand_res = $conn->query($sql_rand_stu);
            $template_stu = ($rand_res && $rand_res->num_rows > 0) ? $rand_res->fetch_assoc() : [];

            for ($i = 0; $i < $count; $i++) {
                $rl = $i + 1;

                // sessioninfo ইনসার্ট
                $sql_sess = "INSERT INTO sessioninfo (stid, sessionyear, classname, sectionname, rollno, sccode, slot, medium, version, status) 
                 VALUES ('$stid', '$sy', '$cc', '$ss', '$rl', '$sccode', '$sl', 'Bengali', 'Bengali', 1)";
                $conn->query($sql_sess);

                // 🔹 প্রতিবার নতুন র‍্যান্ডম student বাছাই
                $sql_rand_stu = "SELECT stnameeng, stnameben, fname, mname, previll, prepo, preps, predist 
                     FROM students ORDER BY RAND() LIMIT 1";
                $rand_res = $conn->query($sql_rand_stu);
                $template_stu = ($rand_res && $rand_res->num_rows > 0) ? $rand_res->fetch_assoc() : [];

                if (!empty($template_stu)) {
                    $stnameeng = $conn->real_escape_string($template_stu['stnameeng']);
                    $stnameben = $conn->real_escape_string($template_stu['stnameben']);
                    $fname = $conn->real_escape_string($template_stu['fname']);
                    $mname = $conn->real_escape_string($template_stu['mname']);
                    $previll = $conn->real_escape_string($template_stu['previll']);
                    $prepo = $conn->real_escape_string($template_stu['prepo']);
                    $preps = $conn->real_escape_string($template_stu['preps']);
                    $predist = $conn->real_escape_string($template_stu['predist']);
                } else {
                    $stnameeng = "Student $stid";
                    $stnameben = "ছাত্র $stid";
                    $fname = "Father $stid";
                    $mname = "Mother $stid";
                    $previll = "Village";
                    $prepo = "Post";
                    $preps = "PS";
                    $predist = "District";
                }

                $sql_student = "INSERT INTO students (stid, sccode, stnameeng, stnameben, fname, mname, previll, prepo, preps, predist) 
                    VALUES ('$stid', '$sccode', '$stnameeng', '$stnameben', '$fname', '$mname', '$previll', '$prepo', '$preps', '$predist')";
                $conn->query($sql_student);

                $stid++;
            }


            echo "Inserted $count students<br>";
        }
    }
}

echo '</div>';



// ***********************************************************************************
// ***********************************************************************************
// ***********************************************************************************
// ***********************************************************************************
// ***********************************************************************************
// ***********************************************************************************
// ***********************************************************************************
// ***********************************************************************************
// ***********************************************************************************
// ***********************************************************************************
?>

<div class="col-12 text-end mt-3">
    <button type="button" class="btn btn-success" id="toStep4" onclick="fourthSetp();">Next</button>
</div>