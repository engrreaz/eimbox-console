<?php
require_once 'core/init.php'; // DB connection, session, etc.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $page_name = $_POST['page_name'];
    $feature_name = $_POST['feature_name'];
    $feature_title = $_POST['feature_title'];
    $feature_description = $_POST['feature_description'];
    $full_documentation = $_POST['full_documentation'];
    $created_by = $_SESSION['username'] ?? 'Admin';

    $stmt = $conn->prepare("INSERT INTO project_documentation (page_name, feature_name, feature_title, feature_description, full_documentation, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $page_name, $feature_name, $feature_title, $feature_description, $full_documentation, $created_by);
    $stmt->execute();
    $stmt->close();

    header("Location: documentation_list.php");
}
?>

<form method="post">
    <label>Page Name............:</label><br>
    <input type="text" name="page_name" required><br><br>

    <label>Feature Name:</label><br>
    <input type="text" name="feature_name" required><br><br>

    <label>Feature Title:</label><br>
    <input type="text" name="feature_title" required><br><br>

    <label>Feature Description:</label><br>
    <textarea name="feature_description" rows="3"></textarea><br><br>

    <label>Full Documentation (Markdown):</label><br>
    <textarea name="full_documentation" rows="10"></textarea><br><br>

    <button type="submit">Add Feature</button>
</form>
