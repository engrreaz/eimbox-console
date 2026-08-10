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

    COALESCE(MAX(sm.markobt),0),

    COALESCE(
        MIN(
            CASE
                WHEN sm.markobt > 0 THEN sm.markobt
            END
        ),
    0),

    COALESCE(VAR_POP(sm.markobt),0),

    COALESCE(STDDEV_POP(sm.markobt),0),

    COALESCE(MAX(sm.markobt),0) -
    COALESCE(
        MIN(
            CASE
                WHEN sm.markobt > 0 THEN sm.markobt
            END
        ),
    0),

    /* Pass Count */
    SUM(
        CASE
            WHEN sm.markobt >= 33 THEN 1
            ELSE 0
        END
    ),

    /* Fail Count */
    SUM(
        CASE
            WHEN sm.markobt < 33 THEN 1
            ELSE 0
        END
    ),

    /* Male Pass Count */
    SUM(CASE WHEN sm.markobt >= 33 AND st.gender = 'Male' THEN 1 ELSE 0 END),

    /* Female Pass Count */
    SUM(CASE WHEN sm.markobt >= 33 AND st.gender = 'Female' THEN 1 ELSE 0 END),

    /* Male Avg Marks */
    COALESCE(AVG(CASE WHEN st.gender = 'Male' THEN sm.markobt END), 0),

    /* Female Avg Marks */
    COALESCE(AVG(CASE WHEN st.gender = 'Female' THEN sm.markobt END), 0),



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
            WHEN sm.markobt >= 70 THEN 1
            ELSE 0
        END
    ),

    /* Excellent Rate */
    COALESCE(
    ROUND(
        (
            SUM(
                CASE
                    WHEN sm.markobt >= 70 THEN 1
                    ELSE 0
                END
            ) * 100
        ) / NULLIF(COUNT(sm.markobt),0),
    2),
0),

    /* Above Average Count */
    SUM(
        CASE
            WHEN sm.markobt >
            (
                SELECT AVG(sm2.markobt)
                FROM stmark sm2
                WHERE sm2.sccode      = ss.sccode
                  AND sm2.sessionyear = ss.sessionyear
                  AND sm2.classname   = ss.classname
                  AND sm2.sectionname = ss.sectionname
                  AND sm2.subject     = ss.subject
                  AND sm2.examid      IN (" . $examid_list_str . ")
            )
            THEN 1
            ELSE 0
        END
    ),

    /* Below Average Count */
    SUM(
        CASE
            WHEN sm.markobt <
            (
                SELECT AVG(sm2.markobt)
                FROM stmark sm2
                WHERE sm2.sccode      = ss.sccode
                  AND sm2.sessionyear = ss.sessionyear
                  AND sm2.classname   = ss.classname
                  AND sm2.sectionname = ss.sectionname
                  AND sm2.subject     = ss.subject
                  AND sm2.examid      IN (" . $examid_list_str . ")
            )
            THEN 1
            ELSE 0
        END
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
?>