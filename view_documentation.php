<?php
require_once 'header.php';


// 🟢 ডাটাবেস থেকে ডকুমেন্ট লোড করা
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo "<div class='alert alert-danger'>Invalid document ID.</div>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM project_documentation WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    echo "<div class='alert alert-warning'>Document not found.</div>";
    exit;
}

// 🧹 HTML Purifier config (safe but allows rich HTML)
$config = HTMLPurifier_Config::createDefault();

// iframe whitelist
$config->set('HTML.SafeIframe', true);
$config->set('URI.SafeIframeRegexp', '#^(https?:)?//(www\.youtube\.com/embed/|player\.vimeo\.com/video/)#');

// ✅ data:image/* allow করা
$config->set('URI.AllowedSchemes', array(
    'http' => true,
    'https' => true,
    'data' => true
));

// ✅ HTML allow list
$config->set('HTML.Allowed', 'p,br,b,strong,i,em,u,a[href|target],ul,ol,li,img[src|alt|width|height|style],h1,h2,h3,h4,h5,h6,table,tr,td,th,thead,tbody,pre,code,blockquote,iframe');
$config->set('CSS.AllowedProperties', ['width', 'height', 'float', 'margin', 'border', 'background', 'color']);

$purifier = new HTMLPurifier($config);

// 🔒 Safe render
$safe_html = $purifier->purify($data['full_documentation']);
?>

<style>
    .documentation-content {
        font-size: 16px;
        line-height: 1.6;
    }

    .documentation-content img {
        max-width: 100%;
        border-radius: 6px;
        margin: 10px 0;
        display: block;
    }

    .documentation-content iframe {
        max-width: 100%;
        min-height: 300px;
        border: none;
    }

    .documentation-content pre {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
        overflow-x: auto;
    }

    .documentation-content a {
        color: #0d6efd;
        text-decoration: underline;
    }
</style>

<div class="container mt-4">
    <h3><?= htmlspecialchars($data['feature_title']) ?></h3>
    <p><strong>Page:</strong> <?= htmlspecialchars($data['page_name']) ?> |
        <strong>Feature:</strong> <?= htmlspecialchars($data['feature_name']) ?>
    </p>
    <p><strong>Version:</strong> <?= htmlspecialchars($data['version']) ?></p>
    <hr>

    <div class="documentation-content">
        <?= $safe_html ?>
    </div>

    <hr>
    <p><em>Created by <?= htmlspecialchars($data['created_by']) ?> on <?= htmlspecialchars($data['created_at']) ?></em>
    </p>
    <a href="documentation_list.php" class="btn btn-secondary">Back</a>
</div>


<?php
require_once 'footer.php';