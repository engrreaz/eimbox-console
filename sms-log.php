<?php
require_once 'core/init.php';
require_once 'core/sms-var.php';
require_once 'header.php';

$sccode = $sccode ?? ($_SESSION['sccode'] ?? '');

// Fetch KPI metrics for current school
$today = date('Y-m-d');
$kpi_total = 0;
$kpi_sent = 0;
$kpi_queued = 0;
$kpi_failed = 0;
$kpi_today = 0;
$kpi_cost = 0.00;

$kpi_q = $conn->query("
    SELECT 
        COUNT(*) AS total_count,
        SUM(CASE WHEN status='sent' THEN 1 ELSE 0 END) AS sent_count,
        SUM(CASE WHEN status='queued' OR status='sending' THEN 1 ELSE 0 END) AS queued_count,
        SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) AS failed_count,
        SUM(CASE WHEN date='$today' THEN 1 ELSE 0 END) AS today_count,
        SUM(cost) AS total_cost
    FROM sms 
    WHERE sccode='$sccode'
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
                        <option value="attendance">Attendance</option>
                        <option value="payment">Payment / Dues</option>
                        <option value="result">Exam Result</option>
                        <option value="meeting">Meeting / Event</option>
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
                            <th style="width: 110px;">Date & Time</th>
                            <th>Recipient</th>
                            <th>Type & Campaign</th>
                            <th style="width: 100px;">Parts & Cost</th>
                            <th style="width: 85px;" class="text-center">Status</th>
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

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function () {
    let smsModal = new bootstrap.Modal(document.getElementById('smsDetailModal'));

    let table = $('#smsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ordering: false,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: "ajax/sms-datatable.php",
            type: "POST",
            data: function (d) {
                d.from = $('#from_date').val();
                d.to = $('#to_date').val();
                d.status = $('#filter_status').val();
                d.sms_type = $('#filter_type').val();
            }
        },
        columns: [
            {
                data: null,
                className: "text-center text-muted small",
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                data: "date",
                render: function (d, type, row) {
                    let time = row.send_time ? row.send_time.split(' ')[1] : '';
                    return `<div class="fw-semibold small">${d}</div><div class="text-muted" style="font-size:11px;">${time}</div>`;
                }
            },
            {
                data: "mobile_number",
                render: function (d, type, row) {
                    let name = row.recipient_name || 'Recipient';
                    let meta = row.classname ? (row.classname + (row.sectionname ? '-' + row.sectionname : '') + (row.rollno ? ' | Roll: ' + row.rollno : '')) : (row.recipient_type || '');
                    return `<div class="fw-bold text-dark">${name}</div><div class="small"><code>${d}</code> <span class="badge bg-light text-dark border ms-1" style="font-size:10px;">${meta}</span></div>`;
                }
            },
            {
                data: "sms_type",
                render: function (d, type, row) {
                    let typeBadge = `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary">${d || 'Notice'}</span>`;
                    let camp = row.campaign ? `<div class="small text-muted text-truncate mt-1" style="max-width:140px;">${row.campaign}</div>` : '';
                    return typeBadge + camp;
                }
            },
            {
                data: "sms_parts",
                render: function (d, type, row) {
                    let parts = d || 1;
                    let cost = parseFloat(row.cost || 0).toFixed(2);
                    return `<span class="badge bg-light text-dark border">${parts} Part(s)</span><div class="small text-muted mt-1">৳${cost}</div>`;
                }
            },
            {
                data: "status",
                className: "text-center",
                render: function (d) {
                    if (d === 'sent') return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sent</span>';
                    if (d === 'queued') return '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Queued</span>';
                    if (d === 'sending') return '<span class="badge bg-info"><i class="bi bi-arrow-repeat me-1"></i>Sending</span>';
                    return '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>';
                }
            },
            {
                data: "sms_text",
                render: function (d) {
                    let snippet = d ? (d.length > 55 ? d.substring(0, 55) + '...' : d) : '';
                    return `<span class="small font-monospace text-muted">${snippet}</span>`;
                }
            },
            {
                data: null,
                className: "text-center",
                render: function (data, type, row) {
                    let rowJson = encodeURIComponent(JSON.stringify(row));
                    return `<button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 btn_view_sms" data-row="${rowJson}">
                        <i class="bi bi-eye me-1"></i> View
                    </button>`;
                }
            }
        ]
    });

    // Filter Change Handlers
    $("#from_date, #to_date, #filter_status, #filter_type").on("change", function () {
        table.ajax.reload();
    });

    $("#btn_refresh_table").on("click", function () {
        table.ajax.reload();
    });

    $("#btn_reset_filters").on("click", function () {
        $("#from_date").val("");
        $("#to_date").val("");
        $("#filter_status").val("all");
        $("#filter_type").val("all");
        table.search("").ajax.reload();
    });

    // View SMS Modal Handler
    $(document).on("click", ".btn_view_sms", function () {
        let row = JSON.parse(decodeURIComponent($(this).data("row")));
        $("#modal_rec_name").text(row.recipient_name || "Direct Recipient");
        $("#modal_rec_mobile").text(row.mobile_number || "-");
        $("#modal_send_time").text(row.send_time || row.date || "-");

        let statusHtml = '<span class="badge bg-danger">Failed</span>';
        if (row.status === 'sent') statusHtml = '<span class="badge bg-success">Sent</span>';
        else if (row.status === 'queued') statusHtml = '<span class="badge bg-warning text-dark">Queued</span>';
        else if (row.status === 'sending') statusHtml = '<span class="badge bg-info">Sending</span>';

        $("#modal_status").html(statusHtml);
        $("#modal_sms_text").text(row.sms_text || "");
        $("#modal_char_count").text(row.sms_len || (row.sms_text ? row.sms_text.length : 0));
        $("#modal_parts_count").text(row.sms_parts || 1);
        $("#modal_cost").text("৳" + parseFloat(row.cost || 0).toFixed(2));
        $("#modal_gateway").text(row.gateway_provider || "bulksmsbd");

        smsModal.show();
    });
});
</script>