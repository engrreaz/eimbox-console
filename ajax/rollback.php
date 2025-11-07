<?php
require_once '../core/config.php';
require_once '../core/db.php';

$sccode = $_GET['sccode'] ?? '';

$tables = [
    "slots",
    "areas",
    "students",
    "sessioninfo",
    "sessionyear",
    "settings",
    "globalsettings",
    "stmark",
    // "tabulatingsheet",
    // "tabulatingsheetex",
    "financesetup",
    "financesetupvalue",
    // "stfinance",
    // "stattnd"
];

foreach ($tables as $tbl) {
    $result = $conn->query("DELETE FROM `$tbl` WHERE sccode='$sccode'");
    if (!$result) {
        echo "<span style='color:red'>Error flushing $tbl: " . $conn->error . "</span><br>";
    } else {
        echo "Flush Data from $tbl<br>";
    }
}

echo 'Rolling back done successfully!';