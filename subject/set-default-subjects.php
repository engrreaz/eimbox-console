<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

// Default subjects copy from sccode=0 → your institution sccode

$sql = "INSERT INTO subjects
        (sccode, sccategory, subcode, subject, subben, subshname, ncode, fourth)
        SELECT 
            '$sccode',
            sccategory,
            subcode,
            subject,
            subben,
            subshname,
            ncode,
            fourth
        FROM subjects
        WHERE sccode=0
        AND sccategory='$sctype'
        AND subcode NOT IN (
            SELECT subcode FROM subjects WHERE sccode='$sccode'
        )";

if ($conn->query($sql)) {
    echo "<span class='text-success'>Default Subjects Applied</span>";
} else {
    echo "<span class='text-danger'>Operation Failed</span>";
}
?>