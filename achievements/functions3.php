<?php
// Add points for a user, with optional details
function addPoints($user_id, $action, $points, $meta = []) {
    global $conn;

    $user_id = intval($user_id);
    $points = intval($points);

    // Insert into points log
    $stmt = $conn->prepare("INSERT INTO points_log (user_id, action, points, meta, created_at) VALUES (?, ?, ?, ?, NOW())");
    $meta_json = !empty($meta) ? json_encode($meta) : null;
    $stmt->bind_param("isis", $user_id, $action, $points, $meta_json);
    $stmt->execute();

    // Update total points
    $conn->query("INSERT INTO user_points (user_id, total_points) VALUES ($user_id, $points) 
                  ON DUPLICATE KEY UPDATE total_points = total_points + $points");

    // Update level based on new total points
    $res = $conn->query("SELECT total_points FROM user_points WHERE user_id=$user_id");
    $total = $res->fetch_assoc()['total_points'];
    $level = floor($total / 100); // উদাহরণ: প্রতি 100 পয়েন্টে 1 লেভেল
    $conn->query("UPDATE user_points SET level=$level WHERE user_id=$user_id");

    // Check title assignment
    $titles = $conn->query("SELECT id, min_points FROM titles_list ORDER BY min_points DESC");
    while ($t = $titles->fetch_assoc()) {
        if ($total >= $t['min_points']) {
            $conn->query("UPDATE user_points SET current_title_id={$t['id']} WHERE user_id=$user_id");
            break;
        }
    }

    // Assign achievements automatically
    assignAchievements($user_id, $action, $total);
}

// Automatically assign achievements based on action & total points
function assignAchievements($user_id, $action, $total_points) {
    global $conn;

    // Get all achievements
    $achRes = $conn->query("SELECT * FROM achievements_list ORDER BY id ASC");
    while ($ach = $achRes->fetch_assoc()) {
        $ach_id = $ach['id'];
        $type = $ach['type'];
        $level_req = intval($ach['level_requirement']);

        // Skip if already assigned
        $check = $conn->query("SELECT * FROM user_achievements WHERE user_id=$user_id AND achievement_id=$ach_id");
        if ($check->num_rows > 0) continue;

        // Conditions
        $assign = false;

        switch($type) {
            case 'poll_vote':
                if ($action == 'poll_vote') $assign = true;
                break;
            case 'feedback':
                if ($action == 'feedback') $assign = true;
                break;
            case 'bug':
                if ($action == 'bug_report') $assign = true;
                break;
            case 'points_based':
                if ($total_points >= $ach['points']) $assign = true;
                break;
            default:
                // Optional: other types
                break;
        }

        // Level requirement check
        $res = $conn->query("SELECT level FROM user_points WHERE user_id=$user_id");
        $user_level = $res->fetch_assoc()['level'];
        if ($user_level < $level_req) $assign = false;

        // Assign if eligible
        if ($assign) {
            $conn->query("INSERT INTO user_achievements (user_id, achievement_id, awarded_at) VALUES ($user_id, $ach_id, NOW())");
        }
    }
}
