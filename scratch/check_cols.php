<?php
require_once 'core/config.php';
require_once 'core/db.php';

foreach (['account_head', 'account_sub_head', 'financesetup', 'financesetupvalue'] as $tbl) {
    echo "=== Table: $tbl ===\n";
    $res = $conn->query("SHOW COLUMNS FROM $tbl");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            echo "  " . $r['Field'] . " (" . $r['Type'] . ")\n";
        }
    } else {
        echo "  Error: " . $conn->error . "\n";
    }
}
