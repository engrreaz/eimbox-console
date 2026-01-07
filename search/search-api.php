<?php

session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$role = $_SESSION['role'] ?? 'guest';
$q = trim($_GET['q'] ?? '');

$data = [];

if ($q !== '') {

    $q = mysqli_real_escape_string($conn, $q);

    $sql = "
        SELECT title, url, icon
        FROM search_index
        WHERE
          (title LIKE '%$q%' OR title_bn LIKE '%$q%')
          AND (role='$role' OR role='all')
          AND (sccode='$sccode' OR sccode IS NULL OR sccode='')
        ORDER BY type, updated_at DESC
        LIMIT 20
    ";

    $res = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = [
            'name' => $row['title'],
            'url'  => $row['url'],
            'icon' => $row['icon'],
            // 'subtitle' => 'Search Results',
            'meta' => 'pdf'
        ];
    }
}

echo json_encode($data);
