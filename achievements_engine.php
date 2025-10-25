<?php
// achievements_engine.php
require_once 'core/config.php';
require_once 'core/db.php';

// achievements_engine.php - page thresholds
$PAGE_ACHIEVEMENTS = [
    'editor' => [
        'page_editor_5m' => 300,
        'page_editor_30m' => 1800,
        'page_editor_1h' => 3600,
        'page_expert' => 3600  // ultimate for this page
    ],
    'dashboard' => [
        'page_dashboard_5m' => 300,
        'page_dashboard_30m' => 1800,
        'page_dashboard_1h' => 3600,
        'page_expert' => 3600
    ]
];

$STREAK_ACHIEVEMENTS = [
    'streak_week' => 7,   // consecutive days
    'streak_month' => 30,  // consecutive days
    'daily_active_7' => 7, // active 7 days in last week (non-consecutive)
];

$FEEDBACK_ACHIEVEMENTS = [
    'feedback_5' => 5,
    'feedback_20' => 20,
    // accepted suggestions can be derived from target_type/accepted column
    'suggestions_accepted' => 1
];


/**
 * Award an achievement to a user (email-based). Idempotent.
 * Returns: ['awarded'=>true/false, 'achievement_id'=>int|null]
 */
function awardAchievementByEmail(mysqli $conn, string $email, string $ach_code): array
{
    // trim & normalize
    $email = mb_strtolower(trim($email));
    $ach_code = trim($ach_code);

    // start transaction to be safe
    $conn->begin_transaction();
    try {
        // find achievement
        $stmt = $conn->prepare("SELECT id, points FROM achievements_list WHERE code = ? LIMIT 1");
        $stmt->bind_param('s', $ach_code);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $stmt->close();
            $conn->rollback();
            return ['awarded' => false, 'achievement_id' => null, 'error' => 'achievement_not_found'];
        }
        $row = $res->fetch_assoc();
        $ach_id = (int) $row['id'];
        $ach_points = (int) $row['points'];
        $stmt->close();

        // insert if not exists using INSERT IGNORE (requires unique key on email+achievement_id)
        $ins = $conn->prepare("INSERT IGNORE INTO user_achievements (email, achievement_id, achieved_at) VALUES (?, ?, NOW())");
        $ins->bind_param('si', $email, $ach_id);
        $ins->execute();
        $affected = $ins->affected_rows;
        $ins->close();

        if ($affected > 0) {
            // log event
            $ev = $conn->prepare("INSERT INTO achievement_events (email, achievement_id, event_type, reason, created_at) VALUES (?, ?, 'awarded', ?, NOW())");
            $reason = "auto_award:" . $ach_code;
            $ev->bind_param('sis', $email, $ach_id, $reason);
            $ev->execute();
            $ev->close();
            // optionally update users.total_points if you maintain a cached field in users table
            // example:
            // $upd = $conn->prepare("UPDATE users SET total_points = COALESCE(total_points,0) + ? WHERE email = ?");
            // $upd->bind_param('is', $ach_points, $email); $upd->execute(); $upd->close();

            $conn->commit();
            return ['awarded' => true, 'achievement_id' => $ach_id];
        } else {
            $conn->commit();
            return ['awarded' => false, 'achievement_id' => $ach_id, 'info' => 'already_has'];
        }
    } catch (Exception $e) {
        $conn->rollback();
        return ['awarded' => false, 'achievement_id' => null, 'error' => $e->getMessage()];
    }
}

/**
 * Recalculate user's points (action points + achievement points).
 * Returns associative array: ['action_points'=>int, 'achievement_points'=>int, 'total_points'=>int]
 */
function recalcUserPointsByEmail(mysqli $conn, string $email): array
{
    $email = mb_strtolower(trim($email));

    // action points
    $stmt = $conn->prepare("SELECT COALESCE(SUM(points),0) AS action_points FROM user_actions WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $action_points = (int) $res['action_points'];
    $stmt->close();

    // achievement points
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(a.points),0) AS achievement_points
        FROM user_achievements ua
        JOIN achievements_list a ON ua.achievement_id = a.id
        WHERE ua.email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $achievement_points = (int) $res['achievement_points'];
    $stmt->close();

    $total = $action_points + $achievement_points;

    // optionally update cached users.total_points if you have one
    // $upd = $conn->prepare("UPDATE users SET total_points = ? WHERE email = ?");
    // $upd->bind_param('is', $total, $email); $upd->execute(); $upd->close();

    return [
        'action_points' => $action_points,
        'achievement_points' => $achievement_points,
        'total_points' => $total
    ];
}

