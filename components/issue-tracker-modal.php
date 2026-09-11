<?php
/**
 * EIMBox Screen Issue & Dimension Health Modal Component
 * Displays screen dimensions health, formula-based problem %, and issue management
 */
$isAdminUser = intval($is_admin ?? $_SESSION['isadmin'] ?? 0);
if ($isAdminUser <= 0) {
    return;
}
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!-- Issue Tracker Floating Trigger: Circular Bar Only (No pill) -->
<div id="eimbox-issue-tracker-floating" style="position: fixed; bottom: 42px; right: 20px; z-index: 99998;">
    <button type="button" class="btn btn-dark shadow-lg rounded-circle p-0 position-relative d-flex align-items-center justify-content-center" 
            onclick="eimboxOpenIssueModal()" 
            id="eimbox-floating-trigger-btn"
            title="Screen Tracker: 0% Problem | 100% Health (Click to open)"
            style="width: 46px; height: 46px; min-width: 46px; min-height: 46px; border: 2px solid rgba(255,255,255,0.25); backdrop-filter: blur(8px); transition: transform 0.2s ease;">
        <!-- Circular Progress SVG Ring -->
        <svg viewBox="0 0 36 36" style="width: 38px; height: 38px; transform: rotate(-90deg);">
            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                  fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="4" />
            <path id="eimbox-mini-circle-progress" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                  fill="none" stroke="#28a745" stroke-width="4.2" stroke-dasharray="0, 100" stroke-linecap="round" />
        </svg>
        <!-- Centered Percentage Value -->
        <div class="position-absolute top-50 start-50 translate-middle text-center text-white" style="line-height: 1; pointer-events: none;">
            <span id="eimbox-mini-issue-text" class="fw-bold" style="font-size: 11px;">0%</span>
        </div>
        <!-- Tiny Notification Badge for Issues (if any) -->
        <span id="eimbox-mini-issue-badge" class="badge rounded-pill bg-danger border border-white position-absolute" 
              style="display: none; font-size: 8.5px; top: -3px; right: -3px; padding: 2px 4px; line-height: 1;">0</span>
    </button>
</div>

<!-- Custom Modal Styling for Compact 18 Dimensions & Mobile Responsiveness -->
<style>
#eimboxIssueTrackerModal .modal-dialog {
    max-width: 980px;
    margin: 0.5rem auto;
}
@media (max-width: 576px) {
    #eimboxIssueTrackerModal .modal-dialog {
        margin: 0.25rem;
        max-width: 100%;
    }
    #eimboxIssueTrackerModal .modal-content {
        border-radius: 12px !important;
    }
}
.eimbox-dim-tile {
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.09);
    border-radius: 8px;
    padding: 6px 6px;
    transition: all 0.15s ease-in-out;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}
.eimbox-dim-tile:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 6px rgba(13, 110, 253, 0.12);
}
.eimbox-dim-select {
    font-size: 10px !important;
    height: 23px !important;
    min-height: 23px !important;
    padding: 0 4px !important;
    border-radius: 5px !important;
    cursor: pointer;
    text-align: center;
    text-align-last: center;
    border: 0 !important;
}
.eimbox-dim-select option {
    background-color: #ffffff;
    color: #212529;
    font-weight: normal;
}
</style>

