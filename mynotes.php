<?php require_once 'header.php'; ?>

<?php
$file = "developer/data.txt";
$fileValue = 0;
$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (isset($lines[0])) {
    $fileValue = (float) explode("//", $lines[0])[0];
}
?>
<?php
if (isset($_POST['save'])) {
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $script = mysqli_real_escape_string($conn, $_POST['script']);
    $issue = mysqli_real_escape_string($conn, $_POST['issue']);
    $priority = (int) $_POST['priority'];
    $imp = (int) $_POST['importance'];
    $time = (int) $_POST['time_estimate'];

    mysqli_query($conn, "INSERT INTO mynotes(unit,script,issue,priority,importance,time_estimate,date_created) 
    VALUES('$unit','$script','$issue','$priority','$imp','$time',NOW())");

    // echo "<script>location.reload()</script>";
}
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    // ইউনিট
    $units = [];
    $res = mysqli_query($conn, "SELECT DISTINCT unit FROM mynotes ORDER BY unit ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $units[] = $row['unit'];
    }

    $selected_unit = $_GET['unit'] ?? '';
    $where_unit = $selected_unit ? "AND m.unit='" . mysqli_real_escape_string($conn, $selected_unit) . "'" : "";

    // latest status
    $query = "
            SELECT m.*, 
                COALESCE(mt.status, m.status) as current_status
            FROM mynotes m
            LEFT JOIN (
                SELECT t1.*
                FROM mynotes_track t1
                INNER JOIN (
                    SELECT note_id, MAX(submit_at) as max_date
                    FROM mynotes_track
                    GROUP BY note_id
                ) t2 ON t1.note_id = t2.note_id AND t1.submit_at = t2.max_date
            ) mt ON mt.note_id = m.id
            WHERE 1 $where_unit
            ORDER BY m.priority ASC
            ";

    $result = mysqli_query($conn, $query);

    $data = [];
    $statusCount = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $data[$row['script']][] = $row;

        $s = $row['current_status'];
        $statusCount[$s] = ($statusCount[$s] ?? 0) + 1;
    }
    ?>

    <!-- 🔹 DASHBOARD -->
    <div class="mb-3">
        <?php foreach ($statusCount as $k => $v): ?>
            <span class="badge bg-primary"><?= $k ?>: <?= $v ?></span>
        <?php endforeach; ?>
    </div>

    <!-- 🔹 SEARCH -->
    <input type="text" id="searchBox" class="form-control mb-3" placeholder="Search issue...">

    <!-- 🔹 UNIT FILTER -->
    <div class="d-flex gap-3">
        <div class="mb-3 flex-grow-1">
            <a href="?" class="btn btn-sm btn-outline-primary <?= $selected_unit == '' ? 'active' : '' ?>">All</a>
            <?php foreach ($units as $u): ?>
                <a href="?unit=<?= urlencode($u) ?>"
                    class="btn btn-sm btn-outline-primary <?= $selected_unit == $u ? 'active' : '' ?>">
                    <?= htmlspecialchars($u) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div id="time_count" class="small text-center"></div>
        <!-- 🔹 ADD BUTTON -->
        <button class="btn btn-primary mb-3 float-end py-1" data-bs-toggle="modal" data-bs-target="#addModal">
            + Add Issue
        </button>
    </div>

    <div id="progress" class="mt-1 mb-3"></div>


    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0 table-sm">
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Unit</th>
                        <th>Script</th>
                        <th>Issue</th>
                        <th>Imp</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody class="sortable">

                    <?php

                    $total_time = 0;

                    $weights = [
                        'Open' => 1,
                        'Progress' => 0.8,
                        'Local Tested' => 0.3,
                        'Server Tested' => 0,
                        'Bug Found' => 2
                    ];

                    // 🔥 FLAT LOOP (no grouping)
                    foreach ($data as $script => $rows):
                        foreach ($rows as $r):

                            $color = '';
                            if ($r['current_status'] == 'Progress')
                                $color = 'table-warning';
                            elseif ($r['current_status'] == 'Bug')
                                $color = 'table-danger';
                            elseif ($r['current_status'] == 'Server Tested')
                                $color = 'table-success';
                            elseif ($r['current_status'] == 'Local Tested')
                                $color = 'table-info';
                            ?>

                            <tr class="issueRow <?= $color ?>" data-id="<?= $r['id'] ?>">
                                <td class="editable" data-field="priority"><?= $r['priority'] ?></td>

                                <td><?= htmlspecialchars($r['unit']) ?></td>
                                <td><?= htmlspecialchars($r['script']) ?></td>
                                <td class="editable" data-field="issue"><?= htmlspecialchars($r['issue']) ?></td>
                                <td class="editable-star" data-field="importance" data-value="<?= $r['importance'] ?>"
                                    style="color:orange;">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $r['importance'] ? '★' : '☆';
                                    }

                                    $w = $weights[$r['current_status']] ?? 1;
                                    $total_time += $r['time_estimate'] * $w;


                                    ?>
                                </td>
                                <td class="editable" data-field="time_estimate"><?= $r['time_estimate'] ?></td>
                                <td class="py-1">
                                    <select class="form-select form-select-sm statusChange my-0" data-id="<?= $r['id'] ?>">
                                        <?php
                                        $statuses = ['Open', 'Progress', 'Local Tested', 'Server Tested', 'Bug Found'];
                                        foreach ($statuses as $s):
                                            ?>
                                            <option <?= $r['current_status'] == $s ? 'selected' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>

                            <?php


                        endforeach;
                    endforeach;
                    ?>

                </tbody>
            </table>
        </div>
    </div>