/**
 * Evaluate titles for an email: award any titles that the user now qualifies for.
 * Uses normalized title_requirements table if populated; else falls back to JSON column.
 * Returns array of awarded title ids.
 */
function evaluateTitlesForEmail(mysqli $conn, string $email): array
{
    $email = mb_strtolower(trim($email));
    $awarded_titles = [];

    // compute total points
    $pointsData = recalcUserPointsByEmail($conn, $email);
    $total_points = $pointsData['total_points'];

    // get candidate titles (min_points <= total_points) ordered desc so highest first
    $stmt = $conn->prepare("SELECT id, title_name, min_points, required_achievements FROM titles_list WHERE min_points <= ? ORDER BY min_points DESC");
    $stmt->bind_param('i', $total_points);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($title = $res->fetch_assoc()) {
        $title_id = (int) $title['id'];

        // Check requirements:
        $meets = true;

        // Prefer normalized table check if entries exist
        $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM title_requirements WHERE title_id = ?");
        $check->bind_param('i', $title_id);
        $check->execute();
        $c = (int) $check->get_result()->fetch_assoc()['cnt'];
        $check->close();

        if ($c > 0) {
            // there are normalized requirements; ensure all exist for user
            $reqStmt = $conn->prepare("
                SELECT tr.achievement_code
                FROM title_requirements tr
                WHERE tr.title_id = ?
            ");
            $reqStmt->bind_param('i', $title_id);
            $reqStmt->execute();
            $reqRes = $reqStmt->get_result();
            while ($r = $reqRes->fetch_assoc()) {
                $ach_code = $r['achievement_code'];
                $chk = $conn->prepare("
                    SELECT 1 FROM user_achievements ua
                    JOIN achievements_list a ON ua.achievement_id = a.id
                    WHERE ua.email = ? AND a.code = ? LIMIT 1
                ");
                $chk->bind_param('ss', $email, $ach_code);
                $chk->execute();
                if ($chk->get_result()->num_rows == 0) {
                    $meets = false;
                    $chk->close();
                    break;
                }
                $chk->close();
            }
            $reqStmt->close();
        } else {
            // fallback: use JSON required_achievements column
            $reqs = json_decode($title['required_achievements'] ?? '[]', true);
            if (is_array($reqs) && count($reqs) > 0) {
                foreach ($reqs as $ach_code) {
                    $chk = $conn->prepare("
                        SELECT 1 FROM user_achievements ua
                        JOIN achievements_list a ON ua.achievement_id = a.id
                        WHERE ua.email = ? AND a.code = ? LIMIT 1
                    ");
                    $chk->bind_param('ss', $email, $ach_code);
                    $chk->execute();
                    if ($chk->get_result()->num_rows == 0) {
                        $meets = false;
                        $chk->close();
                        break;
                    }
                    $chk->close();
                }
            }
        }

        if ($meets) {
            // award title if not already
            $ins = $conn->prepare("INSERT IGNORE INTO user_titles (email, title_id, awarded_at) VALUES (?, ?, NOW())");
            $ins->bind_param('si', $email, $title_id);
            $ins->execute();
            if ($ins->affected_rows > 0) {
                $awarded_titles[] = $title_id;
            }
            $ins->close();
        }
    }
    $stmt->close();

    return $awarded_titles;
}


function auto_issue_achievements($conn, $email) {

    // 1. Fetch user's current achievements
    $current = [];
    $stmt = $conn->prepare("SELECT a.code FROM user_achievements ua JOIN achievements_list a ON ua.achievement_id=a.id WHERE ua.email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) {
        $current[] = $row['code'];
    }
    $stmt->close();

    // 2. Fetch user stats from feedbacks, logbook, user_actions
    // Feedback count
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM feedbacks WHERE email=?");
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $feedbackCount = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
    $stmt->close();

    // Bug reports count (user_actions / feedback type)
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM user_actions WHERE email=? AND action LIKE '%bug%'");
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $bugCount = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
    $stmt->close();

    // Logbook: total page usage in seconds
    $stmt = $conn->prepare("SELECT SUM(duration) as t FROM logbook WHERE email=?");
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $pageTime = $stmt->get_result()->fetch_assoc()['t'] ?? 0;
    $stmt->close();

    // Polls created / voted
    $stmt = $conn->prepare("SELECT COUNT(*) as c FROM user_actions WHERE email=? AND page='poll'");
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $pollCount = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
    $stmt->close();

    // 3. Determine new achievements
    $achievements_to_issue = [];

    // Feedback achievements
    if($feedbackCount >= 1 && !in_array('first_feedback', $current)) $achievements_to_issue[]='first_feedback';
    if($feedbackCount >= 5 && !in_array('feedback_5', $current)) $achievements_to_issue[]='feedback_5';
    if($feedbackCount >= 20 && !in_array('feedback_20', $current)) $achievements_to_issue[]='feedback_20';

    // Bug achievements
    if($bugCount >= 1 && !in_array('first_bug', $current)) $achievements_to_issue[]='first_bug';
    if($bugCount >= 5 && !in_array('five_bugs', $current)) $achievements_to_issue[]='five_bugs';
    if($bugCount >= 10 && !in_array('ten_bugs', $current)) $achievements_to_issue[]='ten_bugs';

    // Page usage achievements
    if($pageTime >= 7*24*3600 && !in_array('streak_week', $current)) $achievements_to_issue[]='streak_week';
    if($pageTime >= 30*24*3600 && !in_array('streak_month', $current)) $achievements_to_issue[]='streak_month';
    if($pageTime >= 180*24*3600 && !in_array('long_usage', $current)) $achievements_to_issue[]='long_usage';
    if($pageTime >= 360*24*3600 && !in_array('any_10_achievements', $current)) $achievements_to_issue[]='any_10_achievements'; // example

    // Poll achievements
    if($pollCount >= 5 && !in_array('poll_voter', $current)) $achievements_to_issue[]='poll_voter';
    if($pollCount >= 1 && !in_array('poll_creator', $current)) $achievements_to_issue[]='poll_creator';
    if($pollCount >= 10 && !in_array('poll_master', $current)) $achievements_to_issue[]='poll_master';

    // 4. Insert new achievements
    foreach($achievements_to_issue as $code){
        $stmt = $conn->prepare("INSERT INTO user_achievements (email, achievement_id, achieved_at) SELECT ?, id, NOW() FROM achievements_list WHERE code=?");
        $stmt->bind_param('ss',$email,$code);
        $stmt->execute();
        $stmt->close();
    }

    // 5. Recalculate total points
    $stmt = $conn->prepare("SELECT SUM(a.points) as total_points
        FROM user_achievements ua
        JOIN achievements_list a ON ua.achievement_id=a.id
        WHERE ua.email=?");
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $totalPoints = $stmt->get_result()->fetch_assoc()['total_points'] ?? 0;
    $stmt->close();

    // 6. Assign/Update title
    $res = $conn->query("SELECT * FROM titles_list ORDER BY min_points DESC");
    $newTitle = null;
    while($row = $res->fetch_assoc()){
        $req_ach = json_decode($row['required_achievements'], true);
        $have_ach = $conn->query("SELECT code FROM user_achievements ua JOIN achievements_list a ON ua.achievement_id=a.id WHERE ua.email='". $conn->real_escape_string($email) ."'")->fetch_all(MYSQLI_ASSOC);
        $have_ach_codes = array_column($have_ach,'code');
        $all_req_met = empty($req_ach) || empty(array_diff($req_ach,$have_ach_codes));
        if($totalPoints >= $row['min_points'] && $all_req_met){
            $newTitle = $row['title_name'];
            break;
        }
    }

    // Return summary
    return [
        'new_achievements'=>$achievements_to_issue,
        'total_points'=>$totalPoints,
        'current_title'=>$newTitle
    ];
}
?>
