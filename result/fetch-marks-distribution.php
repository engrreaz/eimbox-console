<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$slot     = $_POST['slot'];
$session  = $_POST['session'];
$exam     = $_POST['exam'];
$class    = $_POST['class'];
$section  = $_POST['section'];
$subject  = $_POST['subject'];

$q_str = "SELECT * FROM subsetup 
          WHERE sccode='$sccode' 
          AND sessionyear='$session' 
          AND classname='$class' 
          AND sectionname='$section' 
          AND subject='$subject' 
          AND slot='$slot' 
          LIMIT 1";

$q = mysqli_query($conn, $q_str);

if (mysqli_num_rows($q) > 0) {

    $row = mysqli_fetch_assoc($q);

    ?>

    <div id="alg" hidden><?= $row['pass_algorithm'] ?></div>
<div class="card-body">
    <div class="row">
        <div class="col-auto">
            <h5 id="ctmax" class="m-0 p-0"> <?= $row['ctest'] ?></h5>
            <label class="fs-tiny m-0 p-0">Class Test</label>
        </div>
        <div class="col-auto">
            <h5 id="mtmax" class="m-0 p-0"> <?= $row['mtest'] ?></h5>
            <label class="fs-tiny m-0 p-0">Monthly Test</label>
        </div>
        <div class="col-auto">
            <h5 id="submax" class="m-0 p-0"> <?= $row['subj'] ?></h5>
            <label class="fs-tiny m-0 p-0">Subjective</label>
        </div>
        <div class="col-auto">
            <h5 id="objmax" class="m-0 p-0"> <?= $row['obj'] ?></h5>
            <label class="fs-tiny m-0 p-0">Objective</label>
        </div>
        <div class="col-auto">
            <h5 id="pramax" class="m-0 p-0"> <?= $row['pra'] ?></h5>
            <label class="fs-tiny m-0 p-0">Practical</label>
        </div>
        <div class="col-auto">
            <h5 id="camax" class="m-0 p-0"> <?= $row['ca'] ?></h5>
            <label class="fs-tiny m-0 p-0">Learning Assess</label>
        </div>
        <div class="col-auto">
            <h5 id="camax" class="m-0 p-0"> <?= $row['fullmarks'] ?></h5>
            <label class="fs-tiny m-0 p-0">Full Marks</label>
        </div>
        <div class="col-auto">
            <h5 id="camax" class="m-0 p-0"> <?=  $row['pass_algorithm'] ?></h5>
            <label class="fs-tiny m-0 p-0">Pass Method</label>
        </div>
        <div class="col-auto">
            <h5 id="camax" class="m-0 p-0"> <?= $row['fourth'] ?></h5>
            <label class="fs-tiny m-0 p-0">Optional</label>
        </div>
    </div>
</div>

<?php 

} else {

  
}

?>
