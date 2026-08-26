<?php
/**
 * EIMBox REST API — Notice Category Master Management
 * Endpoint: /api/v1/notices/notice-categories.php
 * Table: notice_category (notice_category.sql)
 */

require_once __DIR__ . '/../bootstrap.php';

// Authenticate caller
$user = api_authenticate_request();
$input = get_api_input();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $input['action'] ?? '';

// 1. Handle DELETE: Delete a Notice Category
if ($method === 'DELETE' || ($method === 'POST' && $action === 'delete')) {
    $id = intval($_GET['id'] ?? $input['id'] ?? 0);
    if ($id <= 0) {
        api_response('error', 'Valid Category ID is required for deletion.', null, 422);
    }

    $stmt = $conn->prepare("DELETE FROM notice_category WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        api_response('success', 'Notice category deleted successfully.', ['deleted_id' => $id]);
    } else {
        api_response('error', 'Category not found.', null, 404);
    }
}

// 2. Handle POST / PUT: Create or Update Category
if ($method === 'POST' || $method === 'PUT') {
    $id = intval($input['id'] ?? 0);
    $category = trim($input['category'] ?? $input['name'] ?? '');
    $icon = trim($input['icon'] ?? 'bell-fill');

    if (empty($category)) {
        api_response('error', 'Category name is required.', null, 422);
    }

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE notice_category SET category = ?, icon = ?, modifieddate = NOW() WHERE id = ?");
        $stmt->bind_param("ssi", $category, $icon, $id);
        $stmt->execute();
        $stmt->close();
        api_response('success', 'Notice category updated.', ['id' => $id, 'category' => $category]);
    } else {
        $stmt = $conn->prepare("INSERT INTO notice_category (category, icon, modifieddate) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE icon = VALUES(icon), modifieddate = NOW()");
        $stmt->bind_param("ss", $category, $icon);
        $stmt->execute();
        $insertId = $conn->insert_id;
        $stmt->close();
        api_response('success', 'Notice category created successfully.', ['id' => $insertId, 'category' => $category], 201);
    }
}

// 3. Handle GET: Query Categories
if ($method === 'GET') {
    $res = $conn->query("SELECT id, category, icon, modifieddate FROM notice_category ORDER BY category ASC");
    $categories = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    api_response('success', 'Notice categories retrieved.', [
        'total' => count($categories),
        'categories' => $categories
    ]);
}