<!-- Issue Tracker Modal -->
<div class="modal fade" id="eimboxIssueTrackerModal" tabindex="-1" aria-hidden="true" style="z-index: 105000;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            
            <!-- Modal Header -->
            <div class="modal-header bg-dark text-white py-2 px-3 py-md-3 px-md-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <!-- Circular Health / Problem Score -->
                    <div style="width: 46px; height: 46px; position: relative;" class="flex-shrink-0">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                  fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="3.5" />
                            <path id="eimbox-modal-circle-progress" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                  fill="none" stroke="#28a745" stroke-width="3.8" stroke-dasharray="0, 100" stroke-linecap="round" />
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center" style="line-height: 1;">
                            <span id="eimbox-modal-prob-percent" class="fw-bold" style="font-size: 11px;">0%</span>
                            <div style="font-size: 7.5px; opacity: 0.7;">Problem</div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="modal-title fw-bold text-white mb-0" id="eimbox-modal-title" style="font-size: 15px;">Screen Issue & Health Tracker</h6>
                            <span id="eimbox-health-badge" class="badge bg-success" style="font-size: 10px;">Health: 100%</span>
                        </div>
                        <div class="small opacity-75 mt-0.5 d-flex align-items-center gap-1.5 flex-wrap" style="font-size: 11px;">
                            <code class="text-warning bg-black bg-opacity-25 px-1.5 py-0.5 rounded" id="eimbox-current-script-name"><?= htmlspecialchars($currentScript) ?></code>
                            <span class="d-none d-sm-inline text-white-50">• Platform:</span>
                            <select id="eimbox-platform-select" class="form-select form-select-sm py-0 px-1.5 d-inline-block w-auto bg-secondary text-white border-0" style="font-size: 11px; height: 22px;" onchange="eimboxLoadPageIssues()">
                                <option value="Console" selected>Console</option>
                                <option value="Dashboard">Dashboard</option>
                                <option value="Android Lite">Android Lite</option>
                                <option value="Android Premium">Android Premium</option>
                                <option value="Desktop">Desktop</option>
                            </select>
                        </div>
                        <!-- Feature Selector & Linker Bar -->
                        <div class="d-flex align-items-center gap-1.5 flex-wrap mt-1.5" style="font-size: 11px;">
                            <span class="text-white-50"><i class="bi bi-box-seam text-info me-1"></i>Feature:</span>
                            <div class="input-group input-group-sm" style="width: auto;">
                                <select id="eimbox-feature-select" class="form-select form-select-sm py-0 px-2 bg-secondary text-white border-0" style="font-size: 11px; height: 23px; min-width: 170px; max-width: 260px;" onchange="eimboxOnFeatureSelectChange(this)" title="Select existing feature to link this screen">
                                    <option value="0">-- Loading Features --</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-info text-dark py-0 px-2 fw-semibold d-inline-flex align-items-center gap-0.5" style="font-size: 10px; height: 23px;" onclick="eimboxToggleQuickNewFeature()" title="Create & Link New Feature for this Screen">
                                    <i class="bi bi-plus-lg"></i><span>New</span>
                                </button>
                            </div>
                            <span id="eimbox-feature-module-badge" class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25" style="font-size: 9.5px; display: none;"></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-1.5">
                    <a href="issue-tracker-all.php" class="btn btn-outline-light btn-sm rounded-pill px-2 py-0.5 d-none d-sm-inline-flex align-items-center" style="font-size: 11.5px;" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Full Tracker
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Quick New Feature Inline Card -->
            <div id="eimbox-quick-feature-box" class="bg-dark border-top border-bottom border-primary border-opacity-50 p-2.5 px-3 px-md-4 text-white" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-1.5">
                    <span class="fw-bold small text-info d-flex align-items-center gap-1" style="font-size: 11.5px;">
                        <i class="bi bi-folder-plus"></i> Create & Link New Feature for <code><?= htmlspecialchars($currentScript) ?></code>
                    </span>
                    <button type="button" class="btn-close btn-close-white" style="font-size: 8.5px;" onclick="eimboxToggleQuickNewFeature(false)"></button>
                </div>
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <input type="text" id="eimbox-new-feature-name" class="form-control form-control-sm py-1 px-2 bg-secondary bg-opacity-25 text-white border-secondary" style="font-size: 11.5px; height: 28px;" placeholder="Feature Title (e.g. Student Attendance Register)">
                    </div>
                    <div class="col-7 col-md-4">
                        <select id="eimbox-new-feature-module" class="form-select form-select-sm py-1 px-2 bg-secondary bg-opacity-25 text-white border-secondary" style="font-size: 11.5px; height: 28px;">
                            <!-- Populated dynamically from modules -->
                        </select>
                    </div>
                    <div class="col-5 col-md-3">
                        <button type="button" class="btn btn-sm btn-success py-1 px-2 rounded-pill w-100 d-inline-flex align-items-center justify-content-center gap-1" style="font-size: 11.5px; height: 28px;" onclick="eimboxCreateAndLinkFeature()">
                            <i class="bi bi-check2"></i> <span>Save & Link</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Nav Tabs -->
            <div class="bg-light border-bottom px-2 px-md-4 pt-1.5">
                <ul class="nav nav-tabs border-0" id="eimboxIssueTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold py-1.5 px-2 px-md-3" id="tab-dimensions-link" data-bs-toggle="tab" data-bs-target="#tab-dimensions" type="button" style="font-size: 12.5px;">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> Dimensions (<span id="eimbox-dim-prob-stat">0%</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold py-1.5 px-2 px-md-3" id="tab-issues-link" data-bs-toggle="tab" data-bs-target="#tab-issues" type="button" style="font-size: 12.5px;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Reported Issues (<span id="eimbox-issues-count-stat">0</span>)
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-2 p-md-3 bg-light bg-opacity-50">
                <div class="tab-content" id="eimboxIssueTabsContent">
                    
                    <!-- TAB 1: DIMENSIONS -->
                    <div class="tab-pane fade show active" id="tab-dimensions" role="tabpanel">
                        <!-- Compact Top Toolbar -->
                        <div class="card border shadow-xs p-1.5 p-md-2 mb-2 bg-white" style="border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-center gap-1 flex-wrap">
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 10.5px;">
                                        <i class="bi bi-grid-3x3 me-1"></i> 18 Dimensions
                                    </span>
                                    <span class="badge border text-secondary d-none d-sm-inline-block" style="font-size: 9.5px;" title="Formula: Not Tested: 100%, Error: 70%, Progress: 50%, Bug: 30%, OK/NA: 0%">
                                        <i class="bi bi-info-circle me-0.5"></i> Health Formula
                                    </span>
                                    <span id="eimbox-dim-autosave-status" class="badge bg-light text-secondary border d-none" style="font-size: 10px;">
                                        <i class="bi bi-check2 me-1"></i> Saved
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-1 ms-auto">
                                    <button class="btn btn-sm btn-outline-success py-0.5 px-2 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 10.5px; height: 24px;" onclick="eimboxMarkAllDimensionsOK()" title="Mark all 18 dimensions as OK">
                                        <i class="bi bi-check-all"></i> <span>All OK</span>
                                    </button>
                                    <button class="btn btn-sm btn-primary py-0.5 px-2.5 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 10.5px; height: 24px;" onclick="eimboxSaveAllDimensions(true)" title="Save All Dimensions">
                                        <i class="bi bi-check2-circle"></i> <span>Save</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Dimensions Grid: 6 columns on desktop/laptop, 3 columns on mobile (3 rows of 6 = 18 tiles) -->
                        <div class="row row-cols-3 row-cols-sm-3 row-cols-md-6 g-1.5 g-md-2" id="eimbox-dimensions-grid">
                            <!-- Populated via JS -->
                            <div class="col-12 text-center py-3 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div> Loading dimensions...
                            </div>
                        </div>

                        <!-- Notes Section (Compact) -->
                        <div class="mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-0.5">
                                <label class="form-label fw-semibold text-muted text-uppercase mb-0" style="font-size: 9.5px; letter-spacing: 0.3px;">
                                    <i class="bi bi-sticky me-1"></i> Screen Implementation Notes
                                </label>
                                <span class="text-muted" style="font-size: 9.5px;">Auto-saves on blur</span>
                            </div>
                            <textarea id="eimbox-dimension-notes" class="form-control form-control-sm py-1 px-2" rows="1" style="font-size: 11px; border-radius: 6px;" placeholder="Write any specific technical or functional notes here..." onblur="eimboxSaveNotes()"></textarea>
                        </div>
                    </div>

                    <!-- TAB 2: ISSUES -->
                    <div class="tab-pane fade" id="tab-issues" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark">Active Issues & Enhancements</h6>
                            <button class="btn btn-sm btn-success rounded-pill px-3" onclick="eimboxShowIssueForm()">
                                <i class="bi bi-plus-circle me-1"></i> Add New Issue
                            </button>
                        </div>

                        <!-- Issue Form Card (Collapsible) -->
                        <div id="eimbox-issue-form-card" class="card shadow-sm border-0 mb-4" style="display: none; border-radius: 12px;">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center border-bottom">
                                <span class="fw-bold small" id="eimbox-form-mode-title"><i class="bi bi-pencil-square me-1"></i> Add Issue</span>
                                <button type="button" class="btn-close" onclick="eimboxHideIssueForm()"></button>
                            </div>
                            <div class="card-body p-3">
                                <input type="hidden" id="eimbox-issue-id" value="0">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Module</label>
                                        <select id="eimbox-issue-module" class="form-select form-select-sm">
                                            <!-- Loaded dynamically -->
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-semibold">Sub-Topic / Component</label>
                                        <input type="text" id="eimbox-issue-topic" class="form-control form-select-sm" placeholder="e.g. Export Button, Query Filtering">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Priority</label>
                                        <select id="eimbox-issue-priority" class="form-select form-select-sm">
                                            <option value="Critical">Critical</option>
                                            <option value="High">High</option>
                                            <option value="Medium" selected>Medium</option>
                                            <option value="Low">Low</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Issue / Bug Description</label>
                                        <textarea id="eimbox-issue-desc" class="form-control form-control-sm" rows="2" placeholder="Describe the issue, reproduction steps or requirements..."></textarea>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Status</label>
                                        <select id="eimbox-issue-status" class="form-select form-select-sm">
                                            <option value="Open" selected>Open</option>
                                            <option value="Ongoing">Ongoing</option>
                                            <option value="Testing">Testing</option>
                                            <option value="Completed">Completed</option>
                                            <option value="On Hold">On Hold</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Progress: <span id="eimbox-progress-label">0%</span></label>
                                        <input type="range" class="form-range" id="eimbox-issue-progress" min="0" max="100" value="0" 
                                               oninput="document.getElementById('eimbox-progress-label').innerText = this.value + '%'">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">Assigned To</label>
                                        <input type="text" id="eimbox-issue-assignee" class="form-control form-control-sm" placeholder="e.g. Reaz / Antigravity">
                                    </div>

                                    <div class="col-12 text-end mt-3">
                                        <button type="button" class="btn btn-sm btn-light me-2" onclick="eimboxHideIssueForm()">Cancel</button>
                                        <button type="button" class="btn btn-sm btn-primary px-4" onclick="eimboxSaveIssue()">Save Issue</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Issues List Table / Cards -->
                        <div id="eimbox-issues-list-container">
                            <!-- Populated via JS -->
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-white py-2 px-4 d-flex justify-content-between align-items-center border-top">
                <small class="text-muted">
                    <i class="bi bi-clock-history me-1"></i> Auto-synced with <code>issues_tracker</code> & <code>eimbox_features</code>
                </small>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<!-- Issue Tracker Client Script -->
<script>
const EIMBOX_ISSUE_CONFIG = {
    script: "<?= $currentScript ?>",
    apiBase: "issues/",
    featureId: 0,
    featureName: "",
    moduleName: "General",
    dimensions: [
        { key: 'ui', label: 'UI / UX Layout', short: 'UI/UX', icon: 'bi-window-sidebar' },
        { key: 'light', label: 'Light Theme', short: 'Light', icon: 'bi-sun' },
        { key: 'dark', label: 'Dark Theme', short: 'Dark', icon: 'bi-moon-stars' },
        { key: 'view', label: 'Data View / Query', short: 'View', icon: 'bi-eye' },
        { key: 'insert', label: 'Data Insert / Add', short: 'Insert', icon: 'bi-plus-circle' },
        { key: 'update', label: 'Data Update / Edit', short: 'Update', icon: 'bi-pencil-square' },
        { key: 'delete', label: 'Data Delete / Remove', short: 'Delete', icon: 'bi-trash' },
        { key: 'cache', label: 'Cache / Local State', short: 'Cache', icon: 'bi-hdd' },
        { key: 'push', label: 'Push Sync (Cloud)', short: 'Push', icon: 'bi-cloud-arrow-up' },
        { key: 'pull', label: 'Pull Sync (Local)', short: 'Pull', icon: 'bi-cloud-arrow-down' },
        { key: 'dropdown', label: 'Dropdown / Cascade', short: 'Dropdown', icon: 'bi-menu-button-wide' },
        { key: 'modal', label: 'Modal / Popups', short: 'Modal', icon: 'bi-front' },
        { key: 'print', label: 'Print Layout', short: 'Print', icon: 'bi-printer' },
        { key: 'pdf', label: 'PDF Export', short: 'PDF', icon: 'bi-file-earmark-pdf' },
        { key: 'permission', label: 'RBAC Permission', short: 'RBAC', icon: 'bi-shield-lock' },
        { key: 'documentation', label: 'Documentation', short: 'Doc', icon: 'bi-file-text' },
        { key: 'faq', label: 'FAQ & Help', short: 'FAQ', icon: 'bi-question-circle' },
        { key: 'youtube_video', label: 'YouTube Video', short: 'Video', icon: 'bi-youtube' }
    ]
};

let eimboxCurrentIssueData = null;

// Initialize on page ready
document.addEventListener("DOMContentLoaded", function () {
    eimboxLoadPageIssues(false);
});

function eimboxOpenIssueModal() {
    const modalEl = document.getElementById('eimboxIssueTrackerModal');
    if (!modalEl) return;
    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    bsModal.show();
    eimboxLoadPageIssues(true);
}

function eimboxGetStatusBadgeClass(status) {
    const s = String(status || '').toLowerCase().trim();
    if (s === 'ok' || s === 'completed' || s === 'closed') return 'bg-success text-white border-0';
    if (s === 'on progress' || s === 'ongoing' || s === 'progress') return 'bg-warning text-dark border-0';
    if (s === 'bug') return 'bg-danger bg-opacity-75 text-white border-0';
    if (s === 'error') return 'bg-danger text-white border-0';
    if (s === 'not applicable' || s === 'n/a' || s === 'na') return 'bg-info text-dark border-0';
    return 'bg-secondary text-white border-0'; // Not Tested
}

function eimboxUpdateCircularUI(problemPercent, healthPercent) {
    const prob = parseFloat(problemPercent) || 0;
    const health = parseFloat(healthPercent) || (100 - prob);

    // Color logic
    let strokeColor = '#28a745';
    if (prob > 50) strokeColor = '#dc3545';
    else if (prob > 20) strokeColor = '#ffc107';

    // Mini trigger update
    const miniCircle = document.getElementById('eimbox-mini-circle-progress');
    if (miniCircle) {
        miniCircle.setAttribute('stroke-dasharray', `${prob}, 100`);
        miniCircle.setAttribute('stroke', strokeColor);
    }
    const miniText = document.getElementById('eimbox-mini-issue-text');
    if (miniText) {
        miniText.innerText = `${prob}%`;
    }
    const triggerBtn = document.getElementById('eimbox-floating-trigger-btn');
    if (triggerBtn) {
        triggerBtn.setAttribute('title', `Screen Tracker: ${prob}% Problem | ${health}% Health (Click to open)`);
    }

    // Modal circle update
    const modalCircle = document.getElementById('eimbox-modal-circle-progress');
    if (modalCircle) {
        modalCircle.setAttribute('stroke-dasharray', `${prob}, 100`);
        modalCircle.setAttribute('stroke', strokeColor);
    }
    const modalProbText = document.getElementById('eimbox-modal-prob-percent');
    if (modalProbText) {
        modalProbText.innerText = `${prob}%`;
    }
    const healthBadge = document.getElementById('eimbox-health-badge');
    if (healthBadge) {
        healthBadge.innerText = `Health: ${health}%`;
        healthBadge.className = `badge small ${prob > 50 ? 'bg-danger' : (prob > 20 ? 'bg-warning text-dark' : 'bg-success')}`;
    }
}

function eimboxLoadPageIssues(openAfterLoad) {
    const platform = document.getElementById('eimbox-platform-select') ? document.getElementById('eimbox-platform-select').value : 'Console';
    const script = EIMBOX_ISSUE_CONFIG.script;

    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}get-page-issues.php?script=${encodeURIComponent(script)}&platform=${encodeURIComponent(platform)}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data) {
                eimboxCurrentIssueData = res.data;

                // Update feature tracking config
                const curFeat = res.data.current_feature || null;
                if (curFeat && curFeat.id > 0) {
                    EIMBOX_ISSUE_CONFIG.featureId = parseInt(curFeat.id);
                    EIMBOX_ISSUE_CONFIG.featureName = curFeat.feature_name;
                    EIMBOX_ISSUE_CONFIG.moduleName = curFeat.module_name || 'General';
                } else {
                    EIMBOX_ISSUE_CONFIG.featureId = 0;
                    EIMBOX_ISSUE_CONFIG.featureName = '';
                    EIMBOX_ISSUE_CONFIG.moduleName = 'General';
                }

                // Populate feature dropdown and badge
                eimboxPopulateFeatureSelect(res.data.features || [], res.data.modules || [], curFeat);

                eimboxRenderDimensions(res.data);
                eimboxRenderIssues(res.data);
                eimboxUpdateCircularUI(res.data.problem_percent, res.data.health_percent);

                // Populate modules dropdown in form
                const modSelect = document.getElementById('eimbox-issue-module');
                if (modSelect && res.data.modules) {
                    modSelect.innerHTML = res.data.modules.map(m => `<option value="${m.module_name}">${m.module_name}</option>`).join('');
                }
            }
        })
        .catch(err => console.error('EIMBox Issue load error:', err));
}

