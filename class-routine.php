<?php
require_once 'header.php';

// প্যারামিটার
$cls = $_COOKIE['chain-class'] ?? $_GET['cls'] ?? '';
$sec = $_COOKIE['chain-section'] ?? $_GET['sec'] ?? '';
$year = $_COOKIE['chain-session'] ?? $_GET['session'] ?? date('Y');
$slot = $_COOKIE['chain-slot'] ?? $_GET['slot'] ?? 'School';

$weekendDays = [];
foreach ($sett as $row) {
    if ($row['setting_title'] == 'Weekends') {
        $weekendDays = explode(',', trim($row['settings_value']));
    }
}

// আজকের দিনের ইনডেক্স বের করা (১=শনিবার, ২=রবিবার... ৭=শুক্রবার এই ফরম্যাটে)
// সাধারণত BD স্কুলে ১=শনিবার ধরা হয়। 
$jd = date('w'); // ০ (রবি) থেকে ৬ (শনি)
$today_wday = ($jd + 2 > 7) ? ($jd + 2 - 7) : ($jd + 2);
// উপরে লজিকটি আপনার সিস্টেমের wday (১-৭) এর সাথে সিঙ্ক করে নিতে পারেন।

// ডাটাবেজ অ্যাকশন (Add/Edit/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $rid = $_POST['rid'] ?? null;
    $period = $_POST['period'];
    $wday = $_POST['wday'];
    $subcode = $_POST['subcode'];
    $tid = $_POST['tid'];
    $entryby = $usr;

    if ($_POST['action'] == 'save') {
        if ($rid) {
            $stmt = $conn->prepare("UPDATE clsroutine SET period=?, wday=?, subcode=?, tid=?, modifieddate=NOW() WHERE id=? AND sccode=?");
            $stmt->bind_param("iiiiii", $period, $wday, $subcode, $tid, $rid, $sccode);
            $msg = "Updated successfully.";
            $alert = 'success';
        } else {
            $stmt = $conn->prepare("INSERT INTO clsroutine (sccode, sessionyear, classname, sectionname, period, wday, subcode, tid, entryby) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssiiiis", $sccode, $year, $cls, $sec, $period, $wday, $subcode, $tid, $entryby);
            $msg = "Added successfully.";
            $alert = 'success';
        }
        $stmt->execute();
    } elseif ($_POST['action'] == 'delete') {
        $conn->query("DELETE FROM clsroutine WHERE id='$rid' AND sccode='$sccode'");
        $msg = "Deleted successfully.";
        $alert = 'danger';

    }


}

// রুটিন ডাটা ফেচ করা
// ১. ক্লাস শিডিউল থেকে পিরিয়ডগুলো ফেচ করা
$periods_list = [];
$ps_sql = "SELECT period, timestart, timeend FROM classschedule 
           WHERE sccode = '$sccode' AND sessionyear = '$year' AND slots = '$slot' 
           ORDER BY period ASC";
$ps_res = $conn->query($ps_sql);
while ($ps_row = $ps_res->fetch_assoc()) {
    $periods_list[$ps_row['period']] = $ps_row;
}

