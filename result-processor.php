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

    function get_GP_GL_val($obt, $fm, $slot = 'School', $decimal = 0)
    {
        global $conn, $sccode;
        if ($decimal == 0) {
            $on100 = ($fm > 0) ? ceil(($obt) * 100 / $fm) : 0;
        } else if ($decimal == 2) {
            $on100 = ($fm > 0) ? round(($obt) * 100 / $fm) : 0;
        } else {
            $on100 = ($fm > 0) ? (floatval($obt) * 100 / $fm) : 0;
        }

        $q = "SELECT gp, gl, remark, colorcode FROM gpa 
              WHERE minvalues <= $on100 AND maxvalues >= $on100 
              AND (sccode = '$sccode' OR sccode = '0' OR sccode = 0)
              AND (slot = '$slot' OR slot = '' OR slot IS NULL)
              ORDER BY (sccode = '$sccode') DESC, gp DESC LIMIT 1";
        $res = $conn->query($q);
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            return [
                'gp' => (float)$row['gp'],
                'gl' => $row['gl'],
                'remark' => $row['remark'] ?? '',
                'color' => $row['colorcode'] ?? ''
            ];
        }

        // Standard NCTB grading scale fallback
        if ($on100 >= 80) return ['gp' => 5.00, 'gl' => 'A+'];
        if ($on100 >= 70) return ['gp' => 4.00, 'gl' => 'A'];
        if ($on100 >= 60) return ['gp' => 3.50, 'gl' => 'A-'];
        if ($on100 >= 50) return ['gp' => 3.00, 'gl' => 'B'];
        if ($on100 >= 40) return ['gp' => 2.00, 'gl' => 'C'];
        if ($on100 >= 33) return ['gp' => 1.00, 'gl' => 'D'];
        return ['gp' => 0.00, 'gl' => 'F'];
    }

    function get_grade_letter_for_gpa_score($gpa)
    {
        $g = (float)$gpa;
        if ($g >= 5.0) return 'A+';
        if ($g >= 4.0) return 'A';
        if ($g >= 3.5) return 'A-';
        if ($g >= 3.0) return 'B';
        if ($g >= 2.0) return 'C';
        if ($g >= 1.0) return 'D';
        return 'F';
    }

    // -------------------- LOAD SUBJECT SETUP --------------------
    $subsetup = [];
    $subsetupmap = [];
    $def_fourth = 0;
    $rs = $conn->query("SELECT * FROM subsetup WHERE slot='$slot' AND sessionyear='$sessionyear'
        AND sccode='$sccode' AND classname='$classname' AND sectionname='$sectionname'
        ORDER BY slno ASC, subject ASC");
    while ($rs && $r = $rs->fetch_assoc()) {
        $subsetup[] = $r;
        $subsetupmap[(int)$r['subject']] = $r;
        if ($r['fourth'] == 1 || $r['optional'] == 1) {
            $def_fourth = (int)$r['subject'];
        }
    }

    $subject_codes = array_map('intval', arr_pluck($subsetup, 'subject'));

    $slotMap = [];
    foreach ($subject_codes as $i => $scode) {
        $slotMap[(int)$scode] = $i + 1;
    }

    // Identify combined definitions from subsetup
    $combined_defs = [];
    $combined_member_codes = [];
    foreach ($subsetup as $ss) {
        $main_code = (int)$ss['subject'];
        $children = array_values(array_filter([
            (int)($ss['combind_1'] ?? 0),
            (int)($ss['combind_2'] ?? 0),
            (int)($ss['combind_3'] ?? 0),
            (int)($ss['combind_4'] ?? 0)
        ], fn($c) => $c > 0 && $c != $main_code));

        if (!empty($children)) {
            $all_papers = array_merge([$main_code], $children);
            $comb_code = $main_code < 1000 ? ($main_code + 1000) : $main_code;

            $exists = false;
            foreach ($combined_defs as $cd) {
                if ($cd['comb_code'] == $comb_code) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $combined_defs[] = [
                    'head_code' => $main_code,
                    'comb_code' => $comb_code,
                    'paper_codes' => $all_papers
                ];
                foreach ($all_papers as $p) {
                    $combined_member_codes[$p] = true;
                }
            }
        }
    }

    // Fallback default combinations if not defined in subsetup
    if (empty($combined_defs)) {
        if (in_array(101, $subject_codes) && in_array(102, $subject_codes)) {
            $combined_defs[] = ['head_code' => 101, 'comb_code' => 1101, 'paper_codes' => [101, 102]];
            $combined_member_codes[101] = true;
            $combined_member_codes[102] = true;
        }
        if (in_array(107, $subject_codes) && in_array(108, $subject_codes)) {
            $combined_defs[] = ['head_code' => 107, 'comb_code' => 1107, 'paper_codes' => [107, 108]];
            $combined_member_codes[107] = true;
            $combined_member_codes[108] = true;
        }
    }

    // -------------------- AUTO INSERT TAB SHEET --------------------
    echo microtime(true) . '<br>';
    foreach ($students as $st) {
        $stid = (int)$st['stid'];
        $roll = (int)($st['rollno'] ?? 0);
        $gender = $st['gender'] ?? '';

        $chk = $conn->query("SELECT id FROM tabulatingsheet WHERE stid='$stid' AND exam='$exam' AND sessionyear='$sessionyear' AND classname='$classname' AND sectionname='$sectionname' AND slot='$slot' AND sccode='$sccode'");
        if (!$chk || !$chk->num_rows) {
            $conn->query("INSERT INTO tabulatingsheet
                (sessionyear, sccode, slot, exam, classname, sectionname, stid, rollno, gender)
                VALUES('$sessionyear', '$sccode', '$slot', '$exam', '$classname', '$sectionname', '$stid', '$roll', '$gender')");
        }
    }

    // -------------------- FILL SUBJECT CODE COLUMNS --------------------
    $setParts = [];
    for ($i = 0; $i < 15; $i++) {
        $code = $subject_codes[$i] ?? null;
        $setParts[] = "sub_" . ($i + 1) . "=" . ($code ? (int)$code : "NULL");
    }
    $conn->query("UPDATE tabulatingsheet SET " . implode(',', $setParts) . " WHERE exam='$exam' AND sessionyear='$sessionyear' AND classname='$classname' AND sectionname='$sectionname' AND sccode='$sccode' AND slot='$slot'");

    // -------------------- PROCESS STUDENTS --------------------
    echo microtime(true) . '<br>';
    $si = 0;
    foreach ($students as $st) {
        $si++;
        $stid = (int)$st['stid'];
        $roll = (int)($st['rollno'] ?? 0);
        $gender = $st['gender'] ?? '';

        echo "<script>log('Processing STID: $stid');</script>";
        flush_now();

        $fourth_sub = (int)($st['fourth_subject'] ?? 0);
        if ($fourth_sub == 0) {
            $fourth_sub = (int)$def_fourth;
        }

        $raw_sub_list = trim($st['subject_list'] ?? '');
        if (!empty($raw_sub_list) && $raw_sub_list !== '0') {
            $sublist_arr = array_values(array_filter(array_map('intval', explode('.', $raw_sub_list)), fn($c) => $c > 0 && $c != 1000));
            if ($fourth_sub > 0 && !in_array($fourth_sub, $sublist_arr)) {
                $sublist_arr[] = $fourth_sub;
            }
        } else {
            $sublist_arr = $subject_codes;
        }

        // Load student marks
        $marks = [];
        $mr = $conn->query("SELECT * FROM stmark WHERE stid='$stid' AND exam='$exam' AND sccode='$sccode' AND slot='$slot' AND sessionyear='$sessionyear' AND classname='$classname' AND sectionname='$sectionname'");
        while ($mr && $m = $mr->fetch_assoc()) {
            $marks[(int)$m['subject']] = $m;
        }

        // Initialize individual slot columns (sub_1..15)
        $sub_cols = [];
        for ($i = 1; $i <= 15; $i++) {
            $sub_cols["sub_$i"] = 'NULL';
            $sub_cols["sub_{$i}_sub"] = 'NULL';
            $sub_cols["sub_{$i}_obj"] = 'NULL';
            $sub_cols["sub_{$i}_pra"] = 'NULL';
            $sub_cols["sub_{$i}_ca"] = 'NULL';
            $sub_cols["sub_{$i}_total"] = 'NULL';
            $sub_cols["sub_{$i}_ct"] = '0';
            $sub_cols["sub_{$i}_mt"] = '0';
            $sub_cols["sub_{$i}_100"] = '0';
            $sub_cols["sub_{$i}_gp"] = 'NULL';
            $sub_cols["sub_{$i}_gl"] = 'NULL';
        }

        // Initialize tabulatingsheetex columns
        $ex_cols = [
            'fourth_subj' => 'NULL', 'fourth_obj' => 'NULL', 'fourth_pra' => 'NULL', 'fourth_ca' => 'NULL',
            'fourth_total' => 'NULL', 'fourth_gp' => 0, 'fourth_gl' => "'F'", 'add_gp' => 0.00
        ];
        for ($k = 1; $k <= 10; $k++) {
            $ex_cols["sub_$k"] = 'NULL';
            $ex_cols["sub_code_$k"] = '0';
            $ex_cols["sub_name_$k"] = 'NULL';
            $ex_cols["sub_fm_$k"] = '0';
            $ex_cols["sub_{$k}_sub"] = 'NULL';
            $ex_cols["sub_{$k}_obj"] = 'NULL';
            $ex_cols["sub_{$k}_pra"] = 'NULL';
            $ex_cols["sub_{$k}_ca"] = 'NULL';
            $ex_cols["sub_{$k}_total"] = 'NULL';
            $ex_cols["sub_{$k}_gp"] = 'NULL';
            $ex_cols["sub_{$k}_gl"] = 'NULL';
            $ex_cols["sub_{$k}_ct"] = 'NULL';
            $ex_cols["sub_{$k}_mt"] = 'NULL';
        }

        $raw_entries_array = [];
        $total_marks_obtained = 0;
        $total_full_marks = 0;
        $total_gp_sum = 0;
        $mandatory_subject_count = 0;
        $failed_sub_codes = [];

        // 1. Process Raw Subjects for tabulatingsheet (sub_1..15)
        foreach ($sublist_arr as $idx => $code) {
            $sub_index = $slotMap[$code] ?? ($idx + 1);
            $is_fourth = ($fourth_sub > 0 && $code == $fourth_sub);
            $meta = $subsetupmap[$code] ?? [
                'fullmarks' => 100, 'pass_algorithm' => 1, 'subj' => 70, 'obj' => 30, 'pra' => 0, 'ca' => 0
            ];
            $fullmark = (int)($meta['fullmarks'] ?? 100);
            $subj_fm = (int)($meta['subj'] ?? 0);
            $obj_fm = (int)($meta['obj'] ?? 0);
            $pra_fm = (int)($meta['pra'] ?? 0);
            $pass_algorithm = (int)($meta['pass_algorithm'] ?? 1);

            $has_mark = isset($marks[$code]);
            $subj = 0; $obj = 0; $pra = 0; $ca = 0.00; $ctest = 0; $mtest = 0; $total = 0; $on100 = 0; $gp = 0.00; $gl = 'F';

            if ($has_mark) {
                $m = $marks[$code];
                $subj = (float)($m['sub_final'] ?? $m['subj'] ?? 0);
                $obj = (float)($m['obj_final'] ?? $m['obj'] ?? 0);
                $pra = (float)($m['pra_final'] ?? $m['pra'] ?? 0);
                $ca = (float)($m['ca'] ?? 0);
                $ctest = (int)($m['ctest'] ?? $m['ct'] ?? 0);
                $mtest = (int)($m['mtest'] ?? $m['mt'] ?? 0);
                $total = isset($m['markobt']) ? (float)$m['markobt'] : ($subj + $obj + $pra + $ca);
                $on100 = $fullmark > 0 ? ($total * 100 / $fullmark) : $total;

                // Take GP and GL directly from stmark table
                $gp = isset($m['gp']) ? (float)$m['gp'] : 0.00;
                $gl = (isset($m['gl']) && $m['gl'] !== '' && $m['gl'] !== '0') ? $m['gl'] : ($gp > 0 ? get_grade_letter_for_gpa_score($gp) : 'F');

                $raw_entries_array[] = [(string)$code, (string)(int)$subj, (string)(int)$obj, (string)(int)$pra];
            }

            $total_marks_obtained += $total;
            $total_full_marks += $fullmark;

            if ($sub_index <= 15) {
                $sub_cols["sub_$sub_index"] = $code;
                $sub_cols["sub_{$sub_index}_sub"] = $subj;
                $sub_cols["sub_{$sub_index}_obj"] = $obj;
                $sub_cols["sub_{$sub_index}_pra"] = $pra;
                $sub_cols["sub_{$sub_index}_ca"] = sprintf("%.2f", $ca);
                $sub_cols["sub_{$sub_index}_total"] = sprintf("%.2f", $total);
                $sub_cols["sub_{$sub_index}_ct"] = $ctest;
                $sub_cols["sub_{$sub_index}_mt"] = $mtest;
                $sub_cols["sub_{$sub_index}_100"] = round($on100);
                $sub_cols["sub_{$sub_index}_gp"] = sprintf("%.2f", $gp);
                $sub_cols["sub_{$sub_index}_gl"] = "'$gl'";
            }

            if ($is_fourth) {
                $ex_cols['fourth_subj'] = (int)$subj;
                $ex_cols['fourth_obj'] = (int)$obj;
                $ex_cols['fourth_pra'] = (int)$pra;
                $ex_cols['fourth_ca'] = sprintf("%.2f", $ca);
                $ex_cols['fourth_total'] = sprintf("%.2f", $total);
                $ex_cols['fourth_gp'] = $gp;
                $ex_cols['fourth_gl'] = "'$gl'";
                $ex_cols['add_gp'] = max(0.0, round($gp - 2.0, 2));
            } else if (!isset($combined_member_codes[$code])) {
                // Standalone non-combined mandatory subject
                $mandatory_subject_count++;
                $total_gp_sum += $gp;
                if ($gl === 'F') {
                    $failed_sub_codes[] = $code;
                }
            }
        }

        // 2. Evaluate Combined Subjects for tabulatingsheetex
        $comb_idx = 1;
        foreach ($combined_defs as $cdef) {
            $comb_code = $cdef['comb_code'];
            $paper_codes = $cdef['paper_codes'];

            $sub_sum = 0; $obj_sum = 0; $pra_sum = 0; $ca_sum = 0; $total_sum = 0; $fmm = 0;
            $sst = 0; $oot = 0; $ppt = 0;
            $pass_algo = 1;
            $found_any = false;

            $paper_str_parts = [];
            foreach ($paper_codes as $pc) {
                $paper_str_parts[] = $pc;
                $pmeta = $subsetupmap[$pc] ?? ['fullmarks' => 100, 'pass_algorithm' => 1, 'subj' => 70, 'obj' => 30, 'pra' => 0];
                $fmm += (int)($pmeta['fullmarks'] ?? 100);
                $sst += (int)($pmeta['subj'] ?? 0);
                $oot += (int)($pmeta['obj'] ?? 0);
                $ppt += (int)($pmeta['pra'] ?? 0);
                if (isset($pmeta['pass_algorithm']) && $pmeta['pass_algorithm'] == 0) $pass_algo = 0;

                if (isset($marks[$pc])) {
                    $found_any = true;
                    $pm = $marks[$pc];
                    $psubj = (float)($pm['sub_final'] ?? $pm['subj'] ?? 0);
                    $pobj = (float)($pm['obj_final'] ?? $pm['obj'] ?? 0);
                    $ppra = (float)($pm['pra_final'] ?? $pm['pra'] ?? 0);
                    $pca = (float)($pm['ca'] ?? 0);
                    $ptot = isset($pm['markobt']) ? (float)$pm['markobt'] : ($psubj + $pobj + $ppra + $pca);

                    $sub_sum += $psubj;
                    $obj_sum += $pobj;
                    $pra_sum += $ppra;
                    $ca_sum += $pca;
                    $total_sum += $ptot;
                }
            }

            while (count($paper_str_parts) < 5) {
                $paper_str_parts[] = '0';
            }
            $paper_str = implode('-', $paper_str_parts);

            $comb_pass = true;
            if ($pass_algo == 1) {
                if ($sst > 0 && ceil(($sub_sum * 100) / $sst) < 33) $comb_pass = false;
                if ($oot > 0 && ceil(($obj_sum * 100) / $oot) < 33) $comb_pass = false;
                if ($ppt > 0 && ceil(($pra_sum * 100) / $ppt) < 33) $comb_pass = false;
            } else {
                $on100 = $fmm > 0 ? ($total_sum * 100 / $fmm) : 0;
                if ($on100 < 33) $comb_pass = false;
            }

            if ($found_any && $comb_pass) {
                $grade_info = get_GP_GL_val($total_sum, $fmm, $slot, $decimal);
                $cgp = (float)$grade_info['gp'];
                $cgl = $grade_info['gl'];
            } else {
                $cgp = 0.00;
                $cgl = 'F';
            }

            if ($comb_idx <= 10) {
                $ex_cols["sub_$comb_idx"] = "'$paper_str'";
                $ex_cols["sub_code_$comb_idx"] = $comb_code;
                $ex_cols["sub_fm_$comb_idx"] = $fmm;
                $ex_cols["sub_{$comb_idx}_sub"] = (int)$sub_sum;
                $ex_cols["sub_{$comb_idx}_obj"] = (int)$obj_sum;
                $ex_cols["sub_{$comb_idx}_pra"] = (int)$pra_sum;
                $ex_cols["sub_{$comb_idx}_ca"] = sprintf("%.2f", $ca_sum);
                $ex_cols["sub_{$comb_idx}_total"] = sprintf("%.2f", $total_sum);
                $ex_cols["sub_{$comb_idx}_gp"] = sprintf("%.2f", $cgp);
                $ex_cols["sub_{$comb_idx}_gl"] = "'$cgl'";
                $comb_idx++;
            }

            if ($found_any) {
                $mandatory_subject_count++;
                $total_gp_sum += $cgp;
                if ($cgl === 'F') {
                    $failed_sub_codes[] = $comb_code;
                }
            }
        }

        // 3. Final Summary & Metrics
        $total_fail_count = count($failed_sub_codes);
        $is_failed = ($total_fail_count > 0);
        $add_gp = (float)($ex_cols['add_gp'] ?? 0);
        $gpa_add = $total_gp_sum + $add_gp;

        if (!$is_failed && $mandatory_subject_count > 0) {
            $raw_gpa = $gpa_add / $mandatory_subject_count;
            $final_gpa = min(5.00, round($raw_gpa, 2));
            $final_gla = get_grade_letter_for_gpa_score($final_gpa);
        } else {
            $final_gpa = 0.00;
            $final_gla = 'F';
        }

        $avg_rate = $total_full_marks > 0 ? round(($total_marks_obtained * 100) / $total_full_marks, 2) : 0.00;
        $fourth_gp_val = (float)($ex_cols['fourth_gp'] ?? 0);
        $total_gp_overall = $total_gp_sum + $fourth_gp_val;

        // Build consolidated sublist string e.g. "1101.1107.109.111.136.137.138.150.154.126."
        $sublist_parts = [];
        foreach ($sublist_arr as $sc) {
            if ($sc == 101 || $sc == 102) {
                if (!in_array(1101, $sublist_parts)) $sublist_parts[] = 1101;
            } else if ($sc == 107 || $sc == 108) {
                if (!in_array(1107, $sublist_parts)) $sublist_parts[] = 1107;
            } else {
                $sublist_parts[] = $sc;
            }
        }
        $sublist_str = implode('.', $sublist_parts) . '.';

        // Build allsubject sequence e.g. "101.102.1101.107.108.1107.109.111.136.137.138.150.154.1000.126"
        if (!empty($raw_sub_list) && $raw_sub_list !== '0') {
            $all_subject_seq = $raw_sub_list;
            if (str_contains($all_subject_seq, '101.102.') && !str_contains($all_subject_seq, '101.102.1101.')) {
                $all_subject_seq = str_replace('101.102.', '101.102.1101.', $all_subject_seq);
            }
            if (str_contains($all_subject_seq, '107.108.') && !str_contains($all_subject_seq, '107.108.1107.')) {
                $all_subject_seq = str_replace('107.108.', '107.108.1107.', $all_subject_seq);
            }
            $all_subject_seq = rtrim($all_subject_seq, '.') . '.1000.' . $fourth_sub;
        } else {
            $all_parts = [];
            foreach ($sublist_arr as $sc) {
                if ($sc != $fourth_sub) $all_parts[] = $sc;
            }
            foreach ($combined_defs as $cdef) {
                $all_parts[] = $cdef['comb_code'];
            }
            $all_subject_seq = implode('.', $all_parts);
            if ($fourth_sub > 0) {
                $all_subject_seq .= '.1000.' . $fourth_sub;
            }
        }

        $failsub_str = implode('.', $failed_sub_codes);
        $allfourth_str = $fourth_sub > 0 ? ($fourth_sub . '.') : '000';
        $all_subs_entry_escaped = $conn->real_escape_string(json_encode($raw_entries_array));

        // 4. Update tabulatingsheet
        $tab_updates = [];
        for ($i = 1; $i <= 15; $i++) {
            $tab_updates[] = "sub_$i=" . $sub_cols["sub_$i"];
            $tab_updates[] = "sub_{$i}_sub=" . $sub_cols["sub_{$i}_sub"];
            $tab_updates[] = "sub_{$i}_obj=" . $sub_cols["sub_{$i}_obj"];
            $tab_updates[] = "sub_{$i}_pra=" . $sub_cols["sub_{$i}_pra"];
            $tab_updates[] = "sub_{$i}_ca=" . $sub_cols["sub_{$i}_ca"];
            $tab_updates[] = "sub_{$i}_total=" . $sub_cols["sub_{$i}_total"];
            $tab_updates[] = "sub_{$i}_ct=" . $sub_cols["sub_{$i}_ct"];
            $tab_updates[] = "sub_{$i}_mt=" . $sub_cols["sub_{$i}_mt"];
            $tab_updates[] = "sub_{$i}_100=" . $sub_cols["sub_{$i}_100"];
            $tab_updates[] = "sub_{$i}_gp=" . $sub_cols["sub_{$i}_gp"];
            $tab_updates[] = "sub_{$i}_gl=" . $sub_cols["sub_{$i}_gl"];
        }

        $tab_updates[] = "totalmarks='" . sprintf("%.2f", $total_marks_obtained) . "'";
        $tab_updates[] = "full_marks='$total_full_marks'";
        $tab_updates[] = "avgrate='" . sprintf("%.2f", $avg_rate) . "'";
        $tab_updates[] = "gpa='" . sprintf("%.2f", $final_gpa) . "'";
        $tab_updates[] = "gpaadd='" . sprintf("%.2f", $gpa_add) . "'";
        $tab_updates[] = "gla='$final_gla'";
        $tab_updates[] = "attnd='0'";
        $tab_updates[] = "twday='0'";
        $tab_updates[] = "prevexam='0'";
        $tab_updates[] = "thisexam='0'";
        $tab_updates[] = "totalfail='$total_fail_count'";
        $tab_updates[] = "totalgp='" . sprintf("%.2f", $total_gp_overall) . "'";
        $tab_updates[] = "totalsubject='$mandatory_subject_count'";
        $tab_updates[] = "sublist='$sublist_str'";
        $tab_updates[] = "allsubject='$all_subject_seq'";
        $tab_updates[] = "allfourth='$allfourth_str'";
        $tab_updates[] = "failsub='$failsub_str'";
        $tab_updates[] = "all_subs_entry='$all_subs_entry_escaped'";
        $tab_updates[] = "last_update=NOW()";

        $full_sql = "UPDATE tabulatingsheet SET " . implode(',', $tab_updates) . " WHERE stid='$stid' AND exam='$exam' AND sessionyear='$sessionyear' AND classname='$classname' AND sccode='$sccode' AND slot='$slot' AND sectionname='$sectionname'";
        $conn->query($full_sql);

        // Fetch tsheet id
        $tsheet_id = 0;
        $tsheet_q = $conn->query("SELECT id FROM tabulatingsheet WHERE stid='$stid' AND exam='$exam' AND sessionyear='$sessionyear' AND classname='$classname' AND sccode='$sccode' AND slot='$slot' AND sectionname='$sectionname' LIMIT 1");
        if ($tsheet_q && $tsheet_row = $tsheet_q->fetch_assoc()) {
            $tsheet_id = (int)$tsheet_row['id'];
        }

        // 5. Upsert tabulatingsheetex
        if ($tsheet_id > 0) {
            $check_ex = $conn->query("SELECT id FROM tabulatingsheetex WHERE sccode='$sccode' AND sessionyear='$sessionyear' AND slot='$slot' AND exam='$exam' AND classname='$classname' AND stid='$stid' LIMIT 1");
            if ($check_ex && $check_ex->num_rows > 0) {
                $ex_row = $check_ex->fetch_assoc();
                $ex_id = (int)$ex_row['id'];

                $ex_updates = [
                    "tsheet_id='$tsheet_id'",
                    "sectionname='$sectionname'",
                    "rollno='$roll'",
                    "totalmarks='" . sprintf("%.2f", $total_marks_obtained) . "'",
                    "full_marks='$total_full_marks'",
                    "avgrate='" . sprintf("%.2f", $avg_rate) . "'",
                    "gpa='" . sprintf("%.2f", $final_gpa) . "'",
                    "gla='$final_gla'",
                    "totalfail='$total_fail_count'",
                    "totalgp='" . sprintf("%.2f", $total_gp_overall) . "'",
                    "totalsubject='$mandatory_subject_count'",
                    "gender='$gender'",
                    "allsubject='$all_subject_seq'",
                    "allfourth='$allfourth_str'",
                    "failsub='$failsub_str'",
                    "prevexam='0'",
                    "thisexam='0'",
                    "fourth_subj=" . $ex_cols['fourth_subj'],
                    "fourth_obj=" . $ex_cols['fourth_obj'],
                    "fourth_pra=" . $ex_cols['fourth_pra'],
                    "fourth_ca=" . $ex_cols['fourth_ca'],
                    "fourth_total=" . $ex_cols['fourth_total'],
                    "fourth_gp=" . $ex_cols['fourth_gp'],
                    "fourth_gl=" . $ex_cols['fourth_gl'],
                    "add_gp='" . sprintf("%.2f", $add_gp) . "'",
                    "modifieddate=NOW()"
                ];

                for ($k = 1; $k <= 10; $k++) {
                    $ex_updates[] = "sub_$k=" . $ex_cols["sub_$k"];
                    $ex_updates[] = "sub_code_$k=" . $ex_cols["sub_code_$k"];
                    $ex_updates[] = "sub_fm_$k=" . $ex_cols["sub_fm_$k"];
                    $ex_updates[] = "sub_{$k}_sub=" . $ex_cols["sub_{$k}_sub"];
                    $ex_updates[] = "sub_{$k}_obj=" . $ex_cols["sub_{$k}_obj"];
                    $ex_updates[] = "sub_{$k}_pra=" . $ex_cols["sub_{$k}_pra"];
                    $ex_updates[] = "sub_{$k}_ca=" . $ex_cols["sub_{$k}_ca"];
                    $ex_updates[] = "sub_{$k}_total=" . $ex_cols["sub_{$k}_total"];
                    $ex_updates[] = "sub_{$k}_gp=" . $ex_cols["sub_{$k}_gp"];
                    $ex_updates[] = "sub_{$k}_gl=" . $ex_cols["sub_{$k}_gl"];
                }

                $conn->query("UPDATE tabulatingsheetex SET " . implode(',', $ex_updates) . " WHERE id='$ex_id'");
            } else {
                $ex_insert_cols = [
                    'tsheet_id', 'sessionyear', 'sccode', 'slot', 'exam', 'classname', 'sectionname', 'stid', 'rollno',
                    'sub_code_1', 'sub_code_2', 'sub_code_3', 'sub_code_4', 'sub_code_5',
                    'sub_code_6', 'sub_code_7', 'sub_code_8', 'sub_code_9', 'sub_code_10',
                    'sub_fm_1', 'sub_fm_2', 'sub_fm_3', 'sub_fm_4', 'sub_fm_5',
                    'sub_fm_6', 'sub_fm_7', 'sub_fm_8', 'sub_fm_9', 'sub_fm_10',
                    'sub_1', 'sub_2', 'sub_3', 'sub_4', 'sub_5',
                    'sub_6', 'sub_7', 'sub_8', 'sub_9', 'sub_10',
                    'sub_1_sub', 'sub_1_obj', 'sub_1_pra', 'sub_1_ca', 'sub_1_total', 'sub_1_gp', 'sub_1_gl',
                    'sub_2_sub', 'sub_2_obj', 'sub_2_pra', 'sub_2_ca', 'sub_2_total', 'sub_2_gp', 'sub_2_gl',
                    'sub_3_sub', 'sub_3_obj', 'sub_3_pra', 'sub_3_ca', 'sub_3_total', 'sub_3_gp', 'sub_3_gl',
                    'sub_4_sub', 'sub_4_obj', 'sub_4_pra', 'sub_4_ca', 'sub_4_total', 'sub_4_gp', 'sub_4_gl',
                    'sub_5_sub', 'sub_5_obj', 'sub_5_pra', 'sub_5_ca', 'sub_5_total', 'sub_5_gp', 'sub_5_gl',
                    'sub_6_sub', 'sub_6_obj', 'sub_6_pra', 'sub_6_ca', 'sub_6_total', 'sub_6_gp', 'sub_6_gl',
                    'sub_7_sub', 'sub_7_obj', 'sub_7_pra', 'sub_7_ca', 'sub_7_total', 'sub_7_gp', 'sub_7_gl',
                    'sub_8_sub', 'sub_8_obj', 'sub_8_pra', 'sub_8_ca', 'sub_8_total', 'sub_8_gp', 'sub_8_gl',
                    'sub_9_sub', 'sub_9_obj', 'sub_9_pra', 'sub_9_ca', 'sub_9_total', 'sub_9_gp', 'sub_9_gl',
                    'sub_10_sub', 'sub_10_obj', 'sub_10_pra', 'sub_10_ca', 'sub_10_total', 'sub_10_gp', 'sub_10_gl',
                    'fourth_subj', 'fourth_obj', 'fourth_pra', 'fourth_ca', 'fourth_total', 'fourth_gp', 'fourth_gl', 'add_gp',
                    'totalmarks', 'full_marks', 'avgrate', 'gpa', 'gla', 'totalfail', 'totalgp', 'totalsubject', 'gender',
                    'allsubject', 'allfourth', 'failsub', 'prevexam', 'thisexam', 'modifieddate'
                ];

                $ex_insert_vals = [
                    "'$tsheet_id'", "'$sessionyear'", "'$sccode'", "'$slot'", "'$exam'", "'$classname'", "'$sectionname'", "'$stid'", "'$roll'",
                    $ex_cols['sub_code_1'], $ex_cols['sub_code_2'], $ex_cols['sub_code_3'], $ex_cols['sub_code_4'], $ex_cols['sub_code_5'],
                    $ex_cols['sub_code_6'], $ex_cols['sub_code_7'], $ex_cols['sub_code_8'], $ex_cols['sub_code_9'], $ex_cols['sub_code_10'],
                    $ex_cols['sub_fm_1'], $ex_cols['sub_fm_2'], $ex_cols['sub_fm_3'], $ex_cols['sub_fm_4'], $ex_cols['sub_fm_5'],
                    $ex_cols['sub_fm_6'], $ex_cols['sub_fm_7'], $ex_cols['sub_fm_8'], $ex_cols['sub_fm_9'], $ex_cols['sub_fm_10'],
                    $ex_cols['sub_1'], $ex_cols['sub_2'], $ex_cols['sub_3'], $ex_cols['sub_4'], $ex_cols['sub_5'],
                    $ex_cols['sub_6'], $ex_cols['sub_7'], $ex_cols['sub_8'], $ex_cols['sub_9'], $ex_cols['sub_10'],
                    $ex_cols['sub_1_sub'], $ex_cols['sub_1_obj'], $ex_cols['sub_1_pra'], $ex_cols['sub_1_ca'], $ex_cols['sub_1_total'], $ex_cols['sub_1_gp'], $ex_cols['sub_1_gl'],
                    $ex_cols['sub_2_sub'], $ex_cols['sub_2_obj'], $ex_cols['sub_2_pra'], $ex_cols['sub_2_ca'], $ex_cols['sub_2_total'], $ex_cols['sub_2_gp'], $ex_cols['sub_2_gl'],
                    $ex_cols['sub_3_sub'], $ex_cols['sub_3_obj'], $ex_cols['sub_3_pra'], $ex_cols['sub_3_ca'], $ex_cols['sub_3_total'], $ex_cols['sub_3_gp'], $ex_cols['sub_3_gl'],
                    $ex_cols['sub_4_sub'], $ex_cols['sub_4_obj'], $ex_cols['sub_4_pra'], $ex_cols['sub_4_ca'], $ex_cols['sub_4_total'], $ex_cols['sub_4_gp'], $ex_cols['sub_4_gl'],
                    $ex_cols['sub_5_sub'], $ex_cols['sub_5_obj'], $ex_cols['sub_5_pra'], $ex_cols['sub_5_ca'], $ex_cols['sub_5_total'], $ex_cols['sub_5_gp'], $ex_cols['sub_5_gl'],
                    $ex_cols['sub_6_sub'], $ex_cols['sub_6_obj'], $ex_cols['sub_6_pra'], $ex_cols['sub_6_ca'], $ex_cols['sub_6_total'], $ex_cols['sub_6_gp'], $ex_cols['sub_6_gl'],
                    $ex_cols['sub_7_sub'], $ex_cols['sub_7_obj'], $ex_cols['sub_7_pra'], $ex_cols['sub_7_ca'], $ex_cols['sub_7_total'], $ex_cols['sub_7_gp'], $ex_cols['sub_7_gl'],
                    $ex_cols['sub_8_sub'], $ex_cols['sub_8_obj'], $ex_cols['sub_8_pra'], $ex_cols['sub_8_ca'], $ex_cols['sub_8_total'], $ex_cols['sub_8_gp'], $ex_cols['sub_8_gl'],
                    $ex_cols['sub_9_sub'], $ex_cols['sub_9_obj'], $ex_cols['sub_9_pra'], $ex_cols['sub_9_ca'], $ex_cols['sub_9_total'], $ex_cols['sub_9_gp'], $ex_cols['sub_9_gl'],
                    $ex_cols['sub_10_sub'], $ex_cols['sub_10_obj'], $ex_cols['sub_10_pra'], $ex_cols['sub_10_ca'], $ex_cols['sub_10_total'], $ex_cols['sub_10_gp'], $ex_cols['sub_10_gl'],
                    $ex_cols['fourth_subj'], $ex_cols['fourth_obj'], $ex_cols['fourth_pra'], $ex_cols['fourth_ca'], $ex_cols['fourth_total'], $ex_cols['fourth_gp'], $ex_cols['fourth_gl'], "'" . sprintf("%.2f", $add_gp) . "'",
                    "'" . sprintf("%.2f", $total_marks_obtained) . "'", "'$total_full_marks'", "'" . sprintf("%.2f", $avg_rate) . "'", "'" . sprintf("%.2f", $final_gpa) . "'", "'$final_gla'", "'$total_fail_count'", "'" . sprintf("%.2f", $total_gp_overall) . "'", "'$mandatory_subject_count'", "'$gender'",
                    "'$all_subject_seq'", "'$allfourth_str'", "'$failsub_str'", "'0'", "'0'", "NOW()"
                ];

                $conn->query("INSERT INTO tabulatingsheetex (" . implode(',', $ex_insert_cols) . ") VALUES (" . implode(',', $ex_insert_vals) . ")");
            }
        }

        echo "<script>setBar('p_student',100);</script>";
        echo "<script>setBar('p_overall'," . ($si / $total_students * 100) . ");</script>";
        echo "<script>log('Done STID: $stid');</script>";
        flush_now();
    }
    echo microtime(true) . '<br>';

    // -------------------- MERIT RANKING (EXCLUSIVELY IN TABULATINGSHEET) --------------------
    if (!function_exists('get_ordinal_suffix')) {
        function get_ordinal_suffix($num) {
            $n = (int)$num;
            if ($n <= 0) return '';
            $mod100 = $n % 100;
            if ($mod100 >= 11 && $mod100 <= 13) return $n . 'th';
            switch ($n % 10) {
                case 1: return $n . 'st';
                case 2: return $n . 'nd';
                case 3: return $n . 'rd';
                default: return $n . 'th';
            }
        }
    }

    $merit_lookup = [];
    $ml_res = $conn->query("SELECT numplace, meritplace FROM meritlist");
    while ($ml_res && $ml_row = $ml_res->fetch_assoc()) {
        $merit_lookup[(int)$ml_row['numplace']] = $ml_row['meritplace'];
    }

    // 1. Fetch all processed rows for this class/exam to compute exact ranks
    $merit_rows = [];
    $mr_res = $conn->query("
        SELECT id, stid, sectionname, gender, totalfail, totalmarks, gpa, rollno
        FROM tabulatingsheet
        WHERE exam='$exam' AND slot='$slot' AND sessionyear='$sessionyear' AND classname='$classname' AND sccode='$sccode'
        ORDER BY totalfail ASC, totalmarks DESC, gpa DESC, rollno ASC
    ");
    while ($mr_res && $mrow = $mr_res->fetch_assoc()) {
        $merit_rows[] = $mrow;
    }

    // A. Class Combined Rank across all sections (meritnumcomb, meritplacecomb)
    $comb_rank = 1;
    $comb_map = [];
    foreach ($merit_rows as $mr) {
        $comb_map[$mr['id']] = [
            'num' => $comb_rank,
            'place' => $merit_lookup[$comb_rank] ?? get_ordinal_suffix($comb_rank)
        ];
        $comb_rank++;
    }

    // B. Section-wise Rank (meritnum, meritplace)
    $section_groups = [];
    foreach ($merit_rows as $mr) {
        $sec = $mr['sectionname'];
        $section_groups[$sec][] = $mr;
    }
    $sec_map = [];
    foreach ($section_groups as $sec => $srows) {
        $srank = 1;
        foreach ($srows as $sr) {
            $sec_map[$sr['id']] = [
                'num' => $srank,
                'place' => $merit_lookup[$srank] ?? get_ordinal_suffix($srank)
            ];
            $srank++;
        }
    }

    // C. Gender-wise Rank within section (meritnumgender, meritplacegender)
    $gender_groups = [];
    foreach ($merit_rows as $mr) {
        $key = $mr['sectionname'] . '_' . ($mr['gender'] ?? '');
        $gender_groups[$key][] = $mr;
    }
    $gender_map = [];
    foreach ($gender_groups as $gkey => $grows) {
        $grank = 1;
        foreach ($grows as $gr) {
            $gender_map[$gr['id']] = [
                'num' => $grank,
                'place' => $merit_lookup[$grank] ?? get_ordinal_suffix($grank)
            ];
            $grank++;
        }
    }

    // Apply all 6 merit fields directly to tabulatingsheet
    foreach ($merit_rows as $mr) {
        $mid = (int)$mr['id'];
        $mnum = $sec_map[$mid]['num'] ?? 0;
        $mplace = $sec_map[$mid]['place'] ?? '';
        $mcnum = $comb_map[$mid]['num'] ?? 0;
        $mcplace = $comb_map[$mid]['place'] ?? '';
        $mgnum = $gender_map[$mid]['num'] ?? 0;
        $mgplace = $gender_map[$mid]['place'] ?? '';

        $conn->query("
            UPDATE tabulatingsheet SET 
                meritnum='$mnum',
                meritplace='$mplace',
                meritnumcomb='$mcnum',
                meritplacecomb='$mcplace',
                meritnumgender='$mgnum',
                meritplacegender='$mgplace'
            WHERE id='$mid'
        ");
    }


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