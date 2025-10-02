<?php
require_once 'config.php';
require_once 'db.php';

$page  = $_POST['page_name'] ?? '';
$field = $_POST['field'] ?? '';
$status = $_POST['status'] ?? '';
$value = $_POST['value'] ?? '';

$allowed_fields = ['module_name','module_topic','descrip', 'status_name', 'nav_title', 'nav_icon']; // যে ফিল্ড আপডেট করতে পারবেন

if ($page && in_array($field, $allowed_fields)) {

    // আগে চেক করা হচ্ছে রেকর্ড আছে কিনা
    $check = $conn->prepare("SELECT id FROM modulemanager WHERE FIND_IN_SET(?, related_pages)");
    $check->bind_param("s", $page);
    $check->execute();
    $res = $check->get_result();

    if ($res && $res->num_rows > 0) {
        // থাকলে আপডেট
        $stmt = $conn->prepare("UPDATE modulemanager SET {$field}=? WHERE FIND_IN_SET(?, related_pages)");
        $stmt->bind_param("ss", $value, $page);
        if ($stmt->execute()) {
            echo "Updated $field for $page";
        } else {
            echo "Update failed";
        }
    } else {
        // না থাকলে নতুন ইনসার্ট
        $stmt = $conn->prepare("INSERT INTO modulemanager ({$field}, related_pages) VALUES (?, ?)");
        $stmt->bind_param("ss", $value, $page);
        if ($stmt->execute()) {
            echo "Inserted new record with $field for $page";
        } else {
            echo "Insert failed";
        }
    }

} else {
    echo "Invalid request";
}
?>

