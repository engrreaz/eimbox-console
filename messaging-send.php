<?php
require_once 'header.php';
require_once 'core/sms-var.php';

// Fetch classes
$class_query = $conn->query("SELECT DISTINCT classname FROM sessioninfo WHERE sccode='$sccode' AND classname IS NOT NULL AND classname != '' ORDER BY id ASC");
$classes = [];
if ($class_query) {
    while ($r = $class_query->fetch_assoc()) {
        $classes[] = $r['classname'];
    }
}

// Fetch sections
$section_query = $conn->query("SELECT DISTINCT sectionname FROM sessioninfo WHERE sccode='$sccode' AND sectionname IS NOT NULL AND sectionname != '' ORDER BY id ASC");
$sections = [];
if ($section_query) {
    while ($r = $section_query->fetch_assoc()) {
        $sections[] = $r['sectionname'];
    }
}

// School SMS Gateway Settings
$sc_res = $conn->query("SELECT sms_gateway FROM scinfo WHERE sccode='$sccode' LIMIT 1");
$sc_row = $sc_res ? $sc_res->fetch_assoc() : [];
$gw_conf = get_sms_setting($sc_row['sms_gateway'] ?? '', 'gateway');
$is_sandbox = intval($gw_conf['sandbox_mode'] ?? 0);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-send-check-fill text-primary me-2"></i> Compose & Send Messages</h4>
            <span class="text-muted">Target Students, Guardians, Teachers, and Committee Members</span>
        </div>
        <div>
            <?php if ($is_sandbox == 1): ?>
                <span class="badge bg-warning text-dark px-3 py-2 me-2">
                    <i class="bi bi-bug-fill"></i> Sandbox Mode Active (০ টাকা খরচ)
                </span>
            <?php endif; ?>
            <a href="sms-gateway.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-gear me-1"></i> Gateway Settings
            </a>
            <a href="sms-log.php" class="btn btn-outline-primary">
                <i class="bi bi-journal-text me-1"></i> SMS Logs
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Audience Selection -->
        <div class="col-lg-5">
            <div class="card shadow-sm border h-100">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill text-primary me-2"></i> 1. Select Target Audience</h5>
                </div>
                <div class="card-body">
                    <!-- Audience Type Tabs -->
                    <ul class="nav nav-pills nav-fill mb-3" id="audienceTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="tab-students" data-bs-toggle="pill" data-bs-target="#panel-students" type="button">
                                <i class="bi bi-mortarboard me-1"></i> Students
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-teachers" data-bs-toggle="pill" data-bs-target="#panel-teachers" type="button">
                                <i class="bi bi-person-workspace me-1"></i> Teachers
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-committee" data-bs-toggle="pill" data-bs-target="#panel-committee" type="button">
                                <i class="bi bi-diagram-3 me-1"></i> Committee
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="tab-custom" data-bs-toggle="pill" data-bs-target="#panel-custom" type="button">
                                <i class="bi bi-telephone-plus me-1"></i> Custom
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-2" id="audienceTabContent">
                        <!-- Panel: Students -->
                        <div class="tab-pane fade show active" id="panel-students">
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Class</label>
                                    <select class="form-select form-select-sm" id="st_class">
                                        <option value="">All Classes</option>
                                        <?php foreach ($classes as $c): ?>
                                            <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-muted">Section</label>
                                    <select class="form-select form-select-sm" id="st_section">
                                        <option value="">All Sections</option>
                                        <?php foreach ($sections as $s): ?>
                                            <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btn_fetch_students">
                                <i class="bi bi-funnel me-1"></i> Fetch Student List
                            </button>
                        </div>

                        <!-- Panel: Teachers -->
                        <div class="tab-pane fade" id="panel-teachers">
                            <p class="small text-muted mb-2">Send notices or meeting invites to teachers and staff members.</p>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btn_fetch_teachers">
                                <i class="bi bi-people me-1"></i> Fetch All Teachers & Staff
                            </button>
                        </div>

                        <!-- Panel: Committee -->
                        <div class="tab-pane fade" id="panel-committee">
                            <p class="small text-muted mb-2">Send meeting alerts to Governing Body & SMC members.</p>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btn_fetch_committee">
                                <i class="bi bi-diagram-3 me-1"></i> Fetch SMC Members
                            </button>
                        </div>

                        <!-- Panel: Custom Numbers -->
                        <div class="tab-pane fade" id="panel-custom">
                            <label class="form-label small text-muted">Enter Numbers (Comma or Newline separated)</label>
                            <textarea id="custom_numbers" class="form-control form-control-sm" rows="4" placeholder="01711000000, 01811000000..."></textarea>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-2" id="btn_parse_custom">
                                <i class="bi bi-check2-circle me-1"></i> Load Numbers
                            </button>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Selected Recipients Summary -->
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-dark">Selected Recipients:</span>
                        <span class="badge bg-primary fs-6" id="recipient_count_badge">0</span>
                    </div>

                    <div class="mt-2" style="max-height: 220px; overflow-y: auto;">
                        <div id="recipient_list_preview" class="small text-muted text-center py-3 border rounded bg-light">
                            No recipients selected yet. Use the filters above.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Message Composer & Template -->
        <div class="col-lg-7">
            <div class="card shadow-sm border h-100">
                <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i> 2. Message Composition</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary loadTemp" data-cat="general" data-block="composer">
                            <i class="bi bi-file-earmark-text me-1"></i> Templates
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info loadVar" data-block="composer">
                            <i class="bi bi-code-slash me-1"></i> Insert Tag
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Campaign Name</label>
                            <input type="text" id="campaign_name" class="form-control form-control-sm" value="Broadcast Notice" placeholder="Campaign Name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Message Type</label>
                            <select id="sms_type" class="form-select form-select-sm">
                                <option value="notice">General Notice</option>
                                <option value="attendance">Attendance</option>
                                <option value="payment">Payment / Dues</option>
                                <option value="result">Exam Result</option>
                                <option value="meeting">Meeting / Event</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2" id="composer">
                        <label class="form-label small text-muted fw-semibold">Message Text</label>
                        <textarea id="message_text" class="form-control form-control-sm" rows="5" placeholder="Type your message here or select a template... Use dynamic tags like [[STUDENT_NAME]], [[CLASS_NAME]], [[DUE_AMOUNT]], [[DATE]]..."></textarea>
                    </div>

                    <!-- Character & Parts Counter -->
                    <div class="d-flex justify-content-between align-items-center mb-3 text-muted small bg-light p-2 rounded border">
                        <div>
                            <span>Characters: <b id="char_count" class="text-dark">0</b></span>
                            <span class="ms-3">Encoding: <b id="lang_type" class="text-primary">English (160)</b></span>
                        </div>
                        <div>
                            <span>Estimated SMS Parts: <b id="parts_count" class="text-success">0</b></span>
                        </div>
                    </div>

                    <!-- Dynamic Live Sample Preview -->
                    <div class="card border border-info bg-info bg-opacity-10 mb-3">
                        <div class="card-body p-2">
                            <span class="small fw-bold text-info"><i class="bi bi-eye-fill me-1"></i> Sample Live Preview (First Recipient):</span>
                            <div id="live_preview_box" class="small text-dark mt-1 font-monospace" style="white-space:pre-wrap;">
                                Type message or choose template to see live preview...
                            </div>
                        </div>
                    </div>

                    <!-- Dispatch Action -->
                    <div class="pt-2">
                        <button type="button" class="btn btn-primary btn-lg w-100 shadow-sm" id="btn_send_bulk">
                            <i class="bi bi-send-fill me-2"></i> Send Now (Instant Async Queue)
                        </button>
                    </div>

                    <!-- Progress Feedback -->
                    <div id="dispatch_feedback" class="mt-3" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- Modals -->
