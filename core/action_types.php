<?php
// action_types.php
require_once '../header-plain.php'; 
header('Content-Type: application/json');


$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$category = $_POST['category'] ?? '';
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$risk_score = isset($_POST['risk_score']) ? intval($_POST['risk_score']) : 10;
$recommended_action = $_POST['recommended_action'] ?? 'log_only';

$response = ['success' => false, 'message' => 'Invalid request'];

try {
    if ($action === 'delete' && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM suspicious_activity_types WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false, 'message' => $stmt->error];
        }
    } elseif ($action === 'add' || $action === 'edit') {
        if (empty($category) || empty($title))
            throw new Exception('Category and Title are required');

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO suspicious_activity_types (category, title, description, risk_score, recommended_action) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssis", $category, $title, $description, $risk_score, $recommended_action);
        } else { // edit
            if ($id <= 0)
                throw new Exception('Invalid ID for edit');
            $stmt = $conn->prepare("UPDATE suspicious_activity_types SET category=?, title=?, description=?, risk_score=?, recommended_action=? WHERE id=?");
            $stmt->bind_param("sssisi", $category, $title, $description, $risk_score, $recommended_action, $id);
        }

        if ($stmt->execute()) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false, 'message' => $stmt->error];
        }
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
