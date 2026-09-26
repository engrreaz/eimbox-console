<?php
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/header.php';

$log_file = __DIR__ . '/cron_status.json';
$has_log = file_exists($log_file);
$status_data = $has_log ? json_decode(file_get_contents($log_file), true) : null;

$fine_log_file = __DIR__ . '/fine_cron_status.json';
$has_fine_log = file_exists($fine_log_file);
$fine_status_data = $has_fine_log ? json_decode(file_get_contents($fine_log_file), true) : null;

// Check queue counts in database
$q_queued = $conn->query("SELECT COUNT(*) as c FROM sms WHERE sccode='$sccode' AND status='queued'");
$c_queued = $q_queued ? $q_queued->fetch_assoc()['c'] : 0;

$q_sent = $conn->query("SELECT COUNT(*) as c FROM sms WHERE sccode='$sccode' AND status='sent' AND date=CURDATE()");
$c_sent = $q_sent ? $q_sent->fetch_assoc()['c'] : 0;

$q_failed = $conn->query("SELECT COUNT(*) as c FROM sms WHERE sccode='$sccode' AND status='failed' AND date=CURDATE()");
$c_failed = $q_failed ? $q_failed->fetch_assoc()['c'] : 0;

// Check Fine logs for today
$q_fine_today = $conn->query("SELECT COUNT(*) as c, COALESCE(SUM(fine_rate), 0) as amt FROM student_fine_logs WHERE sccode='$sccode' AND fine_date=CURDATE() AND status='posted'");
$f_today_row = $q_fine_today ? $q_fine_today->fetch_assoc() : ['c' => 0, 'amt' => 0];
$c_fine_today = $f_today_row['c'] ?? 0;
$amt_fine_today = $f_today_row['amt'] ?? 0;
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-activity text-primary me-2"></i> Cron Job & Queue Engine Diagnostics</h4>
            <span class="text-muted">Monitor background workers, scheduled crons, and queue status</span>
        </div>
        <div>
            <a href="messaging-send.php" class="btn btn-primary me-2"><i class="bi bi-send me-1"></i> Send SMS</a>
            <a href="sms-gateway.php" class="btn btn-outline-secondary"><i class="bi bi-gear me-1"></i> Gateway Settings</a>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Cron Job Health</h6>
                    <?php if ($status_data && (time() - $status_data['timestamp']) <= 180): ?>
                        <div class="d-flex align-items-center text-success mb-2">
                            <i class="bi bi-check-circle-fill fs-3 me-2"></i>
                            <div>
                                <h5 class="mb-0 text-success fw-bold">Active & Running</h5>
                                <small class="text-muted">Last run: <?= $status_data['last_run_time'] ?></small>
                            </div>
                        </div>
                    <?php elseif ($status_data): ?>
                        <div class="d-flex align-items-center text-warning mb-2">
                            <i class="bi bi-exclamation-triangle-fill fs-3 me-2"></i>
                            <div>
                                <h5 class="mb-0 text-warning fw-bold">Idle / Stalled</h5>
                                <small class="text-muted">Last run: <?= $status_data['last_run_time'] ?></small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center text-danger mb-2">
                            <i class="bi bi-x-circle-fill fs-3 me-2"></i>
                            <div>
                                <h5 class="mb-0 text-danger fw-bold">Not Configured</h5>
                                <small class="text-muted">No cron execution detected yet</small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-3">Live SMS Queue Status (Today)</h6>
                    <div class="row text-center g-2">
                        <div class="col-4">
                            <div class="p-2 border rounded bg-warning bg-opacity-10">
                                <span class="text-muted small">Queued (Pending)</span>
                                <h4 class="mb-0 text-warning fw-bold"><?= $c_queued ?></h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-success bg-opacity-10">
                                <span class="text-muted small">Sent Successfully</span>
                                <h4 class="mb-0 text-success fw-bold"><?= $c_sent ?></h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-danger bg-opacity-10">
                                <span class="text-muted small">Failed / Error</span>
                                <h4 class="mb-0 text-danger fw-bold"><?= $c_failed ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards Row 2: Daily Fine Auto-Poster -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Fine Auto-Poster Health</h6>
                    <?php if ($fine_status_data && (time() - $fine_status_data['timestamp']) <= 86400): ?>
                        <div class="d-flex align-items-center text-success mb-2">
                            <i class="bi bi-clock-history fs-3 me-2"></i>
                            <div>
                                <h5 class="mb-0 text-success fw-bold">Active Runner</h5>
                                <small class="text-muted">Last run: <?= $fine_status_data['last_run_time'] ?></small>
                            </div>
                        </div>
                        <div class="small text-muted">Institutions checked: <?= $fine_status_data['processed_institutions'] ?? 0 ?></div>
                    <?php elseif ($fine_status_data): ?>
                        <div class="d-flex align-items-center text-warning mb-2">
                            <i class="bi bi-exclamation-circle fs-3 me-2"></i>
                            <div>
                                <h5 class="mb-0 text-warning fw-bold">Pending Today's Run</h5>
                                <small class="text-muted">Last run: <?= $fine_status_data['last_run_time'] ?></small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center text-secondary mb-2">
                            <i class="bi bi-clock fs-3 me-2"></i>
                            <div>
                                <h5 class="mb-0 text-dark fw-bold">Scheduled</h5>
                                <small class="text-muted">Runs daily at scheduled time</small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title text-muted mb-0">Fine Auto-Post Status (Today)</h6>
                        <a href="fine-auto-poster.php?run_now=1" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-play-circle me-1"></i> Trigger Fine Auto-Poster Now</a>
                    </div>
                    <div class="row text-center g-2">
                        <div class="col-6">
                            <div class="p-2 border rounded bg-primary bg-opacity-10">
                                <span class="text-muted small">Daily Incidents Recorded</span>
                                <h4 class="mb-0 text-primary fw-bold"><?= $c_fine_today ?></h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 border rounded bg-success bg-opacity-10">
                                <span class="text-muted small">Total Fine Amount Logged</span>
                                <h4 class="mb-0 text-success fw-bold">৳ <?= number_format($amt_fine_today, 2) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions Card -->
    <div class="card shadow-sm border">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-terminal me-1"></i> Cron Setup Guide (cPanel / Linux / Windows Task Scheduler)</h6>
        </div>
        <div class="card-body">
            <p class="text-muted">To enable automatic daily fine generation and ledger updates, configure the following cron job command on your server (runs every 10–15 minutes):</p>
            <div class="p-3 bg-dark text-white rounded font-monospace small mb-3">
                */10 * * * * /usr/local/bin/php <?= dirname(__DIR__) ?>/cron-job/fine-auto-poster.php >/dev/null 2>&1
            </div>
            <p class="small text-muted mb-0">
                <strong>Mechanism:</strong> The script checks every institution's <code>posting_mode</code>, validates that current time has passed <code>daily_run_time</code>, checks if today is weekend/holiday, records incidents in <code>student_fine_logs</code>, and syncs to <code>stfinance</code> without touching paid records.
            </p>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/footer.php'; ?>
