<?php
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';

$res = $conn->query("SELECT sccode, count(*) as cnt FROM sms GROUP BY sccode");
if ($res) {
    echo "SMS table counts by sccode:" . PHP_EOL;
    while ($r = $res->fetch_assoc()) {
        echo "sccode: " . $r['sccode'] . " -> " . $r['cnt'] . " SMS logs" . PHP_EOL;
    }
}
