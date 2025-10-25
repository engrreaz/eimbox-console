<?php
include '../core/config.php';
include '../core/db.php';

$action = $_GET['action'] ?? ($_POST['id'] ? 'save' : 'insert');

if ($action === 'get' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $faq = $conn->query("SELECT * FROM faqs WHERE id=$id")->fetch_assoc();
    echo json_encode($faq);
    exit;
}

if ($action === 'insert' || $action === 'save') {
    $id = $_POST['id'] ?? '';
    $page = $_POST['page_name'];
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);

    if ($id) {
        $stmt = $conn->prepare("UPDATE faqs SET question=?, answer=? WHERE id=?");
        $stmt->bind_param("ssi", $question, $answer, $id);
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "FAQ updated successfully"]);
    } else {
        $stmt = $conn->prepare("INSERT INTO faqs (page_name, question, answer) VALUES (?,?,?)");
        $stmt->bind_param("sss", $page, $question, $answer);
        $stmt->execute();
        echo json_encode(["status" => "success", "message" => "FAQ added successfully"]);
    }
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM faqs WHERE id=$id");
    echo json_encode(["status" => "success", "message" => "FAQ deleted successfully"]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid request"]);
