<?php
require_once __DIR__ . '/api/v1/bootstrap.php';

$res = $conn->query("SELECT id, sccode, slot, sessionyear, areaname, subarea, classteacher FROM areas WHERE sccode = 103187 LIMIT 10");
while ($r = $res->fetch_assoc()) {
    print_r($r);
}
