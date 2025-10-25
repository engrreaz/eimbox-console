<?php
// functions.php
// Gamification core functions
// Place this file in project root (next to index.php), and ensure includes/header.php exists.
// Header includes core/config.php and core/db.php (so don't include them again).

$page_title = "Gamification Functions";
include 'includes/header.php';

/*
  Core functions:
    - ensureUserPointsRow($user_id)
    - addActivityLog($user_id, $activity_type, $points, $meta = null)
    - addPoints($user_id, $activity_type, $points, $meta = null)
    - getUserPoints($user_id)
    - getUserAchievements($user_id)  // returns array of achievement codes
    - unlockAchievement($user_id, $achievement_code)
    - getEligibleTitleForUser($user_id) // returns ['id'=>..., 'title'=>...]
    - assignTitleIfEligible($user_id) // updates user_points.current_title_id and records history if user_titles table exists
    - helper: tableExists($table_name)
*/

if (!isset($conn) || !$conn) {
    echo "<div class='alert alert-danger'>Database connection (\$conn) not found. Check includes/header.php</div>";
    include 'includes/footer.php';
    exit;
}

/* -------------------------
   Helper: Check table exists
   ------------------------- */
function tableExists($table) {
    global $conn;
    $table = $conn->real_escape_string($table);
    $sql = "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$table' LIMIT 1";
    $res = $conn->query($sql);
    return ($res && $res->num_rows > 0);
}

/* -------------------------
   Ensure user_points row exists
   ------------------------- */
function ensureUserPointsRow($user_id) {
    global $conn;
    $user_id = (int)$user_id;

    $stmt = $conn->prepare("SELECT id FROM user_points WHERE user_id = ? LIMIT 1 FOR UPDATE");
    if (!$stmt) return false;
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row['id'];
    }
    $stmt->close();

    // Insert new row
    $ins = $conn->prepare("INSERT INTO user_points (user_id, total_points, level, updated_at) VALUES (?, 0, 1, NOW())");
    if (!$ins) return false;
    $ins->bind_param("i", $user_id);
    $ins->execute();
    $newId = $ins->insert_id;
    $ins->close();
    return $newId;
}

/* -------------------------
   Add activity log
   ------------------------- */
function addActivityLog($user_id, $activity_type, $points = 0, $meta = null) {
    global $conn;
    $user_id = (int)$user_id;
    $activity_type = substr($activity_type, 0, 100);
    $points = (int)$points;
    $meta_json = $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null;

    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, points, created_at) VALUES (?, ?, ?, NOW())");
    if (!$stmt) return false;
    $stmt->bind_param("isi", $user_id, $activity_type, $points);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/* -------------------------
   Add points (atomic)
   - records activity_log
   - updates user_points.total_points
   ------------------------- */
function addPoints($user_id, $activity_type, $points = 0, $meta = null) {
    global $conn;
    $user_id = (int)$user_id;
    $points = (int)$points;
    if ($points === 0) {
        // still record activity if needed but no points change
        return addActivityLog($user_id, $activity_type, 0, $meta);
    }

    // Start transaction
    $conn->begin_transaction();
    try {
        // ensure user row
        ensureUserPointsRow($user_id);

        // insert activity log
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, points, created_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) throw new Exception("Prepare failed (activity_log): " . $conn->error);
        $stmt->bind_param("isi", $user_id, $activity_type, $points);
        $stmt->execute();
        $stmt->close();

        // get current total_points (FOR UPDATE to avoid race)
        $stmt2 = $conn->prepare("SELECT total_points FROM user_points WHERE user_id = ? LIMIT 1 FOR UPDATE");
        if (!$stmt2) throw new Exception("Prepare failed (select points): " . $conn->error);
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $res = $stmt2->get_result();
        $current = 0;
        if ($row = $res->fetch_assoc()) $current = (int)$row['total_points'];
        $stmt2->close();

        $new_total = $current + $points;
        $stmt3 = $conn->prepare("UPDATE user_points SET total_points = ?, updated_at = NOW() WHERE user_id = ?");
        if (!$stmt3) throw new Exception("Prepare failed (update points): " . $conn->error);
        $stmt3->bind_param("ii", $new_total, $user_id);
        $stmt3->execute();
        $stmt3->close();

        $conn->commit();
        // After updating points, try to auto-assign title if eligible
        assignTitleIfEligible($user_id);
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("addPoints error: " . $e->getMessage());
        return false;
    }
}

/* -------------------------
   Get user points
   ------------------------- */
function getUserPoints($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    $stmt = $conn->prepare("SELECT total_points FROM user_points WHERE user_id = ? LIMIT 1");
    if (!$stmt) return 0;
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $points = 0;
    if ($row = $res->fetch_assoc()) $points = (int)$row['total_points'];
    $stmt->close();
    return $points;
}

/* -------------------------
   Get user achievements (codes)
   ------------------------- */
