<?php
require_once 'core/init.php';
require_once 'core/sms-var.php';
require_once 'header.php';

$sccode = $sccode ?? ($_SESSION['sccode'] ?? '');
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-file-earmark-text text-primary me-2"></i> SMS Template Manager & Editor
            </h4>
            <p class="text-muted mb-0 small">
                Manage global system templates (<span class="badge bg-secondary">sccode=0</span>) and customize institution-specific templates.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="messaging-send.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-send-fill me-1"></i> Send Messaging
            </a>
            <a href="sms-gateway.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-gear me-1"></i> Gateway Settings
            </a>
            <button type="button" class="btn btn-primary btn-sm" id="btn_new_template">
                <i class="bi bi-plus-circle-fill me-1"></i> Create New Template
            </button>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card shadow-sm border mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <!-- Scope Filter -->
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-semibold mb-1">Template Scope</label>
                    <select id="filter_scope" class="form-select form-select-sm">
                        <option value="all">All Templates (System + Custom)</option>
                        <option value="custom">My Institution Templates Only</option>
                        <option value="system">Global System Templates Only</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-semibold mb-1">Category / Module</label>
                    <select id="filter_category" class="form-select form-select-sm">
                        <option value="all">All Categories</option>
                        <option value="general">General Notices</option>
                        <option value="sms_in">Student Entry (sms_in)</option>
                        <option value="sms_out">Student Exit (sms_out)</option>
                        <option value="sms_absent">Daily Absent (sms_absent)</option>
                        <option value="sms_payment">Payment Receipt (sms_payment)</option>
                        <option value="sms_dues">Fee Dues Reminder (sms_dues)</option>
                        <option value="sms_result">Exam & Result (sms_result)</option>
                        <option value="sms_meeting">Meetings / Events (sms_meeting)</option>
                        <option value="sms_greetings">Greetings & National Days</option>
                    </select>
                </div>

                <!-- Search Box -->
                <div class="col-md-5">
                    <label class="form-label small text-muted fw-semibold mb-1">Search Templates</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="filter_search" class="form-control" placeholder="Search by title or text content...">
                        <button class="btn btn-outline-secondary" type="button" id="btn_clear_search">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Grid Container -->
    <div id="templates_grid_container" class="row g-3">
        <div class="col-12 text-center py-5">
            <span class="spinner-border text-primary me-2"></span>
            <span class="text-muted">Loading templates...</span>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- Modal: Create / Edit / Customize Template              -->
