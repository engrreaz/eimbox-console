<?php
/**
 * Step 8: Final Teacher Performance Calculation (TPI & TIA)
 *
 * This script calculates the final performance scores for each teacher.
 * 1. Teacher Performance Index (TPI): A weighted score of a teacher's direct performance metrics.
 *    TPI = (overall_pass_rate * 0.40) + (overall_excellent_rate * 0.25) + (overall_avg_marks * 0.35)
 *
 * 2. Teacher Impact Adjustment (TIA): The TPI adjusted by the difficulty of the classes taught.
 *    TIA = TPI * teacher_impact_index
 *
 * The results are updated in the `analytics_teacher_performance` table.
 *
 * Variables available from the parent script (run_analysis_step.php):
 * @var mysqli $conn The database connection object.
 * @var int $dataset_id The ID of the current analysis dataset.
 */

// Security check to prevent direct access
if (!isset($dataset_id)) {
    die("This script cannot be accessed directly.");
}

// Define weights for TPI calculation
$weight_pass_rate = 0.40;
$weight_excellent_rate = 0.25;
$weight_avg_marks = 0.35;

// The query calculates TPI and TIA in a single UPDATE statement.
$query = "
    UPDATE analytics_teacher_performance
    SET
        -- Calculate Teacher Performance Index (TPI) first
        teacher_performance_index = 
            (COALESCE(overall_pass_rate, 0) * ?) +
            (COALESCE(overall_excellent_rate, 0) * ?) +
            (COALESCE(overall_avg_marks, 0) * ?),
        
        -- Then, calculate Teacher Impact Adjustment (TIA) using the TPI calculated above
        teacher_impact_adjustment = 
            ( (COALESCE(overall_pass_rate, 0) * ?) +
              (COALESCE(overall_excellent_rate, 0) * ?) +
              (COALESCE(overall_avg_marks, 0) * ?) ) * COALESCE(teacher_impact_index, 1)
    WHERE
        dataset_id = ?;
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ddddddi", $weight_pass_rate, $weight_excellent_rate, $weight_avg_marks, $weight_pass_rate, $weight_excellent_rate, $weight_avg_marks, $dataset_id);
$stmt->execute();
$stmt->close();
?>