function getUserAchievements($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    $stmt = $conn->prepare("
        SELECT a.code
        FROM user_achievements ua
        JOIN achievements_list a ON a.id = ua.achievement_id
        WHERE ua.user_id = ?
    ");
    if (!$stmt) return [];
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $codes = [];
    while ($row = $res->fetch_assoc()) {
        $codes[] = $row['code'];
    }
    $stmt->close();
    return $codes;
}

/* -------------------------
   Unlock achievement by code
   - Adds to user_achievements (if not already)
   - Awards achievement points (from achievements_list.points)
   ------------------------- */
function unlockAchievement($user_id, $achievement_code) {
    global $conn;
    $user_id = (int)$user_id;
    $achievement_code = trim($achievement_code);

    // fetch achievement meta
    $stmt = $conn->prepare("SELECT id, points FROM achievements_list WHERE code = ? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param("s", $achievement_code);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $ach_id = (int)$row['id'];
        $ach_points = (int)$row['points'];
        $stmt->close();

        // check already unlocked
        $chk = $conn->prepare("SELECT id FROM user_achievements WHERE user_id = ? AND achievement_id = ? LIMIT 1");
        $chk->bind_param("ii", $user_id, $ach_id);
        $chk->execute();
        $cres = $chk->get_result();
        if ($cres && $cres->num_rows > 0) {
            $chk->close();
            return false; // already unlocked
        }
        $chk->close();

        // unlock & award points
        $ins = $conn->prepare("INSERT INTO user_achievements (user_id, achievement_id, achieved_at) VALUES (?, ?, NOW())");
        $ins->bind_param("ii", $user_id, $ach_id);
        if (!$ins->execute()) {
            $ins->close();
            return false;
        }
        $ins->close();

        // award points (activity_type = achievement_{code})
        addPoints($user_id, 'achievement_' . $achievement_code, $ach_points);
        return true;
    } else {
        $stmt->close();
        return false;
    }
}

/* -------------------------
   Get eligible highest title for user
   - Checks min_points and required_achievements (JSON)
   - Supports special requirement codes like "any_10_achievements"
   ------------------------- */
function getEligibleTitleForUser($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    $points = getUserPoints($user_id);
    $user_ach = getUserAchievements($user_id);
    $user_ach_count = count($user_ach);

    $res = $conn->query("SELECT id, title_name, min_points, required_achievements FROM titles_list WHERE min_points <= $points ORDER BY min_points DESC");
    if (!$res) return null;

    while ($row = $res->fetch_assoc()) {
        $req_json = $row['required_achievements'];
        $reqs = [];
        if ($req_json) {
            $decoded = json_decode($req_json, true);
            if (is_array($decoded)) $reqs = $decoded;
        }
        // if no requirements, eligible
        if (empty($reqs)) {
            return ['id' => (int)$row['id'], 'title' => $row['title_name']];
        }

        $meets = true;
        foreach ($reqs as $r) {
            // special pattern: any_N_achievements
            if (preg_match('/^any_(\d+)_achievements$/', $r, $m)) {
                $need = (int)$m[1];
                if ($user_ach_count < $need) { $meets = false; break; }
                continue;
            }
            // normal: check achievement code exists in user's achievements
            if (!in_array($r, $user_ach)) { $meets = false; break; }
        }

        if ($meets) {
            return ['id' => (int)$row['id'], 'title' => $row['title_name']];
        }
    }

    return null;
}

/* -------------------------
   Assign title if eligible
   - Updates user_points.current_title_id
   - Optionally keeps history in user_titles (creates table if absent)
   ------------------------- */
function assignTitleIfEligible($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    $eligible = getEligibleTitleForUser($user_id);
    if (!$eligible) return false;

    // update current_title_id in user_points
    $stmt = $conn->prepare("UPDATE user_points SET current_title_id = ? WHERE user_id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ii", $eligible['id'], $user_id);
    $stmt->execute();
    $stmt->close();

    // create user_titles table if not exists (history)
    if (!tableExists('user_titles')) {
        $conn->query("
            CREATE TABLE IF NOT EXISTS user_titles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title_id INT NOT NULL,
                assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    // insert history record
    $ins = $conn->prepare("INSERT INTO user_titles (user_id, title_id, assigned_at) VALUES (?, ?, NOW())");
    if ($ins) {
        $ins->bind_param("ii", $user_id, $eligible['id']);
        $ins->execute();
        $ins->close();
    }

    return true;
}

/* -------------------------
   Helper: get user's current title name
   ------------------------- */
function getUserTitle($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    $stmt = $conn->prepare("
        SELECT t.title_name
        FROM user_points p
        LEFT JOIN titles_list t ON t.id = p.current_title_id
        WHERE p.user_id = ? LIMIT 1
    ");
    if (!$stmt) return null;
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $title = null;
    if ($row = $res->fetch_assoc()) $title = $row['title_name'];
    $stmt->close();
    return $title;
}

/* -------------------------
   Optional: Recalculate titles for all users (admin task)
   ------------------------- */
function recalcAllTitles() {
    global $conn;
    $users = $conn->query("SELECT user_id FROM user_points");
    if (!$users) return false;
    while ($u = $users->fetch_assoc()) {
        assignTitleIfEligible((int)$u['user_id']);
    }
    return true;
}


include_once("functions2.php");
include_once("functions3.php");
/* -------------------------
   End of functions
   ------------------------- */

echo "<div class='alert alert-info'>✅ core functions loaded. Use these functions in your pages (addPoints, unlockAchievement, assignTitleIfEligible, etc.).</div>";

include 'includes/footer.php';
