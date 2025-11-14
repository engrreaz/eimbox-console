<?php
require_once '../core/config.php';
require_once '../core/db.php';

$id = $_POST['id'] ?? '';
$q = $conn->query("SELECT * FROM registrations WHERE id='$id'");
if ($q->num_rows == 0) {
    echo "<div class='text-danger'>তথ্য পাওয়া যায়নি!</div>";
    exit;
}

$d = $q->fetch_assoc();
?>

<table class="table table-bordered">
    <tr>
        <th>Applicant's Name (English)</th>
        <td><?= htmlspecialchars($d['stnameeng']) ?></td>
        <td rowspan="4" class="text-center h-50">
            <img src="uploads/photos/<?php echo $d['photo']; ?>" />

        </td>

    </tr>
    <tr>
        <th>আবেদনকারীর নাম (বাংলা)</th>
        <td><?= htmlspecialchars($d['stnameben']) ?></td>
    </tr>
    <tr>
        <th>Father's Name</th>
        <td><?= htmlspecialchars($d['fname']) ?> (<?= $d['fmobile'] ?>)</td>
    </tr>
    <tr>
        <th>Mother's Name</th>
        <td><?= htmlspecialchars($d['mname']) ?> (<?= $d['mmobile'] ?>)</td>
    </tr>
    <tr>
        <th>Guardian</th>
        <td colspan="2"><?= htmlspecialchars($d['guar']) ?> <i class="bi bi-arrow-right"></i><?= htmlspecialchars($d['guarname']) ?> (<?= $d['mnumber'] ?>)</td>
    </tr>
    <tr>
        <th>Address</th>
        <td colspan="2"><?= "{$d['village']}, {$d['po']}, {$d['ps']}, {$d['dist']}" ?></td>
    </tr>
    <tr>
        <th>Date of Birth</th>
        <td colspan="2"><?= $d['dob'] ?></td>
    </tr>
    <tr>
        <th>Birth Regd. No.</th>
        <td colspan="2"><?= $d['brnno'] ?></td>
    </tr>
    <tr>
        <th>Institute</th>
        <td colspan="2"><?= "{$d['insname']}, {$d['inspo']}, {$d['insps']}, {$d['insdist']}" ?></td>
    </tr>
    <tr>
        <th>Mark & Merit</th>
        <td colspan="2"><?= $d['adm_test_mark'] ?>, <?= $d['meritplace'] ?></td>
    </tr>
</table>