<?php 
function sync_student_search($student_id)
{
    global $conn;

    $sql = "SELECT id, name FROM students WHERE id=$student_id";
    $r = mysqli_fetch_assoc(mysqli_query($conn, $sql));

    if (!$r) return;

    $title = mysqli_real_escape_string($conn, $r['name']);

    mysqli_query($conn, "
        REPLACE INTO search_index
        (ref_id, title, title_bn, url, icon, type, role)
        VALUES
        ($student_id, '$title', '$title', 
        '/students/view.php?id=$student_id',
        'ri-user-line', 'student', 'admin')
    ");
}


// sync_student_search($student_id);
// call after add/update student profile.