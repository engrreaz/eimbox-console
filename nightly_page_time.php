<?php
// nightly_page_time.php
// Run from CLI (cron). Uses config.php and achievements_engine.php
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/achievements_engine.php';

// Config
$LOG_FILE = __DIR__ . '/logs/nightly_page_time.log';
$WINDOW_DAYS = 90;           // aggregate window (days)
$BATCH_SIZE = 500;           // number of distinct email+page rows to process per batch
$SLEEP_USEC = 20000;         // sleep between rows (microseconds) to reduce DB pressure

// thresholds in seconds (can be adjusted)
$THRESHOLDS = [
    'page_time_5m' => 300,
    'page_time_30m' => 1800,
    'page_time_1h' => 3600
];

function logmsg($msg)
{
    global $LOG_FILE;
    $line = "[" . date('Y-m-d H:i:s') . "] " . $msg . PHP_EOL;
    echo $line;
    @file_put_contents($LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

logmsg("Starting nightly_page_time. window_days={$GLOBALS['WINDOW_DAYS']}");

// Step: We will select distinct (email, pagename, total_seconds) aggregated by window.
// Process in batches with LIMIT/OFFSET. If DB very large, prefer cursor-like streaming (depends on MySQL version).

$offset = 0;
while (true) {
    $sql = "
      SELECT email, pagename, COALESCE(SUM(duration),0) AS total_seconds
      FROM logbook
      WHERE entrytime >= DATE_SUB(NOW(), INTERVAL ? DAY)
        AND email IS NOT NULL AND email <> ''
      GROUP BY email, pagename
      ORDER BY email
      LIMIT ? OFFSET ?
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iii', $WINDOW_DAYS, $BATCH_SIZE, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (count($rows) === 0)
        break;

    foreach ($rows as $r) {
        $email = mb_strtolower(trim($r['email']));
        $pagename = $r['pagename'];
        $total_seconds = (int) $r['total_seconds'];
        if ($email === '')
            continue;

        logmsg("Processing: {$email} | page={$pagename} | total_seconds={$total_seconds}");

        if (isset($PAGE_ACHIEVEMENTS[$pagename])) {
            foreach ($PAGE_ACHIEVEMENTS[$pagename] as $code => $secs) {
                if ($total_seconds >= $secs) {
                    $resAward = awardAchievementByEmail($mysqli, $email, $code);
                    if (isset($resAward['awarded']) && $resAward['awarded']) {
                        logmsg("  Awarded {$code} to {$email}");
                    }
                }
            }
        }

        // Recalculate points & evaluate titles
        $points = recalcUserPointsByEmail($mysqli, $email);
        $awarded = evaluateTitlesForEmail($mysqli, $email);
        if (!empty($awarded))
            logmsg("  Titles awarded: " . implode(',', $awarded));
    }


    // next batch
    $offset += $BATCH_SIZE;
    // small pause between batches to reduce load
    usleep(200000); // 200ms
}

logmsg("Finished nightly_page_time.");