<!-- ======================================================= -->
<div class="modal fade" id="templateEditorModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3 border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="modal_editor_title">
                    <i class="bi bi-pencil-square text-primary me-2"></i> Create New Template
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="form_template_editor">
                    <input type="hidden" id="editor_id" value="0">
                    <input type="hidden" id="editor_is_system" value="0">

                    <div id="editor_system_notice" class="alert alert-info py-2 px-3 small d-none">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        <b>Customizing System Template:</b> Modifying this will automatically create a custom template specifically for your institution without affecting the global default.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Template Title <span class="text-danger">*</span></label>
                            <input type="text" id="editor_title" class="form-control" placeholder="e.g. Monthly Fee Reminder (Bangla)" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Category / Module <span class="text-danger">*</span></label>
                            <select id="editor_type" class="form-select" required>
                                <option value="general">General Notice</option>
                                <option value="sms_in">Student Entry (sms_in)</option>
                                <option value="sms_out">Student Exit (sms_out)</option>
                                <option value="sms_absent">Daily Absent (sms_absent)</option>
                                <option value="sms_payment">Payment Receipt (sms_payment)</option>
                                <option value="sms_dues">Fee Dues Reminder (sms_dues)</option>
                                <option value="sms_result">Exam & Result (sms_result)</option>
                                <option value="sms_meeting">Meetings / Events (sms_meeting)</option>
                                <option value="sms_greetings">Greetings & National Days</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Target Audience</label>
                            <select id="editor_audience" class="form-select">
                                <option value="all">All Audience</option>
                                <option value="guardian">Guardians</option>
                                <option value="student">Students</option>
                                <option value="teacher">Teachers & Staff</option>
                                <option value="committee">SMC / Governing Body</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Language Encoding</label>
                            <select id="editor_language" class="form-select">
                                <option value="bn">Bangla (Unicode - 70 Chars / Part)</option>
                                <option value="en">English (GSM 7-bit - 160 Chars / Part)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Tags Palette -->
                    <div class="mb-2">
                        <label class="form-label small text-muted fw-semibold d-flex justify-content-between">
                            <span><i class="bi bi-tags-fill text-primary me-1"></i> Click to Insert Dynamic Variable Tag:</span>
                        </label>
                        <div class="d-flex flex-wrap gap-1 p-2 bg-light border rounded" style="max-height: 100px; overflow-y: auto;">
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[STUDENT_NAME]]">+ [[STUDENT_NAME]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[ROLL_NO]]">+ [[ROLL_NO]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[CLASS_NAME]]">+ [[CLASS_NAME]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[SECTION_NAME]]">+ [[SECTION_NAME]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[DUE_AMOUNT]]">+ [[DUE_AMOUNT]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[PAID_AMOUNT]]">+ [[PAID_AMOUNT]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[RECEIPT_NO]]">+ [[RECEIPT_NO]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[DATE]]">+ [[DATE]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[TIME]]">+ [[TIME]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[INSTITUTE_NAME]]">+ [[INSTITUTE_NAME]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[GPA]]">+ [[GPA]]</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary tag-pill" data-tag="[[EXAM_NAME]]">+ [[EXAM_NAME]]</button>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Message Text Template <span class="text-danger">*</span></label>
                        <textarea id="editor_text" class="form-control" rows="5" placeholder="Enter template text here..." required></textarea>
                    </div>

                    <!-- Character & Parts Counter -->
                    <div class="d-flex justify-content-between align-items-center mb-3 text-muted small bg-light p-2 rounded border">
                        <div>
                            <span>Characters: <b id="editor_char_count" class="text-dark">0</b></span>
                            <span class="ms-3">Detected: <b id="editor_lang_detected" class="text-primary">English (160)</b></span>
                        </div>
                        <div>
                            <span>Estimated SMS Parts: <b id="editor_parts_count" class="text-success">0</b></span>
                        </div>
                    </div>

                    <!-- Dynamic Live Sample Preview -->
                    <div class="card border border-info bg-info bg-opacity-10 mb-3">
                        <div class="card-body p-2">
                            <span class="small fw-bold text-info"><i class="bi bi-eye-fill me-1"></i> Live Rendered Sample:</span>
                            <div id="editor_preview_box" class="small text-dark mt-1 font-monospace" style="white-space:pre-wrap;">
                                Type in the template to see live preview...
                            </div>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="editor_is_default">
                        <label class="form-check-label small fw-semibold" for="editor_is_default">
                            Set as Default Template for this category
                        </label>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light py-2 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn_save_template">
                    <i class="bi bi-check2-circle me-1"></i> Save Template
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<style>
.tag-pill {
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 4px;
}
.template-card {
    transition: all 0.2s ease-in-out;
}
.template-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}
</style>

