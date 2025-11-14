<?php
require_once '../core/config.php';
require_once '../core/db.php';

$id = $_POST['id'] ?? '';
$mark = $_POST['mark'] ?? 0;

if($id != ''){
    $conn->query("UPDATE registrations SET adm_test_mark='$mark', marktime=NOW() WHERE id='$id'");
    echo "✅ মার্ক সেভ হয়েছে";
} else {
    echo "⚠️ Invalid ID";
}
