<?php
require_once 'core/config.php';
require_once 'core/db.php';

$res = $conn->query("SHOW COLUMNS FROM cashbook");
while ($r = $res->fetch_assoc()) {
    echo $r['Field'] . " (" . $r['Type'] . ")\n";
}
