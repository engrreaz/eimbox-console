<?php
require_once '../core/config.php';
require_once '../core/db.php'; 


$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

echo $from . ' - ' . $to;

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

<div class="container p-3">
<h4>Logs from <?= htmlspecialchars($from) ?> to <?= htmlspecialchars($to) ?></h4>

<table class="table table-sm table-bordered">
<tr>
    <th>Time</th><th>Open</th><th>Peak</th><th>Limit</th>
</tr>

<?php while($r=$res->fetch_assoc()): ?>
<tr>
    <td><?= $r['sccode'] ?></td>
    <td><?= $r['email'] ?></td>
    <td><?= $r['pagename'] ?></td>
    <td><?= $r['entrytime'] ?></td>
</tr>
<?php endwhile; ?>

</table>
</div>

<?php $stmt->close(); $conn->close(); ?>