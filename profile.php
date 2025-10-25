<?php
$page_title = "User Profile";
include 'header.php';

if (!isset($_SESSION['user_email'])) {
    echo "<div class='alert alert-danger mt-4 text-center'>Please login first.</div>";
    include 'footer.php';
    exit;
}

$email = $usr;

// ======== Auto Assign Achievements ========
$result = auto_issue_achievements($conn, $email);

// ======== Total Points ========
// প্রথমে user_achievements থেকে পয়েন্ট
$stmt1 = $conn->prepare("
    SELECT SUM(a.points) AS total_points_achievements
    FROM user_achievements ua
    JOIN achievements_list a ON ua.achievement_id = a.id
    WHERE ua.email = ?
");
$stmt1->bind_param('s', $email);
$stmt1->execute();
$result1 = $stmt1->get_result()->fetch_assoc();
$total_ach_points = $result1['total_points_achievements'] ?? 0;
$stmt1->close();

// এবার user_action থেকে পয়েন্ট
$stmt2 = $conn->prepare("
    SELECT SUM(points) AS total_points_action
    FROM user_actions
    WHERE email = ?
");
$stmt2->bind_param('s', $email);
$stmt2->execute();
$result2 = $stmt2->get_result()->fetch_assoc();
$total_action_points = $result2['total_points_action'] ?? 0;
$stmt2->close();

// ✅ মোট পয়েন্ট একত্রে
$total_points = $total_ach_points + $total_action_points;

// ======== Get Titles ========
$current_title = null;
$next_title = null;
$res = $conn->query("SELECT * FROM titles_list ORDER BY min_points DESC");
while ($row = $res->fetch_assoc()) {
    $req_ach = json_decode($row['required_achievements'], true);
    if ($total_points >= $row['min_points'])
        $current_title = $row;
    elseif (!$next_title)
        $next_title = $row;
}

// ======== Achievements ========
$achievements = [];
$sql = "SELECT a.*,
            IF(ua.id IS NULL, 0, 1) AS earned,
            CASE
                WHEN a.code = 'feedback_5' THEN LEAST(100, ROUND(COALESCE(f.feedback_cnt,0) * 20))
                WHEN a.code = 'poll_voter' THEN LEAST(100, ROUND(COALESCE(p.poll_cnt,0) * 10))
                ELSE 0
            END AS progress
        FROM achievements_list a
        LEFT JOIN user_achievements ua 
            ON a.id = ua.achievement_id AND ua.email = ?
        LEFT JOIN (
            SELECT email, COUNT(*) AS feedback_cnt
            FROM feedbacks
            WHERE email = ?
            GROUP BY email
        ) f ON f.email = ?
        LEFT JOIN (
            SELECT email, COUNT(*) AS poll_cnt
            FROM user_actions
            WHERE email = ? AND page = 'poll'
            GROUP BY email
        ) p ON p.email = ?
        ORDER BY a.category, a.points ASC
        ";

$stmt = $conn->prepare($sql);
$stmt->bind_param('sssss', $email, $email, $email, $email, $email);
$stmt->execute();
$res = $stmt->get_result();

$achievements = [];
while ($row = $res->fetch_assoc()) {
    // SQL-এ LEAST/ROUND করা আছে; এখানেও নিশ্চিত করা যাবে (redundant safety)
    $row['progress'] = min(100, round($row['progress']));
    $achievements[] = $row;
}
$stmt->close();
// ======== Recent Actions & Feedbacks ========
$recentActions = $conn->query("SELECT * FROM user_actions WHERE email='" . $conn->real_escape_string($email) . "' ORDER BY timestamp DESC LIMIT 10");
$recentFeedbacks = $conn->query("SELECT * FROM feedbacks WHERE email='" . $conn->real_escape_string($email) . "' ORDER BY created_at DESC LIMIT 10");
?>

<div class="container mt-4">
    <div class="row">
        <!-- ===== Left Column ===== -->
        <div class="col-md-4">
            <div class="card mb-3 shadow-sm">
                <div class="card-body text-center">
                    <h5><?= htmlspecialchars($_SESSION['name'] ?? $email) ?></h5>
                    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
                    <p><strong>Current Title:</strong> <?= htmlspecialchars($current_title['title_name'] ?? 'N/A') ?>
                    </p>
                    <p><strong>Total Points:</strong> <?= $total_points ?></p>
                </div>
            </div>
        </div>

        <!-- ===== Right Column ===== -->
        <div class="col-md-8">
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h6>Achievements Dashboard</h6>

                    <?php
                    echo '<pre>';
                    print_r($achievements);
                    echo '</pre>';
                    ?>
                    <div class="row" id="achievementContainer">
                        <?php foreach ($achievements as $a): ?>
                            <div class="col-md-4 col-6 mb-4 text-center achievement-card"
                                data-name="<?= htmlspecialchars($a['name']) ?>"
                                data-desc="<?= htmlspecialchars($a['description']) ?>" data-points="<?= $a['points'] ?>"
                                data-status="<?= $a['earned'] ? 'Earned' : 'Incomplete' ?>"
                                data-progress="<?= $a['progress'] ?>">
                                <div class="circular-progress <?= $a['earned'] ? 'earned' : 'incomplete' ?>"
                                    data-progress="<?= $a['progress'] ?>">
                                    <span><?= $a['progress'] ?>%</span>
                                </div>
                                <p class="mt-2 mb-0 fw-bold"><?= htmlspecialchars($a['name']) ?></p>
                                <small class="text-muted"><?= $a['points'] ?> pts</small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ===== Recent Actions ===== -->
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h6>Recent Actions</h6>
                    <ul class="list-group list-group-flush">
                        <?php while ($a = $recentActions->fetch_assoc()): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($a['page'] ?? $a['action']) ?> - <?= htmlspecialchars($a['points']) ?>
                                pts
                                <br><small><?= $a['timestamp'] ?></small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <!-- ===== Recent Feedbacks ===== -->
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h6>Recent Feedbacks</h6>
                    <ul class="list-group list-group-flush">
                        <?php while ($f = $recentFeedbacks->fetch_assoc()): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($f['feedback']) ?>
                                <br><small>Rating: <?= $f['rating'] ?> | <?= $f['created_at'] ?></small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== Achievement Modal ===== -->
<div class="modal fade" id="achievementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Achievement Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="ach-desc"></p>
                <p><strong>Points:</strong> <span id="ach-points"></span></p>
                <p><strong>Status:</strong> <span id="ach-status"></span></p>
                <div class="progress">
                    <div id="ach-progress" class="progress-bar bg-info" style="width:0%">0%</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .circular-progress {
        position: relative;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: conic-gradient(var(--progress-color, #0d6efd) calc(var(--value) * 1%), #e9ecef 0);
        transition: background 0.6s ease;
    }

    .circular-progress span {
        position: absolute;
        font-weight: bold;
        font-size: 0.9rem;
    }

    .circular-progress.earned {
        --progress-color: #28a745;
    }

    .circular-progress.incomplete {
        --progress-color: #6c757d;
    }
</style>

<script>
    document.querySelectorAll('.circular-progress').forEach(function (el) {
        const val = el.getAttribute('data-progress');
        el.style.setProperty('--value', val);
    });

    // Modal handler
    document.querySelectorAll('.achievement-card').forEach(function (card) {
        card.addEventListener('click', function () {
            const name = this.dataset.name;
            const desc = this.dataset.desc;
            const points = this.dataset.points;
            const status = this.dataset.status;
            const progress = this.dataset.progress;

            document.querySelector('#achievementModal .modal-title').textContent = name;
            document.getElementById('ach-desc').textContent = desc;
            document.getElementById('ach-points').textContent = points;
            document.getElementById('ach-status').textContent = status;

            const bar = document.getElementById('ach-progress');
            bar.style.width = progress + '%';
            bar.textContent = progress + '%';

            const modal = new bootstrap.Modal(document.getElementById('achievementModal'), { backdrop: true });
            modal.show();

            // Fix backdrop lock issue
            document.getElementById('achievementModal').addEventListener('hidden.bs.modal', function () {
                document.body.classList.remove('modal-open');
                document.querySelectorAll('.modal-backdrop').forEach(e => e.remove());
            });
        });
    });
</script>

<?php include 'footer.php'; ?>
</body>

</html>