function eimboxPopulateFeatureSelect(features, modules, currentFeature) {
    const selectEl = document.getElementById('eimbox-feature-select');
    const badgeEl = document.getElementById('eimbox-feature-module-badge');
    const newModSelect = document.getElementById('eimbox-new-feature-module');
    if (!selectEl) return;

    if (newModSelect && modules && modules.length > 0) {
        newModSelect.innerHTML = modules.map(m => `<option value="${m.module_name}">${m.module_name}</option>`).join('');
    }

    let html = `<option value="0">-- Select / Link Feature --</option>`;
    html += `<option value="__NEW__" class="fw-bold text-info">+ [ Create New Feature... ]</option>`;

    // Group features by module
    const groups = {};
    (features || []).forEach(f => {
        const mod = f.module_name || 'General';
        if (!groups[mod]) groups[mod] = [];
        groups[mod].push(f);
    });

    Object.keys(groups).sort().forEach(modName => {
        html += `<optgroup label="${modName}">`;
        groups[modName].forEach(f => {
            const isSel = currentFeature && parseInt(currentFeature.id) === parseInt(f.id);
            html += `<option value="${f.id}" data-module="${f.module_name}" data-name="${f.feature_name}" ${isSel ? 'selected' : ''}>${f.feature_name}</option>`;
        });
        html += `</optgroup>`;
    });

    selectEl.innerHTML = html;

    if (currentFeature && currentFeature.id > 0) {
        selectEl.value = currentFeature.id;
        if (badgeEl) {
            badgeEl.style.display = 'inline-block';
            badgeEl.className = 'badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25';
            badgeEl.innerHTML = `<i class="bi bi-folder2 me-1"></i>${currentFeature.module_name}`;
        }
    } else {
        selectEl.value = '0';
        if (badgeEl) {
            badgeEl.style.display = 'inline-block';
            badgeEl.className = 'badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25';
            badgeEl.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i>Unlinked Feature`;
        }
    }
}

function eimboxOnFeatureSelectChange(selectEl) {
    const val = selectEl.value;
    if (val === '__NEW__') {
        eimboxToggleQuickNewFeature(true);
        selectEl.value = EIMBOX_ISSUE_CONFIG.featureId || '0';
        return;
    }

    const featureId = parseInt(val) || 0;
    if (featureId <= 0) {
        return;
    }

    const opt = selectEl.selectedOptions[0];
    const featureName = opt ? opt.getAttribute('data-name') : '';
    const moduleName = opt ? opt.getAttribute('data-module') : '';

    eimboxShowAutosaveStatus('saving', 'Linking feature...');

    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}manage-feature.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'link_screen',
            route: EIMBOX_ISSUE_CONFIG.script,
            feature_id: featureId
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            EIMBOX_ISSUE_CONFIG.featureId = featureId;
            EIMBOX_ISSUE_CONFIG.featureName = featureName;
            EIMBOX_ISSUE_CONFIG.moduleName = moduleName;

            const badgeEl = document.getElementById('eimbox-feature-module-badge');
            if (badgeEl) {
                badgeEl.style.display = 'inline-block';
                badgeEl.className = 'badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25';
                badgeEl.innerHTML = `<i class="bi bi-folder2 me-1"></i>${moduleName}`;
            }

            eimboxShowAutosaveStatus('saved', `Linked to ${featureName} ✓`);
            eimboxLoadPageIssues();
        } else {
            eimboxShowAutosaveStatus('error', res.message || 'Link failed');
        }
    })
    .catch(err => {
        eimboxShowAutosaveStatus('error', 'Network error');
        console.error('Feature link error:', err);
    });
}

function eimboxToggleQuickNewFeature(show) {
    const box = document.getElementById('eimbox-quick-feature-box');
    if (!box) return;
    if (show === undefined) {
        box.style.display = box.style.display === 'none' ? 'block' : 'none';
    } else {
        box.style.display = show ? 'block' : 'none';
    }
    if (box.style.display === 'block') {
        const input = document.getElementById('eimbox-new-feature-name');
        if (input) {
            if (!input.value) {
                input.value = EIMBOX_ISSUE_CONFIG.script.replace(/\.[^/.]+$/, '').replace(/[-_]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }
            input.focus();
        }
    }
}

function eimboxCreateAndLinkFeature() {
    const nameInput = document.getElementById('eimbox-new-feature-name');
    const modSelect = document.getElementById('eimbox-new-feature-module');
    const name = nameInput ? nameInput.value.trim() : '';
    const moduleName = modSelect ? modSelect.value : 'General';

    if (!name) {
        alert('Please enter a feature name.');
        if (nameInput) nameInput.focus();
        return;
    }

    eimboxShowAutosaveStatus('saving', 'Creating feature...');

    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}manage-feature.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'create',
            feature_name: name,
            module_name: moduleName,
            route: EIMBOX_ISSUE_CONFIG.script
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            eimboxToggleQuickNewFeature(false);
            eimboxShowAutosaveStatus('saved', `Created & Linked: ${name} ✓`);
            eimboxLoadPageIssues();
        } else {
            eimboxShowAutosaveStatus('error', res.message || 'Error creating feature');
            alert(res.message || 'Error creating feature');
        }
    })
    .catch(err => {
        eimboxShowAutosaveStatus('error', 'Network error');
        console.error('Feature creation error:', err);
    });
}

function eimboxRenderDimensions(data) {
    const container = document.getElementById('eimbox-dimensions-grid');
    if (!container) return;

    const dims = data.dimensions || {};
    const notesEl = document.getElementById('eimbox-dimension-notes');
    if (notesEl) notesEl.value = dims.notes || '';

    const dimProbAvg = data.dim_problem_avg || 0;
    const statEl = document.getElementById('eimbox-dim-prob-stat');
    if (statEl) statEl.innerText = `${dimProbAvg}% Prob`;

    const statuses = ['OK', 'On Progress', 'bug', 'error', 'Not Tested', 'Not applicable'];
    const statusLabels = {
        'OK': 'OK',
        'On Progress': 'Progress',
        'bug': 'Bug',
        'error': 'Error',
        'Not Tested': 'Untested',
        'Not applicable': 'N/A'
    };

    let html = '';
    EIMBOX_ISSUE_CONFIG.dimensions.forEach(d => {
        const val = dims[d.key] || 'Not Tested';
        const badgeClass = eimboxGetStatusBadgeClass(val);

        html += `
        <div class="col">
            <div class="eimbox-dim-tile h-100 d-flex flex-column justify-content-between">
                <!-- Top: Icon & Label -->
                <div class="d-flex align-items-center gap-1 text-truncate mb-1" title="${d.label}: ${val}">
                    <i class="bi ${d.icon} text-primary" style="font-size: 11.5px; flex-shrink: 0;"></i>
                    <span class="fw-bold text-truncate" style="font-size: 10px; line-height: 1.2;">
                        <span class="d-none d-lg-inline">${d.label}</span>
                        <span class="d-inline d-lg-none">${d.short}</span>
                    </span>
                </div>
                <!-- Bottom: Compact Select Dropdown -->
                <select class="form-select form-select-sm fw-bold eimbox-dim-select ${badgeClass}" 
                        data-dim-key="${d.key}" 
                        onchange="eimboxChangeDimensionBadge(this)">
                    ${statuses.map(s => {
                        const isSelected = s.toLowerCase() === val.toLowerCase();
                        const shortLbl = statusLabels[s] || s;
                        return `<option value="${s}" ${isSelected ? 'selected' : ''}>${shortLbl}</option>`;
                    }).join('')}
                </select>
            </div>
        </div>`;
    });

    container.innerHTML = html;
}

let eimboxAutosaveTimer = null;
function eimboxShowAutosaveStatus(type, text) {
    const el = document.getElementById('eimbox-dim-autosave-status');
    if (!el) return;

    if (eimboxAutosaveTimer) clearTimeout(eimboxAutosaveTimer);

    el.classList.remove('d-none', 'bg-light', 'bg-success', 'bg-danger', 'bg-warning', 'text-white', 'text-secondary', 'text-primary');
    if (type === 'saving') {
        el.className = 'badge bg-light text-primary border';
        el.innerHTML = `<span class="spinner-border spinner-border-sm me-1" style="width: 10px; height: 10px;"></span> ${text}`;
    } else if (type === 'saved') {
        el.className = 'badge bg-success text-white shadow-sm';
        el.innerHTML = `<i class="bi bi-check2-circle me-1"></i> ${text}`;
        eimboxAutosaveTimer = setTimeout(() => {
            el.classList.add('d-none');
        }, 2500);
    } else if (type === 'error') {
        el.className = 'badge bg-danger text-white shadow-sm';
        el.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> ${text}`;
        eimboxAutosaveTimer = setTimeout(() => {
            el.classList.add('d-none');
        }, 4000);
    }
}

function eimboxGetProblemScore(status) {
    const s = String(status || '').toLowerCase().trim();
    if (s === 'not tested' || s === 'nottest') return 100.0;
    if (s === 'error') return 70.0;
    if (s === 'on progress' || s === 'ongoing' || s === 'progress') return 50.0;
    if (s === 'bug') return 30.0;
    if (s === 'ok' || s === 'completed' || s === 'not applicable' || s === 'n/a' || s === 'na') return 0.0;
    return 100.0;
}

function eimboxRecalculateDimensionsLocal() {
    const selects = document.querySelectorAll('[data-dim-key]');
    if (!selects.length) return;

    let totalScore = 0;
    selects.forEach(s => {
        totalScore += eimboxGetProblemScore(s.value);
    });

    const dimProbAvg = Math.round(totalScore / selects.length);
    const statEl = document.getElementById('eimbox-dim-prob-stat');
    if (statEl) statEl.innerText = `${dimProbAvg}% Prob`;

    const healthPercent = Math.max(0, 100 - dimProbAvg);
    eimboxUpdateCircularUI(dimProbAvg, healthPercent);
}

function eimboxChangeDimensionBadge(selectEl) {
    const val = selectEl.value;
    const dimKey = selectEl.getAttribute('data-dim-key');
    selectEl.className = `form-select form-select-sm fw-bold eimbox-dim-select ${eimboxGetStatusBadgeClass(val)}`;

    // Update in-memory state
    if (eimboxCurrentIssueData && eimboxCurrentIssueData.dimensions) {
        eimboxCurrentIssueData.dimensions[dimKey] = val;
    }

    // Recalculate and update UI circles immediately
    eimboxRecalculateDimensionsLocal();

    // Show instant saving indicator
    eimboxShowAutosaveStatus('saving', `Saving ${dimKey}...`);

    const platform = document.getElementById('eimbox-platform-select') ? document.getElementById('eimbox-platform-select').value : 'Console';

    // Asynchronously save dimension via API
    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}save-dimension.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            route: EIMBOX_ISSUE_CONFIG.script,
            feature_id: EIMBOX_ISSUE_CONFIG.featureId || null,
            platform: platform,
            dimension: dimKey,
            status: val
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            eimboxShowAutosaveStatus('saved', 'Auto-saved ✓');
        } else {
            eimboxShowAutosaveStatus('error', res.message || 'Save failed');
            console.error('Dimension save error:', res.message);
        }
    })
    .catch(err => {
        eimboxShowAutosaveStatus('error', 'Network error');
        console.error('Dimension save error:', err);
    });
}

