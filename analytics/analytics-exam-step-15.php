<?php
/**
 * Step 15: Teacher Ranking Calculation
 *
 * এই স্ক্রিপ্টটি `analytics_teacher_performance` টেবিল থেকে `teacher_impact_adjustment` (TIA) স্কোরের উপর ভিত্তি করে
 * প্রতিটি শিক্ষককে র‍্যাঙ্ক করে এবং `teacher_rank` কলাম আপডেট করে।
 *
 * @var mysqli $conn The database connection object.
 * @var int $dataset_id The ID of the current analysis dataset.
 */

// সরাসরি অ্যাক্সেস রোধ করার জন্য একটি নিরাপত্তা চেক
if (!isset($dataset_id)) {
    die("This script cannot be accessed directly.");
}

// ধাপ ১: `analytics_teacher_performance` টেবিলে teacher_rank কলাম যোগ করা (যদি না থাকে)
// এই কোয়েরিটি সরাসরি ডেটাবেজে একবার রান করতে পারেন অথবা এখানে রাখতে পারেন।
// ALTER TABLE `analytics_teacher_performance` ADD `teacher_rank` INT NULL DEFAULT 0 AFTER `teacher_impact_adjustment`;

// ধাপ ২: TIA স্কোরের উপর ভিত্তি করে র‍্যাঙ্ক গণনা ও আপডেট করার কোয়েরি
$query = "
    UPDATE analytics_teacher_performance AS atp
    JOIN (
        SELECT
            id,
            -- teacher_impact_adjustment (TIA score)-এর উপর ভিত্তি করে র‍্যাঙ্ক তৈরি করা হচ্ছে
            RANK() OVER (ORDER BY teacher_impact_adjustment DESC) as calculated_rank
        FROM
            analytics_teacher_performance
        WHERE
            dataset_id = ?
    ) AS ranked_teachers ON atp.id = ranked_teachers.id
    SET
        atp.teacher_rank = ranked_teachers.calculated_rank
    WHERE
        atp.dataset_id = ?;
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    throw new Exception("Failed to prepare statement for Teacher Ranking: " . $conn->error);
}

$stmt->bind_param("ii", $dataset_id, $dataset_id);

if (!$stmt->execute()) {
    throw new Exception("Failed to execute Teacher Ranking calculation: " . $stmt->error);
}

$stmt->close();

?>