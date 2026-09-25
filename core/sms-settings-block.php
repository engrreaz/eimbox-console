<?php
$conf = $$blockType ?? [];
$is_checked = (!empty($conf['enabled']) || ($conf[0] ?? 0) == 1) ? 'checked' : '';
$p1 = $conf['priority_1'] ?? ($conf[1] ?? '');
$p2 = $conf['priority_2'] ?? ($conf[2] ?? '');
$p3 = $conf['priority_3'] ?? ($conf[3] ?? '');
$fixed_time = htmlspecialchars($conf['fixed_time'] ?? ($conf[4] ?? ''));
$msg_text = htmlspecialchars($conf['template_text'] ?? ($conf[5] ?? ''));
?>

<div id="<?= $blockName ?>" class="card mt-3 mb-3 border shadow-sm">
    <div class="pt-3 pb-3 card-header bg-light d-flex justify-content-between align-items-center">
        <div class="form-check ml-3 m-0">
            <input class="form-check-input" type="checkbox" id="enabled" style="transform:scale(1.4);" <?= $is_checked ?>>
            <label class="form-check-label text-dark fw-bold pt-0 ps-2" for="enabled">
                <i class="bi bi-bell-fill text-primary me-1"></i> <?= $blockTitle; ?>
            </label>
        </div>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#block_<?= $index ?>">
            <i class="bi bi-chevron-down"></i> Settings
        </button>
    </div>

    <!-- Collapsible Body -->
    <div id="block_<?= $index ?>" class="collapse hide">
        <div class="card-body mt-0 pt-3">
            <div class="row g-3">
                <!-- Left side -->
                <div class="col-md-6 border-end pe-3">
                    <h6 class="text-muted mb-3"><i class="bi bi-gear-wide-connected me-1"></i> Trigger & Priority Setup</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label small text-muted">1st Priority</label>
                                <select class="form-select form-select-sm" id="priority_1">
                                    <option value=""></option>
                                    <option value="on_submit" <?= ($p1 == 'on_submit') ? 'selected' : ''; ?>>On Submit Manually</option>
                                    <option value="after_1st_period" <?= ($p1 == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?= ($p1 == 'on_time') ? 'selected' : ''; ?>>On Exact Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label small text-muted">2nd Priority</label>
                                <select class="form-select form-select-sm" id="priority_2">
                                    <option value=""></option>
                                    <option value="on_submit" <?= ($p2 == 'on_submit') ? 'selected' : ''; ?>>On Submit Attendance</option>
                                    <option value="after_1st_period" <?= ($p2 == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?= ($p2 == 'on_time') ? 'selected' : ''; ?>>On Exact Time</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label small text-muted">3rd Priority</label>
                                <select class="form-select form-select-sm" id="priority_3">
                                    <option value=""></option>
                                    <option value="on_submit" <?= ($p3 == 'on_submit') ? 'selected' : ''; ?>>On Submit Attendance</option>
                                    <option value="after_1st_period" <?= ($p3 == 'after_1st_period') ? 'selected' : ''; ?>>After 1st Period</option>
                                    <option value="on_time" <?= ($p3 == 'on_time') ? 'selected' : ''; ?>>On Exact Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label small text-muted">@ Fixed Scheduled Time</label>
                                <input type="time" id="fixed_time" class="form-control form-control-sm" value="<?= $fixed_time ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12 d-flex align-items-center gap-2">
                            <button class="btn btn-outline-success btn-sm px-3" onclick="savesetting('<?= $blockName ?>', '<?= $blockType ?>');">
                                <i class="bi bi-check-circle me-1"></i> Update Setting
                            </button>
                            <span id="jsondata_<?= $blockName ?>" class="small"></span>
                        </div>
                    </div>
                </div>

                <!-- Right side -->
                <div class="col-md-6 ps-3">
                    <h6 class="text-muted mb-2"><i class="bi bi-chat-left-text me-1"></i> Message Body & Live Preview</h6>
                    <div class="mb-2">
                        <textarea class="form-control form-control-sm" id="template_text" rows="4" placeholder="Enter message body with dynamic tags..."><?= $msg_text ?></textarea>
                    </div>

                    <!-- Preview box -->
                    <div class="small form-label border rounded p-2 bg-info bg-opacity-10 border-info text-dark mb-2">
                        <strong>Preview:</strong> <?= sms_templete_2_text($msg_text) ?>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <button class="btn btn-primary btn-sm loadTemp" data-cat="<?= $blockType ?>" data-block="<?= $blockName ?>">
                            <i class="bi bi-file-earmark-text me-1"></i> SMS Templates
                        </button>
                        <button class="btn btn-info btn-sm text-white loadVar" data-block="<?= $blockName ?>">
                            <i class="bi bi-code-square me-1"></i> Variables
                        </button>
                        <button class="btn btn-danger btn-sm sendTest" data-text="<?= sms_templete_2_text($msg_text) ?>">
                            <i class="bi bi-send-check me-1"></i> Test Sample
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>