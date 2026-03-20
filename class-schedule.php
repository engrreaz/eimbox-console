<?php
require_once 'header.php';

// প্যারামিটার গ্রহণ
$sessionyear = $_COOKIE['chain-session'] ?? $_GET['session'] ?? date('Y');
$slots = $_COOKIE['chain-slot'] ?? $_GET['slot'] ?? 'School';

// ১. ডাটাবেজ অ্যাকশন হ্যান্ডলিং (Add/Update/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_schedule'])) {
    $id = $_POST['schedule_id'] ?? null;
    $period = $_POST['period'];
    $t_start = $_POST['timestart'];
    $t_end = $_POST['timeend'];
    $shift = $_POST['shift'] ?? '';

    // ডিউরেশন ক্যালকুলেশন (সেকেন্ডে)
    $duration = (strtotime($t_end) - strtotime($t_start)) / 60;
    $now = date('Y-m-d H:i:s');

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE classschedule SET period=?, timestart=?, timeend=?, duration=?, shift=?, modifieddate=? WHERE id=? AND sccode=?");
        $stmt->bind_param("isssisii", $period, $t_start, $t_end, $duration, $shift, $now, $id, $sccode);
        $msg = 'Updated Successfully';
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO classschedule (sccode, sessionyear, slots, shift, period, timestart, timeend, duration, modifieddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssissis", $sccode, $sessionyear, $slots, $shift, $period, $t_start, $t_end, $duration, $now);
        $msg = 'Inserted Successfully';
    }

    if ($stmt->execute()) {
        // header("Location: class-schedule.php?session=$sessionyear&slot=$slots&msg=success");
        // exit();
        $alert = 'success';
    }
}

// ডিলিট লজিক
if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $conn->query("DELETE FROM classschedule WHERE id = '$del_id' AND sccode = '$sccode'");
    // header("Location: class-schedule.php?session=$sessionyear&slot=$slots&msg=deleted");
    // exit();
    $msg = 'Deleted Successfully';
    $alert = 'danger';
}

