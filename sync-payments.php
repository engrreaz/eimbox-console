<?php 
require_once 'header.php'; 

$slot = $_COOKIE['slot'] ?? $_COOKIE['chain-slot'] ?? $_GET['slot'] ?? '';
$sy = $_COOKIE['session'] ?? $_COOKIE['chain-session'] ?? $_GET['session'] ?? $sessionyear;

$type = $_GET['type'] ?? '';
$part = $_GET['part'] ?? 'all';
$icode = $_GET['icode'] ?? '';
$stid = $_GET['stid'] ?? '';
$cls = $_GET['cls'] ?? '';
$sec = $_GET['sec'] ?? '';
$reset = isset($_GET['reset']) ? 1 : 0;

/* RESET VALIDATION */
$where = "sccode='$sccode' AND sessionyear LIKE '%$sy%'";
if ($stid) {
    $where .= " AND stid='$stid'";
} elseif ($sec && $cls) {
    $where .= " AND classname='$cls' AND sectionname='$sec'";
} elseif ($cls) {
    $where .= " AND classname='$cls'";
}

if ($reset == 1) {
    $conn->query("UPDATE sessioninfo SET validate=0 WHERE $where");
}

/* COUNT PENDING */
$q = $conn->query("SELECT COUNT(*) c FROM sessioninfo WHERE $where AND validate=0");
$total = 0;
if ($q && $row = $q->fetch_assoc()) {
    $total = intval($row['c']);
}

