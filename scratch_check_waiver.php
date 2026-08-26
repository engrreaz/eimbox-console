<?php
require_once __DIR__ . '/api/v1/bootstrap.php';

echo "Connected to MySQL successfully!\n";

$res = $conn->query("SELECT sccode, sessionyear, count(*) as total, count(CASE WHEN rate < 100 THEN 1 END) as waived, count(CASE WHEN sector IS NOT NULL AND sector != '' THEN 1 END) as has_sector FROM sessioninfo GROUP BY sccode, sessionyear");
echo "Sessioninfo Summary:\n";
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\nSample Waived in MySQL sessioninfo:\n";
$waived = $conn->query("SELECT si.id, si.sccode, si.sessionyear, si.classname, si.sectionname, si.rollno, si.stid, si.rate, si.sector, s.stnameeng, s.stnameben, s.previll, s.prepo, s.preps, s.predist, s.guarmobile FROM sessioninfo si LEFT JOIN students s ON s.stid = si.stid AND s.sccode = si.sccode WHERE si.rate < 100 LIMIT 5");
if ($waived) {
    while ($r = $waived->fetch_assoc()) {
        print_r($r);
    }
} else {
    echo "Query error: " . $conn->error . "\n";
}

echo "\nCheck if any sector set even if rate is 100:\n";
$sec = $conn->query("SELECT si.id, si.sccode, si.sessionyear, si.stid, si.rate, si.sector FROM sessioninfo si WHERE si.sector IS NOT NULL AND si.sector != '' LIMIT 5");
if ($sec) {
    while ($r = $sec->fetch_assoc()) {
        print_r($r);
    }
}
