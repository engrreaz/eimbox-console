<?php
require_once '../core/config.php';
require_once '../core/db.php'; // $conn = new mysqli(...);

// -------------------- FLUSH FIX --------------------
set_time_limit(0);
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);
ob_implicit_flush(1);
while (ob_get_level() > 0) ob_end_flush();


if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', '1');
}
// ---------------------------------------------------

// Optional: প্রাথমিক padding — ব্রাউজারকে ফোর্স করতে
echo str_repeat(' ', 2048);
flush();

$sccode = $_GET['sccode'] ?? '';
if (!$sccode) exit('Invalid code');

$rootuser = '';
$sy = date('Y');
$td = date('Y-m-d');
$cur = date('Y-m-d H:i:s');

$sql_check = "SELECT rootuser FROM scinfo WHERE sccode = '$sccode' LIMIT 1";
$result = $conn->query($sql_check);
if ($result && $result->num_rows >= 1) {
    $row = $result->fetch_assoc();
    $rootuser = $row['rootuser'];
    echo "Rootuser: " . htmlspecialchars($rootuser) . "<br>\n";
    echo str_repeat(' ', 1024);
    flush();
}

// ---------------------------------------------------
$sql_check = "SELECT id, examtitle FROM examlist WHERE sccode = '$sccode' AND sessionyear='$sy' ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql_check);
if ($result && $result->num_rows >= 1) {
    $row = $result->fetch_assoc();
    $exam_id = $row['id'];
    $exam = $row['examtitle'];
}

echo '<div class="mt-8">';
echo '<span class="text-info fw-bold"> Generating Mock / Sample Marks </span>';
echo '<hr class="m-0 p-0 mb-1" />';
echo str_repeat(' ', 1024);
flush();
// ---------------------------------------------------

$sql_checkx = "SELECT slot, areaname, subarea FROM areas WHERE sccode = '$sccode' AND user='$rootuser' AND sessionyear='$sy'";
$resultr = $conn->query($sql_checkx);

if ($resultr && $resultr->num_rows > 0) {
    $total = $resultr->num_rows;
    $count = 0;

    while ($rg = $resultr->fetch_assoc()) {
        $count++;
        $sl = $rg['slot'];
        $cc = $rg['areaname'];
        $ss = $rg['subarea'];

        echo "<br><b>$count/$total → $cc / $ss</b><br>\n";
        echo str_repeat(' ', 1024);
        flush();

        $subsetup = [];
        $sql_sub = "SELECT * FROM subsetup WHERE sccode='$sccode' AND sessionyear='$sy' AND classname='$cc' AND sectionname='$ss'";
        $res_sub = $conn->query($sql_sub);
        while ($r = $res_sub->fetch_assoc()) {
            $subsetup[] = $r;
        }

        $sql_st = "SELECT stid FROM sessioninfo WHERE sccode='$sccode' AND slot='$sl' AND sessionyear='$sy' AND classname='$cc' AND sectionname='$ss'";
        $res_st = $conn->query($sql_st);
        while ($r = $res_st->fetch_assoc()) {
            $stid = $r['stid'];
            foreach ($subsetup as $subject) {
                $subcode = $subject['subject'];
                $sql_rand = "SELECT fullmark, subj, obj, pra, sub_final, obj_final, pra_final, markobt, on100, gp, gl
                             FROM stmark WHERE subject='$subcode' AND classname='$cc' ORDER BY RAND() LIMIT 1";
                $res_rand = $conn->query($sql_rand);
                if ($res_rand && $res_rand->num_rows > 0) {
                    $base = $res_rand->fetch_assoc();
                    $sql_insert = "INSERT INTO stmark
                    (sccode, sessionyear, slot, exam, examid, examtype, classname, sectionname, subject,
                    fullmark, stid, subj, obj, pra, sub_final, obj_final, pra_final,
                    markobt, on100, gp, gl, entrydate, entryby)
                    VALUES
                    ('$sccode', '$sy', '$sl', '$exam', '$exam_id', 'PE', '$cc', '$ss', '$subcode',
                    '{$base['fullmark']}', '$stid', '{$base['subj']}', '{$base['obj']}', '{$base['pra']}',
                    '{$base['sub_final']}', '{$base['obj_final']}', '{$base['pra_final']}',
                    '{$base['markobt']}', '{$base['on100']}', '{$base['gp']}', '{$base['gl']}',
                    NOW(), '$rootuser')";
                    $conn->query($sql_insert);

                    echo "✅ Class : $cc, Section : $ss, STID : $stid - $subcode - Done<br>\n";
                    echo str_repeat(' ', 1024);
                    flush();
                }
            }
        }
    }
}

echo "<br><b>✅ Completed!</b></div>";
flush();

