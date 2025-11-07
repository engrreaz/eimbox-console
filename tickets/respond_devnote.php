<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';



$admin_id = $user_id_no;// $_SESSION['id'];
$sccode = $_POST['sccode'] ?? '';
$ref_id = $_POST['ref_id'] ?? 0;
$status = $_POST['status'] ?? 'Replied';
$note = trim($_POST['note'] ?? '');

$ticket_id = 0;

if ($ref_id > 0) {

    $stmt = $conn->prepare("SELECT ticket_id FROM dev_notes WHERE id = ?");
    $stmt->bind_param("i", $ref_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res->num_rows) {
        echo "notfound";
        exit;
    }
    $ticket = $res->fetch_assoc();
    $ticket_id = $ticket['ticket_id'];
    $stmt->close();

}
$insert = $conn->prepare("
    INSERT INTO dev_notes (ref_id, sccode, ticket_id, admin_id, note_line, status)
    VALUES (?, ?, ?, ?, ?, ?)
");
$insert->bind_param("isiiss", $ref_id, $sccode, $ticket_id, $admin_id, $note, $status);

if ($insert->execute()) {
    echo "success";
} else {
    echo "db_error";
}
$inserted_id = $conn->insert_id;
$insert->close();

if ($ref_id == 0) {

    $conn->query("UPDATE dev_notes set ref_id=id where id='$inserted_id'");
}

$conn->close();