function eimboxMarkAllDimensionsOK() {
    const selects = document.querySelectorAll('#eimbox-dimensions-grid select[data-dim-key]');
    if (!selects.length) return;

    const allDims = {};
    selects.forEach(s => {
        s.value = 'OK';
        s.className = `form-select form-select-sm fw-bold eimbox-dim-select ${eimboxGetStatusBadgeClass('OK')}`;
        const k = s.getAttribute('data-dim-key');
        if (k) allDims[k] = 'OK';
    });

    if (eimboxCurrentIssueData && eimboxCurrentIssueData.dimensions) {
        Object.assign(eimboxCurrentIssueData.dimensions, allDims);
    }

    eimboxRecalculateDimensionsLocal();
    eimboxShowAutosaveStatus('saving', 'Setting all dimensions OK...');

    const platform = document.getElementById('eimbox-platform-select') ? document.getElementById('eimbox-platform-select').value : 'Console';

    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}save-dimension.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            route: EIMBOX_ISSUE_CONFIG.script,
            feature_id: EIMBOX_ISSUE_CONFIG.featureId || null,
            platform: platform,
            dimensions: allDims
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            eimboxShowAutosaveStatus('saved', 'All 18 dimensions OK');
        } else {
            eimboxShowAutosaveStatus('error', res.message || 'Error updating');
        }
    })
    .catch(err => {
        eimboxShowAutosaveStatus('error', 'Network error');
    });
}

