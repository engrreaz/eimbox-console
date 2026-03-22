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

while ($row = $q->fetch_assoc()) {
    $labels[] = date('H:i', strtotime($row['created_at']));
    $threads[] = (int) $row['threads_connected'];
    $maxUsed[] = (int) $row['max_used_connections'];
    $maxLimit[] = (int) $row['max_connections'];
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h3>MySQL Connection Monitor</h3>
    <div class="card p-2">

        <canvas id="connChart" height="200"></canvas>
    </div>


</div>

<?php

include_once('core/upgrade-plan.php');

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
                    pointRadius: 2,
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
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            },
            onClick: function (evt, elements) {
                if (!elements.length) return;

                const idx = elements[0].index;
                const ts = timestamps[idx];

                // ±2 মিনিট range
                const d = new Date(ts);
                const from = new Date(d.getTime() - 2 * 60000);
                const to = new Date(d.getTime() + 2 * 60000);

                const fmt = d => d.toISOString().slice(0, 19).replace('T', ' ');

                const url = 'developer/connection-log-view.php?from=' +
                    encodeURIComponent(fmt(from)) +
                    '&to=' +
                    encodeURIComponent(fmt(to));

                window.open(url, '_blank');
            }
        }
    });
</script>

<!-- ----------------------------------- -->
</body>

</html>