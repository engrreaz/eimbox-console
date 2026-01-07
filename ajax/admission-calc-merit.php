<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

// মার্ক অনুযায়ী সাজাও
$q = $conn->query("SELECT id FROM registrations where sccode='$sccodex' ORDER BY adm_test_mark DESC, id ASC");
$rank = 1;
while($r = $q->fetch_assoc()){
    $conn->query("UPDATE registrations SET meritplace='$rank' WHERE id='{$r['id']}'");
    $rank++;
}

echo "✅ মেরিট নির্ধারণ সম্পন্ন হয়েছে";