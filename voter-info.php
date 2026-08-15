<?php
require_once 'header.php';

// কুকি থেকে ফিল্টার প্যারামিটার গ্রহণ
$slot = $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_COOKIE['chain-session'] ?? '';
$class = $_COOKIE['chain-class'] ?? '';
$section = $_COOKIE['chain-section'] ?? '';

$students_list = [];
if (!empty($class) && !empty($sessionyear)) {
    $stmt = $conn->prepare("
        SELECT 
           si.id, si.stid, si.rollno, si.icardst,
            s.stnameeng, s.stnameben, s.fname, s.mname, 
            s.previll, s.prepo, s.preps, s.predist,
            s.fmobile, s.mmobile, s.fnid, s.mnid, s.guarmobile
        FROM sessioninfo AS si
        JOIN students AS s ON si.stid = s.stid AND si.sccode = s.sccode
        WHERE si.sccode = ? 
        AND si.sessionyear = ? 
        AND si.slot = ? 
        AND si.classname = ? 
        AND si.sectionname = ?
        AND si.status = 1
        ORDER BY si.rollno ASC
    ");
    $stmt->bind_param("issss", $sccode, $sessionyear, $slot, $class, $section);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students_list[] = $row;
    }
    $stmt->close();
}

?>

<style>
    .backpic {
        filter: grayscale(100);
        background: black;
    }

    #main-table td {
        border: 1px solid black;
        padding: 5px;
    }

    .txt-right {
        text-align: center;
        font-weight: bold;
        font-size: 14px;
    }

    @media print {
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .no-print,
        .layout-navbar,
        .layout-menu,
        .footer {
            display: none !important;
        }

        .container-xxl {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>


<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card no-print mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Voter Information</h4>
                <div>
                    <a href="managing-voter-list.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to
                        Selection</a>
                    <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($students_list)): ?>
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h4><?= htmlspecialchars($scname) ?></h4>
                    <p><?= htmlspecialchars($scaddress) ?></p>
                    <h5>Voter List: <?= htmlspecialchars($class) ?> (<?= htmlspecialchars($section) ?>) -
                        <?= htmlspecialchars($sessionyear) ?></h5>
                </div>

                <table class="table table-bordered" id="main-table">
                    <thead>
                        <tr class="txt-right">
                            <td>SL</td>
                            <td>Student's Name</td>
                            <td>Father's Name</td>
                            <td>Mother's Name</td>
                            <td>Father's NID</td>
                            <td>Mother's NID</td>
                            <td>Mobile No</td>
                            <td>Signature</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sl = 1;
                        foreach ($students_list as $student):
                            ?>
                            <tr>
                                <td class="txt-right"><?= $sl++ ?></td>
                                <td>
                                    <?= htmlspecialchars($student['stnameeng']) ?><br>
                                    <?= htmlspecialchars($student['stnameben']) ?>
                                </td>
                                <td><?= htmlspecialchars($student['fname']) ?></td>
                                <td><?= htmlspecialchars($student['mname']) ?></td>
                                <td><?= htmlspecialchars($student['fnid']) ?></td>
                                <td><?= htmlspecialchars($student['mnid']) ?></td>
                                <td>
                                    <?php
                                    $mobiles = array_filter([$student['guarmobile'], $student['fmobile'], $student['mmobile']]);
                                    echo htmlspecialchars(implode(', ', $mobiles));
                                    ?>
                                </td>
                                <td style="height: 50px;"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            No voter information found for the selected criteria. Please go back and select again.
        </div>
    <?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>
</body>

</html>