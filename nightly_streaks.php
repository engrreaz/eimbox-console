<?php
// nightly_streaks.php
// Run from CLI (cron). Uses config.php and achievements_engine.php
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/achievements_engine.php';

$LOG_FILE = __DIR__ . '/logs/nightly_streaks.log';
$BATCH_SIZE = 500;       // number of users per batch
$SLEEP_USEC = 20000;

// thresholds
$STREAK_WEEK_DAYS = 7;    // consecutive days required
$STREAK_MONTH_DAYS = 30;  // consecutive days
$ACTIVE_7_DAYS = 7;       // distinct active days in window

function logmsg($msg) {
    global $LOG_FILE;
    $line = "[".date('Y-m-d H:i:s')."] " . $msg . PHP_EOL;
    echo $line;
    @file_put_contents($LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

logmsg("Starting nightly_streaks.");

// We'll iterate distinct emails in user_actions (or union of sources).
$offset = 0;
while (true) {
    $sql = "
      SELECT DISTINCT email
      FROM user_actions
      WHERE email IS NOT NULL AND email <> ''
      ORDER BY email
      LIMIT ? OFFSET ?
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $BATCH_SIZE, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (count($rows) === 0) break;

    foreach ($rows as $r) {
        $email = mb_strtolower(trim($r['email']));
        if ($email === '') continue;

        logmsg("Processing streaks for {$email}");

        // 1) Distinct active days in last 7 days
        $sqlDays = "
          SELECT COUNT(DISTINCT DATE(timestamp)) AS days_active_7
          FROM user_actions
          WHERE email = ? AND timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ";
        $s = $mysqli->prepare($sqlDays);
        $s->bind_param('s', $email);
        $s->execute();
        $daysActive7 = (int)$s->get_result()->fetch_assoc()['days_active_7'];
        $s->close();
        if ($daysActive7 >= $ACTIVE_7_DAYS) {
            $resAward = awardAchievementByEmail($mysqli, $email, 'daily_active_7');
            if (isset($resAward['awarded']) && $resAward['awarded']) logmsg("  Awarded daily_active_7 to {$email}");
        }

        // 2) Consecutive day streak calculation for last N days (7 and 30)
        // We'll fetch distinct dates sorted desc, then check consecutive sequence from most recent day backwards.
        $sqlDates = "
          SELECT DISTINCT DATE(timestamp) AS d
          FROM user_actions
          WHERE email = ? AND timestamp >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
          ORDER BY d DESC
        ";
        $s = $mysqli->prepare($sqlDates);
        $s->bind_param('s', $email);
        $s->execute();
        $resDates = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $s->close();

        $dates = array_map(function($r){ return $r['d']; }, $resDates);
        // compute current consecutive streak ending today (or yesterday if no action today)
        $streak = 0;
        $expected = new DateTime(date('Y-m-d'));
        // if user has no action today, start expected = yesterday
        if (count($dates) === 0 || $dates[0] != $expected->format('Y-m-d')) {
            // check if latest date == yesterday
            $expected->modify('-1 day');
        }
        // iterate through dates
        foreach ($dates as $d) {
            if ($d == $expected->format('Y-m-d')) {
                $streak++;
                $expected->modify('-1 day');
            } else {
                // break on first non-match (consecutive streak ended)
                break;
            }
        }

        if ($streak >= $STREAK_WEEK_DAYS) {
            $resAward = awardAchievementByEmail($mysqli, $email, 'streak_week');
            if (isset($resAward['awarded']) && $resAward['awarded']) logmsg("  Awarded streak_week to {$email} (streak={$streak})");
        }

        if ($streak >= $STREAK_MONTH_DAYS) {
            $resAward = awardAchievementByEmail($mysqli, $email, 'streak_month');
            if (isset($resAward['awarded']) && $resAward['awarded']) logmsg("  Awarded streak_month to {$email} (streak={$streak})");
        }

        // Recalc points & evaluate titles occasionally to reduce load; do it for users who got something or every Nth user
        // For simplicity, do it always here:
        $points = recalcUserPointsByEmail($mysqli, $email);
        $awardedTitles = evaluateTitlesForEmail($mysqli, $email);
        if (!empty($awardedTitles)) {
            logmsg("  Titles awarded: " . implode(',', $awardedTitles));
        }

        usleep($SLEEP_USEC);
    }

    $offset += $BATCH_SIZE;
    usleep(200000); // small pause between batches
}

logmsg("Finished nightly_streaks.");
