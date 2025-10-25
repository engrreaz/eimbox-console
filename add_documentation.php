<?php
require_once 'header.php';

// Save handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_name = trim($_POST['page_name'] ?? '');
    $feature_name = trim($_POST['feature_name'] ?? '');
    $feature_title = trim($_POST['feature_title'] ?? '');
    $feature_description = trim($_POST['feature_description'] ?? '');
    $full_documentation = $_POST['full_documentation'] ?? ''; // HTML from Quill
    $tags = trim($_POST['tags'] ?? '');
    $created_by = $_SESSION['username'] ?? 'Admin';

    $stmt = $conn->prepare("INSERT INTO project_documentation (page_name, feature_name, feature_title, feature_description, full_documentation, tags, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $page_name, $feature_name, $feature_title, $feature_description, $full_documentation, $tags, $created_by);
    $stmt->execute();
    $stmt->close();

    header("Location: documentation_list.php");
    exit;
}
?>
<link rel="stylesheet" href="assets/vendor/libs/quill/editor.css" />
<div class="container mt-4">
    <h3>➕ Add Documentation</h3>
    <form method="POST" id="addForm">
        <div class="mb-3">
            <label>Page Name</label>
            <input name="page_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Feature Name</label>
            <input name="feature_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Feature Title</label>
            <input name="feature_title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Short Description</label>
            <textarea name="feature_description" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label>Tags (comma separated)</label>
            <input name="tags" class="form-control">
        </div>

        <div class="mb-3">
            <label>Full Documentation</label>
            <div id="quillEditor" style="height:400px; border:1px solid #ddd;"></div>
            <input type="hidden" name="full_documentation" id="full_documentation">
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="documentation_list.php" class="btn btn-secondary">Back</a>
    </form>
</div>


<?php include_once('footer.php'); ?>


<script src="assets/vendor/libs/quill/quill.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ header: 1 }, { header: 2 }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            [{ indent: '-1' }, { indent: '+1' }],
            [{ align: [] }],
            ['link', 'image', 'video'],
            ['clean']
        ];
        var quill = new Quill('#quillEditor', { theme: 'snow', modules: { toolbar: toolbarOptions } });

        document.getElementById('addForm').addEventListener('submit', function (e) {
            document.getElementById('full_documentation').value = quill.root.innerHTML;
        });

        // optional: simple image handler could be added similar to previous examples
    });
</script>


</body>

</html>