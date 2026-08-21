<?php
require_once 'd:/XAMPP/htdocs/eimbox-dashboard/eimbox-materio/core/config.php';
require_once 'd:/XAMPP/htdocs/eimbox-dashboard/eimbox-materio/core/db.php';

$tables = ['teacher', 'tabulatingsheet', 'teacherattnd', 'sms', 'notice', 'cashbook', 'finanaceitem', 'financeitem', 'partid', 'finitems', 'feehead'];

foreach ($tables as $t) {
    echo "=== TABLE: $t ===\n";
    try {
        $res = $conn->query("DESCRIBE `$t`");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                echo "  {$row['Field']} ({$row['Type']})\n";
            }
        } else {
            echo "  Table does not exist.\n";
        }
    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "=== ALL TABLES CONTAINING 'fin' or 'fee' or 'item' or 'teach' or 'notice' ===\n";
$allRes = $conn->query("SHOW TABLES");
while ($r = $allRes->fetch_array()) {
    $tbl = $r[0];
    if (preg_match('/(fin|fee|item|teach|notic|tabu)/i', $tbl)) {
        echo "  Found table: $tbl\n";
    }
}
