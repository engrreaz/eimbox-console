<?php
require_once 'core/init.php';
require_once 'core/sms-var.php';
require_once 'header.php';

$current_sccode = $sccode ?? ($_SESSION['sccode'] ?? '');
if (isset($is_admin) && $is_admin >= 4 && !empty($_GET['sccode'])) {
    $selected_sccode = trim($_GET['sccode']);
} else {
    $selected_sccode = $current_sccode;
}

// Fetch schools list for super administrators
$schools = [];
if (isset($is_admin) && $is_admin >= 4) {
    $schoolsQ = $conn->query("SELECT sccode, scname FROM scinfo ORDER BY scname ASC");
    if ($schoolsQ) {
        while ($row = $schoolsQ->fetch_assoc()) {
            $schools[] = $row;
        }
    }
}

// Fetch KPI metrics for selected school
$today = date('Y-m-d');
$kpi_total = 0;
$kpi_sent = 0;
$kpi_queued = 0;
$kpi_failed = 0;
$kpi_today = 0;
$kpi_cost = 0.00;

$kpi_where = !empty($selected_sccode) ? "WHERE sccode='" . mysqli_real_escape_string($conn, $selected_sccode) . "'" : "WHERE 1=1";
$kpi_q = $conn->query("
    SELECT 
        COUNT(*) AS total_count,
        SUM(CASE WHEN LOWER(status)='sent' OR status='1' OR status='success' OR status='1000' THEN 1 ELSE 0 END) AS sent_count,
        SUM(CASE WHEN LOWER(status)='queued' OR status='0' OR LOWER(status)='sending' OR status='' THEN 1 ELSE 0 END) AS queued_count,
        SUM(CASE WHEN LOWER(status)='failed' OR (status != '' AND status != '0' AND status != '1' AND LOWER(status) != 'sent' AND LOWER(status) != 'queued' AND LOWER(status) != 'sending') THEN 1 ELSE 0 END) AS failed_count,
        SUM(CASE WHEN date='$today' THEN 1 ELSE 0 END) AS today_count,
        SUM(cost) AS total_cost
    FROM sms 
    $kpi_where
");

if ($kpi_q && $kpi = $kpi_q->fetch_assoc()) {
    $kpi_total = intval($kpi['total_count'] ?? 0);
    $kpi_sent = intval($kpi['sent_count'] ?? 0);
    $kpi_queued = intval($kpi['queued_count'] ?? 0);
    $kpi_failed = intval($kpi['failed_count'] ?? 0);
    $kpi_today = intval($kpi['today_count'] ?? 0);
    $kpi_cost = floatval($kpi['total_cost'] ?? 0.00);
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-journal-text text-primary me-2"></i> SMS Delivery Logs & Analytics
            </h4>
            <p class="text-muted mb-0 small">
                Track real-time SMS delivery status, costs, recipient logs, and background dispatch queues.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="messaging-send.php" class="btn btn-primary btn-sm">
                <i class="bi bi-send-fill me-1"></i> Send Messaging
            </a>
            <a href="sms-templates.php" class="btn btn-outline-info btn-sm">
                <i class="bi bi-file-earmark-text me-1"></i> SMS Templates
            </a>
            <a href="sms-gateway.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-gear me-1"></i> Gateway Settings
            </a>
        </div>
    </div>

    <!-- Super Admin Institution Switcher -->
    <?php if (isset($is_admin) && $is_admin >= 4 && !empty($schools)): ?>
        <div class="card shadow-sm border mb-3">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-building text-primary"></i>
                    <span class="small fw-semibold text-muted">Viewing Logs For Institution:</span>
                </div>
                <form method="get" class="d-flex align-items-center gap-2 m-0">
                    <select name="sccode" id="admin_select_sccode" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 250px;">
                        <?php foreach ($schools as $sch): ?>
                            <option value="<?= htmlspecialchars($sch['sccode']) ?>" <?= ($sch['sccode'] == $selected_sccode) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sch['scname']) ?> (<?= htmlspecialchars($sch['sccode']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <input type="hidden" id="current_sccode" value="<?= htmlspecialchars($selected_sccode) ?>">

    <!-- Live KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">Total Messages</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= number_format($kpi_total) ?></h4>
                    </div>
                    <div class="avatar bg-primary bg-opacity-10 text-primary p-2 rounded">
                        <i class="bi bi-envelope-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">Delivered / Sent</span>
                        <h4 class="fw-bold mb-0 text-success"><?= number_format($kpi_sent) ?></h4>
                    </div>
                    <div class="avatar bg-success bg-opacity-10 text-success p-2 rounded">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">Queued / Pending</span>
                        <h4 class="fw-bold mb-0 text-warning"><?= number_format($kpi_queued) ?></h4>
                    </div>
                    <div class="avatar bg-warning bg-opacity-10 text-warning p-2 rounded">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">Total Cost (৳ BDT)</span>
                        <h4 class="fw-bold mb-0 text-danger">৳<?= number_format($kpi_cost, 2) ?></h4>
                    </div>
                    <div class="avatar bg-danger bg-opacity-10 text-danger p-2 rounded">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar & Data Card -->
    <div class="card shadow-sm border">
        <div class="card-header bg-light py-3 border-bottom">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-semibold mb-1">From Date</label>
                    <input type="date" id="from_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-semibold mb-1">To Date</label>
                    <input type="date" id="to_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted fw-semibold mb-1">Status</label>
                    <select id="filter_status" class="form-select form-select-sm">
                        <option value="all">All Status</option>
                        <option value="sent">Sent</option>
                        <option value="queued">Queued</option>
                        <option value="sending">Sending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted fw-semibold mb-1">Message Type</label>
                    <select id="filter_type" class="form-select form-select-sm">
                        <option value="all">All Types</option>
                        <option value="notice">General Notice</option>
                        <option value="general">General Broadcast</option>
                        <option value="attendance">Attendance</option>
                        <option value="payment">Payment / Dues</option>
                        <option value="result">Exam Result</option>
                        <option value="meeting">Meeting / Event</option>
                        <option value="otp">OTP / Verification</option>
                        <option value="test">Test Message</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="btn_reset_filters">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                    <button type="button" class="btn btn-primary btn-sm w-100" id="btn_refresh_table">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-3">
            <div class="table-responsive">
                <table id="smsTable" class="table table-bordered table-hover table-sm align-middle w-100">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 35px;" class="text-center">#</th>
                            <th style="width: 120px;">Date & Time</th>
                            <th>Recipient</th>
                            <th>Type & Campaign</th>
                            <th style="width: 100px;">Parts & Cost</th>
                            <th style="width: 95px;" class="text-center">Status</th>
                            <th>Message Preview</th>
                            <th style="width: 80px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- Modal: View Full SMS Details                            -->
<!-- ======================================================= -->
<div class="modal fade" id="smsDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3 border-bottom">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-chat-left-text text-primary me-2"></i> SMS Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted">Recipient:</span>
                            <b id="modal_rec_name" class="d-block text-dark">-</b>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Mobile Number:</span>
                            <code id="modal_rec_mobile" class="d-block fs-6">-</code>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Dispatched At:</span>
                            <span id="modal_send_time" class="d-block text-dark">-</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Status:</span>
                            <span id="modal_status" class="d-block">-</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted fw-semibold">SMS Full Text:</label>
                    <div id="modal_sms_text" class="p-3 bg-light border rounded font-monospace small" style="white-space: pre-wrap; max-height: 180px; overflow-y: auto;">-</div>
                </div>

                <div class="row g-2 small text-muted border-top pt-2">
                    <div class="col-6">Characters: <b id="modal_char_count" class="text-dark">0</b></div>
                    <div class="col-6">Parts: <b id="modal_parts_count" class="text-dark">0</b></div>
                    <div class="col-6">Cost: <b id="modal_cost" class="text-dark">৳0.00</b></div>
                    <div class="col-6">Gateway: <b id="modal_gateway" class="text-dark">-</b></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM loaded. Initializing SMS DataTable...");

    var smsModalInstance = null;
    function getSmsModal() {
        if (!smsModalInstance) {
            var el = document.getElementById('smsDetailModal');
            if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                smsModalInstance = new bootstrap.Modal(el);
            }
        }
        return smsModalInstance;
    }

    var table = null;
    var tableOptions = {
        processing: true,
        serverSide: true,
        searching: true,
        ordering: false,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            emptyTable: "<div class='text-center py-4 text-muted'><i class='bi bi-inbox fs-2 d-block mb-2 text-secondary'></i>No SMS delivery records found.</div>",
            zeroRecords: "<div class='text-center py-4 text-muted'><i class='bi bi-search fs-2 d-block mb-2 text-secondary'></i>No matching SMS records found for your search/filter.</div>",
            processing: "<div class='text-center py-2'><div class='spinner-border spinner-border-sm text-primary me-2'></div> Loading SMS records...</div>"
        },
        ajax: {
            url: "ajax/sms-datatable.php",
            type: "POST",
            data: function (d) {
                var sc = document.getElementById('current_sccode');
                var fd = document.getElementById('from_date');
                var td = document.getElementById('to_date');
                var st = document.getElementById('filter_status');
                var tp = document.getElementById('filter_type');

                d.sccode = sc ? sc.value : '';
                d.from = fd ? fd.value : '';
                d.to = td ? td.value : '';
                d.status = st ? st.value : 'all';
                d.sms_type = tp ? tp.value : 'all';
                console.log("Sending SMS DataTable Request:", d);
            },
            error: function (xhr, error, thrown) {
                console.error("DataTable AJAX Error:", xhr.status, xhr.responseText);
            }
        },
        columns: [
            {
                data: null,
                defaultContent: '',
                className: "text-center text-muted small",
                render: function (data, type, row, meta) {
                    var start = (meta.settings && meta.settings._iDisplayStart !== undefined) ? meta.settings._iDisplayStart : 0;
                    return (meta.row || 0) + start + 1;
                }
            },
            {
                data: "date",
                defaultContent: '-',
                render: function (d, type, row) {
                    var dt = d || (row && row.send_time ? row.send_time.split(' ')[0] : '-');
                    var time = row && row.send_time ? (row.send_time.split(' ')[1] || '') : '';
                    return '<div class="fw-semibold small">' + dt + '</div><div class="text-muted" style="font-size:11px;">' + time + '</div>';
                }
            },
            {
                data: "mobile_number",
                defaultContent: '-',
                render: function (d, type, row) {
                    var name = (row && row.recipient_name) ? row.recipient_name : 'Direct Recipient';
                    var meta = (row && row.classname) ? (row.classname + (row.sectionname ? '-' + row.sectionname : '') + (row.rollno ? ' | Roll: ' + row.rollno : '')) : (row && row.recipient_type ? row.recipient_type : '');
                    return '<div class="fw-bold text-dark">' + name + '</div><div class="small"><code>' + (d || '-') + '</code> ' + (meta ? '<span class="badge bg-light text-dark border ms-1" style="font-size:10px;">' + meta + '</span>' : '') + '</div>';
                }
            },
            {
                data: "sms_type",
                defaultContent: 'Notice',
                render: function (d, type, row) {
                    var typeBadge = '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary">' + (d || 'Notice') + '</span>';
                    var camp = (row && row.campaign) ? '<div class="small text-muted text-truncate mt-1" style="max-width:140px;">' + row.campaign + '</div>' : '';
                    return typeBadge + camp;
                }
            },
            {
                data: "sms_parts",
                defaultContent: '1',
                render: function (d, type, row) {
                    var parts = d || 1;
                    var cost = parseFloat(row && row.cost ? row.cost : 0).toFixed(2);
                    return '<span class="badge bg-light text-dark border">' + parts + ' Part(s)</span><div class="small text-muted mt-1">৳' + cost + '</div>';
                }
            },
            {
                data: "status",
                defaultContent: 'queued',
                className: "text-center",
                render: function (d) {
                    var st = (d || '').toString().toLowerCase().trim();
                    if (st === 'sent' || st === '1' || st === 'success' || st === '1000') {
                        return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sent</span>';
                    }
                    if (st === 'queued' || st === '0') {
                        return '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Queued</span>';
                    }
                    if (st === 'sending') {
                        return '<span class="badge bg-info"><i class="bi bi-arrow-repeat me-1"></i>Sending</span>';
                    }
                    if (st === '' || st === 'null' || st === 'undefined') {
                        return '<span class="badge bg-secondary"><i class="bi bi-dash-circle me-1"></i>Processed</span>';
                    }
                    return '<span class="badge bg-danger" title="Status: ' + d + '"><i class="bi bi-x-circle me-1"></i>Failed</span>';
                }
            },
            {
                data: "sms_text",
                defaultContent: '',
                render: function (d) {
                    var snippet = d ? (d.length > 55 ? d.substring(0, 55) + '...' : d) : '';
                    return '<span class="small font-monospace text-muted">' + (snippet || '-') + '</span>';
                }
            },
            {
                data: null,
                defaultContent: '',
                className: "text-center",
                render: function (data, type, row) {
                    var rowJson = encodeURIComponent(JSON.stringify(row || {}));
                    return '<button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 btn_view_sms" data-row="' + rowJson + '">' +
                        '<i class="bi bi-eye me-1"></i> View' +
                    '</button>';
                }
            }
        ]
    };

    try {
        if (typeof DataTable !== 'undefined') {
            table = new DataTable('#smsTable', tableOptions);
        } else if (window.jQuery && $.fn.DataTable) {
            table = $('#smsTable').DataTable(tableOptions);
        }
        console.log("SMS DataTable Initialized:", table);
    } catch (e) {
        console.error("Error initializing DataTable:", e);
    }

    // Filter Change Handlers
    function reloadTable() {
        if (table) {
            if (table.ajax && typeof table.ajax.reload === 'function') {
                table.ajax.reload();
            } else if (window.jQuery && $('#smsTable').DataTable) {
                $('#smsTable').DataTable().ajax.reload();
            }
        }
    }

    if (window.jQuery) {
        $("#from_date, #to_date, #filter_status, #filter_type").on("change", reloadTable);
        $("#btn_refresh_table").on("click", reloadTable);
        $("#btn_reset_filters").on("click", function () {
            $("#from_date").val("");
            $("#to_date").val("");
            $("#filter_status").val("all");
            $("#filter_type").val("all");
            if (table && table.search) table.search("").ajax.reload();
        });

        // View SMS Modal Handler
        $(document).on("click", ".btn_view_sms", function () {
            var row = JSON.parse(decodeURIComponent($(this).data("row")));
            $("#modal_rec_name").text(row.recipient_name || "Direct Recipient");
            $("#modal_rec_mobile").text(row.mobile_number || "-");
            $("#modal_send_time").text(row.send_time || row.date || "-");

            var st = (row.status || '').toString().toLowerCase().trim();
            var statusHtml = '<span class="badge bg-danger">Failed</span>';
            if (st === 'sent' || st === '1' || st === 'success' || st === '1000') {
                statusHtml = '<span class="badge bg-success">Sent</span>';
            } else if (st === 'queued' || st === '0') {
                statusHtml = '<span class="badge bg-warning text-dark">Queued</span>';
            } else if (st === 'sending') {
                statusHtml = '<span class="badge bg-info">Sending</span>';
            } else if (st === '') {
                statusHtml = '<span class="badge bg-secondary">Processed</span>';
            }

            $("#modal_status").html(statusHtml);
            $("#modal_sms_text").text(row.sms_text || "");
            $("#modal_char_count").text(row.sms_len || (row.sms_text ? row.sms_text.length : 0));
            $("#modal_parts_count").text(row.sms_parts || 1);
            $("#modal_cost").text("৳" + parseFloat(row.cost || 0).toFixed(2));
            $("#modal_gateway").text(row.gateway_provider || "bulksmsbd");

            var modal = getSmsModal();
            if (modal) modal.show();
        });
    }
});
</script>

<?php require_once 'footer.php'; ?>