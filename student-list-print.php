<?php require_once 'header.php'; ?>

<?php
$slot = $_GET['slot'] ?? $_COOKIE['chain-slot'] ?? '';
$sessionyear = $_GET['session'] ?? $_COOKIE['chain-session'] ?? '';
$class = $_GET['class'] ?? $_COOKIE['chain-class'] ?? '';
$section = $_GET['section'] ?? $_COOKIE['chain-section'] ?? '';

$ids = $_GET['ids'] ?? '';

$whereIds = '';
if (!empty($ids)) {
    $idArray = explode(',', $ids);
    $safeIds = array_map('intval', $idArray); // নিরাপত্তার জন্য
    $idList = implode(',', $safeIds);
    $whereIds = " AND si.stid IN ($idList)";
}

$students = [];

if ($class && $sessionyear) {

    $stmt = $conn->prepare("
        SELECT 
            si.stid, si.rollno,
            s.stnameeng, s.stnameben,
            s.fname, s.mname,
            s.previll, s.prepo, s.preps, s.predist,
            s.dob, s.guarmobile
        FROM sessioninfo si
        JOIN students s ON si.stid = s.stid AND si.sccode = s.sccode
        WHERE si.sccode = ?
        AND si.sessionyear = ?
        AND si.slot = ?
        AND si.classname = ?
        AND si.sectionname = ?
        $whereIds
        ORDER BY si.rollno ASC
    ");

    $stmt->bind_param("issss", $sccode, $sessionyear, $slot, $class, $section);
    $stmt->execute();

    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<style>
    body {
        background: white;
        color:black !important;
    }

    .photo {
        width: 75px;
        height: 100px;
        object-fit: cover;
    }

    .tdd {
        padding: 5px !important;
        color:black !important;
    }

    @media print {
        body * {
            visibility: hidden;
            background: white;
        }



        #print-area,
        #print-area * {
            visibility: visible;
            background: transparent;
        }

          #print-area {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            color:black !important;
            background: white;
        }

        .no-print {
            display: none !important;
        }
    }

    @page {
        size: A4 portrait;

        background: white;
    }
</style>

<div class="container mt-3" id="print-area" style="background:white; text-align:center; ">

    <!-- Letter Head -->
    <?php include BASE_ROOT . 'templete/letter-head-01.php'; ?>

    <h4 class="text-center border-bottom my-2">
        Student List - <?= "$class ($section) - $sessionyear" ?>
    </h4>

    <table class="table table-sm" style="width:100%; color:black !important;">
        <thead>
            <tr>
                <th class="tdd">Photo</th>
                <th class="tdd">Roll</th>
                <th class="tdd">Name (Eng/Bn)</th>
                <th class="tdd">Parents</th>
                <th class="tdd">Address</th>
                <th class="tdd">DOB & Mobile</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($students as $st): ?>
                <tr>
                    <td style="text-align:center;" class="tdd">
                        <img src="<?= student_profile_image_path($st['stid']) ?>" class="photo">
                    </td>

                    <td style="text-align:center;" class="tdd">
                        <?= $st['rollno'] ?>
                    </td>

                    <td class="tdd">
                        <b><?= $st['stnameeng'] ?></b><br>
                        <small style="font-size:18px;"><?= $st['stnameben'] ?></small>
                    </td>

                    <td class="tdd">
                        <?= $st['fname'] ?><br>
                        <?= $st['mname'] ?>
                    </td>

                    <td class="tdd">
                        <?= "{$st['previll']}, {$st['prepo']}, {$st['preps']}, {$st['predist']}" ?>
                    </td>

                    <td class="tdd" style="white-space: nowrap;">
                        📅 <?= date('d-m-Y', strtotime($st['dob'])) ?>
                        <br><br>
                        <span style="font-weight: bold;">📞 <?= $st['guarmobile'] ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>



<?php require_once 'footer.php'; ?>

<script>
    window.onload = function () {
        window.print();
    }
</script>