function eimboxSaveNotes() {
    const notesEl = document.getElementById('eimbox-dimension-notes');
    if (!notesEl) return;
    const notes = notesEl.value.trim();
    const platform = document.getElementById('eimbox-platform-select') ? document.getElementById('eimbox-platform-select').value : 'Console';

    eimboxShowAutosaveStatus('saving', 'Saving notes...');

    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}save-dimension.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            route: EIMBOX_ISSUE_CONFIG.script,
            feature_id: EIMBOX_ISSUE_CONFIG.featureId || null,
            platform: platform,
            notes: notes
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            eimboxShowAutosaveStatus('saved', 'Notes saved ✓');
        } else {
            eimboxShowAutosaveStatus('error', res.message || 'Notes save failed');
        }
    })
    .catch(err => {
        eimboxShowAutosaveStatus('error', 'Network error');
        console.error('Notes save error:', err);
    });
}

function eimboxSaveAllDimensions(isManual = false) {
    const selects = document.querySelectorAll('[data-dim-key]');
    const dimUpdates = {};
    selects.forEach(s => {
        dimUpdates[s.getAttribute('data-dim-key')] = s.value;
    });

    const notes = document.getElementById('eimbox-dimension-notes') ? document.getElementById('eimbox-dimension-notes').value : '';
    const platform = document.getElementById('eimbox-platform-select') ? document.getElementById('eimbox-platform-select').value : 'Console';

    const payload = {
        route: EIMBOX_ISSUE_CONFIG.script,
        feature_id: EIMBOX_ISSUE_CONFIG.featureId || null,
        platform: platform,
        dimensions: dimUpdates,
        notes: notes
    };

    eimboxShowAutosaveStatus('saving', 'Saving all...');

    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}save-dimension.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            eimboxShowAutosaveStatus('saved', 'All saved successfully ✓');
            eimboxLoadPageIssues(false);
        } else {
            eimboxShowAutosaveStatus('error', res.message || 'Error saving');
            if (isManual) alert(res.message || 'Error saving dimensions');
        }
    })
    .catch(err => {
        eimboxShowAutosaveStatus('error', 'Network error');
        console.error('Save dimension error:', err);
    });
}

