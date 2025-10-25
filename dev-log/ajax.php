<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

// Fetch single entry for edit
if (isset($_POST['id']) && !isset($_POST['delete'])) {
    $id = intval($_POST['id']);
    $res = $conn->prepare("SELECT * FROM dev_timeline WHERE id=?");
    $res->bind_param("i", $id);
    $res->execute();
    $result = $res->get_result()->fetch_assoc();
    $res->close();
    echo json_encode($result);
    exit;
}

// Delete
if (isset($_POST['delete'])) {
    $id = intval($_POST['delete']);
    $stmt = $conn->prepare("DELETE FROM dev_timeline WHERE id=?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
    exit;
}

// Insert / Update
if ($_POST && !isset($_POST['delete'])) {
    $id = intval($_POST['entry_id'] ?? 0);
    $page_name = $_POST['page_name'];
    $feature_name = ($_POST['feature_select'] ?? '') == 'new' ? ($_POST['feature_new'] ?? '') : ($_POST['feature_select'] ?? '');
    $action_type = $_POST['action_type'] ?? '';
    $status = $_POST['status'] ?? '';
    $description = $_POST['notes'] ?? '';
    $logged_by = $usr;

    // Status colors
    $statusColors = [
        'draft' => 'secondary',
        'planning' => 'warning',
        'in_progress' => 'info',
        'testing' => 'info',
        'alpha' => 'primary',
        'beta' => 'primary',
        'rc' => 'primary',
        'staging' => 'info',
        'stable' => 'success',
        'lts' => 'success',
        'deprecated' => 'dark',
        'archived' => 'secondary'
    ];

    $status_color = $statusColors[$status] ?? 'secondary';

    $stmt = $conn->prepare("INSERT INTO dev_timeline (page_name, feature_name, action_type, status, description, logged_by, created_at) VALUES (?,?,?,?,?,?,NOW())");
    $stmt->bind_param("ssssss", $page_name, $feature_name, $action_type, $status, $description, $logged_by);
    $ok = $stmt->execute();

    if ($ok) {
        $newId = $stmt->insert_id; // এখানে $newId সঠিকভাবে set
        $resp = [
            'success' => true,
            'id' => $newId,
            'created_at' => date('Y-m-d H:i'),
            'feature_name' => htmlspecialchars($feature_name),
            'action_type' => $action_type,
            'status' => $status,
            'status_color' => $status_color,
            'status_badge' => '<span class="badge bg-' . $status_color . '">' . $status . '</span>',
            'description' => htmlspecialchars($description),
            'logged_by' => $logged_by
        ];
        echo json_encode($resp);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