// ২. ডাটা ফেচিং
$sql = "SELECT * FROM classschedule WHERE sccode = '$sccode' AND sessionyear = '$sessionyear' AND slots = '$slots' ORDER BY period ASC";
$result = $conn->query($sql);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <?php if ($alert) {
        ?>
        <div class="alert alert-<?= $alert ?>"><?= $msg ?></div>
        <?php
    } ?>



    <?php
    $chain_param = '-c 12 -t Choose Values -u -r -b View Schedule -h class';
    include 'components/slot-tree-ui.php';
    ?>



    <?php
    // ১. টাইমলাইনের শুরু এবং শেষ বের করা
    $bound_sql = "SELECT MIN(timestart) as first_start, MAX(timeend) as last_end FROM classschedule 
              WHERE sccode = '$sccode' AND sessionyear = '$sessionyear' AND slots = '$slots'";
    $bound_res = $conn->query($bound_sql);
    $bounds = $bound_res->fetch_assoc();

    $global_start = strtotime($bounds['first_start']);
    $global_end = strtotime($bounds['last_end']);
    $total_secs = $global_end - $global_start;

    // ২. ডাটা ফেচ করা
    $result_bar = $conn->query($sql);
    $periods = [];
    while ($r = $result_bar->fetch_assoc()) {
        $periods[] = $r;
    }

    $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
    ?>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Live Class Timeline</h6>
            <span id="live-clock" class="badge bg-dark"></span>
        </div>
        <div class="card-body">
            <div class="position-relative" style="height: 20px;">

                <div class="progress-stacked"
                    style="height: 100%; border-radius: 8px; overflow: hidden; background: #f1f1f1;">
                    <?php
                    $last_time = $global_start;
                    foreach ($periods as $index => $row):
                        $p_start = strtotime($row['timestart']);
                        $p_end = strtotime($row['timeend']);

                        // ১. গ্যাপ চেক করা (যদি আগের ক্লাসের শেষ আর বর্তমান ক্লাসের শুরুর মাঝে সময় থাকে)
                        if ($p_start > $last_time):
                            $gap_width = (($p_start - $last_time) / $total_secs) * 100;
                            ?>
                            <div class="progress" style="width: <?= $gap_width ?>%">
                                <div class="progress-bar bg-light text-dark"
                                    style="background-image: linear-gradient(45deg, rgba(0,0,0,.05) 25%, transparent 25%, transparent 50%, rgba(0,0,0,.05) 50%, rgba(0,0,0,.05) 75%, transparent 75%, transparent); background-size: 10px 10px; height:20px;">
                                    <small style="font-size: 9px;">Break</small>
                                </div>
                            </div>
                            <?php
                        endif;

                        // ২. পিরিয়ড সেগমেন্ট
                        $p_width = (($p_end - $p_start) / $total_secs) * 100;
                        $color = $colors[$index % count($colors)];
                        ?>
                        <div class="progress" style="width: <?= $p_width ?>%">
                            <div style="height:20px;"
                                class="progress-bar <?= $color ?> border-end border-white border-1 d-flex flex-column justify-content-center">
                                <span class="fw-bold" style="font-size: 11px; "><?= $row['period'] ?></span>
                            </div>
                        </div>
                        <?php
                        $last_time = $p_end;
                    endforeach;
                    ?>
                </div>

                <div id="time-indicator" class="position-absolute shadow-sm"
                    style=" width: 3px; background: #ff0000; z-index: 10; display: none; transition: left 1s linear;">
                    <div
                        style="width: 10px; height: 20px; background: #ff0000; border-radius: 50%; position: absolute;  left: -3.5px;">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-2 text-muted" style="font-size: 10px;">
                <span><?= date('h:i A', $global_start) ?></span>
                <span class="fw-bold text-primary">Schedule Progress View</span>
                <span><?= date('h:i A', $global_end) ?></span>
            </div>
        </div>
    </div>


    <div class="card border-0 shadow-sm">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 80px;">Period</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Duration</th>
                        <th>Shift</th>
                        <th class="text-center">
                            <button class="btn btn-primary btn-sm" onclick="openScheduleModal()">
                                <i class="bi bi-plus-lg me-1"></i> Add New
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge bg-label-primary fs-6"><?= $row['period'] ?></span></td>
                                <td class="fw-bold text-success"><?= date('h:i A', strtotime($row['timestart'])) ?></td>
                                <td class="fw-bold text-danger"><?= date('h:i A', strtotime($row['timeend'])) ?></td>
                                <td><?= ($row['duration']) ?> Mins</td>
                                <td><?= $row['shift'] ?: 'N/A' ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-icon btn-outline-primary me-1"
                                        onclick='editSchedule(<?= json_encode($row) ?>)'>
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?delete=<?= $row['id'] ?>&session=<?= $sessionyear ?>&slot=<?= $slots ?>"
                                        class="btn btn-sm btn-icon btn-outline-danger"
                                        onclick="return confirm('Are you sure to delete this period?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No schedule found for this criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header ">
                <h5 class="modal-title fw-bold" id="modalTitle">Manage Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body row">
                    <input type="hidden" name="schedule_id" id="schedule_id">

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Period Number</label>


                        <select class="form-select form-select-sm" name="period" id="m_period" required>
                            <option value="0">Break</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                        </select>
                    </div>



                    <div class="mb-0 col-md-6">
                        <label class="form-label">Shift (Optional)</label>
                        <select name="shift" id="m_shift" class="form-select">
                            <option value="">Default</option>
                            <option value="Morning">Morning</option>
                            <option value="Day">Day</option>
                        </select>
                    </div>

                    <div class="mb-3  col-md-6">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="timestart" id="m_start" class="form-control" required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">End Time</label>
                        <input type="time" name="timeend" id="m_end" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_schedule" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    const sModal = new bootstrap.Modal(document.getElementById('scheduleModal'));

    function chainBtnFunc() {
        window.location.reload();
    }

    function openScheduleModal() {
        document.getElementById('modalTitle').innerText = "Add New Period";
        document.getElementById('schedule_id').value = "";
        document.getElementById('m_period').value = "";
        document.getElementById('m_start').value = "";
        document.getElementById('m_end').value = "";
        sModal.show();
    }

    function editSchedule(data) {
        document.getElementById('modalTitle').innerText = "Edit Period " + data.period;
        document.getElementById('schedule_id').value = data.id;
        document.getElementById('m_period').value = data.period;
        document.getElementById('m_start').value = data.timestart;
        document.getElementById('m_end').value = data.timeend;
        document.getElementById('m_shift').value = data.shift;
        sModal.show();
    }
</script>
<script>
    // টুলটিপ ইনিশিয়ালাইজ করা
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })


</script>


<script>
    $(document).ready(function () {
        // যদি URL-এ delete বা msg প্যারামিটার থাকে
        if (window.location.href.includes('delete') || window.location.href.includes('msg')) {
            // নতুন URL তৈরি করা (প্যারামিটার ছাড়া)
            var clean_url = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({}, document.title, clean_url);
        }
    });
</script>
</body>

</html>