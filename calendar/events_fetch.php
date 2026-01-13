<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$user_id = $_SESSION['user_id'];

$type = $_GET['type'] ?? '';

// echo '.....' . $typeFilter . '.............';

$colorMap = [
    'holiday' => '#ff3d57',
    'exam' => '#ff9f43',
    'class' => '#28c76f',
    'sports' => '#00cfe8',
    'meeting' => '#7367f0',
    'notice' => '#ea5455',
    'other' => '#6c757d'
];




if ($type != '') {

    $whereType = ($type != 'all') ? "AND e.event_type='$type'" : "";
    $sql = "
        SELECT 
            e.*,
            IF(i.id IS NULL, 0, 1) AS imported
        FROM events e
        LEFT JOIN events i 
            ON i.parent_event_id = e.id 
            AND i.sccode = '$sccode'
        WHERE e.sccode = 0
        $whereType
        ORDER BY e.start
        ";

    // if ($typeFilter == 'all') {
    //     $sql = "SELECT * FROM events WHERE sccode=0";
    // } else {
    //     $sql = "SELECT * FROM events WHERE sccode=0 AND event_type='$typeFilter'";
    // }

} else {
    $sql = "
        SELECT * FROM events
        WHERE sccode='$sccode'
        AND (
            scope='institution'
            OR (scope='personal' AND user_id='$user_id')
        )
        ";
}
// echo $sql;

$res = mysqli_query($conn, $sql);

$events = [];
while ($row = mysqli_fetch_assoc($res)) {
    $color = $colorMap[$row['event_type']] ?? '#7367f0';
    $events[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start'],
        'end' => $row['end'],
        'allDay' => $row['all_day'] ? true : false,
        'backgroundColor' => $color,
        'borderColor' => $color,
        'event_type' => $row['event_type'],
        'scope' => $row['scope'],
        'imported' => intval($row['imported'] ?? 0)   // ⭐ এই লাইন
    ];
}

header('Content-Type: application/json');
echo json_encode($events);

