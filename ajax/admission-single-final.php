<?php
require_once '../core/config.php';
require_once '../core/db.php';

$id = $_POST['id'] ?? '';
if(!$id){ echo "❌ Invalid ID"; exit; }

$q = $conn->query("SELECT * FROM registrations WHERE id='$id'");
if($q->num_rows == 0){ echo "❌ শিক্ষার্থী পাওয়া যায়নি"; exit; }
$d = $q->fetch_assoc();

// ফাইনাল ভর্তি ইনসার্ট students টেবিলে
$stid = $d['stid'] ?: $d['reg_id'];

$sql = "INSERT INTO students (stid, stnameeng, stnameben, fname, mname, gender, bgroup, dob, sccode)
        VALUES ('{$stid}', '{$d['stnameeng']}', '{$d['stnameben']}', '{$d['fname']}', '{$d['mname']}',
                '{$d['gender']}', '{$d['bgroup']}', '{$d['dob']}', '{$d['sccode']}')";

if($conn->query($sql)){
    echo "✅ শিক্ষার্থী চূড়ান্তভাবে ভর্তি করা হয়েছে";
}else{
    echo "⚠️ Database Error: " . $conn->error;
}
