<?php
include 'header.php';
include 'core/sms-var.php';

$sql = "SELECT * FROM scinfo WHERE sccode='$sccode' LIMIT 1";
$res = $conn->query($sql);

if (!$res || $res->num_rows == 0) {
    echo "<div class='container-xxl flex-grow-1 container-p-y'><div class='alert alert-danger'>School info not found!</div></div>";
    include 'footer.php';
    exit;
}

$scinfo = $res->fetch_assoc();

// Parse JSON with fallback to legacy pipe
$sms_gateway = get_sms_setting($scinfo['sms_gateway'] ?? '', 'gateway');
$sms_in = get_sms_setting($scinfo['sms_in'] ?? '', 'block');
$sms_out = get_sms_setting($scinfo['sms_out'] ?? '', 'block');
$sms_absent = get_sms_setting($scinfo['sms_absent'] ?? '', 'block');
$sms_payment = get_sms_setting($scinfo['sms_payment'] ?? '', 'block');
$sms_dues = get_sms_setting($scinfo['sms_dues'] ?? '', 'block');
$sms_month_report = get_sms_setting($scinfo['sms_month_report'] ?? '', 'block');

$sms_setting = !empty($sms_gateway['enabled']) ? 1 : (($sms_gateway[0] ?? 0) == 1 ? 1 : 0);
?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-chat-dots-fill text-primary me-2"></i> Messaging & SMS Gateway Engine</h4>
            <span class="text-muted">Configure SMS Gateways, Automation Triggers, and Preset Templates</span>
        </div>
        <div>
            <a href="messaging-send.php" class="btn btn-success me-2">
                <i class="bi bi-send-fill me-1"></i> Send Bulk SMS
            </a>
            <a href="sms-log.php" class="btn btn-outline-primary">
                <i class="bi bi-journal-text me-1"></i> SMS Logs & Audit
            </a>
        </div>
    </div>

    <!-- Gateway Setup Block -->
    <?php include 'core/sms-settings-block-0.php'; ?>

    <div class="row d-print-none mt-4">
        <div class="col-12 grid-margin stretch-card">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-robot text-primary me-1"></i> Automated Event Triggers</h5>
            <?php
            $blockList = [
                ["blockName" => "block_1", "blockType" => "sms_in", "blockTitle" => "Student Attendance (In-Time)"],
                ["blockName" => "block_2", "blockType" => "sms_out", "blockTitle" => "Student Attendance (Out-Time)"],
                ["blockName" => "block_3", "blockType" => "sms_absent", "blockTitle" => "Student Absence Alert"],
                ["blockName" => "block_4", "blockType" => "sms_payment", "blockTitle" => "Payment Confirmation Receipt"],
                ["blockName" => "block_5", "blockType" => "sms_dues", "blockTitle" => "Fee Due & Collection Reminder"],
                ["blockName" => "block_6", "blockType" => "sms_month_report", "blockTitle" => "Monthly Progress Report"]
            ];

            foreach ($blockList as $index => $block) {
                $blockName = $block['blockName'];
                $blockType = $block['blockType'];
                $blockTitle = $block['blockTitle'];
                include 'core/sms-settings-block.php';
            }
            ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- SMS Template Modal -->
<div class="modal fade" id="smsTempModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="max-height:85vh; overflow-y:auto;">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text text-primary me-1"></i> Select SMS Template</h5>
                <div>
                    <button type="button" class="btn btn-success btn-sm createNew me-2">
                        <i class="bi bi-plus-circle me-1"></i> Create New Template
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <div class="modal-body" id="smsTempBody">
                <div class="text-center py-4"><span class="spinner-border text-primary"></span> Loading templates...</div>
            </div>
        </div>
    </div>
</div>

<!-- Create New Template Modal -->
<div class="modal fade" id="createTempModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-pencil-square text-success me-1"></i> Create New Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="newTempForm">
                    <input type="hidden" name="temp_type" id="temp_type">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Template Title</label>
                            <input type="text" name="temp_title" class="form-control form-control-sm" required placeholder="e.g. In-Time Alert Standard">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Target Audience</label>
                            <select name="target_audience" class="form-select form-select-sm">
                                <option value="student">Student / Guardian</option>
                                <option value="teacher">Teacher & Staff</option>
                                <option value="committee">Managing Committee / SMC</option>
                                <option value="all">Broadcast (All)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Template Text</label>
                            <textarea name="temp_text" id="new_temp_text" class="form-control form-control-sm" rows="4" required placeholder="Type template body with dynamic tags like [[STUDENT_NAME]], [[TIME]], [[DATE]]..."></textarea>
                        </div>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-save me-1"></i> Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SMS Variable Modal -->