/* COUNT TOTAL IN FILTER */
$qAll = $conn->query("SELECT COUNT(*) c FROM sessioninfo WHERE $where");
$totalFiltered = 0;
if ($qAll && $rowAll = $qAll->fetch_assoc()) {
    $totalFiltered = intval($rowAll['c']);
}
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Slot / Session Filter Bar -->
    <div class="card mb-3">
        <div class="card-body p-3">
            <div class="row align-items-end g-2">
                <?php
                $chain_param = '-c 10 -t Choose Values -u -r -b View Students';
                include 'components/slot-tree-ui.php';
                ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-1 text-primary"><i class="bi bi-arrow-repeat me-2"></i>Payment Dues Synchronization</h5>
                <span class="text-muted small">Synchronize academic fee policies with student accounts and recalculate dues safely without altering paid records.</span>
            </div>
            <div>
                <a href="payment-settings.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-gear me-1"></i> Fee Settings
                </a>
            </div>
        </div>

        <div class="card-body pt-4">
            <!-- ADVANCED FILTER PARAMETERS -->
            <?php include 'payments/six-parameters.php'; ?>

            <!-- STATUS KPI & PROGRESS -->
            <div class="p-3 bg-light-subtle rounded border mb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                    <div>
                        <span class="fw-semibold text-heading">Pending Students:</span>
                        <strong class="text-danger fs-5 ms-1" id="countus"><?= $total ?></strong>
                        <span class="text-muted small ms-1">/ <?= $totalFiltered ?> total</span>
                    </div>
                    <div class="text-warning small fw-semibold">
                        <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Fast Batch Sync: ≈ <b><span id="remain"><?= max(1, ceil($total / 25)) ?></span></b> sec
                    </div>
                </div>

                <div class="progress" style="height: 22px;">
                    <div id="prog" class="progress-bar progress-bar-striped progress-bar-animated bg-primary fw-bold" 
                         role="progressbar" style="width: 0%; height: 22px;">0%</div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="d-flex mb-3 justify-content-between flex-wrap gap-2">
                <button id="syncBtn" class="btn btn-success btn-sm px-4" onclick="runSync()" <?= ($total == 0) ? 'disabled' : '' ?>>
                    <i class="bi bi-play-circle me-1"></i> Sync Now
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="resetAll()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Validation Queue (Re-sync All)
                </button>
            </div>

            <!-- REALTIME LOG -->
            <div class="card border">
                <div class="card-header bg-light py-2 small fw-semibold text-muted d-flex justify-content-between">
                    <span><i class="bi bi-terminal me-1"></i> Sync Process Log</span>
                    <span id="syncStatusBadge" class="badge bg-label-secondary">Idle</span>
                </div>
                <div id="log" class="card-body p-3 small font-monospace overflow-auto" style="max-height: 250px; min-height: 80px; background: #fafafa;">
                    <div class="text-muted fst-italic">Click "Sync Now" to start processing student fee ledgers...</div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- SweetAlert2 if available -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let initialTotal = <?= max($total, 1) ?>;

    function resetAll() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Reset Queue?',
                text: "This will mark all students as pending validation so you can re-sync everything.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Reset Queue',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('reset', '1');
                    window.location.href = url.toString();
                }
            });
        } else {
            if (confirm("This will mark all students as pending validation so you can re-sync everything. Proceed?")) {
                const url = new URL(window.location.href);
                url.searchParams.set('reset', '1');
                window.location.href = url.toString();
            }
        }
    }

    function runSync() {
        $('#syncBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Syncing...');
        $('#syncStatusBadge').removeClass('bg-label-secondary bg-label-success').addClass('bg-label-primary').text('Processing...');
        $('#log').html('');
        processSync();
    }

    function processSync() {
        let sy = '<?= addslashes($sy) ?>';
        let slot = '<?= addslashes($slot) ?>';

        $.post('payments/check-student-finance.php', {
            sy: sy,
            slot: slot,
            type: '<?= addslashes($type) ?>',
            part: '<?= addslashes($part) ?>',
            icode: '<?= addslashes($icode) ?>',
            stid: '<?= addslashes($stid) ?>',
            cls: '<?= addslashes($cls) ?>',
            sec: '<?= addslashes($sec) ?>'
        }, function (res) {
            $('#log').prepend(res);

            let left = parseInt($('#totaltotal').text());
            if (isNaN(left)) left = 0;

            $('#countus').text(left);

            let completed = initialTotal - left;
            let p = Math.min(100, Math.max(0, Math.round((completed / initialTotal) * 100)));
            $('#prog').css('width', p + '%').text(p + '%');

            let remainSec = Math.max(0, Math.ceil(left / 25));
            $('#remain').text(remainSec);

            if (left > 0) {
                processSync();
            } else {
                $('#prog').css('width', '100%').text('100%').removeClass('bg-primary').addClass('bg-success');
                $('#syncBtn').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Sync Complete');
                $('#syncStatusBadge').removeClass('bg-label-primary').addClass('bg-label-success').text('Completed');
                $('#log').prepend('<div class="text-success fw-bold p-2 bg-success-subtle rounded mb-2"><i class="bi bi-check2-circle me-1"></i> All student dues synchronized successfully!</div>');

                if ('<?= $type ?>' === 'stid' && '<?= $stid ?>' !== '') {
                    setCookie('payment-stid', '<?= addslashes($stid) ?>');
                    setTimeout(() => window.history.back(), 1200);
                }
            }
        }).fail(function(xhr, status, error) {
            $('#syncBtn').prop('disabled', false).html('<i class="bi bi-play-circle me-1"></i> Resume Sync');
            $('#syncStatusBadge').removeClass('bg-label-primary').addClass('bg-label-danger').text('Error');
            $('#log').prepend('<div class="text-danger fw-bold p-2 bg-danger-subtle rounded mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Request failed. Click Resume to continue.</div>');
        });
    }

    // Set defaults into main dropdowns
    $(document).ready(function() {
        let slotVal = '<?= addslashes($slot) ?>';
        let sessionVal = '<?= addslashes($sy) ?>';
        if (slotVal) $('#slot-main').val(slotVal);
        if (sessionVal) $('#session-main').val(sessionVal);

        // Populate parameters from URL
        $('#type-main').val('<?= addslashes($type) ?>');
        $('#part-main').val('<?= addslashes($part) ?>');
        $('#icode-main').val('<?= addslashes($icode) ?>');
        $('#student-main').val('<?= addslashes($stid) ?>');
        $('#class-main').val('<?= addslashes($cls) ?>');

        let secVal = '<?= addslashes($sec) ?>';
        if ($('#class-main').val()) {
            $.post('payments/get-sections.php', { cls: $('#class-main').val() }, function (res) {
                $('#section-main').html(res);
                if (secVal) $('#section-main').val(secVal);
            });
        }

        // Automatic run if reset param is set
        if (<?= $reset ?> === 1) {
            const url = new URL(window.location.href);
            url.searchParams.delete('reset');
            history.replaceState(null, '', url.toString());
            runSync();
        } else if ('<?= $type ?>' === 'stid' && '<?= $stid ?>' !== '') {
            runSync();
        }
    });

    // Cascading Class to Section
    $('#class-main').on('change', function () {
        let cls = $(this).val();
        $('#section-main').html('<option value="">Loading...</option>');
        $.post('payments/get-sections.php', { cls: cls }, function (res) {
            $('#section-main').html(res);
        });
    });

    // Filter Apply Button
    $('#applyFilter').on('click', function () {
        let params = {
            type: $('#type-main').val(),
            part: $('#part-main').val(),
            icode: $('#icode-main').val(),
            stid: $('#student-main').val(),
            cls: $('#class-main').val(),
            sec: $('#section-main').val(),
            slot: $('#slot-main').val(),
            session: $('#session-main').val()
        };

        let query = [];
        $.each(params, function (k, v) {
            if (v !== '' && v !== null && v !== undefined) {
                query.push(encodeURIComponent(k) + '=' + encodeURIComponent(v));
            }
        });

        window.location.href = window.location.pathname + (query.length ? '?' + query.join('&') : '');
    });

    $('#slot-main').on('change', function () {
        setCookie('slot', $(this).val());
        setCookie('chain-slot', $(this).val());
    });

    $('#session-main').on('change', function () {
        setCookie('session', $(this).val());
        setCookie('chain-session', $(this).val());
    });
</script>

</body>
</html>