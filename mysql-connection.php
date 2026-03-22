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
    const ctx = document.getElementById('connChart');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Open Connections',
                    data: <?= json_encode($threads) ?>,
                    tension: .3
                },
                {
                    label: 'Peak Used',
                    data: <?= json_encode($maxUsed) ?>,
                    tension: .3
                },
                {
                    label: 'Max Limit',
                    data: <?= json_encode($maxLimit) ?>,
                    tension: .3
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
</script>

<!-- ----------------------------------- -->
</body>

</html>