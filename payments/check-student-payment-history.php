<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$stid = $_POST['stid'];
$stid_digit = substr($stid, 6, 4);
$sy = $_COOKIE['chain-session'] ?? $y_v4;

$stpr = [];
$sql0x = "SELECT * FROM stpr WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%' AND stid='$stid' ORDER BY prdate, prno";
$result0xxtr = $conn->query($sql0x);
if ($result0xxtr->num_rows > 0) {
    while ($row0x = $result0xxtr->fetch_assoc()) {
        $stpr[] = $row0x;
    }
}

$stfin = [];
$sql0x = "SELECT * FROM stfinance WHERE sccode='$sccode' AND sessionyear LIKE '%$sy%' AND stid='$stid' AND (pr1>0 OR pr2>0) ORDER BY id";
$result0xxtr = $conn->query($sql0x);
if ($result0xxtr->num_rows > 0) {
    while ($row0x = $result0xxtr->fetch_assoc()) {
        $stfin[] = $row0x;
    }
}
?>

<h5 class="text-center mb-2">Payment History for Student ID: <?= $stid ?></h5>

<div class="list-group">
    <?php foreach ($stpr as $pr):
        $prno = strtolower($pr['prno']);
        $prdate = $pr['prdate'];
        $amount = (float) $pr['amount'];
        $prno_digit = substr($prno, 2, 4);
        $mismatch = ($stid_digit !== $prno_digit);
        $collapseId = "prDetails_" . $prno;

        $prtaka = 0;
        foreach ($stfin as $f) {
            if ($f['pr1no'] == $prno || $f['pr2no'] == $prno)
                $prtaka += $f['pr1'] + $f['pr2'];
        }
        ?>
        <div class="list-group-item mb-1 <?= $mismatch ? 'border border-danger' : '' ?>">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong><?= $prno ?></strong> - <?= date('d-m-Y', strtotime($prdate)) ?> -
                    <?= number_format($amount, 2) ?>
                    <?php if ($mismatch): ?>
                        <span class="text-danger">(Mismatch)</span>
                    <?php endif; ?>

                    <?php if ($stid_digit !== $prno_digit) { ?>
                        <i onclick="changedate('<?php echo $prno; ?>', 5, '<?php echo $prdate; ?>');"
                           class="bi bi-arrow-repeat text-muted ps-1 "></i>
                    <?php } else { ?>
                        <i onclick="changedate('<?php echo $prno; ?>', 3, '<?php echo $prdate; ?>');"
                           class="bi bi-calendar-week-fill text-muted ps-1"></i>
                    <?php } ?>
                </div>
                <button class="btn btn-sm btn-outline-primary" type="button" onclick="toggleDetails('<?= $collapseId ?>')">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>

            <div id="<?= $collapseId ?>" class="mt-2" style="display:none;">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Particular</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stfin as $f):
                                if ($f['pr1no'] == $prno || $f['pr2no'] == $prno):
                                    $idno = $f['id'];
                                    $peng = $f['particulareng'];
                                    $pramt = $f['pr1'] + $f['pr2'];
                                    $second = $f['pr2'];
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($peng) ?></td>
                                        <td class="text-end"><?= number_format($f['pr1'], 2) ?><?php if ($second > 0): ?><br><span
                                                    class="text-danger"><?= number_format($second, 2) ?></span><?php endif; ?></td>
                                        <td class="text-center">
                                            <?php if ($second > 0): ?>
                                                <button class="btn btn-warning btn-sm p-0"
                                                    onclick="rollback(<?= $idno ?>, <?= $second ?>, 2)">
                                                    <i class="bi bi-arrow-down"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-danger btn-sm p-0"
                                                    onclick="rollback(<?= $idno ?>, <?= $pramt ?>, 1)">
                                                    <i class="bi bi-trash2"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (abs($prtaka - $amount) > 0): ?>
                        <div class="text-danger font-weight-bold mt-1">Extra : <?= number_format($prtaka - $amount, 2) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function toggleDetails(id) {
        let el = document.getElementById(id);
        if (el.style.display === 'none' || el.style.display === '') {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }
</script>