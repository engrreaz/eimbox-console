<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot = $_POST['slot'] ?? $sctype;
$session = $_POST['session'] ?? $y_v4;
$cls = $_POST['cls'] ?? '';
$sec = $_POST['sec'] ?? '';
$datefrom = $_POST['dateFrom'] ?? $td;
$dateto = $_POST['dateTo'] ?? $td;
$collector = $_POST['collector'] ?? '';



$sql = "
SELECT 
    sp.stid,
    si.rollno,
    si.classname,
    si.sectionname,
    st.stnameeng,
    st.stnameben,
    sp.prdate,
    sp.entryby,
    sp.prno,

    sp.amount AS total_taka,
    IFNULL(SUM(sf.pr1), 0) AS total_finance

FROM stpr sp

LEFT JOIN sessioninfo si 
    ON si.stid = sp.stid 
    AND si.sccode = sp.sccode

LEFT JOIN students st 
    ON st.stid = si.stid 
    AND st.sccode = si.sccode

LEFT JOIN stfinance sf 
    ON sf.stid = sp.stid 
    AND sf.sessionyear = sp.sessionyear
    AND sf.pr1no = sp.prno
    AND sf.pr1date = sp.prdate
    AND sf.sccode = sp.sccode

WHERE 
    sp.sccode = ?
    AND sp.sessionyear LIKE ?
    AND sp.prdate BETWEEN ? AND ?
";

if (!empty($cls)) {
    $sql .= " AND si.classname = ?";
}
if (!empty($sec)) {
    $sql .= " AND si.sectionname = ?";
}
if (!empty($collector)) {
    $sql .= " AND sp.entryby = ?";
}

$sql .= "
GROUP BY 
    sp.stid,
    sp.prno,
    sp.prdate

ORDER BY sp.entrytime DESC
";

$stmt = $conn->prepare($sql);
$likeSy = "%$session%";
$params = [$sccode, $likeSy, $datefrom, $dateto];
$types = "isss";

if (!empty($cls)) {
    $params[] = $cls;
    $types .= "s";
}
if (!empty($sec)) {
    $params[] = $sec;
    $types .= "s";
}
if (!empty($collector)) {
    $params[] = $collector;
    $types .= "s";
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();


$total_taka = 0;
while ($row = $result->fetch_assoc()) {
    $row_col = $row['total_taka'] != $row['total_finance'] ? 'table-danger' : '';
    ?>
    <tr class="click-row <?= $row_col ?>"
        onclick="viewReceipt(<?= htmlspecialchars($row['stid']) ?>, '<?= htmlspecialchars($row['prno']) ?>', '<?= htmlspecialchars($row['prdate']) ?>')">
        <td><input type="checkbox" onclick="event.stopPropagation(); " class="row-checkbox form-check-input"
                value="<?= htmlspecialchars($row['prno']) ?>"></td>
        <td class="text-center"><?= htmlspecialchars($row['rollno']) ?></td>
        <td><?= htmlspecialchars($row['stnameeng']) ?></td>
        <td><?= htmlspecialchars($row['stnameben']) ?></td>
        <td><?= htmlspecialchars($row['prno']) ?></td>
        <td><?= htmlspecialchars($row['prdate']) ?></td>
        <td class="text-end fw-bold"><?= number_format($row['total_taka'], 2) ?> |
            <?= number_format($row['total_finance'], 2) ?>
        </td>
        <td onclick="event.stopPropagation()">
            <div class="dropdown">
                <button class="btn p-0 dropdown-toggle hide-arrow shadow-none" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item"
                            onclick="viewReceipt(<?= htmlspecialchars($row['stid']) ?>, '<?= htmlspecialchars($row['prno']) ?>', '<?= htmlspecialchars($row['prdate']) ?>')">View
                            Receipt</a></li>
                    <li><a class="dropdown-item"
                            onclick="printReceipt(<?= htmlspecialchars($row['stid']) ?>, '<?= htmlspecialchars($row['prno']) ?>', '<?= htmlspecialchars($row['prdate']) ?>')">Print
                            Receipt</a></li>
                    <li><a class="dropdown-item"
                            onclick="downloadReceipt(<?= htmlspecialchars($row['stid']) ?>, '<?= htmlspecialchars($row['prno']) ?>', '<?= htmlspecialchars($row['prdate']) ?>')">Download
                            Receipt</a></li>
                </ul>
            </div>
        </td>
    </tr>
    <?php
    $total_taka += $row['total_taka'];
}
?>
<tr hidden>
    <td>
        <div id="motmot"><?= htmlspecialchars(number_format($total_taka, 2)) ?></div>
    </td>
</tr>