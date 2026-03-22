<?php require_once 'header.php'; ?>

<?php
$labels = [];
$threads = [];
$maxUsed = [];
$maxLimit = [];

$q = $conn->query("
    SELECT created_at, threads_connected, max_used_connections, max_connections
    FROM (
        SELECT created_at, threads_connected, max_used_connections, max_connections
        FROM connection_log
        ORDER BY created_at DESC
        LIMIT 1500
    ) t
    ORDER BY created_at ASC
");

$timestamps = [];
while ($row = $q->fetch_assoc()) {
    $labels[] = date('H:i', strtotime($row['created_at']));
    $threads[] = (int) $row['threads_connected'];
    $timestamps[] = $row['created_at'];
    $maxUsed[] = (int) $row['max_used_connections'];
    $maxLimit[] = (int) $row['max_connections'];
}
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card p-2">
        
        
        <div class="card-header d-flex">
<h3 class="flex-grow-1">MySQL Connection Monitor</h3>

        <div >
        <!-- <label for="adj" class="form-label">Time Adjust (min)</label>    -->
        <select id="adj" class="select-control ">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="20">20</option>
            <option value="30" selected>30</option>
            <option value="45">45</option>
            <option value="60">60</option>
            <option value="90">90</option>
            <option value="120">120</option>
            <option value="180">180</option>
            <option value="240">240</option>
            <option value="300">300</option>
        </select> 
        </div>
        </div>

        <canvas id="connChart" height="200"></canvas>
    </div>


</div>


<div class="modal fade" id="logModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Connection Logs</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="logFrame" style="width:100%; height:70vh; border:0;"></iframe>
            </div>
        </div>
    </div>
</div>


<?php

require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>

<script>
    const timestamps = <?= json_encode($timestamps) ?>;



    const chart = new Chart(document.getElementById('connChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Open Connections',
                    data: <?= json_encode($threads) ?>,
                    tension: .3,
                    pointRadius: 1,
                    pointHoverRadius: 5,
                    pointHitRadius: 12
                },
                {
                    label: 'Peak Used',
                    data: <?= json_encode($maxUsed) ?>,
                    tension: .3,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                    pointHitRadius: 12
                },
                {
                    label: 'Max Limit',
                    data: <?= json_encode($maxLimit) ?>,
                    tension: .3,
                    pointRadius: 2,
                    pointHoverRadius: 5,
                    pointHitRadius: 12
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'nearest',
                intersect: true   // ← point এ direct click
            },
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            },
            onClick: function (evt, elements) {
                if (!elements.length) return;

                const idx = elements[0].index;
                const ts = timestamps[idx];

                const adj = parseInt($('#adj').val(), 10) || 0;
                const d = new Date(ts);
                const offset = 6 * 60 * 60 * 1000; // 6 hours in ms
                const from = new Date(d.getTime() - adj * 60000 + offset);
                const to = new Date(d.getTime() + adj * 60000 + offset);
                console.log(from, to);


                const fmt = d => d.toISOString().slice(0, 19).replace('T', ' ');

                const url = 'developer/connection-log-view.php?from=' +
                    encodeURIComponent(fmt(from)) +
                    '&to=' +
                    encodeURIComponent(fmt(to));

                document.getElementById('logFrame').src = url;

                new bootstrap.Modal(document.getElementById('logModal')).show();
            }
        }
    });


    document.getElementById('logModal')
        .addEventListener('hidden.bs.modal', function () {
            document.getElementById('logFrame').src = '';
        });
</script>

<!-- ----------------------------------- -->
</body>

</html>