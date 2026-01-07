<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/core-val.php';
require_once '../core/global_values.php';

$id = $_POST['id'] ?? '';
if (!$id) {
    echo "❌ Invalid ID";
    exit;
}

$q = $conn->query("SELECT * FROM registrations WHERE id='$id'");
if ($q->num_rows == 0) {
    echo "❌ শিক্ষার্থী পাওয়া যায়নি";
    exit;
}
$d = $q->fetch_assoc();

$qq = $conn->query("SELECT stid FROM students WHERE sccode='$sccode' ORDER BY stid desc limit 1");
if ($qq->num_rows == 0) {
    echo "❌ শিক্ষার্থী পাওয়া যায়নি";
    exit;
}
$dd = $qq->fetch_assoc();

if ($d['stid'] != '' && $d['stid'] != null) {
    echo "❌ Already Admitted";
    exit;
}
// ফাইনাল ভর্তি ইনসার্ট students টেবিলে
$stid = $dd['stid'] + 1 ?: $sccode . '0001';

$sec = $d['admit_section'] ?? '';


$sql = "INSERT INTO students (stid, stnameeng, stnameben, fname, mname, gender, bgroup, dob, sccode)
        VALUES ('{$stid}', '{$d['stnameeng']}', '{$d['stnameben']}', '{$d['fname']}', '{$d['mname']}',
                '{$d['gender']}', '{$d['bgroup']}', '{$d['dob']}', '{$d['sccode']}')";



if ($conn->query($sql)) {

    $sinfo = "INSERT INTO sessioninfo (stid, sccode, sessionyear, classname, sectionname, rollno)
          VALUES ('{$stid}', '{$d['sccode']}', '{$d['sessionyear']}', '{$d['admit_class']}', '$sec', '{$d['meritplace']}')";

    if ($conn->query($sinfo)) {

        $regd = "UPDATE registrations set stid = '{$stid}' WHERE id = '{$id}'";
        if ($conn->query($regd)) {

            $source = APP_PATH . "uploads/photos/" . $d['photo'];
            $dest = BASE_PATH . "students/" . $stid . ".jpg";

            echo $source . "\n" . $dest . "\n";

            if (file_exists($dest)) {
                if (copy($source, $dest)) {
                    echo "Image copied successfully!";
                } else {
                    echo "Failed to copy image.";
                }
            } else {
                echo "Source image not found!";
            }


            echo "✅ শিক্ষার্থী চূড়ান্তভাবে ভর্তি করা হয়েছে";
        }

    }
} else {
    echo "⚠️ Database Error: " . $conn->error;
}
