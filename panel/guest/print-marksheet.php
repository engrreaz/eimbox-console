<?php
session_start();
require_once dirname(dirname(dirname(__FILE__))) . '/core/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/core/db.php';
require_once dirname(dirname(dirname(__FILE__))) . '/core/global_values.php';

$id = isset($_GET['id']) ? $conn->real_escape_string($_GET['id']) : '';
$stid = isset($_GET['stid']) ? $conn->real_escape_string($_GET['stid']) : '';

// fetch single row again (safe for direct access)
$sql = "SELECT * FROM tabulatingsheet WHERE stid='$stid' and id='$id' ORDER BY id DESC LIMIT 1";
$row = $conn->query($sql)->fetch_assoc();

$subjectList = array_filter(explode('.', $row['allsubject']));

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

            body{
                font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                padding:18mm;
            }
            table,
            td,
            th,
            tr {
                border: 1px solid black;
                border-collapse: collapse;
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
            <th>Subject Code</th>
            <th>Written</th>
            <th>Objective</th>
            <th>Practical</th>
            <th>CA</th>
            <th>CT</th>
            <th>MT</th>
            <th>Total</th>
            <th>GP</th>
            <th>Grade</th>
        </tr>

        <?php foreach ($subjects as $s): ?>
            <tr>
                <td><?= $s['code'] ?></td>
                <td><?= $s['subj'] ?></td>
                <td><?= $s['obj'] ?></td>
                <td><?= $s['pra'] ?></td>
                <td><?= $s['ca'] ?></td>
                <td><?= $s['ctest'] ?></td>
                <td><?= $s['mtest'] ?></td>
                <td><?= $s['total'] ?></td>
                <td><?= $s['gp'] ?></td>
                <td><?= $s['gl'] ?></td>
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