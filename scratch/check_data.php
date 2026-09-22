<?php
require_once 'core/config.php';
require_once 'core/db.php';

$res = $conn->query("SELECT id, date, month, year, amount, status, is_locked FROM cashbook ORDER BY id DESC LIMIT 10");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        print_r($r);
    }
} else {
    echo "Query Error: " . $conn->error;
}
