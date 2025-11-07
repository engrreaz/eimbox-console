<?php
require_once '../core/config.php';
require_once '../core/db.php';

// === Start ===
$sccode = $_GET['sccode'] ?? '';
if (!$sccode) {
    echo "❌ Invalid code";
    exit;
}

$sy = date('Y');
$rootuser = '';
$exam = '';
$exam_id = 0;

// প্রাথমিক বার্তা
echo '<hr class="m-0 p-0 mt-1 mb-1" />';
echo "<span class='text-dark'>Preparing to Initializing for $sccode...</span>";
echo '<div class="float-end"><i class="bi bi-check-circle-fill text-secondary"></i></div>';
echo '<hr class="m-0 p-0 mt-1 mb-1" />';

// --- Root user খোঁজা ---
$sql = "SELECT rootuser FROM scinfo WHERE sccode='$sccode' LIMIT 1";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    $rootuser = $res->fetch_assoc()['rootuser'];
}

// --- Exam খোঁজা ---
$sql = "SELECT id, examtitle FROM examlist WHERE sccode='$sccode' AND sessionyear='$sy' ORDER BY id DESC LIMIT 1";
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    $r = $res->fetch_assoc();
    $exam_id = $r['id'];
    $exam = $r['examtitle'];
}

// --- Areas প্রসেস করা ---
$sql = "SELECT slot, areaname, subarea FROM areas WHERE sccode='$sccode' AND user='$rootuser' AND sessionyear='$sy'";
$res = $conn->query($sql);

if (!$res || $res->num_rows == 0) {

    echo "No Class/Section Found";
    echo '<div class="float-end"><i class="bi bi-x-circle-fill text-danger"></i></div>';
    echo '<hr class="m-0 p-0 mt-1 mb-1" />';
    exit;
}

$total = $res->num_rows;
$count = 0;

while ($rg = $res->fetch_assoc()) {
    $count++;
    $sl = $rg['slot'];
    $cc = $rg['areaname'];
    $ss = $rg['subarea'];

    $data_exist = 0;
    $sql_rand = "SELECT id   FROM stmark WHERE classname='$cc' and sectionname = '$ss' and exam='$exam' ";
    $res_rand = $conn->query($sql_rand);
    if ($res_rand && $res_rand->num_rows > 0) {
        $data_exist = 1;
    }

    ?>
    <div class="clearfix ">
        <span class="mt-1 "><?= "[$count/$total] ▶ Processing for $cc / $ss ..." ?></span>
       
       <?php if($data_exist ==0){ ?>
        <button class="btn btn-primary btn-insert p-0 ps-3 pe-3 float-end" data-sccode="<?= $sccode; ?>"
            data-slot="<?= $sl; ?>" data-sessionyear="<?= $sy; ?>" data-classname="<?= $cc; ?>"
            data-sectionname="<?= $ss; ?>" data-exam="<?= $exam; ?>" data-exam_id="<?= $exam_id; ?>">
            Insert Dummy
        </button>
        <?php } else {
            echo '<span class="float-end">Data Exists <i class="bi bi-check-circle-fill text-primary"></i></span>';
        }
        ?>
    </div>


    <?php
    echo '<hr class="m-0 p-0 mt-1 mb-1" />';
}

echo "✅ Preparing Completed! ";
echo " [Ready to Insertion]";