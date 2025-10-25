<?php
require_once 'header.php'; // provides $conn (mysqli) and session

// --- INPUTS (server-side filter) ---
$page_name = trim($_GET['page_name'] ?? '');
$feature = trim($_GET['feature_name'] ?? '');
$created_by = trim($_GET['created_by'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

// --- Pagination setup ---
$perPage = 15;
$p = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$offset = ($p - 1) * $perPage;

// --- Count total (with same filters) ---
$where = " WHERE 1=1 ";
$params = [];
$types = "";

if ($page_name !== '') {
    $where .= " AND page_name LIKE ?";
    $params[] = "%$page_name%";
    $types .= "s";
}
if ($feature !== '') {
    $where .= " AND feature_name LIKE ?";
    $params[] = "%$feature%";
    $types .= "s";
}
if ($created_by !== '') {
    $where .= " AND created_by LIKE ?";
    $params[] = "%$created_by%";
    $types .= "s";
}
if ($date_from !== '') {
    $where .= " AND created_at >= ?";
    $params[] = $date_from . " 00:00:00";
    $types .= "s";
}
if ($date_to !== '') {
    $where .= " AND created_at <= ?";
    $params[] = $date_to . " 23:59:59";
    $types .= "s";
}

$countSql = "SELECT COUNT(*) AS total FROM project_documentation $where";
$stmt = $conn->prepare($countSql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

$totalPages = (int) ceil($total / $perPage);

// --- Fetch page rows ---
$listSql = "SELECT id, page_name, feature_name, feature_title, tags, version, created_by, created_at
            FROM project_documentation
            $where
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";
$stmt = $conn->prepare($listSql);

// bind params with LIMIT & OFFSET
if ($params) {
    // add types for two ints
    $allTypes = $types . "ii";
    $allParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($allTypes, ...$allParams);
} else {
    $stmt->bind_param("ii", $perPage, $offset);
}

$stmt->execute();
$res = $stmt->get_result();

function q($v)
{
    return htmlspecialchars($v);
}

// build query string for pagination & export (preserve filters)
$queryString = http_build_query(array_filter([
    'page_name' => $page_name ?: null,
    'feature_name' => $feature ?: null,
    'created_by' => $created_by ?: null,
    'date_from' => $date_from ?: null,
    'date_to' => $date_to ?: null
]));
?>
<!doctype html>
<html lang="bn">

<head>
    <meta charset="utf-8">
    <title>Documentation List</title>
    <link rel="stylesheet" href="assets/vendor/libs/bootstrap/bootstrap.css"><!-- adjust if needed -->
    <style>
        .badge-tags {
            margin-right: 6px;
        }

        .table-small td,
        .table-small th {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <?php // optional nav/header ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>📘 Feature Documentation</h3>
            <div>
                <a class="btn btn-success" href="edit_documentation.php">➕ Add New</a>
                <a class="btn btn-secondary" href="export_pdf.php?<?= $queryString ?>">📄 Export PDF</a>
            </div>
        </div>

        <form class="row g-2 mb-3" method="get" id="filterForm">
            <div class="col-md-2"><input class="form-control" name="page_name" placeholder="Page"
                    value="<?= q($page_name) ?>"></div>
            <div class="col-md-2"><input class="form-control" name="feature_name" placeholder="Feature"
                    value="<?= q($feature) ?>"></div>
            <div class="col-md-2"><input class="form-control" name="created_by" placeholder="Created by"
                    value="<?= q($created_by) ?>"></div>
            <div class="col-md-2"><input type="date" class="form-control" name="date_from" value="<?= q($date_from) ?>">
            </div>
            <div class="col-md-2"><input type="date" class="form-control" name="date_to" value="<?= q($date_to) ?>">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="documentation_list.php">Reset</a>
            </div>
        </form>

        <div class="mb-2">
            <input id="quickSearch" class="form-control" placeholder="Quick client-side search (debounced)">
        </div>

        <div class="mb-2 text-muted">
            Total: <strong><?= $total ?></strong> — Page <?= $p ?> of <?= max(1, $totalPages) ?>
        </div>

        <table id="docTable" class="table table-bordered table-sm table-small">
            <thead class="table-dark">
                <tr>
                    <th>Page</th>
                    <th>Feature</th>
                    <th>Title</th>
                    <th>Tags</th>
                    <th>Version</th>
                    <th>Created By</th>
                    <th>Date</th>
                    <th style="width:150px">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= q($row['page_name']) ?></td>
                        <td><?= q($row['feature_name']) ?></td>
                        <td><?= q($row['feature_title']) ?></td>
                        <td>
                            <?php if (!empty($row['tags'])): ?>
                                <?php foreach (explode(',', $row['tags']) as $t): ?>
                                    <span class="badge bg-info badge-tags"><?= q(trim($t)) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?= q($row['version'] ?: 'v1.0') ?></span></td>
                        <td><?= q($row['created_by']) ?></td>
                        <td><?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-info" href="view_documentation.php?id=<?= $row['id'] ?>">View</a>
                            <a class="btn btn-sm btn-warning" href="edit_documentation.php?id=<?= $row['id'] ?>">Edit</a>
                            <form method="post" action="delete_documentation.php" style="display:inline"
                                onsubmit="return confirm('Are you sure to delete this document?');">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile;
                $stmt->close(); ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="page-nav">
            <ul class="pagination">
                <?php if ($p > 1): ?>
                    <li class="page-item"><a class="page-link"
                            href="?<?= $queryString ? $queryString . '&' : '' ?>p=<?= $p - 1 ?>">Prev</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Prev</span></li>
                <?php endif; ?>

                <?php
                // show a window of pages
                $start = max(1, $p - 3);
                $end = min($totalPages, $p + 3);
                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i == $p ? 'active' : '' ?>"><a class="page-link"
                            href="?<?= $queryString ? $queryString . '&' : '' ?>p=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>

                <?php if ($p < $totalPages): ?>
                    <li class="page-item"><a class="page-link"
                            href="?<?= $queryString ? $queryString . '&' : '' ?>p=<?= $p + 1 ?>">Next</a></li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>




    <?php include_once('footer.php'); ?>


    <script>
        // debounced quick client-side search
        let timer;
        document.getElementById('quickSearch').addEventListener('input', function () {
            clearTimeout(timer);
            const el = this;
            timer = setTimeout(() => {
                const q = el.value.toLowerCase();
                document.querySelectorAll('#docTable tbody tr').forEach(tr => {
                    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            }, 200);
        });
    </script>

</body>

</html>