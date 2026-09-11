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
<!-- Issue Tracker Floating Trigger -->
<div id="eimbox-issue-tracker-floating" style="position: fixed; bottom: 42px; right: 20px; z-index: 99998;">
    <button type="button" class="btn btn-sm btn-dark shadow-lg d-flex align-items-center gap-2 px-3 py-2 rounded-pill" 
            onclick="eimboxOpenIssueModal()" style="border: 2px solid rgba(255,255,255,0.2); backdrop-filter: blur(8px);">
        <!-- Mini circular SVG -->
        <div style="width: 22px; height: 22px; position: relative;">
            <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                      fill="none" stroke="#444" stroke-width="4.5" />
                <path id="eimbox-mini-circle-progress" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                      fill="none" stroke="#28a745" stroke-width="4.5" stroke-dasharray="0, 100" />
            </svg>
        </div>
        <span id="eimbox-mini-issue-text" class="fw-semibold small" style="letter-spacing: 0.3px;">Track Issue</span>
        <span id="eimbox-mini-issue-badge" class="badge rounded-pill bg-danger" style="display:none; font-size: 10px;">0</span>
    </button>
</div>

<!-- Issue Tracker Modal -->
<div class="modal fade" id="eimboxIssueTrackerModal" tabindex="-1" aria-hidden="true" style="z-index: 105000;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            
            <!-- Modal Header -->
            <div class="modal-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <!-- Circular Health / Problem Score -->
                    <div style="width: 58px; height: 58px; position: relative;" class="flex-shrink-0">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                  fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="3.5" />
                            <path id="eimbox-modal-circle-progress" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                  fill="none" stroke="#28a745" stroke-width="3.8" stroke-dasharray="0, 100" stroke-linecap="round" />
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center" style="line-height: 1;">
                            <span id="eimbox-modal-prob-percent" class="fw-bold" style="font-size: 13px;">0%</span>
                            <div style="font-size: 8px; opacity: 0.7;">Problem</div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-white mb-0" id="eimbox-modal-title">Screen Issue & Health Tracker</h5>
                            <span id="eimbox-health-badge" class="badge bg-success small">Health: 100%</span>
                        </div>
                        <div class="small opacity-75 mt-1 d-flex align-items-center gap-2">
                            <code class="text-warning bg-black bg-opacity-25 px-2 py-0.5 rounded" id="eimbox-current-script-name"><?= htmlspecialchars($currentScript) ?></code>
                            <span>• Platform:</span>
                            <select id="eimbox-platform-select" class="form-select form-select-sm py-0 px-2 d-inline-block w-auto bg-secondary text-white border-0" onchange="eimboxLoadPageIssues()">
                                <option value="Console" selected>Console</option>
                                <option value="Dashboard">Dashboard</option>
                                <option value="Android Lite">Android Lite</option>
                                <option value="Android Premium">Android Premium</option>
                                <option value="Desktop">Desktop</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="issue-tracker-all.php" class="btn btn-outline-light btn-sm rounded-pill px-3" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Full Tracker
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Nav Tabs -->
            <div class="bg-light border-bottom px-4 pt-2">
                <ul class="nav nav-tabs border-0" id="eimboxIssueTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold" id="tab-dimensions-link" data-bs-toggle="tab" data-bs-target="#tab-dimensions" type="button">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> Screen Dimensions (<span id="eimbox-dim-prob-stat">0%</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="tab-issues-link" data-bs-toggle="tab" data-bs-target="#tab-issues" type="button">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Reported Issues (<span id="eimbox-issues-count-stat">0</span>)
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light bg-opacity-50">
                <div class="tab-content" id="eimboxIssueTabsContent">
                    
                    <!-- TAB 1: DIMENSIONS -->
                    <div class="tab-pane fade show active" id="tab-dimensions" role="tabpanel">
                        <div class="alert alert-info py-2 px-3 small d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <i class="bi bi-info-circle-fill me-1"></i> <strong>Dimension Health Formula:</strong> 
                                Not Tested: 100%, Error: 70%, On Progress: 50%, Bug: 30%, OK / N/A: 0% problem.
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span id="eimbox-dim-autosave-status" class="badge bg-light text-secondary border d-none" style="font-size: 11px;">
                                    <i class="bi bi-check2 me-1"></i> Auto-saved
                                </span>
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="eimboxSaveAllDimensions(true)">
                                    <i class="bi bi-check2-circle me-1"></i> Save All
                                </button>
                            </div>
                        </div>

                        <!-- Dimensions Grid -->
                        <div class="row g-3" id="eimbox-dimensions-grid">
                            <!-- Populated via JS -->
                            <div class="col-12 text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2"></div> Loading dimensions...
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-0">Screen Implementation Notes</label>
                                <span class="text-muted" style="font-size: 11px;">Auto-saves on blur</span>
                            </div>
                            <textarea id="eimbox-dimension-notes" class="form-control" rows="2" placeholder="Write any specific screen-level technical or functional notes here..." onblur="eimboxSaveNotes()"></textarea>
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
                        <div id="eimbox-issue-form-card" class="card shadow-sm border-0 mb-4" style="display: none; background: #fff;">
                            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center border-bottom">
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
    apiBase: "api/v1/issues/",
    dimensions: [
        { key: 'ui', label: 'UI / UX Layout', icon: 'bi-window-sidebar' },
        { key: 'light', label: 'Light Theme', icon: 'bi-sun' },
        { key: 'dark', label: 'Dark Theme', icon: 'bi-moon-stars' },
        { key: 'view', label: 'Data View / Query', icon: 'bi-eye' },
        { key: 'insert', label: 'Data Insert / Add', icon: 'bi-plus-circle' },
        { key: 'update', label: 'Data Update / Edit', icon: 'bi-pencil-square' },
        { key: 'delete', label: 'Data Delete / Remove', icon: 'bi-trash' },
        { key: 'cache', label: 'Cache / Local State', icon: 'bi-hdd' },
        { key: 'push', label: 'Push Sync (Cloud)', icon: 'bi-cloud-arrow-up' },
        { key: 'pull', label: 'Pull Sync (Local)', icon: 'bi-cloud-arrow-down' },
        { key: 'dropdown', label: 'Dropdown / Cascade', icon: 'bi-menu-button-wide' },
        { key: 'modal', label: 'Modal / Popups', icon: 'bi-front' },
        { key: 'print', label: 'Print Layout', icon: 'bi-printer' },
        { key: 'pdf', label: 'PDF Export', icon: 'bi-file-earmark-pdf' },
        { key: 'permission', label: 'RBAC Permission', icon: 'bi-shield-lock' }
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
    if (s === 'ok' || s === 'completed' || s === 'closed') return 'bg-success text-white';
    if (s === 'on progress' || s === 'ongoing') return 'bg-warning text-dark';
    if (s === 'bug') return 'bg-danger bg-opacity-75 text-white';
    if (s === 'error') return 'bg-danger text-white';
    if (s === 'not applicable' || s === 'n/a') return 'bg-info text-dark';
    return 'bg-secondary text-white'; // Not Tested
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
        miniText.innerText = `${prob}% Prob`;
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

    let html = '';
    EIMBOX_ISSUE_CONFIG.dimensions.forEach(d => {
        const val = dims[d.key] || 'Not Tested';
        const badgeClass = eimboxGetStatusBadgeClass(val);

        html += `
        <div class="col-md-4 col-sm-6">
            <div class="card border h-100 shadow-sm p-2" style="background:#fff; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi ${d.icon} text-primary fs-5"></i>
                        <span class="fw-semibold small">${d.label}</span>
                    </div>
                </div>
                <div class="mt-2">
                    <select class="form-select form-select-sm fw-bold ${badgeClass}" 
                            data-dim-key="${d.key}" 
                            onchange="eimboxChangeDimensionBadge(this)">
                        ${statuses.map(s => `<option value="${s}" ${s.toLowerCase() === val.toLowerCase() ? 'selected' : ''}>${s}</option>`).join('')}
                    </select>
                </div>
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
    selectEl.className = `form-select form-select-sm fw-bold ${eimboxGetStatusBadgeClass(val)}`;

    // Update in-memory state
    if (eimboxCurrentIssueData && eimboxCurrentIssueData.dimensions) {
        eimboxCurrentIssueData.dimensions[dimKey] = val;
    }

    // Recalculate and update UI circles immediately
    eimboxRecalculateDimensionsLocal();

    // Show instant saving indicator
    eimboxShowAutosaveStatus('saving', `Saving ${dimKey}...`);

    // Asynchronously save dimension via API
    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}save-dimension.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            route: EIMBOX_ISSUE_CONFIG.script,
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

function eimboxSaveNotes() {
    const notesEl = document.getElementById('eimbox-dimension-notes');
    if (!notesEl) return;
    const notes = notesEl.value.trim();

    eimboxShowAutosaveStatus('saving', 'Saving notes...');

    fetch(`${EIMBOX_ISSUE_CONFIG.apiBase}save-dimension.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            route: EIMBOX_ISSUE_CONFIG.script,
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

    const payload = {
        route: EIMBOX_ISSUE_CONFIG.script,
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