function eimboxRenderIssues(data) {
    const listContainer = document.getElementById('eimbox-issues-list-container');
    const badgeEl = document.getElementById('eimbox-mini-issue-badge');
    const countStat = document.getElementById('eimbox-issues-count-stat');
    if (!listContainer) return;

    const issues = data.issues || [];
    if (countStat) countStat.innerText = issues.length;

    if (badgeEl) {
        if (issues.length > 0) {
            badgeEl.innerText = issues.length;
            badgeEl.style.display = 'inline-block';
        } else {
            badgeEl.style.display = 'none';
        }
    }

    if (issues.length === 0) {
        listContainer.innerHTML = `
        <div class="card border-0 text-center py-5" style="background:#fff; border-radius: 12px;">
            <i class="bi bi-check-circle-fill text-success fs-1 mb-2"></i>
            <h6 class="fw-bold text-dark">No Issues Reported for this Screen</h6>
            <p class="text-muted small mb-0">Use "Add New Issue" above to report bugs, enhancements or pending items.</p>
        </div>`;
        return;
    }

    let html = '<div class="list-group gap-2">';
    issues.forEach(iss => {
        const priorityColor = iss.priority === 'Critical' ? 'danger' : (iss.priority === 'High' ? 'warning text-dark' : 'secondary');
        const statusColor = iss.status === 'Completed' ? 'success' : (iss.status === 'Ongoing' ? 'primary' : 'dark');

        html += `
        <div class="list-group-item border rounded-3 p-3 shadow-sm bg-white">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="badge bg-${priorityColor} me-1">${iss.priority}</span>
                    <span class="badge bg-${statusColor} me-2">${iss.status}</span>
                    <strong class="text-dark">${iss.topic || iss.feature || 'Issue #' + iss.id}</strong>
                    <span class="badge bg-light text-muted border ms-1">${iss.module}</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="eimboxEditIssue(${JSON.stringify(iss).replace(/"/g, '&quot;')})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="eimboxDeleteIssue(${iss.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            
            <p class="mb-2 text-secondary small">${iss.issues || 'No description provided'}</p>

            <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                <div class="d-flex align-items-center gap-2 w-50">
                    <span class="small text-muted" style="font-size: 11px;">Progress:</span>
                    <div class="progress flex-grow-1" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: ${iss.progress_percent || 0}%;"></div>
                    </div>
                    <span class="small fw-bold" style="font-size: 11px;">${iss.progress_percent || 0}%</span>
                </div>
                <div class="small text-muted" style="font-size: 11px;">
                    <i class="bi bi-person me-1"></i>${iss.assigned_to || 'Unassigned'}
                </div>
            </div>
        </div>`;
    });
    html += '</div>';

    listContainer.innerHTML = html;
}

