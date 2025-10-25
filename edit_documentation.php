<?php
require_once 'header.php';

// Fetch current documentation (if id is set)
$id = intval($_GET['id'] ?? 0);

$row = [];

if ($id > 0) {
  $stmt = $conn->prepare("SELECT * FROM project_documentation WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) {
    echo "<div class='alert alert-danger'>❌ ডকুমেন্ট পাওয়া যায়নি</div>";
    exit;
  }
}

// Form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = intval($_POST['id'] ?? 0); // hidden input from form
  $page_name = $_POST['page_name'] ?? '';
  $feature_name = $_POST['feature_name'] ?? '';
  $feature_title = $_POST['feature_title'] ?? '';
  $feature_description = $_POST['feature_description'] ?? '';
  $full_documentation = $_POST['full_documentation'] ?? '';
  $tags = $_POST['tags'] ?? '';
  $version = $_POST['version'] ?? '';
  $username = $_SESSION['username'] ?? 'Admin';

  if ($id > 0) {
    // --- UPDATE existing row ---
    $sql = "UPDATE project_documentation 
                SET page_name=?, feature_name=?, feature_title=?, feature_description=?, 
                    full_documentation=?, tags=?, version=?, created_by=? 
                WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
      "ssssssssi",
      $page_name,
      $feature_name,
      $feature_title,
      $feature_description,
      $full_documentation,
      $tags,
      $version,
      $username,
      $id
    );
    $stmt->execute();
    $stmt->close();
    $msg = "✅ Successfully updated!";
  } else {
    // --- INSERT new row ---
    $sql = "INSERT INTO project_documentation 
                (page_name, feature_name, feature_title, feature_description, full_documentation, tags, version, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
      "ssssssss",
      $page_name,
      $feature_name,
      $feature_title,
      $feature_description,
      $full_documentation,
      $tags,
      $version,
      $username
    );
    $stmt->execute();
    $id = $stmt->insert_id; // get new id
    $stmt->close();
    $msg = "✅ Successfully added!";
  }

  echo "<div class='alert alert-success mt-3'>{$msg}</div>";

  // --- Refresh row data for form ---
  $stmt = $conn->prepare("SELECT * FROM project_documentation WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
}

?>

<!-- Quill CSS -->
<link rel="stylesheet" href="assets/vendor/libs/quill/typography.css" />
<link rel="stylesheet" href="assets/vendor/libs/highlight/highlight.css" />
<link rel="stylesheet" href="assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="assets/vendor/libs/quill/editor.css" />

<div class="container mt-4">
  <h3>Edit Feature Documentation</h3>
  <div class="row">

    <div class="col-md-6">
      <form method="POST" id="docForm" action="">
        <div class="mb-3">
          <label>Page Name</label>
          <input name="page_name" class="form-control" value="<?php echo htmlspecialchars($row['page_name'] ?? '') ?>"
            required>
        </div>
        <div class="mb-3">
          <label>Feature Name</label>
          <input name="feature_name" class="form-control" value="<?= htmlspecialchars($row['feature_name'] ?? '') ?>"
            required>
        </div>
        <div class="mb-3">
          <label>Feature Title</label>
          <input name="feature_title" class="form-control" value="<?= htmlspecialchars($row['feature_title'] ?? '') ?>"
            required>
        </div>
        <div class="mb-3">
          <label>Short Description</label>
          <textarea name="feature_description"
            class="form-control"><?= htmlspecialchars($row['feature_description'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
          <label>Tags</label>
          <input name="tags" class="form-control" value="<?= htmlspecialchars($row['tags'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label>Version</label>
          <input name="version" class="form-control" value="<?= htmlspecialchars($row['version'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label>Full Documentation</label>
          <div id="quillEditor" style="max-height:400px; overflow:auto; width:100%;">
            <?= $row['full_documentation'] ?? '' ?></div>
          <input type="hidden" name="full_documentation" id="full_documentation">
          <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'] ?? '') ?>">
        </div>
        <button class="btn btn-primary">💾 SAVE</button>
        <a href="documentation_list.php" class="btn btn-secondary">Back</a>
      </form>
    </div>

    <div class="col-md-6">
      <h5>Live Preview</h5>
      <div id="preview"
        style="background:#fff; border:1px solid #ddd; padding:15px; border-radius:8px; min-height:400px; overflow:auto;">
      </div>
    </div>
  </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>


<!-- Quill JS -->
<script src="assets/vendor/libs/quill/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.0-beta.0/dist/quill.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.0-beta.0/dist/quill.min.js"></script>


<script src="assets/js/forms-editors.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    var toolbarOptions = [
      ['bold', 'italic', 'underline', 'strike'],
      ['blockquote', 'code-block'],
      [{ 'header': 1 }, { 'header': 2 }],
      [{ 'list': 'ordered' }, { 'list': 'bullet' }],
      [{ 'script': 'sub' }, { 'script': 'super' }],
      [{ 'indent': '-1' }, { 'indent': '+1' }],
      [{ 'direction': 'rtl' }],
      [{ 'size': ['small', false, 'large', 'huge'] }],
      [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
      [{ 'color': [] }, { 'background': [] }],
      [{ 'font': [] }],
      [{ 'align': [] }],
      ['link', 'image', 'video'],
      ['clean']
    ];

    var quill;
    quill = new Quill('#quillEditor', {
      theme: 'snow',
      modules: { toolbar: toolbarOptions }
    });

    // Live preview
    document.getElementById('preview').innerHTML = quill.root.innerHTML;

    quill.on('text-change', function () {
      document.getElementById('preview').innerHTML = quill.root.innerHTML;
    });

    // On form submit, copy HTML to hidden input
    document.getElementById('docForm').addEventListener('submit', function (e) {
      document.getElementById('full_documentation').value = quill.root.innerHTML;
    });

    // Image handler
    quill.getModule('toolbar').addHandler('image', imageHandler);



    // Initialize live preview
    document.getElementById('preview').innerHTML = quill.root.innerHTML;

    quill.on('text-change', function () {
      document.getElementById('preview').innerHTML = quill.root.innerHTML;
    });

    // On form submit, copy HTML to hidden input
    document.getElementById('docForm').addEventListener('submit', function (e) {
      document.getElementById('full_documentation').value = quill.root.innerHTML;
    });
  });
</script>

<script>
  function imageHandler() {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = function () {
      var file = input.files[0];
      if (/^image\//.test(file.type)) {
        var formData = new FormData();
        formData.append('image', file);

        fetch('upload_image.php', { method: 'POST', body: formData })
          .then(response => response.json())
          .then(result => {
            if (result.success) {
              const range = quill.getSelection();
              quill.insertEmbed(range.index, 'image', result.url);
            } else {
              alert('Image upload failed: ' + result.error);
            }
          }).catch(err => {
            console.log('Error:', err);
            alert('Image upload failed.');
          });
      } else {
        alert('Please select an image.');
      }
    };
  }

  // Replace default image handler
  quill.getModule('toolbar').addHandler('image', imageHandler);

</script>


<!-- ----------------------------------- -->
</body>

</html>