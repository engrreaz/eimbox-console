<?php
$page_title = "Poll Results & Analytics";
include 'includes/header.php';

// শুধুমাত্র অ্যাডমিন
if (!isset($_SESSION['user_id']) || $is_admin === 0) {
    echo "<div class='alert alert-danger text-center mt-4'>Access denied. Admins only.</div>";
    include 'includes/footer.php';
    exit;
}

// সব পোল লোড
$pollsRes = $conn->query("SELECT * FROM polls ORDER BY created_at DESC");
?>

<div class="container my-4">
    <h2 class="mb-4 text-center">📊 Poll Results & Analytics</h2>

    <?php while ($poll = $pollsRes->fetch_assoc()): 
        $optionsRes = $conn->query("SELECT * FROM poll_options WHERE poll_id={$poll['id']}");
        $votesRes = $conn->query("SELECT option_id, COUNT(*) as count FROM poll_votes WHERE poll_id={$poll['id']} GROUP BY option_id");

        // Options array
        $options = [];
        while ($opt = $optionsRes->fetch_assoc()) {
            $options[$opt['id']] = $opt['option_text'];
        }

        // Votes array
        $votes = [];
        while ($v = $votesRes->fetch_assoc()) {
            $votes[$v['option_id']] = $v['count'];
        }

        // Prepare chart data
        $labels = [];
        $data = [];
        foreach ($options as $id => $text) {
            $labels[] = $text;
            $data[] = isset($votes[$id]) ? $votes[$id] : 0;
        }
    ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5><?= htmlspecialchars($poll['question']) ?></h5>
                <canvas id="chart<?= $poll['id'] ?>" height="150"></canvas>
            </div>
        </div>

        <script>
            const ctx<?= $poll['id'] ?> = document.getElementById('chart<?= $poll['id'] ?>').getContext('2d');
            new Chart(ctx<?= $poll['id'] ?>, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        label: 'Votes',
                        data: <?= json_encode($data) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            precision:0
                        }
                    }
                }
            });
        </script>
    <?php endwhile; ?>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php include 'includes/footer.php'; ?>
