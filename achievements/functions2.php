<?php
require_once 'core/config.php';
require_once 'core/db.php';

/**
 * Add points to a user
 * @param int $user_id
 * @param string $action (e.g., feedback, poll, bug_report, admin_award)
 * @param int $points
 * @param array $meta (optional) - additional info like reason, target, awarded_by
 * @return bool
 */
function addPoints($user_id, $action, $points, $meta = []) {
    global $conn;

    // 1. Update user_points table
    $sql = "INSERT INTO user_points (user_id, total_points, last_action, updated_at)
            VALUES ($user_id, $points, ?, NOW())
            ON DUPLICATE KEY UPDATE total_points = total_points + $points, last_action = ?, updated_at = NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $action, $action);
    $stmt->execute();

    // 2. Log activity
    $meta_json = !empty($meta) ? json_encode($meta) : null;
    $stmt2 = $conn->prepare("INSERT INTO activity_log (user_id, action_type, points, meta, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt2->bind_param("isis", $user_id, $action, $points, $meta_json);
    $stmt2->execute();

    // 3. Update user title / level if necessary
    updateUserTitle($user_id);

    return true;
}

/**
 * Update user title/level based on total points
 */
function updateUserTitle($user_id) {
    global $conn;

    $res = $conn->query("SELECT total_points FROM user_points WHERE user_id = $user_id");
    if (!$res || $res->num_rows == 0) return;

    $total_points = $res->fetch_assoc()['total_points'];

    // Fetch highest eligible title
    $titleRes = $conn->query("SELECT id, title_name, min_points FROM titles_list WHERE min_points <= $total_points ORDER BY min_points DESC LIMIT 1");
    if ($titleRes && $titleRes->num_rows > 0) {
        $title = $titleRes->fetch_assoc();
        $conn->query("UPDATE user_points SET current_title_id = {$title['id']} WHERE user_id = $user_id");
    }

    // Calculate level (example: every 100 points = 1 level)
    $level = floor($total_points / 100) + 1;
    $conn->query("UPDATE user_points SET level = $level WHERE user_id = $user_id");
}

/**
 * Check if user has unlocked an achievement
 */
function unlockAchievement($user_id, $achievement_id) {
    global $conn;
    // Check already unlocked
    $res = $conn->query("SELECT id FROM user_achievements WHERE user_id=$user_id AND achievement_id=$achievement_id");
    if ($res && $res->num_rows > 0) return false;

    // Unlock
    $stmt = $conn->prepare("INSERT INTO user_achievements (user_id, achievement_id, unlocked_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $user_id, $achievement_id);
    $stmt->execute();

    // Optional: add points for achievement
    $pointsRes = $conn->query("SELECT points FROM achievements_list WHERE id=$achievement_id");
    if ($pointsRes && $pointsRes->num_rows > 0) {
        $points = $pointsRes->fetch_assoc()['points'];
        addPoints($user_id, "achievement_unlock", $points, ['achievement_id'=>$achievement_id]);
    }

    return true;
}
