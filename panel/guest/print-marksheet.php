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



$sqlSession = "SELECT * FROM sessioninfo WHERE stid='$stid' and sccode='$sccode' and sessionyear LIKE '%$y_v2%' ORDER BY id DESC LIMIT 1";
$sessionInfo = $conn->query($sqlSession)->fetch_assoc();
$sqlProfile = "SELECT * FROM students WHERE stid='$stid' and sccode='$sccode'  ORDER BY id DESC LIMIT 1";
$profileInfo = $conn->query($sqlProfile)->fetch_assoc();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;

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

        .smtxt {
            font-size: 11px;
            font-weight: 400;
            padding: 3px;
        }

        .lgtxt {
            font-size: 15px;
            font-weight: 700;
            padding: 0 3px;
        }
    </style>


</head>

<body>

    <h2 style="text-align:center; margin-bottom:0;">Progress Report</h2>

    <table width="100%" cellpadding="8"
        style="border:0; margin:0; padding:0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        <tr>
            <td>
                <table width="100%" style=" margin:0; padding:0;">
                    <tr>

                        <td colspan="4">
                            <span style="font-size:11px;"><?= $stid; ?></span><br>
                            <span style="font-size:16px; font-weight: 700;;"><?= $profileInfo['stnameeng'] ?></span>

                        </td>
                    </tr>
                    <tr>
                        <td class="smtxt">Class</td>
                        <td class="smtxt">Section</td>
                        <td class="smtxt">Roll No</td>
                        <td class="smtxt">Session</td>

                    </tr>
                    <tr>
                        <td class="lgtxt"><?= $sessionInfo['classname'] ?></td>
                        <td class="lgtxt"><?= $sessionInfo['sectionname'] ?></td>
                        <td class="lgtxt"><?= $sessionInfo['rollno'] ?></td>

                        <td class="lgtxt"><?= $sessionInfo['sessionyear'] ?></td>
                    </tr>

                </table>

            </td>
            <td>
                <img src="https://eimbox.com/students/<?= $stid; ?>" />
            </td>
            <td>
                <?php
                $slot = $sessionInfo['slots'];
                $sql = "
                    SELECT *
                    FROM gpa
                    WHERE slot = '$slot'
                    AND (sccode = '$sccode' OR sccode = 0)
                    ORDER BY 
                        CASE 
                            WHEN sccode = '$sccode' THEN 1
                            ELSE 2
                        END,
                        minvalues DESC
                    ";

                $res = mysqli_query($conn, $sql);
                ?>

                <table border="1" width="100%" cellpadding="6" cellspacing="0">
                    <tr>
                        <th>Marks Range</th>
                        <th>Grade Point</th>
                        <th>Grade</th>
                        <th>Remark</th>
                    </tr>

                    <?php while ($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td class="cen">
                                <?= $row['minvalues'] ?> – <?= $row['maxvalues'] ?>
                            </td>
                            <td class="cen">
                                <?= number_format($row['gp'], 2) ?>
                            </td>
                            <td class="cen">
                                <?= $row['gl'] ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['remark']) ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                </table>


            </td>
        </tr>
    </table>



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