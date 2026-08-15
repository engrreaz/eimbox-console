<?php
/**
 * Step 5: Teacher Performance Calculation
 *
 * এই স্ক্রিপ্টটি প্রতিটি শিক্ষকের সামগ্রিক পারফরম্যান্স গণনা করে।
 * এটি `analytics_subject_performance` এবং `analytics_class_performance` টেবিল থেকে ডেটা একত্রিত করে
 * `analytics_teacher_performance` টেবিলে সংরক্ষণ করে।
 *
 * Variables available from the parent script (run_analysis_step.php):
 * @var mysqli $conn The database connection object.
 * @var int $dataset_id The ID of the current analysis dataset.
 * @var int $examid The ID of the selected exam.
 * @var string $slot The selected slot.
 */

// সরাসরি অ্যাক্সেস রোধ করার জন্য একটি নিরাপত্তা চেক
if (!isset($dataset_id) || !isset($examid_list_str)) {
    die("This script cannot be accessed directly. Required parameters are missing.");
}



$sql = "

INSERT INTO analytics_teacher_performance
(
    dataset_id,
    sccode,
    sessionyear,
    examid,
    slot,
    tid,

    total_students_taught,
    total_subjects_taught,
    total_classes_taught,

    overall_avg_marks,
    overall_pass_rate,
    overall_excellent_rate,

    avg_class_difficulty_factor,
    teacher_impact_index,

    avg_variance,
    avg_std_deviation,

    teacher_performance_index,
    teacher_impact_adjustment
)

SELECT

    asp.dataset_id,
    asp.sccode,
    asp.sessionyear,

    ? AS examid, -- This will now be a string list of IDs
    ? AS slot,

    asp.tid,

    SUM(asp.appeared_student_count) AS total_students_taught,

    COUNT(DISTINCT asp.subject_code) AS total_subjects_taught,

    COUNT(
        DISTINCT CONCAT(
            asp.classname,
            '|',
            asp.sectionname
        )
    ) AS total_classes_taught,

    /* Overall Average Marks */

    COALESCE(
        SUM(asp.total_marks_obtained)
        /
        NULLIF(SUM(asp.appeared_student_count), 0),
        0
    ) AS overall_avg_marks,

    /* Overall Pass Rate */

    COALESCE(
        SUM(asp.pass_count) * 100
        /
        NULLIF(SUM(asp.appeared_student_count), 0),
        0
    ) AS overall_pass_rate,

    /* Overall Excellent Rate */

    COALESCE(
        SUM(asp.excellent_count) * 100
        /
        NULLIF(SUM(asp.appeared_student_count), 0),
        0
    ) AS overall_excellent_rate,

    /* Average Class Difficulty Factor */

    COALESCE(
        AVG(acp.difficulty_factor),
        0
    ) AS avg_class_difficulty_factor,

    /* Teacher Impact Index */

    -- Teacher Impact Index: এটি শিক্ষকের পারফরম্যান্সকে ক্লাসের কাঠিন্যের সাথে সামঞ্জস্য করে।
    -- যদি ক্লাসের গড় নম্বর কম হয় (অর্থাৎ ক্লাস কঠিন), তাহলে TII > 1 হবে, যা TIA স্কোরকে বুস্ট করবে।
    -- আমরা এখানে সরাসরি গড় নম্বর ব্যবহার করছি, কারণ difficulty_factor এর নতুন সূত্রটি ভিন্ন স্কেলে মান দিতে পারে।
    1 + (100 - COALESCE(AVG(acp.overall_marks_percentage), 50)) / 100 AS teacher_impact_index,

    /* Average Variance */

    COALESCE(
        AVG(asp.variance),
        0
    ) AS avg_variance,

    /* Average Standard Deviation */

    COALESCE(
        AVG(asp.std_deviation),
        0
    ) AS avg_std_deviation,

    /* TPI will be calculated later */

    0 AS teacher_performance_index,

    /* TIA will be calculated later */

    0 AS teacher_impact_adjustment

FROM analytics_subject_performance asp

LEFT JOIN analytics_class_performance acp

    ON asp.dataset_id = acp.dataset_id

    AND asp.classname COLLATE utf8mb4_unicode_ci
        =
        acp.classname COLLATE utf8mb4_unicode_ci

    AND asp.sectionname COLLATE utf8mb4_unicode_ci
        =
        acp.sectionname COLLATE utf8mb4_unicode_ci

WHERE

    asp.dataset_id = ?

    AND asp.tid IS NOT NULL

    AND asp.tid != ''

GROUP BY

    asp.dataset_id,
    asp.sccode,
    asp.sessionyear,
    asp.tid
    -- ON DUPLICATE KEY UPDATE is removed to ensure each dataset/teacher/exam combo is unique.
    -- The table should have a unique key on (dataset_id, tid, examid) to prevent duplicates.
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    throw new Exception(
        "Failed to prepare teacher performance query: "
        . $conn->error
    );
}

$stmt->bind_param(
    "ssi",
    $examid_list_str,
    $slot,
    $dataset_id
);

if (!$stmt->execute()) {
    throw new Exception(
        "Failed to execute teacher performance query: "
        . $stmt->error
    );
}

$stmt->close();
?>