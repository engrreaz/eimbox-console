<?php
require_once '../core/config.php';
require_once '../core/db.php';

$limit = intval($_POST['limit'] ?? 0);

if($limit > 0){
    $q = $conn->query("SELECT * FROM registrations ORDER BY meritplace ASC LIMIT $limit");
    $c = 0;
    while($r = $q->fetch_assoc()){
        $stid = $r['stid'] ?: $r['reg_id']; // fallback if stid empty

        // মূল students টেবিলে ইনসার্ট
        $conn->query("INSERT INTO students (stid, stnameeng, stnameben, fname, mname, gender, bgroup, dob, sccode)
                    VALUES ('$stid', '{$r['stnameeng']}', '{$r['stnameben']}', '{$r['fname']}', '{$r['mname']}', '{$r['gender']}', '{$r['bgroup']}', '{$r['dob']}', '{$r['sccode']}')");
        $c++;
    }

    echo "✅ মোট $c জন শিক্ষার্থীকে চূড়ান্তভাবে ভর্তি করা হয়েছে";
} else {
    echo "⚠️ সীমা সঠিক নয়!";
}