function eimboxShowIssueForm() {
    document.getElementById('eimbox-issue-form-card').style.display = 'block';
    document.getElementById('eimbox-form-mode-title').innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add New Issue';
    document.getElementById('eimbox-issue-id').value = '0';
    document.getElementById('eimbox-issue-topic').value = '';
    document.getElementById('eimbox-issue-desc').value = '';
    document.getElementById('eimbox-issue-status').value = 'Open';
    document.getElementById('eimbox-issue-priority').value = 'Medium';
    document.getElementById('eimbox-issue-progress').value = 0;
    document.getElementById('eimbox-progress-label').innerText = '0%';
}

function eimboxHideIssueForm() {
    document.getElementById('eimbox-issue-form-card').style.display = 'none';
}

function eimboxEditIssue(iss) {
    document.getElementById('eimbox-issue-form-card').style.display = 'block';
    document.getElementById('eimbox-form-mode-title').innerHTML = `<i class="bi bi-pencil-square me-1"></i> Edit Issue #${iss.id}`;
    document.getElementById('eimbox-issue-id').value = iss.id;
    document.getElementById('eimbox-issue-module').value = iss.module;
    document.getElementById('eimbox-issue-topic').value = iss.topic || '';
    document.getElementById('eimbox-issue-desc').value = iss.issues || '';
    document.getElementById('eimbox-issue-status').value = iss.status;
    document.getElementById('eimbox-issue-priority').value = iss.priority;
    document.getElementById('eimbox-issue-progress').value = iss.progress_percent || 0;
    document.getElementById('eimbox-progress-label').innerText = (iss.progress_percent || 0) + '%';
    document.getElementById('eimbox-issue-assignee').value = iss.assigned_to || '';
}

