<?php
require_once dirname(__DIR__) . '/core/config.php';
require_once dirname(__DIR__) . '/core/db.php';
require_once dirname(__DIR__) . '/header.php';

$log_file = __DIR__ . '/cron_status.json';
$has_log = file_exists($log_file);
$status_data = $has_log ? json_decode(file_get_contents($log_file), true) : null;

// Check queue counts in database
$q_queued = $conn->query("SELECT COUNT(*) as c FROM sms WHERE sccode='$sccode' AND status='queued'");
$c_queued = $q_queued ? $q_queued->fetch_assoc()['c'] : 0;

$q_sent = $conn->query("SELECT COUNT(*) as c FROM sms WHERE sccode='$sccode' AND status='sent' AND date=CURDATE()");
$c_sent = $q_sent ? $q_sent->fetch_assoc()['c'] : 0;

$q_failed = $conn->query("SELECT COUNT(*) as c FROM sms WHERE sccode='$sccode' AND status='failed' AND date=CURDATE()");
$c_failed = $q_failed ? $q_failed->fetch_assoc()['c'] : 0;
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

    <!-- Instructions Card -->
    <div class="card shadow-sm border">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold"><i class="bi bi-terminal me-1"></i> Cron Setup Guide (cPanel / Server)</h6>
        </div>
        <div class="card-body">
            <p class="text-muted">To enable automatic queue dispatching every minute on your production server, add the following cron job command in cPanel:</p>
            <div class="p-3 bg-dark text-white rounded font-monospace small mb-3">
                * * * * * /usr/local/bin/php <?= dirname(__DIR__) ?>/cron-job/sms-dispatcher.php >/dev/null 2>&1
            </div>
            <p class="small text-muted mb-0">
                <strong>Note:</strong> Even without server cron, whenever you click <em>Send SMS</em>, our async engine triggers the background worker automatically!
            </p>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/footer.php'; ?>
