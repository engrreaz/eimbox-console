<?php require_once 'header.php'; ?>

<style>
    [class^="comb-group-"] {
        border-left: 0 solid #ff9800;
    }
</style>

<style>
    /* বামপাশে বর্ডার দেওয়ার জন্য */
    .has-combined {
        border-left: 0 solid #ff9800 !important;
    }

    .comb-group-1 td {
        color: #fc2590 !important;
        background-color: rgba(252, 37, 144, 0.1) !important;
        font-weight: 600;
    }

    .comb-group-2 td {
        color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.1) !important;
        font-weight: 600;
    }

    .comb-group-3 td {
        color: #198754 !important;
        background-color: rgba(25, 135, 84, 0.1) !important;
        font-weight: 600;
    }

    .comb-group-4 td {
        color: #fd7e14 !important;
        background-color: rgba(253, 126, 20, 0.1) !important;
        font-weight: 600;
    }

    .comb-group-5 td {
        color: #6f42c1 !important;
        background-color: rgba(111, 66, 193, 0.1) !important;
        font-weight: 600;
    }

    .comb-g6 td {
        color: #20c997 !important;
        background-color: rgba(32, 201, 151, 0.1) !important;
        font-weight: 600;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    // -----------------------------
// Input & Defaults
// -----------------------------
    $slot = $_COOKIE['chain-slot'] ?? '';
    $sessionyear = $_COOKIE['chain-session'] ?? '';
    $cls = $_COOKIE['chain-class'] ?? '';
    $sec = $_COOKIE['chain-section'] ?? '';



    $years = [];
    $q = "SELECT syear FROM sessionyear Where sccode='$sccode' AND active=1 ORDER BY syear DESC";
    $r = $conn->query($q);
    while ($row = $r->fetch_assoc()) {
        $years[] = $row['syear'];
    }


    // -----------------------------
// Helpers
// -----------------------------
    function selected($a, $b)
    {
        return $a == $b ? 'selected' : '';
    }
    ?>

    <!-- ================= FILTER BAR ================= -->
    <?php
    $chain_param = '-c 10 -t Choose Values -u -r -b View List';
    include 'components/slot-tree-ui.php';
    ?>


    <?php if (!$slot || !$sessionyear || !$cls || !$sec): ?>
        <div class="alert alert-warning">Select Slot, Year, Class & Section</div>
    <?php else: ?>

        <!-- ================= ACTION BAR ================= -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Subjects — <?php echo "$cls ($sec)"; ?></h5>
            <h5 class="mb-0 text-end">Session — <?php echo "$sessionyear ($slot)"; ?></h5>

        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 py-2 border-primary border-bottom border-top">
            <div>
                Same color tone are indicates combined subjects with each other(s)
            </div>
            <div>
                <i class="bi bi-square-fill text-primary ms-2"></i> &mdash; Combined Pass
            </div>
            <div>
                <i class="bi bi-square-half text-primary ms-2"></i> &mdash; Indivisual Pass
            </div>
            <div>
                <i class="bi bi-circle-fill text-primary ms-2"></i> &mdash; 4<sup>th</sup> Subject
            </div>
        </div>

        <!-- ================= TABLE ================= -->
        <div class="card">
            <div class=" table-responsive">
                <table class="table table-hover table-sm" id="myTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:10px;"></th>
                            <th>#</th>
                            <th hidden>ID</th>
                            <th>Code</th>
                            <th>Subject</th>

                            <th>CT</th>
                            <th>MT</th>
                            <th>Sub</th>
                            <th>Obj</th>
                            <th>Pra</th>
                            <th>CA</th>
                            <th>Full</th>
                            <th>Pass</th>


                            <th></th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $sl = 1;
                        $unique_ids = []; // ডুপ্লিকেট ট্র্যাক করার জন্য
                        $groupIndex = 0;

                        $q = "SELECT * FROM subsetup
                            WHERE slot='$slot' AND sccode='$sccode' AND sessionyear='$sessionyear'
                            AND classname='$cls' AND sectionname='$sec'
                            ORDER BY slno, subject";
                        $r = $conn->query($q);
                        $rows = [];


                        $groupMap = [];   // subcode => group id
                        $groupId = 1;

                        $txt = '';

                        if ($r->num_rows):
                            while ($row = $r->fetch_assoc()):

                                $sub = $row['subject'];

                                $rowClass = '';
                                if (isset($groupMap[$sub])) {
                                    $rowClass = 'comb-g' . $groupMap[$sub];
                                }


                                $c = [
                                    $row['combind_1'],
                                    $row['combind_2'],
                                    $row['combind_3'],
                                    $row['combind_4']
                                ];

                                $hasCombined = false;
                                foreach ($c as $v) {
                                    if ($v > 0) {
                                        $hasCombined = true;
                                        break;
                                    }
                                }

                                if ($hasCombined && !isset($groupMap[$sub])) {
                                    // নিজের group
                                    $groupMap[$sub] = $groupId;

                                    // যাদের সাথে combined
                                    foreach ($c as $v) {
                                        if ($v > 0) {
                                            $groupMap[$v] = $groupId;
                                        }
                                    }

                                    $groupId++;
                                }


                                // এখানে ধরো subcode unique
                                if (in_array($row['subject'], $unique_ids)) {
                                    $hx = $row['id'];
                                    // $conn->query("DELETE FROM subsetup WHERE sccode='$sccode' AND id='$hx'");
                                    continue; // ডুপ্লিকেট হলে স্কিপ
                                }
                                $unique_ids[] = $row['subject'];

                                $id = $row['id'];
                                $sub = $row['subject'];
                                $tid = $row['tid'];

                                $ctest = $row['ctest'];
                                $mtest = $row['mtest'];
                                $subj = $row['subj'];
                                $obj = $row['obj'];
                                $pra = $row['pra'];
                                $ca = $row['ca'];
                                $full_marks = $row['fullmarks'];
                                $pass_algorithm = $row['pass_algorithm'];
                                if ($pass_algorithm == 0) {
                                    $pass_icon = 'square-fill';
                                } else {
                                    $pass_icon = 'square-half';
                                }
                                $fourth = $row['fourth'];
                                $combined_1 = $row['combind_1'];
                                $combined_2 = $row['combind_2'];
                                $combined_3 = $row['combind_3'];
                                $combined_4 = $row['combind_4'];

                                $isCombined = (
                                    $combined_1 > 0 || $combined_2 > 0 || $combined_3 > 0 || $combined_4 > 0
                                );

                                if ($isCombined) {
                                    $groupIndex++;
                                    $rowClass = 'comb-group-' . $groupIndex;
                                    $txt .= $sub . ',' . $combined_1 . ',' . $combined_2 . ',' . $combined_3 . ',' . $combined_4 . '|';
                                } else {
                                    $rowClass = '';
                                }

                                $subClass = 'subject-' . $sub;

                                $sq = "SELECT subject FROM subjects
                                    WHERE (sccode='$sccode' OR sccode=0) AND subcode='$sub' AND sccategory='$sctype'
                                    ORDER BY sccode DESC LIMIT 1";
                                $sr = $conn->query($sq);
                                $subname = $sr->num_rows ? $sr->fetch_assoc()['subject'] : '';

                                $tq = "SELECT tname FROM teacher WHERE sccode='$sccode' AND tid='$tid'";
                                $tr = $conn->query($tq);
                                $tname = $tr->num_rows ? $tr->fetch_assoc()['tname'] : '';


                                ?>
                                <tr class="<?php echo $rowClass; ?> <?= $subClass ?> " data-id="<?php echo $id; ?>"
                                    class="<?php echo $rowClass; ?>" <?php if ($fourth == 1)
                                           echo 'style="background:#f2f2f2"'; ?>>
                                    <td></td>
                                    <td><?php echo $sl++; ?></td>
                                    <td hidden><?php echo $id; ?></td>
                                    <td><?php echo $sub; ?></td>
                                    <td><?php echo $subname; ?></td>
                                    <td><?php echo $ctest; ?></td>
                                    <td><?php echo $mtest; ?></td>
                                    <td><?php echo $subj; ?></td>
                                    <td><?php echo $obj; ?></td>
                                    <td><?php echo $pra; ?></td>
                                    <td><?php echo $ca; ?></td>
                                    <td><?php echo $full_marks; ?></td>
                                    <td style="width:80px;">
                                        <?php
                                        echo '<i class="bi bi-' . $pass_icon . '"></i>';
                                        if ($fourth == 1) {
                                            echo '<i class="bi bi-circle-fill text-primary ms-2"></i>';
                                        }
                                        ?>
                                    </td>








                                    <td class="text-end">
                                        <button class="btn btn-sm btn-info" onclick="openEdit(<?php echo $id; ?>)"><i
                                                class="bi bi-pencil"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="15" class="text-center py-4">
                                    No subjects found<br><br>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</div>


<!-- ================= EDIT MODAL ================= -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Subject Setup</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="eid">

                <div class="row g-2">

                    <div class="col-md-2">
                        <label>CT</label>
                        <input type="number" id="ectest" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label>MT</label>
                        <input type="number" id="emtest" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label>Subjective</label>
                        <input type="number" id="esubj" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label>Objective</label>
                        <input type="number" id="eobj" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label>Practical</label>
                        <input type="number" id="epra" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label>CA</label>
                        <input type="number" id="eca" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label>Full Marks</label>
                        <input type="number" id="efull" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label>Pass Method</label>
                        <select id="epass" class="form-select form-select-sm">
                            <option value="0">Combined</option>
                            <option value="1">Individual</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Fourth</label>
                        <select id="efourth" class="form-select form-select-sm">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                </div>
                <div class="row g-2 mt-3">
                    <!-- Combined Subjects -->
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="col-md-3">
                            <label>Combined <?php echo $i; ?></label>
                            <select id="ecomb<?php echo $i; ?>" class="form-select form-select-sm comb-select">
                                <option value="0">None</option>
                            </select>
                        </div>
                    <?php endfor; ?>

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="updateSubject()">Update</button>
            </div>
        </div>
    </div>
</div>



<?php require_once('footer.php'); ?>

<!-- ----------------------------------- -->

<script>
    const years = <?= json_encode($years) ?>;
    console.log(years);

    function chainBtnFunc() {
        window.location.reload();
    }
</script>


<script>
    async function openEdit(id) {
        const res = await fetch('subject/get-subject-setup.php?id=' + id);
        const d = await res.json();

        eid.value = id;
        ectest.value = d.ctest;
        emtest.value = d.mtest;
        esubj.value = d.subj;
        eobj.value = d.obj;
        epra.value = d.pra;
        eca.value = d.ca;
        efull.value = d.fullmarks;
        epass.value = d.pass_algorithm;
        efourth.value = d.fourth;

        // ✅ আগে options load
        await loadCombinedSubjects();

        // ✅ তারপর value set
        for (let i = 1; i <= 4; i++) {
            const v = String(d['combind_' + i] ?? 0);
            document.getElementById('ecomb' + i).value = v;
        }

        new bootstrap.Modal(editModal).show();
    }

    function loadCombinedSubjects() {
        return fetch('subject/get-subject-list.php')
            .then(r => r.text())
            .then(html => {
                document.querySelectorAll('.comb-select').forEach(sel => {
                    sel.innerHTML = '<option value="0">None</option>' + html;
                });
            });
    }
</script>



<script>
    function updateSubject() {
        const fd = new URLSearchParams({
            id: eid.value,
            ctest: ectest.value,
            mtest: emtest.value,
            subj: esubj.value,
            obj: eobj.value,
            pra: epra.value,
            ca: eca.value,
            full: efull.value,
            pass: epass.value,
            fourth: efourth.value,
            c1: ecomb1.value,
            c2: ecomb2.value,
            c3: ecomb3.value,
            c4: ecomb4.value
        });


        fetch('subject/update-subject-setup.php', {
            method: 'POST',
            body: fd
        })
            .then(r => r.text())
            .then(msg => {
                if (msg === 'OK') location.reload();
                else alert(msg);
            });
    }
</script>


<script>
    // ইনপুট আইডি গুলোর একটি লিস্ট তৈরি করি
    const inputIds = ['ectest', 'emtest', 'esubj', 'eobj', 'epra'];
    inputIds.forEach(id => {
        document.getElementById(id).addEventListener('input', calculateFullMarks);
    });

    function calculateFullMarks() {
        let total = 0;
        inputIds.forEach(id => {
            const val = parseFloat(document.getElementById(id).value);
            total += isNaN(val) ? 0 : val;
        });
        document.getElementById('efull').value = total;
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const data = "<?php echo $txt; ?>";

        // Step 1: | দিয়ে গ্রুপ ভাগ
        const groups = data.split('|').filter(g => g.trim() !== '');

        groups.forEach((groupText, index) => {

            const groupClass = 'comb-group-' + (index + 1);

            // Step 2: , দিয়ে আইটেম ভাগ + Step 3: 0 বাদ
            const items = groupText
                .split(',')
                .map(v => v.trim())
                .filter(v => v !== '0' && v !== '');

            // Step 4 & 5
            items.forEach(val => {
                const elements = document.querySelectorAll('.subject-' + val);
                elements.forEach(el => el.classList.add(groupClass));
            });

        });

    });
</script>

</body>

</html>