<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

$cookieList = [];
if (!empty($_COOKIE['examitems'])) {
    $cookieList = explode(",", $_COOKIE['examitems']);
}

$slot = $_POST['slot'];
$session = $_POST['session'];
$exam = $_POST['exam'];
$class = $_POST['class'];
$section = $_POST['section'];
$subject = $_POST['subject'];


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
            <div class="col-m-12">
                <div class="row">
                    <div class="col-auto me-2 text-center">
                        <span id="ctmax" class="m-0 p-0"> <?= $row['ctest'] ?></span> | <span id="ctmax_final"
                            class="m-0 p-0"> 0</span>
                        <br><label class="fs-tiny m-0 p-0">Class Test</label>
                    </div>
                    <div class="col-auto me-2 text-center">
                        <span id="mtmax" class="m-0 p-0"> <?= $row['mtest'] ?></span> | <span id="mtmax_final"
                            class="m-0 p-0"> 0</span>
                        <br><label class="fs-tiny m-0 p-0">Monthly Test</label>
                    </div>
                    <div class="col-auto me-2 text-center">
                        <span id="submax" class="m-0 p-0"> <?= $row['subj'] ?></span> | <span id="submax_final"
                            class="m-0 p-0"> 0</span>
                        <br><label class="fs-tiny m-0 p-0">Subjective</label>
                    </div>
                    <div class="col-auto me-2 text-center">
                        <span id="objmax" class="m-0 p-0"> <?= $row['obj'] ?></span> | <span id="objmax_final"
                            class="m-0 p-0"> 0</span>
                        <br><label class="fs-tiny m-0 p-0">Objective</label>
                    </div>
                    <div class="col-auto me-2 text-center">
                        <span id="pramax" class="m-0 p-0"> <?= $row['pra'] ?></span> | <span id="pramax_final"
                            class="m-0 p-0"> 0</span>
                        <br><label class="fs-tiny m-0 p-0">Practical</label>
                    </div>
                    <div class="col-auto me-2 text-center">
                        <span id="camax" class="m-0 p-0"> <?= $row['ca'] ?></span> | <span id="camax_final" class="m-0 p-0">
                            0</span>
                        <br><label class="fs-tiny m-0 p-0">Learning Assess</label>
                    </div>
                    <div class="col-auto me-2 text-center">
                        <span id="totalmax" class="m-0 p-0"> <?= $row['fullmarks'] ?></span> | <span id="totalmax_final"
                            class="m-0 p-0"> 0</span>
                        <br><label class="fs-tiny m-0 p-0">Full Marks</label>
                    </div>
                    <div class="col-auto me-2 text-center ">
                        <span id="alg" class="m-0 p-0"> <?= $row['pass_algorithm'] ?></span> | <span id="alg_final"
                            class="m-0 p-0"> <?= $row['pass_algorithm'] ?></span>
                        <br><label class="fs-tiny m-0 p-0">Pass Method</label>
                    </div>
                    <div class="col-auto me-2 text-center">
                        <span id="fourth" class="m-0 p-0"> <?= $row['fourth'] ?></span> | <span id="fourth_final"
                            class="m-0 p-0"> <?= $row['fourth'] ?></span>
                        <br><label class="fs-tiny m-0 p-0">Optional</label>
                    </div>
                </div>


            </div>


        </div>



    </div>

    <?php

} else {


}




$dd = "SELECT examtitle FROM examlist 
          WHERE sccode='$sccode' 
          AND sessionyear='$session' 
          AND slot='$slot'";

$qq = mysqli_query($conn, $dd);

if (mysqli_num_rows($qq) > 0) {
    ?>
    <div class="card-body">
        <div class="row p-3">
            <?php
            while ($rec = mysqli_fetch_assoc($qq)) {
                $title = $rec['examtitle'];
                $checked = in_array($title, $cookieList) ? "checked" : "";
                ?>
                <div class="form-check mb-1 col-md-2 col-sm-6">
                    <input class="form-check-input examItem" type="checkbox" name="examitems[]" value="<?php echo $title; ?>"
                        id="ex_<?php echo md5($title); ?>" <?php echo $checked; ?>>

                    <label class="form-check-label" for="ex_<?php echo md5($title); ?>">
                        <?php echo $title; ?>
                    </label>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
}

?>
<script>
    updateFinalMarks();
</script>