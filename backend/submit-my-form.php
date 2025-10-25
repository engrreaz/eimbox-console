<?php
include '../core/config.php';
include '../core/db.php';

$table = $_POST['table'] ?? '';
if ($table == '') {
    die("<div class='alert alert-danger'>❌ টেবিল নির্দিষ্ট করা হয়নি!</div>");
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
unset($_POST['table']); // অপ্রয়োজনীয় অংশ বাদ
unset($_POST['id']);

$data = $_POST;

// Checkbox (skills[]) এলে array কে comma দিয়ে আলাদা করা
foreach ($data as $key => $value) {
    if (is_array($value)) {
        $data[$key] = implode(",", $value);
    }
}

// ইনপুট এস্কেপ করা
$escaped = [];
foreach ($data as $key => $val) {
    $k = mysqli_real_escape_string($conn, $key);
    $v = mysqli_real_escape_string($conn, $val);
    $escaped[$k] = $v;
}

// আপডেট বা ইনসার্ট নির্ধারণ
if ($id > 0) {
    // UPDATE Query তৈরি
    $setParts = [];
    foreach ($escaped as $key => $val) {
        $setParts[] = "`$key` = '$val'";
    }
    $setQuery = implode(", ", $setParts);
    $sql = "UPDATE `$table` SET $setQuery WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        echo "<div class='alert alert-success'>✅ রেকর্ড আপডেট সম্পন্ন হয়েছে!</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ আপডেট ত্রুটি: " . mysqli_error($conn) . "</div>";
    }
} else {
    // INSERT Query তৈরি
    $columns = "`" . implode("`, `", array_keys($escaped)) . "`";
    $values = "'" . implode("', '", array_values($escaped)) . "'";
    $sql = "INSERT INTO `$table` ($columns) VALUES ($values)";

    if (mysqli_query($conn, $sql)) {
        echo "<div class='alert alert-success'>✅ নতুন রেকর্ড সংরক্ষিত হয়েছে!</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ ইনসার্ট ত্রুটি: " . mysqli_error($conn) . "</div>";
    }
}
?>