// ২. রুটিন ডাটা ফেচ করা (আগের মতোই)
$routine = [];
if ($cls && $sec) {
    $res = $conn->query("SELECT r.*, t.tname, s.subject as subname 
                        FROM clsroutine r 
                        LEFT JOIN teacher t ON r.tid = t.tid 
                        LEFT JOIN subjects s ON r.subcode = s.subcode 
                        WHERE r.sccode='$sccode' AND r.classname='$cls' AND r.sectionname='$sec' AND r.sessionyear='$year'
                        ORDER BY r.period, r.wday");
    while ($row = $res->fetch_assoc()) {
        $routine[$row['period']][$row['wday']] = $row;
    }
}

$days = [1 => 'Saturday', 2 => 'Sunday', 3 => 'Monday', 4 => 'Tuesday', 5 => 'Wednesday', 6 => 'Thursday', 7 => 'Friday'];
?>

<div class="container-xxl flex-grow-1 container-p-y">


    

    <?php

    if($alert){
        echo "<div class='alert alert-$alert'>$msg</div>";
    }
    $chain_param = '-c 12 -t Choose Values -u -r -b View Routine ';
    include 'components/slot-tree-ui.php';
    ?>



    
<div class="row">
    <?php if (empty($periods_list)): ?>
        <div class="col-12 text-center py-5">
            <div class="alert alert-warning">প্রথমে ক্লাস শিডিউলে পিরিয়ড সেটআপ করুন।</div>
        </div>
    <?php else: ?>
        <?php foreach ($periods_list as $p_num => $p_info): 
            $today_data = $routine[$p_num][$today_wday] ?? null;
            $start_time = date('h:i A', strtotime($p_info['timestart']));
            $end_time = date('h:i A', strtotime($p_info['timeend']));
        ?>
            <div class="col-12 mb-3">
                <div class="card shadow-none border">
                    <div class="card-header d-flex justify-content-between align-items-center py-2 bg-light">
                        <div class="fw-bold">
                            <span class="badge bg-primary me-2">Period <?= $p_num ?></span>
                            <span class="text-muted small me-3"><i class="bi bi-clock"></i> <?= $start_time ?> - <?= $end_time ?></span>
                            
                            <?php if ($today_data): ?>
                                <span class="text-dark fw-bold"><?= $today_data['subname'] ?></span>
                                <span class="text-muted mx-1">|</span>
                                <span class="text-secondary small"><?= $today_data['tname'] ?></span>
                                <span class="badge bg-label-success ms-2">Today</span>
                            <?php else: ?>
                                <span class="text-danger small italic">No Class has been assigned for today</span>
                            <?php endif; ?>
                        </div>
                        
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#period-<?= $p_num ?>">
                            Weekly Schedule <i class="bi bi-chevron-down ms-1"></i>
                        </button>
                    </div>

                    <div class="collapse" id="period-<?= $p_num ?>">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr class="small text-uppercase">
                                            <th class="ps-3" style="width: 150px;">Day</th>
                                            <th>Subject</th>
                                            <th>Teacher</th>
                                            <th class="text-end pe-3">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($days as $di => $dn): 
                                            $d_data = $routine[$p_num][$di] ?? null;
                                            $is_today_row = ($di == $today_wday) ? 'table-warning' : '';

                                            
                                        ?>
                                            <tr class="<?= $is_today_row ?>">
 <td class="ps-3 fw-bold"><?= $dn ?></td>
                                            <?php 
if (in_array($dn, $weekendDays)) {
    echo '<td colspan="3" class="fw-bold text-muted small">Weekends</td>';
} else {
                                            ?>
                                               
                                                <td>
                                                    <?php if ($d_data): ?>
                                                        <span class="text-primary fw-bold"><?= $d_data['subname'] ?></span>
                                                    <?php else: ?>
                                                        <span class="text-light">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($d_data): ?>
                                                        <span class="text-secondary small"><?= $d_data['tname'] ?></span>
                                                    <?php else: ?>
                                                        <span class="text-light">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <?php if ($d_data): ?>
                                                      <button class="btn btn-xs btn-icon btn-label-warning me-1"   onclick='editRoutine(<?= htmlspecialchars(json_encode($d_data), ENT_QUOTES, 'UTF-8') ?>)'> <i class="bi bi-pencil"></i> </button>
                                                        <button class="btn btn-xs btn-icon btn-label-danger" onclick="deleteRoutine(<?= $d_data['id'] ?>)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-xs btn-icon btn-label-secondary" onclick="addRoutine()">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>

                                                <?php } ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<div class="modal fade" id="routineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form class="modal-content" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Manage Routine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="rid" id="m_rid">
                <input type="hidden" name="action" value="save">

                <div class="mb-2">
                    <label class="form-label small">Day</label>
                    <select name="wday" id="m_wday" class="form-select form-select-sm" required>
                        <?php foreach ($days as $i => $n)
                            echo "<option value='$i'>$n</option>"; ?>
                    </select>
                </div>
                <div class="mb-2">
    <label class="form-label small">Period</label>
    <select name="period" id="m_period" class="form-select form-select-sm" required>
        <option value="">Select Period</option>
        <?php 
        // আপনার আগে ফেচ করা $periods_list অ্যারে থেকে ডাটা নেওয়া হচ্ছে
        foreach ($periods_list as $p_num => $p_info): 
            $time_label = date('h:i A', strtotime($p_info['timestart'])) . " - " . date('h:i A', strtotime($p_info['timeend']));
        ?>
            <option value="<?= $p_num ?>">
                Period <?= $p_num ?> (<?= $time_label ?>)
            </option>
        <?php endforeach; ?>
    </select>
</div>
                <div class="mb-2">
                    <label class="form-label small">Subject</label>
                    <select name="subcode" id="m_sub" class="form-select form-select-sm" required>
                        <option value="">Select Subject</option>
                        <?php
                        // ১. subsetup এবং subjects টেবিলের মধ্যে JOIN কুয়েরি
                        // $slots ভেরিয়েবলটি আপনার আগের পেজ থেকে আসতে হবে (যেমন: 'School' বা 'College')
                        $sql_subs = "SELECT s.subcode, s.subject as subname 
                 FROM subjects s 
                 INNER JOIN subsetup ss ON s.subcode = ss.subject 
                 WHERE ss.sccode = '$sccode' 
                AND s.sccategory = '$sctype'
                 AND ss.sessionyear = '$year' 
                 AND ss.classname = '$cls' 
                 AND ss.sectionname = '$sec'
                 AND ss.slot = '$slot'"; // আপনার সেশন/স্লট লজিক অনুযায়ী
                        
                        $subs_res = $conn->query($sql_subs);

                        if ($subs_res && $subs_res->num_rows > 0) {
                            while ($s = $subs_res->fetch_assoc()) {
                                echo "<option value='{$s['subcode']}'>{$s['subname']} ({$s['subcode']})</option>";
                            }
                        } else {
                            echo "<option value=''>No subjects found in setup</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Teacher</label>
                    <select name="tid" id="m_tid" class="form-select form-select-sm" required>
                        <?php
                        $ts = $conn->query("SELECT tid, tname FROM teacher WHERE sccode='$sccode'");
                        while ($t = $ts->fetch_assoc())
                            echo "<option value='{$t['tid']}'>{$t['tname']}</option>";
                        ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer p-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Save Routine</button>
            </div>
        </form>
    </div>
</div>

<form id="delForm" method="POST" style="display:none;">
    <input type="hidden" name="rid" id="del_id">
    <input type="hidden" name="action" value="delete">
</form>

<?php require_once 'footer.php'; ?>

<script>
    const myModala = new bootstrap.Modal(document.getElementById('routineModal'));

    function addRoutine() {
        document.getElementById('m_rid').value = '';
        myModala.show();
    }
</script>

<script>
    function addAt(p, d) {
        document.getElementById('m_rid').value = '';
        document.getElementById('m_period').value = p;
        document.getElementById('m_wday').value = d;
        myModal.show();
    }
</script>

<script>
    function editRoutine(data) {
    // ডাটা কন্সোলে চেক করে নিতে পারেন
    console.log(data); 

    document.getElementById('m_rid').value = data.id;
    document.getElementById('m_period').value = data.period;
    document.getElementById('m_wday').value = data.wday;
    document.getElementById('m_sub').value = data.subcode;
    document.getElementById('m_tid').value = data.tid;
    
    // মডালের টাইটেল পরিবর্তন (ঐচ্ছিক কিন্তু ইউজার ফ্রেন্ডলি)
    document.querySelector('#routineModal .modal-title').innerText = "Edit Routine Entry";
    
    myModala.show();
}
</script>

<script>
    function deleteRoutine(id) {
        if (confirm('Are you sure to remove this entry?')) {
            document.getElementById('del_id').value = id;
            document.getElementById('delForm').submit();
        }
    }




</script>

<script>
    function chainBtnFunc() {
        window.location.reload();
    }
</script>