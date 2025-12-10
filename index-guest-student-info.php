<?php
$_SESSION['stid'] = $user_id_no;
$stid = $_SESSION['stid'];

$sql_sessioninfo = "SELECT * 
                    FROM sessioninfo 
                    WHERE stid='$stid' 
                      AND sccode='$sccode' 
                      AND sessionyear LIKE '%$y_v2%' 
                    LIMIT 1";

$res1 = mysqli_query($conn, $sql_sessioninfo);
$sessioninfo_data = null;
if ($res1 && mysqli_num_rows($res1) > 0) {
    $sessioninfo_data = mysqli_fetch_assoc($res1);
}

// ---------- Query 2 : student info ----------
$sql_student = "SELECT * 
                FROM students 
                WHERE stid='$stid' 
                  AND sccode='$sccode' 
                LIMIT 1";

$res2 = mysqli_query($conn, $sql_student);
$student_data = null;
if ($res2 && mysqli_num_rows($res2) > 0) {
    $student_data = mysqli_fetch_assoc($res2);
}


$stnameeng = $student_data['stnameeng'];


$classname = $sessioninfo_data['classname'];
$sectionname = $sessioninfo_data['sectionname'];
$rollno = $sessioninfo_data['rollno'];

?>