<script>
$(document).ready(function () {
    let currentTemplates = [];
    let editorModalInstance = new bootstrap.Modal(document.getElementById('templateEditorModal'));

    // Category names mapping
    const categoryLabels = {
        'general': 'General Notice',
        'sms_in': 'Student Entry (In)',
        'sms_out': 'Student Exit (Out)',
        'sms_absent': 'Daily Absent',
        'sms_payment': 'Payment Receipt',
        'sms_dues': 'Fee Dues Reminder',
        'sms_result': 'Exam & Result',
        'sms_meeting': 'Meeting / Event',
        'sms_greetings': 'Greetings'
    };

    // Load templates initially
    loadTemplates();

    // Event listeners for filters
    $("#filter_scope, #filter_category").on("change", function () {
        loadTemplates();
    });

    let searchTimer = null;
    $("#filter_search").on("input", function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadTemplates, 300);
    });

    $("#btn_clear_search").on("click", function () {
        $("#filter_search").val("");
        loadTemplates();
    });

    // Fetch and render templates from backend
    function loadTemplates() {
        let scope = $("#filter_scope").val();
        let cat = $("#filter_category").val();
        let search = $("#filter_search").val().trim();

        $("#templates_grid_container").html(`
            <div class="col-12 text-center py-5">
                <span class="spinner-border text-primary me-2"></span>
                <span class="text-muted">Loading templates...</span>
            </div>
        `);

        $.ajax({
            url: "ajax/sms-template-actions.php",
            type: "POST",
            data: {
                action: "fetch",
                scope: scope,
                category: cat,
                search: search
            },
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    currentTemplates = res.data;
                    renderTemplatesGrid(currentTemplates);
                } else {
                    $("#templates_grid_container").html(`
                        <div class="col-12">
                            <div class="alert alert-danger">${res.message}</div>
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                $("#templates_grid_container").html(`
                    <div class="col-12">
                        <div class="alert alert-danger">Error connecting to server: ${xhr.responseText || status}</div>
                    </div>
                `);
            }
        });
    }

    // Render templates cards
    function renderTemplatesGrid(list) {
        if (!list || list.length === 0) {
            $("#templates_grid_container").html(`
                <div class="col-12 text-center py-5">
                    <i class="bi bi-file-earmark-x fs-1 text-muted d-block mb-2"></i>
                    <h6 class="text-muted fw-bold">No Templates Found</h6>
                    <p class="text-muted small">Try changing your filters or click "+ Create New Template" to add one.</p>
                </div>
            `);
            return;
        }

        let html = '';
        list.forEach(item => {
            let badgeScope = item.is_system 
                ? '<span class="badge bg-secondary"><i class="bi bi-globe me-1"></i> System Global</span>'
                : '<span class="badge bg-info text-white"><i class="bi bi-building me-1"></i> Custom School</span>';

            let defaultBadge = item.is_default == 1
                ? '<span class="badge bg-success ms-1"><i class="bi bi-check-circle-fill me-1"></i> Default</span>'
                : '';

            let catLabel = categoryLabels[item.temp_type] || item.temp_type;
            let highlightedText = highlightTags(item.temp_text);

            let isUnicode = /[\u0980-\u09FF]/.test(item.temp_text);
            let len = item.temp_text.length;
            let parts = isUnicode ? Math.ceil(len / 70) : Math.ceil(len / 160);

            html += `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border shadow-sm template-card">
                    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            ${badgeScope}
                            ${defaultBadge}
                        </div>
                        <span class="badge bg-light text-dark border small">${catLabel}</span>
                    </div>
                    <div class="card-body p-3 d-flex flex-column">
                        <h6 class="fw-bold text-dark mb-1">${item.temp_title}</h6>
                        <div class="d-flex gap-2 text-muted small mb-2">
                            <span><i class="bi bi-person me-1"></i>${item.target_audience}</span>
                            <span>•</span>
                            <span>${len} chars (${parts} SMS part)</span>
                        </div>
                        <div class="p-2 bg-light border rounded small font-monospace flex-grow-1 mb-3" style="white-space:pre-wrap; max-height:130px; overflow-y:auto;">${highlightedText}</div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn_use_temp" data-id="${item.id}" title="Use this template">
                                    <i class="bi bi-send me-1"></i> Use
                                </button>
                                <button type="button" class="btn btn-outline-success btn_edit_temp" data-id="${item.id}" title="${item.is_system ? 'Customize for school' : 'Edit template'}">
                                    <i class="bi bi-pencil-square me-1"></i> ${item.is_system ? 'Customize' : 'Edit'}
                                </button>
                            </div>
                            <div class="btn-group btn-group-sm">
                                ${item.is_default != 1 ? `
                                    <button type="button" class="btn btn-outline-secondary btn_set_default" data-id="${item.id}" title="Set as Default">
                                        <i class="bi bi-star"></i>
                                    </button>
                                ` : ''}
                                ${!item.is_system ? `
                                    <button type="button" class="btn btn-outline-danger btn_delete_temp" data-id="${item.id}" title="Delete Custom Template">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        });

        $("#templates_grid_container").html(html);
    }

    // Highlight [[TAGS]]
    function highlightTags(text) {
        if (!text) return "";
        return text.replace(/(\[\[[A-Z0-9_]+\]\])/g, '<span class="badge bg-primary bg-opacity-25 text-primary fw-semibold">$1</span>');
    }

    // Live preview replacer
    function replaceSampleTags(text) {
        if (!text) return "Type template text to see live preview...";
        return text
            .replace(/\[\[STUDENT_NAME\]\]/g, "রাকিব হাসান")
            .replace(/\[\[ROLL_NO\]\]/g, "12")
            .replace(/\[\[CLASS_NAME\]\]/g, "Class Ten")
            .replace(/\[\[SECTION_NAME\]\]/g, "A")
            .replace(/\[\[DUE_AMOUNT\]\]/g, "1500")
            .replace(/\[\[PAID_AMOUNT\]\]/g, "2000")
            .replace(/\[\[RECEIPT_NO\]\]/g, "REC-8902")
            .replace(/\[\[INSTITUTE_NAME\]\]/g, "EIMBox Model School")
            .replace(/\[\[DATE\]\]/g, "<?= date('Y-m-d') ?>")
            .replace(/\[\[TIME\]\]/g, "<?= date('h:i A') ?>")
            .replace(/\[\[GPA\]\]/g, "5.00")
            .replace(/\[\[EXAM_NAME\]\]/g, "Annual Examination");
    }

    // Character & parts calculation in editor
    function updateEditorCounter() {
        let text = $("#editor_text").val();
        let len = text.length;
        let isUnicode = /[\u0980-\u09FF]/.test(text);

        $("#editor_char_count").text(len);

        let parts = 0;
        if (len > 0) {
            if (isUnicode) {
                parts = len <= 70 ? 1 : Math.ceil(len / 67);
                $("#editor_lang_detected").text("Bangla Unicode (70 chars)");
                $("#editor_language").val("bn");
            } else {
                parts = len <= 160 ? 1 : Math.ceil(len / 153);
                $("#editor_lang_detected").text("English GSM (160 chars)");
                $("#editor_language").val("en");
            }
        } else {
            $("#editor_lang_detected").text("English (160)");
        }

        $("#editor_parts_count").text(parts);
        $("#editor_preview_box").text(replaceSampleTags(text));
    }

    $("#editor_text").on("input", updateEditorCounter);

    // Click Tag Pill to insert
    $(".tag-pill").on("click", function () {
        let tag = $(this).data("tag");
        let textarea = document.getElementById("editor_text");
        let start = textarea.selectionStart || 0;
        let end = textarea.selectionEnd || 0;
        let text = textarea.value;

        textarea.value = text.substring(0, start) + tag + text.substring(end, text.length);
        textarea.focus();
        textarea.setSelectionRange(start + tag.length, start + tag.length);
        updateEditorCounter();
    });

    // Create New Template
    $("#btn_new_template").on("click", function () {
        $("#form_template_editor")[0].reset();
        $("#editor_id").val("0");
        $("#editor_is_system").val("0");
        $("#modal_editor_title").html('<i class="bi bi-plus-circle text-primary me-2"></i> Create New Custom Template');
        $("#editor_system_notice").addClass("d-none");
        updateEditorCounter();
        editorModalInstance.show();
    });

    // Edit or Customize Template
    $(document).on("click", ".btn_edit_temp", function () {
        let id = $(this).data("id");
        let item = currentTemplates.find(t => t.id == id);
        if (!item) return;

        $("#editor_id").val(item.id);
        $("#editor_is_system").val(item.is_system ? "1" : "0");
        $("#editor_title").val(item.temp_title);
        $("#editor_type").val(item.temp_type);
        $("#editor_audience").val(item.target_audience);
        $("#editor_language").val(item.language || "bn");
        $("#editor_text").val(item.temp_text);
        $("#editor_is_default").prop("checked", item.is_default == 1);

        if (item.is_system) {
            $("#modal_editor_title").html('<i class="bi bi-copy text-success me-2"></i> Customize System Template for Your School');
            $("#editor_system_notice").removeClass("d-none");
        } else {
            $("#modal_editor_title").html('<i class="bi bi-pencil-square text-primary me-2"></i> Edit Custom Template');
            $("#editor_system_notice").addClass("d-none");
        }

        updateEditorCounter();
        editorModalInstance.show();
    });

    // Save Template Handler
    $("#btn_save_template").on("click", function () {
        let title = $("#editor_title").val().trim();
        let text = $("#editor_text").val().trim();

        if (!title || !text) {
            Swal.fire({
                icon: 'warning',
                title: 'Required Fields Missing',
                text: 'Please enter both template title and message text.'
            });
            return;
        }

        let id = $("#editor_id").val();
        let type = $("#editor_type").val();
        let aud = $("#editor_audience").val();
        let lang = $("#editor_language").val();
        let isDef = $("#editor_is_default").is(":checked") ? 1 : 0;

        let btn = $(this);
        btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
            url: "ajax/sms-template-actions.php",
            type: "POST",
            data: {
                action: "save",
                id: id,
                temp_title: title,
                temp_type: type,
                target_audience: aud,
                temp_text: text,
                language: lang,
                is_default: isDef
            },
            dataType: "json",
            success: function (res) {
                btn.prop("disabled", false).html('<i class="bi bi-check2-circle me-1"></i> Save Template');
                if (res.status === 'success') {
                    editorModalInstance.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved Successfully',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadTemplates();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Save Failed',
                        text: res.message
                    });
                }
            },
            error: function (xhr, status, error) {
                btn.prop("disabled", false).html('<i class="bi bi-check2-circle me-1"></i> Save Template');
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Failed to save template: ' + (xhr.responseText || status)
                });
            }
        });
    });

    // Set Default Template Handler
    $(document).on("click", ".btn_set_default", function () {
        let id = $(this).data("id");
        $.ajax({
            url: "ajax/sms-template-actions.php",
            type: "POST",
            data: { action: "set_default", id: id },
            dataType: "json",
            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Default Updated',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    loadTemplates();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            }
        });
    });

    // Delete Template Handler
    $(document).on("click", ".btn_delete_temp", function () {
        let id = $(this).data("id");
        Swal.fire({
            title: 'Delete Template?',
            text: 'Are you sure you want to delete this custom template?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "ajax/sms-template-actions.php",
                    type: "POST",
                    data: { action: "delete", id: id },
                    dataType: "json",
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            loadTemplates();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Delete Failed', text: res.message });
                        }
                    }
                });
            }
        });
    });

    // Use Template in Messaging Console
    $(document).on("click", ".btn_use_temp", function () {
        let id = $(this).data("id");
        let item = currentTemplates.find(t => t.id == id);
        if (item) {
            // Save to sessionStorage or redirect directly to messaging-send.php with text
            sessionStorage.setItem("picked_template_text", item.temp_text);
            window.location.href = "messaging-send.php?load_template=session";
        }
    });
});
</script>
