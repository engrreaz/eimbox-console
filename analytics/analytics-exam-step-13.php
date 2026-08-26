<?php
/**
 * Step 13: Student Risk Score (SRS) Calculation
 *
 * এই স্ক্রিপ্টটি প্রতিটি শিক্ষার্থীর জন্য একটি ঝুঁকি স্কোর গণনা করে।
 * এটি বিষয়গুলোর কাঠিন্য (SDF), নম্বরের ব্যবধান (Mark Deficit) এবং শিক্ষকের বিষয়ভিত্তিক পারফরম্যান্স (TSPI) বিবেচনা করে।
 * ফলাফল `analytics_student_performance` টেবিলের `risk_score` কলামে আপডেট করা হয়।
 *
 * @var mysqli $conn
 * @var int $dataset_id
 * @var string $sccode
 * @var string $sessionyear
 * @var string $examid_list_str
 * @var string $slot
 */

// সরাসরি অ্যাক্সেস রোধ করার জন্য একটি নিরাপত্তা চেক
if (!isset($dataset_id)) {
    die("This script cannot be accessed directly.");
}

// কনফিগারেশন
define('PASS_MARK_PERCENTAGE', 33);
define('SDF_WEIGHT', 0.6);
define('MARK_DEFICIT_WEIGHT', 0.4);

// প্রথমে সকল শিক্ষার্থীর risk_score রিসেট করা
$conn->query("UPDATE analytics_student_performance SET risk_score = 0.00 WHERE dataset_id = $dataset_id");

// মূল কোয়েরি: প্রতিটি অকৃতকার্য বিষয়ের জন্য ঝুঁকি গণনা
$sql = "
    SELECT
        sm.stid,
        -- বিষয় ঝুঁকি (SR) = (SDF * ওজন) + (Marks Deficit % * ওজন)
        ( (COALESCE(aosp.subject_difficulty_factor, 40) * " . SDF_WEIGHT . ") + ((" . PASS_MARK_PERCENTAGE . " - (sm.markobt / sm.fullmark * 100)) * " . MARK_DEFICIT_WEIGHT . ") )
        *
        -- শিক্ষক ঝুঁকি মডিফায়ার (TRM) = 1 + ((50 - TSPI) / 100)
        ( 1 + ( (50 - COALESCE(asp.tspi, 50)) / 100 ) )
        AS final_risk_score
    FROM stmark sm
    -- বিষয়ভিত্তিক কাঠিন্য ফ্যাক্টর (SDF) আনার জন্য জয়েন
    JOIN analytics_overall_subject_performance aosp
        ON sm.subject = aosp.subject_code
        AND aosp.dataset_id = ?
    -- শিক্ষকের বিষয়ভিত্তিক পারফরম্যান্স (TSPI) আনার জন্য জয়েন
    JOIN analytics_subject_performance asp
        ON sm.sccode = asp.sccode
        AND sm.sessionyear = asp.sessionyear
        AND sm.classname = asp.classname
        AND sm.sectionname = asp.sectionname
        AND sm.subject = asp.subject_code
        AND asp.dataset_id = ?
    WHERE
        sm.sccode = ?
        AND sm.sessionyear = ?
        AND sm.examid IN (" . $examid_list_str . ")
        AND sm.slot = ?
        AND (sm.presence = 1 OR sm.markobt > 0)
        -- শুধুমাত্র অকৃতকার্য বিষয়গুলো ফিল্টার করা
        AND (sm.markobt / sm.fullmark * 100) < " . PASS_MARK_PERCENTAGE . "
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    throw new Exception("Prepare failed for risk calculation: " . $conn->error);
}
$stmt->bind_param("iisss", $dataset_id, $dataset_id, $sccode, $sessionyear, $slot);
$stmt->execute();
$result = $stmt->get_result();

// শিক্ষার্থীদের ঝুঁকি স্কোর একটি অ্যারেতে জমা করা
$student_risks = [];
while ($row = $result->fetch_assoc()) {
    $stid = $row['stid'];
    if (!isset($student_risks[$stid])) {
        $student_risks[$stid] = 0;
    }
    // একাধিক বিষয়ে ফেল করলে ঝুঁকি স্কোর যোগ হবে
    $student_risks[$stid] += $row['final_risk_score'];
}
$stmt->close();

if (empty($student_risks)) {
    // কোনো ঝুঁকিপূর্ণ শিক্ষার্থী না থাকলে এখানেই শেষ
    return;
}

//批量 আপডেট করার জন্য CASE স্টেটমেন্ট তৈরি করা
$update_sql = "UPDATE analytics_student_performance SET risk_score = CASE stid ";
foreach ($student_risks as $stid => $score) {
    $escaped_stid = $conn->real_escape_string($stid);
    $update_sql .= "WHEN '{$escaped_stid}' THEN " . round($score, 2) . " ";
}
$stids_to_update = array_keys($student_risks);
$stid_list_for_sql = "'" . implode("','", array_map([$conn, 'real_escape_string'], $stids_to_update)) . "'";
$update_sql .= "END WHERE dataset_id = {$dataset_id} AND stid IN ({$stid_list_for_sql})";

// ডাটাবেজ আপডেট
if (!$conn->query($update_sql)) {
    throw new Exception("Failed to bulk update risk scores: " . $conn->error);
}

?>