</div>

<!-- 🔹 MODAL -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5>Add Issue</h5>
                </div>
                <div class="modal-body ">
                    <div class="row">
                        <div class="col-6">
                            <input name="unit" class="form-control form-control-sm mb-2 col-6" placeholder="Unit"
                                list="unitList" required>
                            <datalist id="unitList">
                                <?php
                                $res = mysqli_query($conn, "SELECT DISTINCT unit FROM mynotes");
                                while ($r = mysqli_fetch_assoc($res)) {
                                    echo "<option value='{$r['unit']}'>";
                                }
                                ?>
                            </datalist>
                        </div>
                        <div class="col-6">
                            <input name="script" class="form-control form-control-sm mb-2 col-6" placeholder="Script"
                                list="scriptList" required>
                            <datalist id="scriptList">
                                <?php
                                $res = mysqli_query($conn, "SELECT DISTINCT script FROM mynotes");
                                while ($r = mysqli_fetch_assoc($res)) {
                                    echo "<option value='{$r['script']}'>";
                                }
                                ?>
                            </datalist>
                        </div>


                    </div>
                    <div class="row">
                        <div class="col-12">
                            <input name="issue" class="form-control form-control-sm mb-2 col-12" placeholder="Issue" required>

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-4">
                            <input name="priority" type="number" class="form-control form-control-sm col-4" value="99"
                                placeholder="Priority">
                        </div>


                        <div class="col-4 mb-2">
                            <label class="form-label py-0 my-0 fs-tiny">Importance</label>

                            <div id="starRating" class="d-flex gap-1"
                                style="font-size:24px; cursor:pointer; color: teal;">
                                <span data-val="1">☆</span>
                                <span data-val="2">☆</span>
                                <span data-val="3">☆</span>
                                <span data-val="4">☆</span>
                                <span data-val="5">☆</span>
                            </div>

                            <input type="hidden" name="importance" id="importanceInput">


                        </div>

                        <div class="col-4">
                            <input name="time_estimate" type="number" class="form-control form-control-sm col-4"
                                placeholder="time_estimate">
                        </div>



                    </div>


                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" name="save">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
if ($total_time > $fileValue) {
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    $lines[0] = $total_time . ' // Total Estimated time for development..........';
    file_put_contents($file, implode(PHP_EOL, $lines));
}
?>


<?php require_once 'footer.php'; ?>


