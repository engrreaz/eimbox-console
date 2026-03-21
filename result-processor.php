<?php require_once 'header.php';
echo microtime(true) . '<br>';
$sttime = microtime(true); ?>

<script>
    function chainBtnFunc() { window.location.href = 'result-processor.php'; }
    function process() { setCookie("Result-process", "on"); window.location.reload(); }
</script>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    $chain_param = '-c 12 -t Result Processing Engine -u -r -b Ready to Process -h exam';
    include 'components/slot-tree-ui.php';
    ?>

    <?php
    @ini_set('output_buffering', 'off');
    @ini_set('zlib.output_compression', false);
    ob_implicit_flush(true);
    ob_start();

    function flush_now()
    {
        echo str_repeat(' ', 1024);
        ob_flush();
        flush();
    }

    // -------------------- CONTEXT --------------------
    $slot = $_COOKIE['chain-slot'] ?? 'School';
    $sessionyear = $_COOKIE['chain-session'] ?? date('Y');
    $classname = $_COOKIE['chain-class'] ?? '-';
    $sectionname = $_COOKIE['chain-section'] ?? '-';
    $exam = $_COOKIE['chain-exam'] ?? '-';
    $process_on = $_COOKIE['Result-process'] ?? 'off';
    // $process_on = 'on';
    

    // --------------------- FETCH MIN Values && DECImal Style ---------------------------------
    $sql = "SELECT maxvalues FROM gpa 
        WHERE (sccode='$sccode' OR sccode = '0') 
        AND (slot IS NULL OR slot = '$slot')
        AND gp=0
        ORDER BY  sccode DESC, slot DESC LIMIT 1";

    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $min = $row['maxvalues'] ?? null;
    $min = floor($min) + 1;


    $sqly = "SELECT decimal_mark FROM slots   WHERE sccode='$sccode'    AND slotname = '$slot'  LIMIT 1";
    $resy = mysqli_query($conn, $sqly);
    $rowy = mysqli_fetch_assoc($resy);
    $decimal = $rowy['decimal_mark'] ?? 0;
    // -------------------------------------------
    

    $single_stid = isset($_GET['stid']) ? (int) $_GET['stid'] : 0;
    $limit_n = isset($_GET['limit']) ? (int) $_GET['limit'] : 0;

    $stid_list_param = $_GET['stids'] ?? '';
    $stid_list = [];

    if ($stid_list_param !== '') {
        $stid_list = array_filter(array_map('intval', explode(',', $stid_list_param)));
    }

    $where = "slot='$slot' AND sessionyear='$sessionyear' AND sccode='$sccode' AND classname='$classname' AND sectionname='$sectionname'";

    if ($single_stid > 0) {
        $where .= " AND stid='$single_stid'";
    } elseif (!empty($stid_list)) {
        $ids = implode(',', $stid_list);
        $where .= " AND stid IN ($ids)";
    }



    $students = [];
    $rs = $conn->query("SELECT * FROM sessioninfo WHERE $where ORDER BY rollno ASC");
    while ($rs && $r = $rs->fetch_assoc())
        $students[] = $r;
    if ($limit_n > 0)
        $students = array_slice($students, 0, $limit_n);
    $total_students = count($students);

    // -------------------- UI --------------------
    ?>

    <!-- ********************************************************************** -->



    <!-- ********************************************************************** -->


    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6 class="py-0 my-0">Process for - <?= $total_students ?> students</h6>
                    <div class="text-info small py-0">It will take <?= $total_students * 5 ?> seconds to complete.</div>
                </div>
                <div class="col-md-2"><button class="btn btn-outline-dark w-100 px-0" id="st-list-modal">
                        Students List</button></div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" onclick="process();">Process Result</button>
                </div>

            </div>

        </div>
    </div>
    <?php
    if ($process_on != 'on') {
        echo "<div class='alert alert-info'><b>Processor Idle.</b> Choose students from student list or click process result</div>";

        ?>
        <div class="modal fade" id="studentModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable  modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Select Students</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form id="studentSelectForm">
                            <div class="row">
                                <?php
                                $stu_rs = $conn->query("
                            SELECT s.stid, st.stnameeng, s.rollno
                            FROM sessioninfo s
                            LEFT JOIN students st ON st.stid = s.stid
                            WHERE s.slot='$slot'
                            AND s.sessionyear='$sessionyear'
                            AND s.sccode='$sccode'
                            AND s.classname='$classname'
                            AND s.sectionname='$sectionname'
                            ORDER BY s.rollno ASC
                            ");


                                while ($stu_rs && $stu = $stu_rs->fetch_assoc()):
                                    $stid = (int) $stu['stid'];
                                    $name = htmlspecialchars($stu['stnameeng'] ?? 'No Name');
                                    $roll = (int) $stu['rollno'];
                                    ?>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-check">
                                            <input class="form-check-input st-check" type="checkbox" value="<?= $stid ?>">
                                            <span class="form-check-label">
                                                <?= $roll ?>. <?= $name ?>
                                            </span>
                                        </label>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="applyStudentFilter">Process
                            Selected</button>
                    </div>

                </div>
            </div>
        </div>
        <?php


        include_once 'footer.php';


        ?>

        <script>
            document.getElementById('st-list-modal').addEventListener('click', function () {
                let modal = new bootstrap.Modal(document.getElementById('studentModal'));
                modal.show();
            });

            document.getElementById('applyStudentFilter').addEventListener('click', function () {
                let ids = Array.from(document.querySelectorAll('.st-check:checked'))
                    .map(e => e.value);

                if (ids.length === 0) {
                    alert('Select at least one student');
                    return;
                }

                window.location.href = 'result-processor.php?stids=' + ids.join(',');
            });
        </script>
        <?php return;
    } ?>

    <div class="modal fade" id="studentModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Select Students</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="studentSelectForm">
                        <div class="row">
                            <?php
                            $stu_rs = $conn->query("
                            SELECT s.stid, st.stnameeng, s.rollno
                            FROM sessioninfo s
                            LEFT JOIN students st ON st.stid = s.stid
                            WHERE s.slot='$slot'
                            AND s.sessionyear='$sessionyear'
                            AND s.sccode='$sccode'
                            AND s.classname='$classname'
                            AND s.sectionname='$sectionname'
                            ORDER BY s.rollno ASC
                            ");


                            while ($stu_rs && $stu = $stu_rs->fetch_assoc()):
                                $stid = (int) $stu['stid'];
                                $name = htmlspecialchars($stu['stnameeng'] ?? 'No Name');
                                $roll = (int) $stu['rollno'];
                                ?>
                                <div class="col-md-4 mb-2">
                                    <label class="form-check">
                                        <input class="form-check-input st-check" type="checkbox" value="<?= $stid ?>">
                                        <span class="form-check-label">
                                            <?= $roll ?>. <?= $name ?>
                                        </span>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="applyStudentFilter">Process Selected</button>
                </div>

            </div>
        </div>
    </div>


    <div class="card mb-3">
        <div class="card-body">
            <h6>Result Processing Progress</h6>
            <div class="progress mb-2" style="height:20px;">
                <div id="p_overall" class="progress-bar" style="width:0%; height:20px;">0%</div>
            </div>
            <div class="progress mb-2" style="height:20px;">
                <div id="p_student" class="progress-bar bg-success" style="width:0%; height:20px;">0%</div>
            </div>
            <pre id="log"
                style="height:100px;overflow:auto;background:#0b1220;color:#d1e7ff;padding:12px; border-radius:8px;"></pre>
        </div>
    </div>

    <script>
        function setBar(id, p) { p = Math.floor(p); let e = document.getElementById(id); e.style.width = p + '%'; e.innerText = p + '%'; }
        function log(t) { let e = document.getElementById('log'); e.textContent += t + "\n"; e.scrollTop = e.scrollHeight; }
    </script>
    <?php flush_now(); ?>

    <?php
    // -------------------- HELPERS --------------------
    function arr_pluck($rows, $key)
    {
        $o = [];
        foreach ($rows as $r)
            $o[] = $r[$key];
        return $o;
    }
    function grade_from_gpa_table($conn, $on100, $slot, $sccode)
    {
        $on100 = (float) $on100;
        $q = $conn->query("SELECT gp,gl,remark,colorcode FROM gpa
        WHERE slot='$slot' AND (sccode='$sccode' OR sccode='0')
        AND minvalues<=$on100 AND maxvalues>=$on100
        ORDER BY sccode DESC LIMIT 1");
        return ($q && $q->num_rows) ? $q->fetch_assoc() : ['gp' => 0, 'gl' => 'F', 'remark' => 'Fail', 'colorcode' => 'ff0000'];
    }

    function grade_from_gpa($conn,  $gpa)
    {
        global $sccode, $slot;
        $gpa = (float) $gpa;
        $q = $conn->query("SELECT gl FROM gpa
        WHERE (sccode='$sccode' OR sccode = '0') 
        AND (slot IS NULL OR slot = '$slot')
        AND gp<=$gpa
        ORDER BY  sccode DESC, slot DESC LIMIT 1
        ");
        return ($q && $q->num_rows) ? $q->fetch_assoc() : ['gl' => 'F'];
    }



    // -------------------- LOAD SUBJECT SETUP --------------------
    $subsetup = [];
    $subsetupmap = [];
    $def_fourth = 0;
    $rs = $conn->query("SELECT * FROM subsetup WHERE slot='$slot' AND sessionyear='$sessionyear'
        AND sccode='$sccode' AND classname='$classname' AND sectionname='$sectionname'
        ORDER BY slno ASC,subject ASC");
    while ($rs && $r = $rs->fetch_assoc()) {
        $subsetup[] = $r;
        $subsetupmap[$r['subject']] = $r;
        if ($r['fourth'] == 1) {
            $def_fourth = $r['subject'];
        }
    }


    // var_dump($subsetup);
    
    $subject_codes = arr_pluck($subsetup, 'subject');


    $slotMap = [];
    foreach ($subject_codes as $i => $scode)
        $slotMap[(int) $scode] = $i + 1;

    // combined map
    $combine_map = [];
    foreach ($subsetup as $ss) {
        $main = (int) $ss['subject'];
        $grp = array_values(array_filter([$main, (int) $ss['combind_1'], (int) $ss['combind_2'], (int) $ss['combind_3'], (int) $ss['combind_4']]));
        if (count($grp) > 1)
            $combine_map[$main] = $grp;
    }



    // -------------------- LOAD STUDENTS --------------------
    
    ?>



    <?php
    // -------------------- AUTO INSERT TAB SHEET --------------------
    echo microtime(true) . '<br>';
    foreach ($students as $st) {
        $stid = $st['stid'];
        $roll = $st['rollno'] ?? 0;
        $gender = $st['gender'] ?? '';

        $chk = $conn->query("SELECT id FROM tabulatingsheet WHERE stid='$stid' AND exam='$exam' and sessionyear='$sessionyear' and classname='$classname' and sectionname='$sectionname' AND slot='$slot' AND sccode='$sccode'");
        if (!$chk->num_rows) {
            $conn->query("INSERT INTO tabulatingsheet
        (sessionyear,sccode,slot,exam,classname,sectionname,stid,rollno,gender)
        VALUES('$sessionyear','$sccode','$slot','$exam','$classname','$sectionname','$stid','$roll', '$gender')");
        }
    }
    echo microtime(true) . '<br>';
    // ------------------------ FETCH LAST UPDATE TIME ------------------------------]..
    $q = $conn->query("SELECT MAX(last_update) AS last_update FROM tabulatingsheet WHERE sccode='$sccode' AND slot='$slot' AND exam='$exam' AND sessionyear='$sessionyear' AND classname='$classname' AND sectionname='$sectionname'");
    $last_update = null;
    if ($q && $q->num_rows > 0) {
        $row = $q->fetch_assoc();
        $last_update = $row['last_update'];
    }
    echo $last_update;

    // -------------------- FILL SUBJECT CODE COLUMNS --------------------
    $setParts = [];
    for ($i = 0; $i < 15; $i++) {
        $code = $subject_codes[$i] ?? 'NULL';
        $setParts[] = "sub_" . ($i + 1) . "=" . (($code) ? (int) $code : 'NULL');
    }
    $conn->query("UPDATE tabulatingsheet SET " . implode(',', $setParts) . " WHERE exam='$exam' AND sessionyear='$sessionyear' AND classname='$classname' AND sectionname='$sectionname' AND sccode='$sccode' AND slot='$slot'");


    // echo '<pre>';
    // echo print_r($combine_map);
    // echo '</pre>';
    
    // -------------------- PROCESS STUDENTS --------------------
    echo microtime(true) . '<br>';
    $si = 0;
    foreach ($students as $st) {
        $all_subject_string = '';
        $st_full_marks = 0;
        $comb_index = 0;
        $si++;
        $stid = $st['stid'];
        echo "<script>log('Processing STID: $stid');</script>";
        flush_now();

        $sublist_arr = $st['subject_list'] ? array_map('intval', explode('.', $st['subject_list'])) : $subject_codes;
        $fourth_sub = (int) ($st['fourth_subject'] ?? 0);
        if ($fourth_sub == 0) {
            $fourth_sub = $def_fourth;
        }
        // echo
    
        // load marks
        $marks = [];
        $mr = $conn->query("SELECT * FROM stmark WHERE stid='$stid' AND exam='$exam' AND sccode='$sccode' AND slot='$slot' AND sessionyear='$sessionyear' AND classname='$classname' AND sectionname='$sectionname'  ");
        while ($mr && $m = $mr->fetch_assoc())
            $marks[(int) $m['subject']] = $m;

        $updates = [];
        $total_marks = 0;
        $total_gp = 0;
        $subject_taken = 0;
        $fail_count = 0;
        $fail_list = [];
        $processed_combined = [];

        $bar = 1;
        foreach ($sublist_arr as $code) {

            // echo $code . ' => ' . $subsetupmap[$code]['fullmarks'] . ' | ';
            $st_full_marks += (int) $subsetupmap[$code]['fullmarks'];
            $sub_fm = (int) $subsetupmap[$code]['subj'];
            $obj_fm = (int) $subsetupmap[$code]['obj'];
            $pra_fm = (int) $subsetupmap[$code]['pra'];
            $pass_algorithm = (int) $subsetupmap[$code]['pass_algorithm'];


            // if (!$code || !isset($marks[$code]))
            //     continue;
    
            // ***********************************
            if (!$code)
                continue;

            $slotno = $slotMap[$code] ?? 0;
            $has_mark = isset($marks[$code]);

            /* ==============================
               ❌ MARK NOT FOUND → AUTO FAIL
            ============================== */
            if (!$has_mark) {
                $all_subject_string .= $code . '.';

                if ($slotno) {
                    $updates[] = "
                    sub_{$slotno}_sub='0',
                    sub_{$slotno}_obj='0',
                    sub_{$slotno}_pra='0',
                    sub_{$slotno}_ca='0',
                    sub_{$slotno}_total='0',
                    sub_{$slotno}_ct='0',
                    sub_{$slotno}_mt='0',
                    sub_{$slotno}_100='0',
                    sub_{$slotno}_gp='0',
                    sub_{$slotno}_gl='F'
                ";
                }

                if ($code != $fourth_sub) {
                    $fail_count++;
                    $fail_list[] = $code;
                }

                // total এ যুক্ত হবে না, gp হবে না
                continue;
            }
            // ***********************************
    



            // ---------- COMBINED ----------
            // ---------- COMBINED ----------
            if (isset($combine_map[$code]) && !in_array($code, $processed_combined)) {
                $grp = $combine_map[$code];
                $processed_combined = array_merge($processed_combined, $grp);

                $sub = $obj = $pra = $ca = $fm = $ct = $mt = $obt = $on100 = 0;



                foreach ($grp as $g) {
                    $all_subject_string .= $g . '.';

                    if (isset($marks[$g])) {
                        $m = $marks[$g];
                        $sub += (int) $m['sub_final'];
                        $obj += (int) $m['obj_final'];
                        $pra += (int) $m['pra_final'];
                        $ca += (int) $m['ca'];
                        $fm += (int) $m['fullmark'];
                        $ct += (int) $m['ctest'];
                        $mt += (int) $m['mtest'];
                        $obt += (int) $m['markobt'];

                    }
                }


                $on100 = ($obt > 0 && $fm > 0) ? ($obt * 100 / $fm) : 0;

                $pass = pass_validation($ct, $mt, $sub, $obj, $pra, $ca, $sub_fm, $obj_fm, $pra_fm, $fm, $pass_algorithm, $min, $decimal);
                $grade = get_GP_GL($obt, $fm, $slot);
                $gp = $pass ? $grade['gp'] : 0;
                $gl = $pass ? $grade['gl'] : 'F';

                if (!$pass && $code != $fourth_sub) {
                    $fail_count++;
                    $fail_list[] = $code + 1000;
                }

                // ✅ ONLY combined কলামে যাবে
                $comb_index++;
                $comb_code = $code + 1000;
                $all_subject_string .= $comb_code . '.';

                $updates[] = "
                    comb_{$comb_index}_code='$comb_code',
                    comb_{$comb_index}_sub='$sub',
                    comb_{$comb_index}_obj='$obj',
                    comb_{$comb_index}_pra='$pra',
                    comb_{$comb_index}_ca='$ca',
                    comb_{$comb_index}_ct='$ct',
                    comb_{$comb_index}_mt='$mt',
                    comb_{$comb_index}_total='$obt',
                    comb_{$comb_index}_100='$on100',
                    comb_{$comb_index}_gp='$gp',
                    comb_{$comb_index}_gl='$gl'
                ";

                $total_marks += $obt;
                $total_gp += $gp;
                $subject_taken++;

            }



            // ---------- NORMAL ----------
    
            $is_main_combined = isset($combine_map[$code]);


            $m = $marks[$code];
            $all_subject_string .= $code . '.';

            $obt = (int) $m['markobt'];
            $gp = (float) $m['gp'];
            $on100 = (float) $m['on100'];
            $ct = (int) $m['ct'];
            $mt = (int) $m['mt'];
            $gp = (int) $m['gp'];
            $gl = $m['gl'];
            if ($gp == 0 && $code != $fourth_sub && !$is_main_combined) {
                $fail_count++;
                $fail_list[] = $code;

            }

            $slotno = $slotMap[$code] ?? 0;
            if ($slotno) {
                $updates[] = "
            sub_{$slotno}_sub='{$m['sub_final']}',
            sub_{$slotno}_obj='{$m['obj_final']}',
            sub_{$slotno}_pra='{$m['pra_final']}',
            sub_{$slotno}_ca='{$m['ca']}',
            sub_{$slotno}_total='$obt',
            sub_{$slotno}_ct='$ct',
            sub_{$slotno}_mt='$mt',
            sub_{$slotno}_100='$on100',
            sub_{$slotno}_gp='$gp',
            sub_{$slotno}_gl='$gl'
            ";
            }



            if (!$is_main_combined) {
                $total_marks += $obt;
                $total_gp += $gp;
                $subject_taken++;
            }

            echo "<script>setBar('p_student',$bar*5);</script>";
            flush_now();
            $bar++;

        }

        // ---------- TOTAL ----------
        $gpa = $subject_taken ? round($total_gp / $subject_taken, 2) : 0;
        $glrow = '-'; // grade_from_gpa_table($conn, $gpa * 20, $slot, $sccode);

        $gl = $fail_count ? 'F' : $glrow['gl'];
        if ($fail_count)
            $gpa = 0;
        $failsub = implode('.', $fail_list) . '.';
        $sublist_string = implode('.', $sublist_arr);
        if ($total_marks > 0) {
            $avgrate = $total_marks * 100 / $st_full_marks;
        } else {
            $avgrate = 0;
        }
        if ($total_gp > 0) {
            $gpa = $total_gp / $subject_taken;
        } else {
            $gpa = 0;
        }

        $gla_r = grade_from_gpa($conn, $gpa);
        $gla = $gla_r['gl'];

        $sub_arr = array_values(array_unique(
            array_filter(
                explode('.', $all_subject_string),
                fn($v) => $v !== '' && (int) $v !== (int) $fourth_sub
            )
        ));
        $all_subject_string = implode('.', $sub_arr) . '.1000.' . $fourth_sub;

        $updates[] = "
        totalmarks='$total_marks',
        full_marks = '$st_full_marks',
        totalgp='$total_gp',
        totalsubject='$subject_taken',
        gpa='$gpa',
        gla='$gla',
        totalfail='$fail_count',
        failsub='$failsub',
        sublist='$sublist_string',
        allsubject='$all_subject_string',
        avgrate = '$avgrate',
        allfourth = '$fourth_sub',
        last_update=NOW()
    ";

        $full_sql = "UPDATE tabulatingsheet SET " . implode(',', $updates) . " WHERE stid='$stid' AND exam='$exam' AND sessionyear='$sessionyear' AND classname='$classname' AND sccode='$sccode' AND slot='$slot' AND sectionname='$sectionname'";
        $conn->query($full_sql);

        echo "<script>setBar('p_student',100);</script>";
        echo "<script>setBar('p_overall'," . ($si / $total_students * 100) . ");</script>";
        echo "<script>log('Done STID: $stid');</script>";
        flush_now();

    }
    echo microtime(true) . '<br>';
    // -------------------- MERIT --------------------
    $conn->query("SET @r=0");

    $conn->query("
        UPDATE tabulatingsheet t
        JOIN(
            SELECT stid,(@r:=@r+1) rn
            FROM tabulatingsheet
            WHERE exam='$exam' and slot='$slot' and sessionyear='$ssessionyear' and classname='$classname' and sectionname='$sectionname' AND sccode='$sccode'
            ORDER BY totalmarks DESC, gpa DESC, rollno ASC
        ) x USING(stid)

        LEFT JOIN meritlist m ON m.numplace = x.rn

        SET 
            t.meritnum   = x.rn,
            t.meritplace = IFNULL(m.meritplace, CONCAT(x.rn,'th'))

        WHERE t.exam='$exam'  and slot='$slot' and sessionyear='$ssessionyear' and classname='$classname' and sectionname='$sectionname'
        ");


    // ************************************
    
    $show_result = [];
    $view_result = $conn->query("SELECT * FROM tabulatingsheet LIMIT 1");
    if (!$view_result) {
        echo "Query Failed: " . $conn->error;
    } elseif ($view_result->num_rows == 0) {
        echo "No result found.";
    } else {
        $show_result = $view_result->fetch_assoc();
        echo "<pre>";
        print_r($show_result);
        echo "</pre>";
    }


    // ************************************
    

    echo "<script>setBar('p_overall',100);log('Processing Complete');</script>";
    flush_now();
    echo microtime(true) . '<br>';
    echo '<br><br>Time: ' . (microtime(true) - $sttime) . ' seconds';
    ?>

</div>



<?php require 'footer.php'; ?>

<script>setCookie("Result-process", "off");</script>

<script>
    document.getElementById('st-list-modal').addEventListener('click', function () {
        let modal = new bootstrap.Modal(document.getElementById('studentModal'));
        modal.show();
    });

    document.getElementById('applyStudentFilter').addEventListener('click', function () {
        let ids = Array.from(document.querySelectorAll('.st-check:checked'))
            .map(e => e.value);

        if (ids.length === 0) {
            alert('Select at least one student');
            return;
        }

        let url = new URL(window.location.href);
        url.searchParams.set('stids', ids.join(','));
        window.location.href = url.toString();
    });
</script>
</body>

</html>