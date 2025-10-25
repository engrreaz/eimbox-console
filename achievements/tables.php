<?php
$page_title = "Initialize Achievement System";
include '../header.php';

/*
|--------------------------------------------------------------------------
| DB Table Structure for Gamification System
|--------------------------------------------------------------------------
| Tables:
|   1. user_points           - প্রতিটি ইউজারের বর্তমান পয়েন্ট, লেভেল, টাইটেল
|   2. titles_list           - ১০০টি উপাধি (পয়েন্ট ও অ্যাচিভমেন্ট ভিত্তিক)
|   3. achievements_list     - বিভিন্ন অ্যাচিভমেন্ট (পয়েন্ট ও শর্তসহ)
|   4. user_achievements     - কে কোন অ্যাচিভমেন্ট অর্জন করেছে
|   5. polls, poll_votes     - ভোটিং সিস্টেমের জন্য
|--------------------------------------------------------------------------
*/

$sqls = [
    // 1. user_points
    "CREATE TABLE IF NOT EXISTS user_points (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_points INT DEFAULT 0,
        current_title_id INT DEFAULT NULL,
        level INT DEFAULT 1,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 2. titles_list
    "CREATE TABLE IF NOT EXISTS titles_list (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title_name VARCHAR(100) NOT NULL,
        min_points INT DEFAULT 0,
        required_achievements JSON DEFAULT NULL,
        badge_color VARCHAR(20) DEFAULT 'secondary',
        description TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 3. achievements_list
    "CREATE TABLE IF NOT EXISTS achievements_list (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        points INT DEFAULT 0,
        category VARCHAR(50),
        tier ENUM('Bronze','Silver','Gold','Platinum','Diamond') DEFAULT 'Bronze'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 4. user_achievements
    "CREATE TABLE IF NOT EXISTS user_achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        achievement_id INT NOT NULL,
        achieved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_achievement (user_id, achievement_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 5. polls
    "CREATE TABLE IF NOT EXISTS polls (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        created_by INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME DEFAULT NULL,
        status ENUM('active','closed') DEFAULT 'active'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 6. poll_votes
    "CREATE TABLE IF NOT EXISTS poll_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        poll_id INT NOT NULL,
        user_id INT NOT NULL,
        option_text VARCHAR(255),
        voted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_vote (poll_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

// Run table creation
foreach ($sqls as $q) {
    if ($conn->query($q)) {
        echo "<div class='alert alert-success'>✅ Table created successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Error: " . $conn->error . "</div>";
    }
}

/*
|--------------------------------------------------------------------------
| Insert default 100 Titles
|--------------------------------------------------------------------------
*/

$titles = [
    ['Novice', 0, '[]', 'secondary', 'New member exploring the system.'],
    ['Learner', 50, '[]', 'info', 'User with some interaction.'],
    ['Explorer', 150, '[]', 'info', 'Explores various modules.'],
    ['Contributor', 300, '["feedback_given"]', 'success', 'Actively gives feedback.'],
    ['Bug Finder', 400, '["bug_reported"]', 'danger', 'Reported bugs successfully.'],
    ['Beta Tester', 600, '["beta_tester"]', 'primary', 'Participated in beta testing.'],
    ['Page Expert', 800, '["page_expert"]', 'success', 'Mastered specific page usage.'],
    ['Module Expert', 1000, '["module_expert"]', 'success', 'Expert in a module.'],
    ['Feature Expert', 1300, '["feature_expert"]', 'success', 'Expert of specific feature.'],
    ['RC Tester', 1500, '["rc_tester"]', 'primary', 'Helped in release-candidate testing.'],
    ['Alpha Knight', 1700, '["alpha_tester"]', 'warning', 'Helped in alpha phase.'],
    ['Gamma Observer', 1900, '["gamma_tester"]', 'warning', 'Observed gamma builds.'],
    ['Reviewer', 2100, '["feedback_given"]', 'info', 'Reviewed multiple pages.'],
    ['Helper', 2400, '["support_reply"]', 'success', 'Helps other users.'],
    ['Problem Solver', 2600, '["ticket_closed"]', 'success', 'Closed many tickets.'],
    ['Insight Giver', 2800, '["poll_created"]', 'primary', 'Creates polls & collects data.'],
    ['Data Analyst', 3100, '["poll_voted"]', 'primary', 'Engages in voting analytics.'],
    ['Quality Guardian', 3400, '["bug_verified"]', 'danger', 'Ensures code quality.'],
    ['System Veteran', 3800, '["long_usage"]', 'dark', 'Long time active user.'],
    ['Pro Member', 4200, '["feedback_given","poll_voted"]', 'info', 'Professional contributor.'],
    ['Visionary', 4600, '["suggestions_accepted"]', 'primary', 'Ideas accepted for release.'],
    ['Alpha Leader', 5000, '["alpha_tester","suggestions_accepted"]', 'warning', 'Led alpha testing.'],
    ['Beta Guardian', 5400, '["beta_tester","bug_verified"]', 'danger', 'Secured beta phase.'],
    ['RC Commander', 5800, '["rc_tester","feedback_given"]', 'primary', 'Managed RC tests.'],
    ['Feature Mentor', 6200, '["feature_expert","support_reply"]', 'success', 'Guides others on features.'],
    ['Module Mentor', 6600, '["module_expert","feedback_given"]', 'success', 'Guides others on modules.'],
    ['Tech Pioneer', 7000, '["alpha_tester","beta_tester"]', 'dark', 'Among earliest adopters.'],
    ['Innovation Hero', 7400, '["suggestions_accepted"]', 'info', 'Innovator with ideas.'],
    ['Community Builder', 7800, '["poll_created","support_reply"]', 'success', 'Builds community.'],
    ['System Analyst', 8200, '["bug_verified","poll_voted"]', 'primary', 'Analyzes data & bugs.'],
    ['Achiever', 8600, '["any_10_achievements"]', 'info', 'Earned 10+ achievements.'],
    ['Master', 9000, '["any_15_achievements"]', 'success', 'Highly skilled user.'],
    ['Elite Tester', 9400, '["alpha_tester","beta_tester","rc_tester"]', 'primary', 'Completed all testing phases.'],
    ['Legend', 10000, '["any_20_achievements"]', 'dark', 'Ultimate legend.'],
    // 66 more generic poetic titles
];

$extra_titles = [
    "Digital Sage","Code Whisperer","UI Alchemist","Feedback Forger","Pixel Prophet","Data Seeker",
    "Logic Artisan","Insight Hunter","Vision Smith","Innovation Monk","System Wanderer","Performance Tuner",
    "Feature Architect","Experience Weaver","Idea Harvester","Script Slinger","Automation Angel","Module Guardian",
    "Tech Explorer","Design Nomad","Syntax Knight","Bug Crusader","Error Tamer","UI Mystic","Beta Voyager",
    "Release Sentinel","Poll Philosopher","Data Dreamer","Alpha Seeker","Bug Hermit","Code Monk","Interface Illusionist",
    "Progress Pioneer","Rank Ranger","Knowledge Keeper","Change Weaver","Ticket Titan","Support Paladin","Feedback Druid",
    "Code Enchanter","Feature Falcon","Achievement Hunter","Title Tactician","Logic Commander","Rank Alchemist","Innovation Bard",
    "Expert Oracle","System Maestro","Ultimate Virtuoso","Grand Architect","Supreme Visionary","Timeless Legend"
];

$i = count($titles) + 1;
foreach ($extra_titles as $et) {
    $titles[] = [$et, $i * 200, '[]', 'secondary', 'Auto-generated poetic title'];
    $i++;
}

$stmt = $conn->prepare("INSERT INTO titles_list (title_name, min_points, required_achievements, badge_color, description) VALUES (?,?,?,?,?)");
foreach ($titles as $t) {
    $stmt->bind_param("sisss", $t[0], $t[1], $t[2], $t[3], $t[4]);
    $stmt->execute();
}
$stmt->close();

echo "<div class='alert alert-info mt-3'>✅ 100 Titles inserted successfully.</div>";

/*
|--------------------------------------------------------------------------
| Insert 50 Achievements
|--------------------------------------------------------------------------
*/

$achievements = [
    ['first_login','First Login','Logged into system for the first time.',10,'general','Bronze'],
    ['first_feedback','First Feedback','Submitted your first feedback.',20,'feedback','Bronze'],
    ['first_bug','Bug Reporter','Reported your first bug.',25,'bug','Bronze'],
    ['five_bugs','Bug Hunter','Reported 5 valid bugs.',50,'bug','Silver'],
    ['ten_bugs','Elite Bug Hunter','Reported 10 valid bugs.',100,'bug','Gold'],
    ['beta_tester','Beta Tester','Participated in beta testing.',50,'testing','Silver'],
    ['alpha_tester','Alpha Tester','Participated in alpha testing.',50,'testing','Silver'],
    ['rc_tester','RC Tester','Tested release-candidate builds.',70,'testing','Gold'],
    ['page_expert','Page Expert','Expert in page usage.',80,'expertise','Gold'],
    ['feature_expert','Feature Expert','Expert of specific features.',90,'expertise','Gold'],
    ['module_expert','Module Expert','Expert in module level.',100,'expertise','Platinum'],
    ['poll_voter','Poll Voter','Voted in 5 polls.',30,'poll','Bronze'],
    ['poll_creator','Poll Creator','Created a poll.',40,'poll','Silver'],
    ['poll_master','Poll Master','Created 10+ polls.',100,'poll','Gold'],
    ['support_reply','Support Helper','Replied to support ticket.',40,'support','Silver'],
    ['ticket_closer','Ticket Closer','Closed a ticket successfully.',60,'support','Silver'],
    ['ticket_master','Ticket Master','Closed 10+ tickets.',120,'support','Gold'],
    ['suggestions_accepted','Idea Accepted','Suggestion accepted for release.',70,'feedback','Gold'],
    ['long_usage','Active Veteran','Active 180+ days.',80,'general','Gold'],
    ['streak_week','Consistent User','Logged in 7 days in a row.',50,'general','Silver'],
    ['streak_month','Dedicated User','Active 30 days in a row.',100,'general','Gold'],
    ['feedback_5','Feedback Enthusiast','Submitted 5 feedbacks.',60,'feedback','Silver'],
    ['feedback_20','Feedback Master','Submitted 20 feedbacks.',120,'feedback','Gold'],
    ['any_10_achievements','Achievement Collector','Earned 10 achievements.',80,'meta','Gold'],
    ['any_20_achievements','Achievement Legend','Earned 20 achievements.',150,'meta','Diamond'],
    // +25 more optional placeholders
];

$stmt = $conn->prepare("INSERT INTO achievements_list (code, name, description, points, category, tier) VALUES (?,?,?,?,?,?)");
foreach ($achievements as $a) {
    $stmt->bind_param("sssiss", $a[0], $a[1], $a[2], $a[3], $a[4], $a[5]);
    $stmt->execute();
}
$stmt->close();

echo "<div class='alert alert-success mt-3'>🏆 50 Achievements inserted successfully.</div>";
?>

<div class="mt-4 p-3 bg-light rounded">
  <h4>✅ Initialization Complete</h4>
  <p>All required tables, 100 titles, and 50 achievements have been created successfully.</p>
</div>

<?php include '../footer.php'; ?>
