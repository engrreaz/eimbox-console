<?php
session_start();
require_once dirname(dirname(dirname(__FILE__))) . '/core/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/core/db.php';
require_once dirname(dirname(dirname(__FILE__))) . '/core/global_values.php';

include_once dirname(dirname(dirname(__FILE__))) . '/templete/letter-head-01.php';

$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : '';
$stid = isset($_GET['stid']) ? $conn->real_escape_string($_GET['stid']) : '';

// fetch single row again (safe for direct access)
$sql = "SELECT * FROM tabulatingsheet WHERE stid='$stid' and id='$id' ORDER BY id DESC LIMIT 1";
$row = $conn->query($sql)->fetch_assoc();

$subjectList = array_filter(explode('.', $row['allsubject']));

$sqlSub = "SELECT * FROM subjects WHERE sccategory='$sctype' and (sccode = 0 OR sccode = '$sccode')  ORDER BY subcode, sccode DESC ";
$resSub = $conn->query($sqlSub);
$subjectLists = [];
while ($subRow = $resSub->fetch_assoc()) {
    $subjectLists[$subRow['subcode']] = $subRow['subject'];
}

?>
<!DOCTYPE html>
<html>

<head>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 20mm;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                padding: 18mm;
            }

            table,
            td,
            th,
            tr {
                border: 1px solid black;
                border-collapse: collapse;
            }

            .cen {
                text-align: center;
                color: red;
            }
        }
    </style>

    <script>
        window.onload = function () {
            window.print();
        };

        window.onafterprint = function () {
            // window.close();
        };
    </script>
</head>

<body onload="window.print()">

    <h3 style="text-align:center;">Progress Report</h3>


    <?php
    $subjectIndexMap = [];

    for ($i = 1; $i <= 15; $i++) {
        if (!empty($row["sub_$i"])) {
            $subjectIndexMap[$row["sub_$i"]] = $i;
        }
    }

    $subjects = [];

    foreach ($subjectList as $subCode) {

        if (!isset($subjectIndexMap[$subCode])) {
            continue;
        }

        $i = $subjectIndexMap[$subCode];

        $subjects[] = [
            'code' => $subCode,
            'subj' => $row["sub_{$i}_sub"],
            'obj' => $row["sub_{$i}_obj"],
            'pra' => $row["sub_{$i}_pra"],
            'ca' => $row["sub_{$i}_ca"],
            'ctest' => $row["sub_{$i}_ct"],
            'mtest' => $row["sub_{$i}_mt"],
            'total' => $row["sub_{$i}_total"],
            'gp' => $row["sub_{$i}_gp"],
            'gl' => $row["sub_{$i}_gl"],
        ];
    }

    ?>

    <table border="1" width="100%" cellpadding="6" cellspacing="0">
        <tr>
            <th>Subject</th>
            <th>SUB</th>
            <th>OBJ</th>
            <th>PRA</th>
            <th>CA</th>
            <th>CT</th>
            <th>MT</th>
            <th>Total</th>
            <th colspan="2">Grade</th>
        </tr>

        <?php foreach ($subjects as $s): ?>
            <tr>
                <td><?= $subjectLists[$s['code']] ?></td>
                <td class="cen"><?= $s['subj'] ?></td>
                <td class="cen"><?= $s['obj'] ?></td>
                <td class="cen"><?= $s['pra'] ?></td>
                <td class="cen"><?= $s['ca'] ?></td>
                <td class="cen"><?= $s['ctest'] ?></td>
                <td class="cen"><?= $s['mtest'] ?></td>
                <td class="cen"><?= $s['total'] ?></td>
                <td class="cen"><?= $s['gp'] ?></td>
                <td class="cen"><?= $s['gl'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>


    GPA: <?= $row['gpa'] ?> |
    Grade: <?= $row['gla'] ?> |
    Total Marks: <?= $row['totalmarks'] ?>


    <table border="1" width="100%" cellpadding="8">
        <tr>
            <th>GPA</th>
            <td><?= $row['gpa'] ?></td>
        </tr>
        <tr>
            <th>Grade</th>
            <td><?= $row['gla'] ?></td>
        </tr>
        <tr>
            <th>Total Marks</th>
            <td><?= $row['totalmarks'] ?></td>
        </tr>
    </table>

</body>

</html>