function eimboxSaveIssue() {
    const id = parseInt(document.getElementById('eimbox-issue-id').value) || 0;
    const action = id > 0 ? 'update' : 'create';
    const platform = document.getElementById('eimbox-platform-select').value;

    const payload = {
        action: action,
        id: id,
        module: document.getElementById('eimbox-issue-module').value,
        feature: document.getElementById('eimbox-issue-topic').value || 'Feature Issue',
        topic: document.getElementById('eimbox-issue-topic').value,
        issues: document.getElementById('eimbox-issue-desc').value,
        status: document.getElementById('eimbox-issue-status').value,
        priority: document.getElementById('eimbox-issue-priority').value,
        progress_percent: parseInt(document.getElementById('eimbox-issue-progress').value) || 0,
        assigned_to: document.getElementById('eimbox-issue-assignee').value,
        platform: platform,
        script: EIMBOX_ISSUE_CONFIG.script
    };

    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}manage-issue.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            eimboxHideIssueForm();
            eimboxLoadPageIssues();
        } else {
            alert(res.message || 'Error saving issue');
        }
    })
    .catch(err => console.error('Save issue error:', err));
}

function eimboxDeleteIssue(id) {
    if (!confirm('Are you sure you want to delete this issue?')) return;

    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}manage-issue.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: id })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            eimboxLoadPageIssues();
        } else {
            alert(res.message || 'Error deleting issue');
        }
    })
    .catch(err => console.error('Delete issue error:', err));
}
</script>
