<?php
require_once 'd:/XAMPP/htdocs/eimbox-dashboard/eimbox-materio/core/config.php';
require_once 'd:/XAMPP/htdocs/eimbox-dashboard/eimbox-materio/core/db.php';

echo "=== TABLE: financesetup ===\n";
$res = $conn->query("DESCRIBE `financesetup`");
while ($row = $res->fetch_assoc()) {
    echo "  {$row['Field']} ({$row['Type']})\n";
}

echo "\n=== TABLE: teacher ===\n";
$res2 = $conn->query("DESCRIBE `teacher`");
while ($row2 = $res2->fetch_assoc()) {
    echo "  {$row2['Field']} ({$row2['Type']})\n";
}
