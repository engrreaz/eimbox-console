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

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$q = $stmt->get_result();

if ($q->num_rows == 0) {
    echo "❌ শিক্ষার্থী পাওয়া যায়নি";
    exit;
}
$d = $q->fetch_assoc();

$stmt = $conn->prepare("SELECT stid FROM students WHERE sccode = ? ORDER BY stid DESC LIMIT 1");
$stmt->bind_param("s", $sccode);
$stmt->execute();
$qq = $stmt->get_result();

if ($qq->num_rows == 0) {
    echo "❌ শিক্ষার্থী পাওয়া যায়নি";
    exit;
}
$dd = $qq->fetch_assoc();

if (!empty($d['stid'])) {
    echo "❌ Already Admitted";
    exit;
}

$stid = ($dd['stid'] ?? 0) + 1 ?: "{$sccode}0001";
$sec = $d['admit_section'] ?? '';

$stmt = $conn->prepare("INSERT INTO students (stid, stnameeng, stnameben, fname, mname, gender, bgroup, dob, sccode) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $stid, $d['stnameeng'], $d['stnameben'], $d['fname'], $d['mname'], 
                  $d['gender'], $d['bgroup'], $d['dob'], $d['sccode']);

if ($stmt->execute()) {
    $stmt = $conn->prepare("INSERT INTO sessioninfo (stid, sccode, sessionyear, classname, sectionname, rollno) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $stid, $d['sccode'], $d['sessionyear'], $d['admit_class'], $sec, $d['meritplace']);
    
    if ($stmt->execute()) {
        $stmt = $conn->prepare("UPDATE registrations SET stid = ? WHERE id = ?");
        $stmt->bind_param("ss", $stid, $id);
        
        if ($stmt->execute()) {
            $chk_source = dirname(__DIR__) . "/uploads/photos/" . $d['photo'];
            $source = APP_PATH . "uploads/photos/" . $d['photo'];
            $dest = BASE_PATH . "students/{$stid}.jpg";

            if (file_exists($chk_source) && copy($chk_source, $dest)) {
                echo "✅ শিক্ষার্থী চূড়ান্তভাবে ভর্তি করা হয়েছে";
            } else {
                echo "⚠️ Image copy failed, but student admitted";
            }
        }
    }
} else {
    echo "⚠️ Database Error: " . $conn->error;
}