<div class="modal fade" id="smsTempModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="max-height:85vh; overflow-y:auto;">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text text-primary me-1"></i> Choose Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="smsTempBody"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="smsVarModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="max-height:85vh; overflow-y:auto;">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-code-slash text-info me-1"></i> Dynamic Variable Tags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-bordered table-hover table-sm mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Variable Tag</th>
                            <th>Description</th>
                            <th>Sample Output</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < count($sms_hint); $i++): ?>
                            <tr>
                                <td><code class="text-primary fw-bold"><?= $sms_hint[$i] ?></code></td>
                                <td><?= $sms_desc[$i] ?? '' ?></td>
                                <td class="text-muted small"><?= $sms_sample[$i] ?? '' ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-success chooseVar py-1 px-2" data-var="<?= $sms_hint[$i] ?>">
                                        Insert
                                    </button>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let currentRecipients = [];

    function updateCounter() {
        let text = $("#message_text").val();
        let len = text.length;
        let isBengali = /[\u0980-\u09FF]/.test(text);

        let parts = 0;
        if (len > 0) {
            if (isBengali) {
                parts = Math.ceil(len / 70);
                $("#lang_type").text("Bengali / Unicode (70 chars)");
            } else {
                parts = Math.ceil(len / 160);
                $("#lang_type").text("English (160 chars)");
            }
        }

        $("#char_count").text(len);
        $("#parts_count").text(parts);
        updateLivePreview();
    }

    function updateLivePreview() {
        let text = $("#message_text").val();
        if (!text) {
            $("#live_preview_box").text("Type message or choose template to see preview...");
            return;
        }

        let sample = currentRecipients.length > 0 ? currentRecipients[0] : {
            name: "Labib Shahriar",
            classname: "Nine",
            sectionname: "Padma",
            rollno: "12",
            dueamount: "1,250.00",
            paymentamount: "650.00",
            receiptno: "MR-2026-0891"
        };

        let replaced = text
            .replace(/\[\[INSTITUTE_NAME\]\]/g, "EIMBox Model School & College")
            .replace(/\[\[STUDENT_NAME\]\]/g, sample.name || "Labib Shahriar")
            .replace(/\[\[STUDENT_NAME_ENG\]\]/g, sample.name || "Labib Shahriar")
            .replace(/\[\[STUDENT_NAME_BEN\]\]/g, sample.name || "লাবিব শাহরিয়ার")
            .replace(/\[\[CLASS_NAME\]\]/g, sample.classname || "Nine")
            .replace(/\[\[SECTION_NAME\]\]/g, sample.sectionname || "Padma")
            .replace(/\[\[ROLL_NO\]\]/g, sample.rollno || "12")
            .replace(/\[\[DUE_AMOUNT\]\]/g, sample.dueamount || "1,250.00")
            .replace(/\[\[PAID_AMOUNT\]\]/g, sample.paymentamount || "650.00")
            .replace(/\[\[PAYMENT_AMOUNT\]\]/g, sample.paymentamount || "650.00")
            .replace(/\[\[RECEIPT_NO\]\]/g, sample.receiptno || "MR-2026-0891")
            .replace(/\[\[DATE\]\]/g, "<?= date('Y-m-d') ?>")
            .replace(/\[\[TIME\]\]/g, "<?= date('h:i A') ?>")
            .replace(/\[\[CUR\]\]/g, "<?= date('Y-m-d H:i:s') ?>");

        $("#live_preview_box").text(replaced);
    }

    $("#message_text").on("input keyup", updateCounter);

    // Fetch Students with SweetAlert
    $("#btn_fetch_students").on("click", function () {
        let cls = $("#st_class").val();
        let sec = $("#st_section").val();

        let btn = $(this);
        btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> Fetching...');

        $.ajax({
            url: "ajax/fetch-messaging-audience.php",
            type: "POST",
            data: { audience: "students", classname: cls, sectionname: sec },
            dataType: "json",
            success: function (res) {
                btn.prop("disabled", false).html('<i class="bi bi-funnel me-1"></i> Fetch Student List');
                if (res.status === 'success') {
                    currentRecipients = res.data;
                    renderRecipientList();
                    if (res.total === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'No Students Found',
                            text: 'No active student records with valid mobile numbers were found for the selected filter.'
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Students Loaded',
                            text: `Successfully loaded ${res.total} student recipient(s).`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Fetch Failed',
                        text: res.message || 'Unable to retrieve students.'
                    });
                }
            },
            error: function (xhr, status, error) {
                btn.prop("disabled", false).html('<i class="bi bi-funnel me-1"></i> Fetch Student List');
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Error connecting to student fetch service: ' + (xhr.responseText ? xhr.responseText.substring(0, 150) : error)
                });
            }
        });
    });

    // Fetch Teachers with SweetAlert
    $("#btn_fetch_teachers").on("click", function () {
        let btn = $(this);
        btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> Fetching...');

        $.ajax({
            url: "ajax/fetch-messaging-audience.php",
            type: "POST",
            data: { audience: "teachers" },
            dataType: "json",
            success: function (res) {
                btn.prop("disabled", false).html('<i class="bi bi-people me-1"></i> Fetch All Teachers & Staff');
                if (res.status === 'success') {
                    currentRecipients = res.data;
                    renderRecipientList();
                    Swal.fire({
                        icon: 'success',
                        title: 'Teachers Loaded',
                        text: `Loaded ${res.total} teacher(s) and staff members.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function () {
                btn.prop("disabled", false).html('<i class="bi bi-people me-1"></i> Fetch All Teachers & Staff');
                Swal.fire({
                    icon: 'error',
                    title: 'Fetch Error',
                    text: 'Failed to fetch teacher list.'
                });
            }
        });
    });

    // Fetch Committee with SweetAlert
    $("#btn_fetch_committee").on("click", function () {
        let btn = $(this);
        btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> Fetching...');

        $.ajax({
            url: "ajax/fetch-messaging-audience.php",
            type: "POST",
            data: { audience: "committee" },
            dataType: "json",
            success: function (res) {
                btn.prop("disabled", false).html('<i class="bi bi-diagram-3 me-1"></i> Fetch SMC Members');
                if (res.status === 'success') {
                    currentRecipients = res.data;
                    renderRecipientList();
                    Swal.fire({
                        icon: 'success',
                        title: 'Committee Loaded',
                        text: `Loaded ${res.total} SMC / Governing body member(s).`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function () {
                btn.prop("disabled", false).html('<i class="bi bi-diagram-3 me-1"></i> Fetch SMC Members');
                Swal.fire({
                    icon: 'error',
                    title: 'Fetch Error',
                    text: 'Failed to fetch committee members.'
                });
            }
        });
    });

    // Parse Custom Numbers
    $("#btn_parse_custom").on("click", function () {
        let raw = $("#custom_numbers").val();
        let list = raw.split(/[\n,;]+/).map(s => s.trim()).filter(s => s.length >= 10);

        if (list.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Input',
                text: 'Please enter at least one valid mobile number.'
            });
            return;
        }

        currentRecipients = list.map(num => ({
            id: "",
            name: "Direct Recipient",
            mobile: num,
            recipient_type: "custom",
            classname: "",
            sectionname: "",
            rollno: 0
        }));

        renderRecipientList();
        Swal.fire({
            icon: 'success',
            title: 'Numbers Loaded',
            text: `Added ${currentRecipients.length} custom mobile number(s).`,
            timer: 2000,
            showConfirmButton: false
        });
    });

    function renderRecipientList() {
        $("#recipient_count_badge").text(currentRecipients.length);
        if (currentRecipients.length === 0) {
            $("#recipient_list_preview").html("No recipients selected yet.");
            return;
        }

        let html = '<ul class="list-group list-group-flush">';
        currentRecipients.slice(0, 10).forEach(r => {
            html += `<li class="list-group-item d-flex justify-content-between align-items-center py-1 px-2 small">
                <span><b>${r.name || 'Recipient'}</b> <span class="text-muted">(${r.mobile})</span></span>
                <span class="badge bg-light text-dark border">${r.classname ? r.classname + '-' + r.sectionname : r.recipient_type}</span>
            </li>`;
        });
        if (currentRecipients.length > 10) {
            html += `<li class="list-group-item text-center text-muted small py-1 bg-light">...and ${currentRecipients.length - 10} more recipients</li>`;
        }
        html += '</ul>';
        $("#recipient_list_preview").html(html);
        updateLivePreview();
    }

    // Load Templates
    $(document).on("click", ".loadTemp", function () {
        $("#smsTempBody").html('<div class="text-center py-4"><span class="spinner-border text-primary"></span> Loading templates...</div>');
        new bootstrap.Modal(document.getElementById('smsTempModal')).show();

        $.ajax({
            url: "ajax/load-sms-templates.php",
            type: "POST",
            data: { cat: "general", block: "composer" },
            success: function (res) {
                $("#smsTempBody").html(res);
            }
        });
    });

    // Choose Template
    $(document).on("click", ".chooseTemp", function () {
        let txt = $(this).data("text");
        $("#message_text").val(txt);
        bootstrap.Modal.getInstance(document.getElementById('smsTempModal')).hide();
        updateCounter();
    });

    // Variables Modal
    $(document).on("click", ".loadVar", function () {
        new bootstrap.Modal(document.getElementById('smsVarModal')).show();
    });

    $(document).on("click", ".chooseVar", function () {
        let tag = $(this).data("var");
        let textarea = document.getElementById("message_text");
        let start = textarea.selectionStart || 0;
        let end = textarea.selectionEnd || 0;
        let text = textarea.value;

        textarea.value = text.substring(0, start) + tag + text.substring(end, text.length);
        textarea.focus();
        bootstrap.Modal.getInstance(document.getElementById('smsVarModal')).hide();
        updateCounter();
    });

    // Send Bulk Messages with SweetAlert Confirmation & Feedback
    $("#btn_send_bulk").on("click", function () {
        if (currentRecipients.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Recipients',
                text: 'Please select or fetch your target audience first!'
            });
            return;
        }

        let rawText = $("#message_text").val().trim();
        if (!rawText) {
            Swal.fire({
                icon: 'warning',
                title: 'Empty Message',
                text: 'Please enter message content or choose a template!'
            });
            return;
        }

        Swal.fire({
            title: 'Send Bulk SMS?',
            html: `You are about to queue <b>${currentRecipients.length}</b> personalized message(s).<br><small class="text-muted">The queue worker will dispatch these in the background.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="bi bi-send me-1"></i> Yes, Dispatch Queue'
        }).then((result) => {
            if (result.isConfirmed) {
                let btn = $("#btn_send_bulk");
                btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-2"></span> Queueing Messages...');

                // Compile personalized text for each recipient
                let compiledRecipients = currentRecipients.map(r => {
                    let personalized = rawText
                        .replace(/\[\[INSTITUTE_NAME\]\]/g, "EIMBox Model School & College")
                        .replace(/\[\[STUDENT_NAME\]\]/g, r.name || "")
                        .replace(/\[\[STUDENT_NAME_ENG\]\]/g, r.name || "")
                        .replace(/\[\[STUDENT_NAME_BEN\]\]/g, r.name || "")
                        .replace(/\[\[CLASS_NAME\]\]/g, r.classname || "")
                        .replace(/\[\[SECTION_NAME\]\]/g, r.sectionname || "")
                        .replace(/\[\[ROLL_NO\]\]/g, r.rollno || "")
                        .replace(/\[\[DUE_AMOUNT\]\]/g, r.dueamount || "0.00")
                        .replace(/\[\[PAID_AMOUNT\]\]/g, r.paymentamount || "0.00")
                        .replace(/\[\[PAYMENT_AMOUNT\]\]/g, r.paymentamount || "0.00")
                        .replace(/\[\[RECEIPT_NO\]\]/g, r.receiptno || "")
                        .replace(/\[\[DATE\]\]/g, "<?= date('Y-m-d') ?>")
                        .replace(/\[\[TIME\]\]/g, "<?= date('h:i A') ?>")
                        .replace(/\[\[CUR\]\]/g, "<?= date('Y-m-d H:i:s') ?>");

                    return {
                        id: r.id || "",
                        name: r.name || "",
                        mobile: r.mobile || "",
                        classname: r.classname || "",
                        sectionname: r.sectionname || "",
                        rollno: r.rollno || 0,
                        recipient_type: r.recipient_type || "guardian",
                        text: personalized
                    };
                });

                $.ajax({
                    url: "ajax/ajax-queue-sms.php",
                    type: "POST",
                    data: {
                        campaign: $("#campaign_name").val(),
                        sms_type: $("#sms_type").val(),
                        recipients: compiledRecipients
                    },
                    dataType: "json",
                    success: function (res) {
                        btn.prop("disabled", false).html('<i class="bi bi-send-fill me-2"></i> Send Now (Instant Async Queue)');
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dispatched to Queue!',
                                html: `<b>${res.total_recipients}</b> messages successfully queued (Total parts: <b>${res.total_sms_parts}</b>).<br><small class="text-muted">Batch ID: <code>${res.batch_id}</code>.<br>You can safely close this page while messages are sent in the background.</small>`,
                                confirmButtonText: 'Great!'
                            });

                            $("#dispatch_feedback").show().html(`
                                <div class="alert alert-success d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                                    <div>
                                        <h6 class="alert-heading mb-1 fw-bold">Success! ${res.message}</h6>
                                        <p class="mb-0 small text-muted">Batch ID: <code>${res.batch_id}</code> | Total SMS Parts: <b>${res.total_sms_parts}</b></p>
                                    </div>
                                </div>
                            `);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Queueing Error',
                                text: res.message
                            });
                        }
                    },
                    error: function () {
                        btn.prop("disabled", false).html('<i class="bi bi-send-fill me-2"></i> Send Now (Instant Async Queue)');
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Failed to reach server while queueing messages.'
                        });
                    }
                });
            }
        });
    });
</script>
