<?php

/**
 * Step 1: Subject-wise Performance Analysis
 *
 * analytics_subject_performance টেবিলে বিষয়ভিত্তিক পারফরম্যান্স সংরক্ষণ।
 */

if (!isset($sccode) || !isset($examid_list_str)) {
    die("This script cannot be accessed directly.");
}

$dataset_id = $_SESSION['analytics_dataset_id'] ?? 0;

if ($dataset_id == 0) {
    throw new Exception("Dataset ID not found in session.");
}

/* ----------------------------------------------------------
   Optimization: Pre-calculate averages to prevent slow subqueries
---------------------------------------------------------- */

// 1. Create a temporary table to hold the average marks for each subject group.
$conn->query("DROP TEMPORARY TABLE IF EXISTS temp_subject_avg");
$create_temp_table_sql = "
CREATE TEMPORARY TABLE temp_subject_avg (
    sccode VARCHAR(10),
    sessionyear VARCHAR(10),
    classname VARCHAR(50),
    sectionname VARCHAR(50),
    subject VARCHAR(50),
    avg_mark_percentage_for_subject DECIMAL(10, 2), -- Changed to store percentage
    PRIMARY KEY (sccode, sessionyear, classname, sectionname, subject)
);
";
$conn->query($create_temp_table_sql);

// 2. Populate the temporary table with average marks.
$populate_temp_table_sql = "
INSERT INTO temp_subject_avg (sccode, sessionyear, classname, sectionname, subject, avg_mark_percentage_for_subject)
SELECT
    sm.sccode,
    sm.sessionyear,
    sm.classname,
    sm.sectionname,
    sm.subject,
    AVG((sm.markobt / NULLIF(sm.fullmark, 0)) * 100) -- Calculate average percentage
FROM stmark sm
WHERE sm.sccode = ? AND sm.sessionyear = ? AND sm.examid IN (" . $examid_list_str . ")
GROUP BY sm.sccode, sm.sessionyear, sm.classname, sm.sectionname, sm.subject;
";
$stmt_avg = $conn->prepare($populate_temp_table_sql);
$stmt_avg->bind_param("ss", $sccode, $sessionyear);
$stmt_avg->execute();
$stmt_avg->close();

/* ----------------------------------------------------------
   Insert / Update Analytics
---------------------------------------------------------- */

$sql = "

INSERT INTO analytics_subject_performance
(
    dataset_id,
    sccode,
    sessionyear,
    examid,
    classname,
    sectionname,
    subject_code,
    tid,

    -- Gender based columns
    male_count,
    female_count,

    student_count,

    total_marks_obtained,
    total_full_marks,

    avg_marks,
    marks_percentage,
    max_marks,
    min_marks,

    variance,
    std_deviation,

    marks_range,

    pass_count,
    fail_count,

    -- Gender based pass count
    male_pass_count,
    female_pass_count,
    male_avg_marks,
    female_avg_marks,
    pass_rate,
    fail_rate,

    excellent_count,
    excellent_rate,

    count_above_avg,
    count_below_avg
)

