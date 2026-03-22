<?php
require_once '../core/config.php';
require_once '../core/db.php';


$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';


$stmt = $conn->prepare("
    SELECT *
    FROM logbook
    WHERE entrytime BETWEEN ? AND ?
    ORDER BY entrytime ASC
");
$stmt->bind_param("ss", $from, $to);
$stmt->execute();
$res = $stmt->get_result();
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<div class="container p-3">
    <h4>Logs from <?= htmlspecialchars($from) ?> to <?= htmlspecialchars($to) ?></h4>

    <table class="table table-sm table-bordered">
        <tr>
            <th>Time</th>
            <th>Platform</th>
            <th>EIIN</th>
            <th>User</th>
            <th>Script</th>
        </tr>

        <?php while ($r = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $r['entrytime'] ?></td>
                <td><?= $r['platform'] ?></td>
                <td><?= $r['sccode'] ?></td>
                <td><?= $r['email'] ?></td>
                <td><?= $r['pagename'] ?></td>

            </tr>
        <?php endwhile; ?>

    </table>
</div>

<?php $stmt->close();
$conn->close(); ?>