<script>
    let totalTime = <?= $total_time ?>;
    let fileValue = <?= $fileValue ?>;

    function renderProgress(totalTime, fileValue) {
        let percent = 0;

        if (fileValue > 0) {
            percent = (totalTime / fileValue) * 100;
            if (percent > 100) percent = 100;
        }

        let color = 'bg-info';

        if (percent >= 100) color = 'bg-success';
        else if (percent >= 80) color = 'bg-warning';
        else if (percent >= 50) color = 'bg-primary';
        else color = 'bg-info';

        document.getElementById('progress').innerHTML = `
        <div class="progress" style="height: 25px; border-radius:20px;">
            <div class="progress-bar ${color}" 
                role="progressbar"
                style="width: ${percent}%">
                ${percent.toFixed(2)}%
            </div>
        </div>
        <div class="small mt-1" hidden>
            ${totalTime.toFixed(2)} / ${fileValue.toFixed(2)} mins
        </div>
    `;
    }

    // প্রথমবার render
    renderProgress(totalTime, fileValue);
</script>


<script>



    document.getElementById('time_count').innerHTML = '<b><?= round($total_time, 2) ?> Mins</b><br>Required';



    // 🔍 LIVE SEARCH
    document.getElementById('searchBox').addEventListener('keyup', function () {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.issueRow').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
        });
    });

    // 🔄 STATUS UPDATE (AJAX)
    document.querySelectorAll('.statusChange').forEach(el => {
        el.addEventListener('change', function () {
            let id = this.dataset.id;
            let status = this.value;

            fetch('mynotes/update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id + '&status=' + status
            });
        });
    });

    // 🔁 AUTO REFRESH (10 sec)
    setInterval(() => {
        fetch(location.href)
            .then(res => res.text())
            .then(html => {
                let doc = new DOMParser().parseFromString(html, 'text/html');
                document.querySelector('.container-xxl').innerHTML = doc.querySelector('.container-xxl').innerHTML;
            });
    }, 10000);

</script>

<script>
    // Drag & Drop init
    document.querySelectorAll('.sortable').forEach(tbody => {
        new Sortable(tbody, {
            animation: 150,
            handle: 'td',
            onEnd: function () {
                let order = [];

                tbody.querySelectorAll('tr').forEach((row, index) => {
                    let id = row.dataset.id;
                    if (id) {
                        order.push({
                            id: id,
                            priority: index + 1
                        });
                    }
                });

                fetch('mynotes/update_priority.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order: order })
                });
            }
        });
    });
</script>
<script>
    let stars = document.querySelectorAll('#starRating span');
    let input = document.getElementById('importanceInput');

    stars.forEach(star => {
        star.addEventListener('click', function () {
            let val = this.dataset.val;
            input.value = val;

            stars.forEach(s => {
                s.textContent = s.dataset.val <= val ? '★' : '☆';
            });
        });
    });
</script>


<script>

    // normal text edit
    document.querySelectorAll('.editable').forEach(td => {
        td.addEventListener('click', function () {

            if (this.querySelector('input')) return;

            let old = this.innerText;
            let field = this.dataset.field;
            let tr = this.closest('tr');
            let id = tr.dataset.id;

            this.innerHTML = `<input type="text" value="${old}" class="form-control form-control-sm">`;

            let input = this.querySelector('input');
            input.focus();

            input.addEventListener('blur', save);
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') save();
            });

            function save() {
                let val = input.value;

                fetch('mynotes/update_inline.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&field=${field}&value=${val}`
                }).then(() => {
                    td.innerText = val;
                });
            }

        });
    });


    // ⭐ star edit
    document.querySelectorAll('.editable-star').forEach(td => {
        td.addEventListener('click', function () {

            let field = this.dataset.field;
            let tr = this.closest('tr');
            let id = tr.dataset.id;

            let html = '';
            for (let i = 1; i <= 5; i++) {
                html += `<span data-val="${i}" style="cursor:pointer;">☆</span>`;
            }

            this.innerHTML = html;

            this.querySelectorAll('span').forEach(star => {
                star.addEventListener('click', () => {
                    let val = star.dataset.val;

                    fetch('mynotes/update_inline.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${id}&field=${field}&value=${val}`
                    }).then(() => {
                        let out = '';
                        for (let i = 1; i <= 5; i++) {
                            out += i <= val ? '★' : '☆';
                        }
                        td.innerHTML = out;
                    });
                });
            });

        });
    });

</script>

</body>

</html>