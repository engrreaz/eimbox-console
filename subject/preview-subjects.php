<?php
session_start();
include_once '../core/config.php';
include_once '../core/db.php';
include_once '../core/global_values.php';

$year = $_POST['year'] ?? '';
$cls = $_POST['cls'] ?? '';
$sec = $_POST['sec'] ?? '';
$global = $_POST['global'] ?? '';

$sl = $_POST['sl'] ?? $sctype;
$yr = $_POST['yr'] ?? $_COOKIE['chain-session'] ?? date('Y');
$cl = $_POST['cl'] ?? $cls;
$se = $_POST['se'] ?? $sec;

// echo $sl . '. ' . $yr . '. ' . $cl . '. ' . $se ;

$getId = '';
$lq = "SELECT subject
    FROM subsetup
    WHERE sessionyear='$yr' 
    AND slot='$sl' 
    AND sccode='$sccode'
    AND classname='$cl'
    AND sectionname='$se'";

// echo $lq;
$list = $conn->query($lq);

if ($list && $list->num_rows > 0) {
    while ($row = $list->fetch_assoc()) {
        $id = $row['subject'];
        $getId .= $id . ', ';
    }
}

// echo $getId . '<hr>';

$sub_list = '';
$sub_list_ex = '';

if ($global) {
    $q = "SELECT id, subject, tid, slno
          FROM subsetup
          WHERE sccode=0 and sessionyear='$yr' AND classname='$cl' AND sectionname='$se'
          ORDER BY slno";

    $check = $conn->query($q);
    if (!$check->num_rows) {
        $q = "SELECT id, subject, tid, slno
          FROM subsetup
          WHERE sccode=0 and sessionyear='$yr' AND classname='$cl'
          ORDER BY slno";
    }



} else {
    if (!$year || !$cls) {
        exit('Invalid source');
    }

    if ($sec == '') {
        $q = "SELECT id, subject, tid, slno
          FROM subsetup
          WHERE sessionyear='$year'
          AND classname='$cls'
          ORDER BY slno";
    } else {
        $q = "SELECT id, subject, tid, slno
          FROM subsetup
          WHERE sessionyear='$year'
          AND classname='$cls'
          AND sectionname='$sec'
          ORDER BY slno";
    }

}

// echo $q;

$r = $conn->query($q);

if (!$r->num_rows) {
    exit('<div class="text-danger">No subjects found</div>');
}

echo '<table class="table table-sm table-striped mb-0">';
echo '<tr class="table-light">
        <th style="width:40px">#</th>
        <th>Subject</th>
        <th>Teacher</th>
      </tr>';

$sl = 1;
while ($row = $r->fetch_assoc()) {
    $sub = $row['subject'];
    $tid = $row['tid'];
    $ii = $row['id'];

    // subject name
    $sq = $conn->query("
        SELECT subject FROM subjects
        WHERE subcode='$sub'
        ORDER BY sccode DESC
        LIMIT 1
    ");
    $subname = $sq->num_rows ? $sq->fetch_assoc()['subject'] : $sub;

    if (strpos($getId, $sub) !== false) {
        $sub_list_ex .= $ii . ', ';
    } else {
        $sub_list .= $ii . ', ';
    }



    // teacher name
    $tq = $conn->query("
        SELECT tname FROM teacher
        WHERE tid='$tid'
        LIMIT 1
    ");
    $tname = $tq->num_rows ? $tq->fetch_assoc()['tname'] : '';

    echo "<tr>
            <td>$sl</td>
            <td>$subname</td>
            <td>$tname</td>
          </tr>";
    $sl++;
}

echo '</table>';

echo '
<div class="d-flex gap-4 mt-4">

  <div class="form-check">
    <input class="form-check-input" type="radio" name="act" id="act_merge" value="merge">
    <label class="form-check-label fw-semibold" for="act_merge">
      Merge
    </label>
  </div>

  <div class="form-check">
    <input class="form-check-input" type="radio" name="act" id="act_replace" value="replace">
    <label class="form-check-label fw-semibold text-danger" for="act_replace">
      Replace
    </label>
  </div>

  <div class="form-check">
    <input class="form-check-input" type="radio" name="act" id="act_append" value="append" checked>
    <label class="form-check-label fw-semibold text-success" for="act_append">
      Append
    </label>
  </div>

</div>
';
echo '<input type="hidden" value="' . $sub_list . '" id="ids">';
echo '<input type="hidden" value="' . $sub_list_ex . '" id="ids_ex">';