<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';


$cls2 = $_POST['cls'] ?? '';
$sec2 = $_POST['sec'] ?? '';
$sy = $_POST['year'] ?? date('Y');

if (!$cls2 || !$sec2) {
    exit;
}

$month = (int)date('m');

if ((int)date('d') > 20) {
    $month++;
    
    // ডিসেম্বর হলে জানুয়ারি
    if ($month > 12) {
        $month = 12;
    }
}


$sql = "
SELECT 
    si.stid,
    si.classname,
    si.sectionname,
    si.rollno,
    si.lastpr,

    st.stnameeng,
    st.stnameben,
    st.previll,
    st.guarmobile,

    IFNULL(SUM(sf.dues),0)       AS total_dues,
    IFNULL(SUM(sf.payableamt),0) AS total_payable,
    IFNULL(SUM(sf.paid),0)       AS total_paid

FROM sessioninfo si
LEFT JOIN students st 
    ON st.stid = si.stid AND st.sccode = si.sccode

LEFT JOIN stfinance sf 
    ON sf.stid = si.stid 
    AND sf.sccode = si.sccode
    AND sf.sessionyear LIKE ?
    AND sf.month <= ?

WHERE 
    si.sccode = ?
    AND si.sessionyear LIKE ?
    AND si.classname = ?
    AND si.sectionname = ?

GROUP BY si.stid
ORDER BY si.rollno
";



$stmt = $conn->prepare($sql);

$likeSy = "%$sy%";
$stmt->bind_param(
    "sissss",
    $likeSy,
    $month,
    $sccode,
    $likeSy,
    $cls2,
    $sec2
);

$stmt->execute();
$result = $stmt->get_result();



while ($row = $result->fetch_assoc()) {

    $name = $row['stnameeng'];
    $nameb = $row['stnameben'];
    $totaldues = $row['total_dues'];
    $lastpr = $row['lastpr'] ?? 0;

    $mno = $row['guarmobile'] ?? 0;
    $lastpr = $row['lastpr'] ?? 0;

    $datam = $cls2 . '_' . $sec2 . '_' . $row['rollno'] . '_' . $name . '_' . $nameb . '_' . $mno;
    ?>
    <tr class="click-row" style="cursor:pointer" data-stid="<?= $row['stid'] ?>" onclick="getdues(
        <?= (int) $row['stid'] ?>,
        <?= (int) $lastpr ?>,
        '<?= addslashes($datam) ?>',
        '<?= $sy ?>', <?= $totaldues ?>
        )">
        <td class="text-center"><?= $row['rollno'] ?></td>

        <td><?= htmlspecialchars($name) ?></td>

        <td class="text-end fw-bold">
            <?= number_format($totaldues, 2) ?>
        </td>

        <td class="text-center text-muted fw-bold">
            <i class="bi bi-arrow-right"></i>
        </td>
    </tr>

    <?php
}


