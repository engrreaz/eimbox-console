<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: dejavusans, 'Segoe UI', sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        .cen {
            text-align: center;
        }

        .cen0 {
            text-align: center;
            padding: 1px;
        }

        @media print {
            body {
                font-size: 12px;
            }
        }
    </style>


</head>

<body>

    <?php include dirname(dirname(dirname(__FILE__))) . '/templete/letter-head-01.php'; ?>

    <h3 style="text-align:center;">Progress Report</h3>

    <table style="border:0;">
        <tr>
            <td style="border:0;">
                <div style="padding:0; margin:0; font-size:11px; font-weight:600;">ID : <?= $stid ?> </div>
                <b><?= $profileInfo['stnameeng'] ?></b><br>

            </td>

            <td style="border:0; " rowspan="2">
                <img src="https://eimbox.com/students/<?= $stid; ?>.jpg" style="border:1px solid gray; padding:2px; width:40mm;" />
            </td>
            <td style="width:65mm; border:0;" rowspan="2">
                <?php
                $slot = $sessionInfo['slot'];
                $sql = "
                    SELECT *
                    FROM gpa
                    WHERE (slot = '$slot' OR slot IS NULL)
                    AND (sccode = '$sccode' OR sccode = 0)
                    ORDER BY 
                        CASE 
                            WHEN slot = '$slot' THEN 1
                            ELSE 2
                        END,
                        CASE 
                            WHEN sccode = '$sccode' THEN 1
                            ELSE 2
                        END,
                        minvalues DESC
                    ";

                $res = mysqli_query($conn, $sql);
                ?>

                <table border="1" width="100%" cellpadding="1" cellspacing="0" style="font-size:10px;">
                    <tr>
                        <th>Marks Range</th>
                        <th>Grade Point</th>
                        <th>Grade</th>
                    </tr>

                    <?php while ($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td class="cen0">
                                <?= $row['minvalues'] ?> – <?= $row['maxvalues'] ?>
                            </td>
                            <td class="cen0">
                                <?= number_format($row['gp'], 2) ?>
                            </td>
                            <td class="cen0">
                                <?= $row['gl'] ?>
                            </td>

                        </tr>
                    <?php endwhile; ?>

                </table>


            </td>
        </tr>

        <tr>

            <td style="border:0;">
                <table style="border:0; font-size:11px; width:100%;">
                    <tr>
                        <td style="border:0;">
                            Class: <br>
                            <span style="font-size:14px; font-weight:600;"><?= $sessionInfo['classname'] ?></span>
                        </td>
                        <td style="border:0;">
                            Section: <br>
                            <span style="font-size:14px; font-weight:600;"><?= $sessionInfo['sectionname'] ?></span>
                        </td>
                        <td style="border:0;">
                            Roll: <br>
                            <span style="font-size:14px; font-weight:600;"><?= $sessionInfo['rollno'] ?></span>
                        </td>

                    </tr>

                </table>



            </td>

        </tr>
    </table>

    <br>

    <table>
        <tr>
            <th>Subject</th>
            <th>SUB</th>
            <th>OBJ</th>
            <th>PRA</th>
            <th>CA</th>
            <th>CT</th>
            <th>MT</th>
            <th>Total</th>
            <th>GP</th>
            <th>GL</th>
        </tr>

        <?php foreach ($subjects as $s): ?>
            <tr>
                <td><?= $subjectLists[$s['code']] ?? '' ?></td>
                <td class="cen"><?= $s['subj'] ?></td>
                <td class="cen"><?= $s['obj'] ?></td>
                <td class="cen"><?= $s['pra'] ?></td>
                <td class="cen"><?= $s['ca'] ?></td>
                <td class="cen"><?= $s['ct'] ?></td>
                <td class="cen"><?= $s['mt'] ?></td>
                <td class="cen"><?= $s['total'] ?></td>
                <td class="cen"><?= $s['gp'] ?></td>
                <td class="cen"><?= $s['gl'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <br>

    <b>GPA:</b> <?= $gpa ?> |
    <b>Grade:</b> <?= $gla ?> |
    <b>Total Marks:</b> <?= $totalmarks ?>

</body>

</html>