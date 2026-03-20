<?php
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = intval($_POST['ticket_id']);
    $admin_id = intval($_SESSION['user_id']);
    $notes = trim($_POST['notes']);
    $status = $_POST['status'] ?? 'New';
    $msgid = intval($_POST['msgid']);



    if (!$sccode || !$notes) {
        echo "missing";
        exit;
    }


    $lines = preg_split('/\r\n|\r|\n/', $notes);
    $status = 'New';

    $stmt = $conn->prepare("
    INSERT INTO dev_notes (ref_id, sccode, ticket_id, admin_id, note_line, status)
    VALUES (0, ?, ?, ?, ?, ?)
");

    $updateRefStmt = $conn->prepare("UPDATE dev_notes SET ref_id = ? WHERE id = ?");

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '')
            continue;

        // Step 1: Insert the record (ref_id = 0 initially)
        $stmt->bind_param("siiss", $sccode, $ticket_id, $admin_id, $line, $status);
        $stmt->execute();

        // Step 2: Get inserted id
        $inserted_id = $conn->insert_id;

        // Step 3: Update ref_id = id for this record
        $updateRefStmt->bind_param("ii", $inserted_id, $inserted_id);
        $updateRefStmt->execute();
    }

    $stmt->close();
    $updateRefStmt->close();

    $conn->query("UPDATE ticket_messages set dev_sub='1' where id='$msgid'");


    // এখন চাইলে প্রথম রেকর্ডের ref_id নিজেই নিজের আইডি হিসেবে আপডেট করা যেতে পারে:

    echo "success";
}
?>