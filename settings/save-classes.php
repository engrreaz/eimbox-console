<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$sccode  = $_SESSION['sccode'];
$classes = $_POST['classes'] ?? [];

// চেক করা ক্লাসগুলো সংগ্রহ
$activeClasses = [];

foreach ($classes as $c) {
    if ((int)$c['active'] === 1) {
        $activeClasses[] = $c['name'];
    }
}

$value = implode(',', $activeClasses);

// আগে আছে কিনা চেক
$chk = $conn->query("
    SELECT id 
    FROM settings 
    WHERE sccode='$sccode' AND setting_title='Classes'
    LIMIT 1
");

if ($chk && $chk->num_rows > 0) {

    // UPDATE
    $conn->query("
        UPDATE settings 
        SET settings_value='$value'
        WHERE sccode='$sccode' AND setting_title='Classes'
    ");

} else {

    // INSERT
    $conn->query("
        INSERT INTO settings (sccode, setting_title, settings_value)
        VALUES ('$sccode', 'Classes', '$value')
    ");
}

echo '<span class="text-success">Classes updated</span>';