<div class="modal fade" id="smsVarModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="max-height:85vh; overflow-y:auto;">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="bi bi-code-slash text-info me-1"></i> SMS Dynamic Variables & Tags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Variable Tag</th>
                                <th>Description</th>
                                <th>Sample Value</th>
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
                                            <i class="bi bi-plus-lg"></i> Insert
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
</div>

<script>
    let activeBlock = 'block_1';
    let activeCategory = 'sms_in';

    function readBlockInputs(containerId) {
        const elements = document.querySelectorAll(`#${containerId} input, #${containerId} select, #${containerId} textarea`);
        let values = {};
        elements.forEach(el => {
            let key = el.id || el.name;
            if (!key) return;
            if (el.type === "checkbox") {
                values[key] = el.checked ? 1 : 0;
            } else {
                values[key] = el.value;
            }
        });
        return { values };
    }

    function savesetting(blockId, blockType) {
        let { values } = readBlockInputs(blockId);
        let feedbackEl = $('#jsondata_' + blockId);

        $.ajax({
            type: "POST",
            url: "backend/save-sms-settings.php",
            data: {
                sms_settings: JSON.stringify(values),
                blockbox: blockType
            },
            cache: false,
            beforeSend: function () {
                feedbackEl.html('<span class="text-primary small"><i class="spinner-border spinner-border-sm me-1"></i> Saving...</span>');
            },
            success: function (html) {
                feedbackEl.html(html);
                if (typeof showToast === 'function') {
                    showToast('success', 'Settings updated successfully', 'Success');
                }
            },
            error: function () {
                feedbackEl.html('<span class="text-danger small"><i class="bi bi-exclamation-triangle"></i> Failed to save!</span>');
            }
        });
    }

    $(document).ready(function () {
        // Load Templates
        $(document).on("click", ".loadTemp", function () {
            activeCategory = $(this).data("cat");
            activeBlock = $(this).data("block");

            $("#smsTempBody").html('<div class="text-center py-4"><span class="spinner-border text-primary"></span> Loading templates...</div>');
            var modal = new bootstrap.Modal(document.getElementById('smsTempModal'));
            modal.show();

            $.ajax({
                url: "ajax/load-sms-templates.php",
                type: "POST",
                data: { cat: activeCategory, block: activeBlock },
                success: function (res) {
                    $("#smsTempBody").html(res);
                }
            });
        });

        // Choose template
        $(document).on("click", ".chooseTemp", function () {
            let txt = $(this).data("text");
            let targetTextarea = $("#" + activeBlock).find("textarea");
            targetTextarea.val(txt);

            var modalEl = document.getElementById('smsTempModal');
            var modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        });

        // Open Create Modal
        $(document).on("click", ".createNew", function () {
            $("#temp_type").val(activeCategory);
            var modal = new bootstrap.Modal(document.getElementById('createTempModal'));
            modal.show();
        });

        // Submit New Template
        $("#newTempForm").on("submit", function (e) {
            e.preventDefault();
            $.ajax({
                url: "ajax/sms-new-template.php",
                type: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    if (res === "SUCCESS") {
                        alert("Template Saved Successfully!");
                        var modalEl = document.getElementById('createTempModal');
                        var modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();

                        // Reload template list
                        $(".loadTemp[data-cat='" + activeCategory + "']").first().click();
                    } else {
                        alert("Failed to save: " + res);
                    }
                }
            });
        });

        // Open Variable Modal
        $(document).on("click", ".loadVar", function () {
            activeBlock = $(this).data("block");
            var modal = new bootstrap.Modal(document.getElementById('smsVarModal'));
            modal.show();
        });

        // Choose Variable
        $(document).on("click", ".chooseVar", function () {
            let variable = $(this).data("var");
            let textarea = $("#" + activeBlock).find("textarea")[0];

            if (textarea) {
                let startPos = textarea.selectionStart || 0;
                let endPos = textarea.selectionEnd || 0;
                let oldText = textarea.value;

                textarea.value = oldText.substring(0, startPos) + variable + oldText.substring(endPos, oldText.length);
                textarea.focus();
            }

            var modalEl = document.getElementById('smsVarModal');
            var modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        });

        // Send Test
        $(document).on("click", ".sendTest", function () {
            let text = $(this).data("text");
            let mobile = prompt("Enter test mobile number (e.g. 017xxxxxxxx):");

            if (!mobile || mobile.trim() === "") {
                return;
            }

            $.ajax({
                url: "core/ajax-send-sms.php",
                type: "POST",
                data: {
                    mobile: mobile,
                    text: text,
                    camp: 'Test Sample'
                },
                success: function (res) {
                    if (typeof showToast === 'function') {
                        showToast('info', res, 'Test Message Status');
                    } else {
                        alert("Result: " + res);
                    }
                }
            });
        });
    });
</script>