SELECT

    ?,
    ss.sccode,
    ss.sessionyear,
    ?, -- Placeholder for examid list string
    ss.classname,
    ss.sectionname,
    ss.subject,
    ss.tid,

    /* Male Count */
    SUM(CASE WHEN st.gender = 'Male' THEN 1 ELSE 0 END),

    /* Female Count */
    SUM(CASE WHEN st.gender = 'Female' THEN 1 ELSE 0 END),

    COUNT(sm.markobt),

    COALESCE(SUM(sm.markobt),0),
    COALESCE(SUM(sm.fullmark),0),

    COALESCE(AVG(sm.markobt),0),

    /* Marks Percentage */
    COALESCE(SUM(sm.markobt) / NULLIF(SUM(sm.fullmark), 0) * 100, 0),

    COALESCE(MAX((sm.markobt / NULLIF(sm.fullmark, 1)) * 100), 0),

    COALESCE(
        MIN(
            CASE
                WHEN sm.markobt > 0 THEN (sm.markobt / NULLIF(sm.fullmark, 1)) * 100
            END
        ),
    0),

    COALESCE(VAR_POP((sm.markobt / NULLIF(sm.fullmark, 1)) * 100), 0),

    COALESCE(STDDEV_POP((sm.markobt / NULLIF(sm.fullmark, 1)) * 100), 0),

    COALESCE(MAX((sm.markobt / NULLIF(sm.fullmark, 1)) * 100), 0) -
    COALESCE(
        MIN(
            CASE
                WHEN sm.markobt > 0 THEN (sm.markobt / NULLIF(sm.fullmark, 1)) * 100
            END
        ),
    0),

    /* Pass Count */
    SUM(
        CASE
            WHEN (sm.markobt / NULLIF(sm.fullmark, 1)) * 100 >= 33 THEN 1
            ELSE 0
        END
    ),

    /* Fail Count */
    SUM(
        CASE
            WHEN (sm.markobt / NULLIF(sm.fullmark, 1)) * 100 < 33 THEN 1
            ELSE 0
        END
    ),

    /* Male Pass Count */
    SUM(CASE WHEN sm.markobt >= 33 AND st.gender = 'Male' THEN 1 ELSE 0 END),

    /* Female Pass Count */
    SUM(CASE WHEN sm.markobt >= 33 AND st.gender = 'Female' THEN 1 ELSE 0 END),

    /* Male Avg Marks */
    COALESCE(AVG(CASE WHEN st.gender = 'Male' THEN (sm.markobt / NULLIF(sm.fullmark, 1)) * 100 END), 0),

    /* Female Avg Marks */
    COALESCE(AVG(CASE WHEN st.gender = 'Female' THEN (sm.markobt / NULLIF(sm.fullmark, 1)) * 100 END), 0),



    /* Pass Rate */
    ROUND(
        (
            SUM(
                CASE
                    WHEN sm.markobt >= 33 THEN 1
                    ELSE 0
                END
            ) * 100
        ) / NULLIF(COUNT(sm.markobt),0),
    2),

    /* Fail Rate */
    COALESCE(
    ROUND(
        (
            SUM(
                CASE
                    WHEN sm.markobt < 33 THEN 1
                    ELSE 0
                END
            ) * 100
        ) / NULLIF(COUNT(sm.markobt),0),
    2),
    0),

    /* Excellent Count (>=70) */
    SUM(
        CASE
            WHEN (sm.markobt / NULLIF(sm.fullmark, 1)) * 100 >= 70 THEN 1
            ELSE 0
        END
    ),

    /* Excellent Rate */
    COALESCE(
    ROUND(
        (
            SUM(
                CASE -- Changed to use percentage for excellent rate
                    WHEN (sm.markobt / NULLIF(sm.fullmark, 1)) * 100 >= 70 THEN 1
                    ELSE 0
                END
            ) * 100
        ) / NULLIF(COUNT(sm.markobt),0),
    2),
0),

    /* Above Average Count */
    SUM(
        CASE WHEN (sm.markobt / NULLIF(sm.fullmark, 1)) * 100 > t.avg_mark_percentage_for_subject THEN 1 ELSE 0 END
    ),

    /* Below Average Count */
    SUM(
        CASE WHEN (sm.markobt / NULLIF(sm.fullmark, 1)) * 100 < t.avg_mark_percentage_for_subject THEN 1 ELSE 0 END
    )

FROM subsetup ss

LEFT JOIN stmark sm
       ON sm.sccode      = ss.sccode
      AND sm.sessionyear = ss.sessionyear
      AND sm.classname   = ss.classname
      AND sm.sectionname = ss.sectionname
      AND sm.subject     = ss.subject
      AND sm.examid      IN (" . $examid_list_str . ")

LEFT JOIN students st
       ON sm.stid = st.stid AND sm.sccode = st.sccode

LEFT JOIN temp_subject_avg t
       ON ss.sccode = t.sccode
      AND ss.sessionyear = t.sessionyear
      AND ss.classname = t.classname
      AND ss.sectionname = t.sectionname
      AND ss.subject = t.subject
      -- Changed to use the new temporary table column for percentage average

WHERE
        ss.sccode      = ?
    AND ss.sessionyear = ?
    AND ss.slot        = ?

GROUP BY
    ss.sccode,
    ss.sessionyear,
    ss.classname,
    ss.sectionname,
    ss.subject,
    ss.tid

ON DUPLICATE KEY UPDATE

    male_count            = VALUES(male_count),
    female_count          = VALUES(female_count),
    male_pass_count       = VALUES(male_pass_count),
    female_pass_count     = VALUES(female_pass_count),
    male_avg_marks        = VALUES(male_avg_marks),
    female_avg_marks      = VALUES(female_avg_marks),
    student_count         = VALUES(student_count),

    total_marks_obtained  = VALUES(total_marks_obtained),
    total_full_marks      = VALUES(total_full_marks),

    avg_marks             = VALUES(avg_marks),
    marks_percentage      = VALUES(marks_percentage),
    max_marks             = VALUES(max_marks),
    min_marks             = VALUES(min_marks),

    variance              = VALUES(variance),
    std_deviation         = VALUES(std_deviation),

    marks_range           = VALUES(marks_range),

    pass_count            = VALUES(pass_count),
    fail_count            = VALUES(fail_count),

    pass_rate             = VALUES(pass_rate),
    fail_rate             = VALUES(fail_rate),

    excellent_count       = VALUES(excellent_count),
    excellent_rate        = VALUES(excellent_rate),

    count_above_avg       = VALUES(count_above_avg),
    count_below_avg       = VALUES(count_below_avg),

    updated_at            = CURRENT_TIMESTAMP;

";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    throw new Exception("Prepare failed: " . $conn->error);
}

$stmt->bind_param(
    "issss",
    $dataset_id,
    $examid_list_str,
    $sccode,
    $sessionyear,
    $slot
);

if (!$stmt->execute()) {
    throw new Exception($stmt->error);
}

$stmt->close();

// Clean up the temporary table
$conn->query("DROP TEMPORARY TABLE IF EXISTS temp_subject_avg");
?>