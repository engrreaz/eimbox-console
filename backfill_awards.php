<?php
// backfill_awards.php
// Run from CLI only
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from CLI.\n";
    exit(1);
}
require_once 'core/config.php';
require_once 'core/db.php';
require_once 'core/global_values.php';

require_once 'achievements_engine.php';

$BATCH_SIZE = 500;     // adjust as needed
$offset = 0;

echo "[".date('Y-m-d H:i:s')."] Starting backfill\n";

while (true) {
    // fetch a chunk of distinct emails from union of tables
    $sql = "
        SELECT DISTINCT email FROM (
            SELECT email FROM user_actions
            UNION
            SELECT email FROM feedbacks
            UNION
            SELECT email FROM user_achievements
            UNION
            SELECT email FROM user_titles
            UNION
            SELECT email FROM logbook
        ) AS u
        WHERE email IS NOT NULL AND email <> ''
        LIMIT ? OFFSET ?
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $BATCH_SIZE, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    $emails = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (count($emails) === 0) break;

    foreach ($emails as $row) {
        $email = mb_strtolower(trim($row['email']));
        if ($email === '') continue;

        echo "[".date('Y-m-d H:i:s')."] Processing: $email\n";

        // 1) first_login: any login action or any action record
        $s = $mysqli->prepare("SELECT 1 FROM user_actions WHERE email = ? AND action IN ('login','sign_in') LIMIT 1");
        $s->bind_param('s', $email);
        $s->execute();
        if ($s->get_result()->num_rows > 0) {
            awardAchievementByEmail($mysqli, $email, 'first_login');
        }
        $s->close();

        // 2) feedback counts
        $s = $mysqli->prepare("SELECT COUNT(*) AS c FROM feedbacks WHERE email = ?");
        $s->bind_param('s', $email);
        $s->execute();
        $c = (int)$s->get_result()->fetch_assoc()['c'];
        $s->close();
        if ($c >= 1) awardAchievementByEmail($mysqli, $email, 'first_feedback');
        if ($c >= 5) awardAchievementByEmail($mysqli, $email, 'feedback_5');
        if ($c >= 20) awardAchievementByEmail($mysqli, $email, 'feedback_20');

        // 3) helpful_feedback: count of feedbacks rating >=4
        $s = $mysqli->prepare("SELECT COUNT(*) AS c FROM feedbacks WHERE email = ? AND rating >= 4");
        $s->bind_param('s', $email);
        $s->execute();
        $hc = (int)$s->get_result()->fetch_assoc()['c'];
        $s->close();
        if ($hc >= 1) awardAchievementByEmail($mysqli, $email, 'helpful_feedback');

        // 4) feature usage example: feature_x
        $s = $mysqli->prepare("SELECT COUNT(*) AS c FROM user_actions WHERE email = ? AND action = 'feature_x'");
        $s->bind_param('s', $email);
        $s->execute();
        $fx = (int)$s->get_result()->fetch_assoc()['c'];
        $s->close();
        if ($fx >= 10) awardAchievementByEmail($mysqli, $email, 'feature_x_10');
        if ($fx >= 100) awardAchievementByEmail($mysqli, $email, 'feature_x_100');

        // 5) page time aggregate (total across pages). You can change to per-page thresholds if desired
        $s = $mysqli->prepare("SELECT COALESCE(SUM(duration),0) AS tot FROM logbook WHERE email = ?");
        $s->bind_param('s', $email);
        $s->execute();
        $tot = (int)$s->get_result()->fetch_assoc()['tot'];
        $s->close();
        if ($tot >= 300) awardAchievementByEmail($mysqli, $email, 'page_time_5m');
        if ($tot >= 1800) awardAchievementByEmail($mysqli, $email, 'page_time_30m');
        if ($tot >= 3600) awardAchievementByEmail($mysqli, $email, 'page_time_1h');

        // 6) bugs: if you have a bug table, count validated bugs here (example commented)
        // $s = $mysqli->prepare("SELECT COUNT(*) AS c FROM bugs WHERE reporter_email = ? AND status = 'validated'");
        // ...

        // 7) support/ticket-based achievements
        $s = $mysqli->prepare("SELECT COUNT(*) AS c FROM user_actions WHERE email = ? AND action IN ('support_reply','ticket_closed')");
        $s->bind_param('s', $email);
        $s->execute();
        $sc = (int)$s->get_result()->fetch_assoc()['c'];
        $s->close();
        if ($sc >= 1) awardAchievementByEmail($mysqli, $email, 'support_reply');
        // For ticket_closer counts you should query your tickets table if exists.

        // 8) recalc & evaluate titles
        $points = recalcUserPointsByEmail($mysqli, $email);
        evaluateTitlesForEmail($mysqli, $email);

        // small sleep optional to reduce DB load
        usleep(50000); // 50ms
    }

    // next batch
    $offset += $BATCH_SIZE;
    // optional: break early for testing
    // break;
}

echo "[".date('Y-m-d H:i:s')."] Backfill finished\n";
