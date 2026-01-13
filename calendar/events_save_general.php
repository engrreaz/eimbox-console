<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$ids = $_POST['ids'] ?? [];


$imported = 0;

foreach ($ids as $id) {

    // already imported?
    $chk = mysqli_query($conn, "
        SELECT id FROM events
        WHERE parent_event_id='$id'
        AND sccode='$sccode'
        LIMIT 1
    ");

    if (mysqli_num_rows($chk) > 0) {
        continue; // skip duplicate
    }

    mysqli_query($conn, "
        INSERT INTO events
        (title, start, end, event_type, scope, sccode, parent_event_id)
        SELECT 
            title, start, end, event_type, 'institution', '$sccode', id
        FROM events
        WHERE id='$id'
    ");

    $imported++;
}

echo json_encode([
    'status' => 'success',
    'imported' => $imported
]);
