<?php
session_start();
require_once '../core/config.php';
require_once '../core/db.php';
require_once '../core/global_values.php';

/* ===== ১. বর্তমান সময় ও দিন নির্ধারণ ===== */
$today_full = date('l'); // যেমন: Monday
$now_str = date('H:i:s');
$now_ts = time();
$sessionyear_param = '%' . date('y') . '%';

/* ===== ২. বর্তমানে চলমান পিরিয়ড খোঁজা ===== */
$sql_period = "
    SELECT * FROM classschedule 
    WHERE sccode = ? 
      AND sessionyear LIKE ? 
      AND ? BETWEEN timestart AND timeend 
    LIMIT 1
";

$stmt_p = $conn->prepare($sql_period);
$stmt_p->bind_param("sss", $sccode, $sessionyear_param, $now_str);
$stmt_p->execute();
$current_period = $stmt_p->get_result()->fetch_assoc();

/* ===== ৩. UI এর জন্য স্টাইল ডিজাইন (CSS) ===== */
?>

<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #4cc9f0;
        --success-color: #2ec4b6;
        --danger-color: #e71d36;
        --glass-bg: rgba(255, 255, 255, 0.95);
    }

    .live-dashboard-card {
        border-radius: 8px;
        background: var(--glass-bg);
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
    }

    /* Hero Section */
    .hero-banner {
        background: linear-gradient(135deg, #f8edfa, #e9daf5);
        border-radius: 15px;
        padding: 20px;
  
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    /* Circular Progress */
    .circle-box {
        position: relative;
        width: 80px;
        height: 80px;
    }

    .circle-box svg {
        transform: rotate(-90deg);
        width: 80px;
        height: 80px;
    }

    .circle-box circle {
        fill: none;
        stroke-width: 8;
        stroke-linecap: round;
    }

    .circle-box .bg {
        stroke: rgba(145, 17, 184, 0.5)
    }

    .circle-box .prog {
        stroke: white;
        transition: stroke-dashoffset 1s ease;
    }

    .circle-val {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-weight: bold;
        text-align: center;
        line-height: 1;
    }

    /* Class Cards */
    .class-item {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 12px;
        border-left: 4px solid var(--primary-color);
        transition: 0.3s;
    }

    .class-item:hover {
        background: #fff;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .pulse-dot {
        height: 10px;
        width: 10px;
        background-color: var(--success-color);
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 rgba(46, 204, 113, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(46, 204, 113, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(46, 204, 113, 0);
        }
    }
</style>


<div class="card live-dashboard-card overflow-hidden">
    <div class="card-body p-3">

        <?php if ($current_period):
            // Progress Calculation
            $start_ts = strtotime($current_period['timestart']);
            $end_ts = strtotime($current_period['timeend']);
            $total_sec = $end_ts - $start_ts;
            $passed_sec = time() - $start_ts;

            $percent = ($total_sec > 0) ? round(($passed_sec / $total_sec) * 100) : 0;
            $percent = max(0, min(100, $percent));
            $offset = 251.2 - ($percent / 100 * 251.2);
            $min_left = round(($end_ts - time()) / 60);

            // Fetch Active Classes
            $sql_classes = "
                SELECT r.*, s.subject AS sub_name, s.subben, t.tname
                FROM clsroutine r
                LEFT JOIN subjects s ON r.subcode = s.subcode AND r.sccode = s.sccode
                LEFT JOIN teacher t ON r.tid = t.tid AND r.sccode = t.sccode
                WHERE r.sccode = ? AND r.sessionyear = ? AND r.day = ? AND r.period = ?
                ORDER BY r.classname ASC
            ";
            $stmt_c = $conn->prepare($sql_classes);
            $stmt_c->bind_param("isss", $sccode, $sessionyear, $today_full, $current_period['period']);
            $stmt_c->execute();
            $active_classes = $stmt_c->get_result();
            ?>

            <div class="hero-banner">
                <div>
                    <span class="badge bg-white text-primary mb-1 text-uppercase">Now Running</span>
                    <h3 class="mb-0 fw-bold">Period <?= $current_period['period'] ?></h3>
                    <small class="opacity-75">
                        <i class="bi bi-clock me-1"></i>
                        <?= date("h:i A", $start_ts) ?> - <?= date("h:i A", $end_ts) ?>
                    </small>
                </div>

                <div class="circle-box">
                    <svg>
                        <circle class="bg" cx="40" cy="40" r="35"></circle>
                        <circle class="prog" cx="40" cy="40" r="35"
                            style="stroke-dasharray: 220; stroke-dashoffset: <?= (220 - (220 * $percent / 100)) ?>;">
                        </circle>
                    </svg>
                    <div class="circle-val">
                        <span class="d-block fs-4"><?= $min_left ?></span>
                        <small style="font-size: 8px;">MIN</small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                <h6 class="mb-0 fw-bold text-dark">
                    <span class="pulse-dot me-2"></span> Active Classes Now
                </h6>
                <span class="badge bg-label-primary"><?= $active_classes->num_rows ?> Section(s)</span>
            </div>

            <div class="row g-3" style="max-height: 250px; overflow-y: auto;">
                <?php if ($active_classes->num_rows > 0): ?>
                    <?php while ($row = $active_classes->fetch_assoc()): ?>
                        <div class="col-12 col-md-6">
                            <div class="class-item">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-bold text-dark">Class <?= $row['classname'] ?>
                                        (<?= $row['sectionname'] ?>)</span>
                                    <span class="text-primary small fw-bold"><?= $row['sub_name'] ?? $row['subben'] ?></span>
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <?= $row['tname'] ?? 'Assigning...' ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-3">
                        <p class="text-muted small italic">No classes found in this period</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-cup-hot display-4 text-muted opacity-25"></i>
                </div>
                <h5 class="fw-bold text-muted">Off-Period / Break Time</h5>
                <p class="text-muted small">No Classes has been assigned on this period. </p>
                <div class="badge bg-light text-dark border p-2">
                    <i class="bi bi-clock-history me-1"></i> Current Time: <?= date("h:i A") ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>