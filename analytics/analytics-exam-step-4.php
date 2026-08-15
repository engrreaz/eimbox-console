<?php
/**
 * Step 4: Class Performance Index (CPI) Calculation
 *
 * এই স্ক্রিপ্টটি প্রতিটি ক্লাসের পারফরম্যান্সের উপর ভিত্তি করে একটি র‍্যাঙ্ক তৈরি করে।
 * এটি `analytics_class_performance` টেবিল থেকে ডেটা নিয়ে `overall_marks_percentage`-এর উপর ভিত্তি করে
 * প্রতিটি ক্লাসকে র‍্যাঙ্ক করে এবং `cpi_score` ও `class_rank` কলাম আপডেট করে।
 *
 * Variables available from the parent script (run_analysis_step.php):
 * @var mysqli $conn The database connection object.
 * @var int $dataset_id The ID of the current analysis dataset.
 */

// সরাসরি অ্যাক্সেস রোধ করার জন্য একটি নিরাপত্তা চেক

if (!isset($dataset_id)) {
    die("This script cannot be accessed directly.");
}

// ধাপ ১: `analytics_class_performance` টেবিলে cpi_score এবং class_rank কলাম দুটি যোগ করা (যদি না থাকে)
// ALTER TABLE `analytics_class_performance` ADD `cpi_score` DECIMAL(10,2) NULL DEFAULT 0 AFTER `teacher_impact_index`, ADD `class_rank` INT NULL DEFAULT 0 AFTER `cpi_score`;


// ধাপ ২: CPI স্কোর এবং র‍্যাঙ্ক গণনা করার জন্য কোয়েরি
// এখানে আমরা একটি সাব-কোয়েরি ব্যবহার করে `overall_marks_percentage`-এর উপর ভিত্তি করে প্রতিটি ক্লাসের জন্য একটি র‍্যাঙ্ক তৈরি করছি।
$query = "
    UPDATE analytics_class_performance AS acp
    JOIN (
        SELECT
            id,
            -- CPI Score Calculation: Weighted average of marks, pass rate, and excellent rate
            (
                (COALESCE(overall_marks_percentage, 0) * 0.5) +
                (COALESCE(pass_rate, 0) * 0.3) +
                (COALESCE(excellent_rate, 0) * 0.2)
            ) AS calculated_cpi,
            -- Rank based on the new calculated CPI score
            RANK() OVER (ORDER BY
                (COALESCE(overall_marks_percentage, 0) * 0.5) + (COALESCE(pass_rate, 0) * 0.3) + (COALESCE(excellent_rate, 0) * 0.2)
            DESC) as performance_rank
        FROM
            analytics_class_performance
        WHERE
            dataset_id = ?
    ) AS ranked_classes ON acp.id = ranked_classes.id
    SET
        acp.cpi_score = ranked_classes.calculated_cpi,
        acp.class_rank = ranked_classes.performance_rank
    WHERE
        acp.dataset_id = ?;
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    throw new Exception("Failed to prepare statement for CPI calculation: " . $conn->error);
}

$stmt->bind_param("ii", $dataset_id, $dataset_id);

if (!$stmt->execute()) {
    throw new Exception("Failed to execute CPI calculation: " . $stmt->error);
}

$stmt->close();

?>