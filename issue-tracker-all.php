<?php 
require_once 'header.php'; 
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Title & Header Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-bug-fill text-danger fs-3"></i> Cross-Platform Issue Tracker & System Health
            </h4>
            <p class="text-muted mb-0 small">
                Unified issue management and 15-dimension screen verification across Console, Dashboard, Android & Desktop
            </p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="loadAllIssues()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
            </button>
            <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="openCreateFeatureModal()">
                <i class="bi bi-folder-plus me-1"></i> Add Feature
            </button>
            <button class="btn btn-success btn-sm rounded-pill px-3" onclick="openCreateIssueModal()">
                <i class="bi bi-plus-circle me-1"></i> Report New Issue
            </button>
        </div>
    </div>

    <!-- Platform Selector Tabs -->
    <div class="card mb-4 border-0 shadow-sm" style="border-radius: 14px;">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill gap-2" id="platformFilterPills">
                <li class="nav-item">
                    <button class="nav-link active fw-bold py-2" data-platform="All" onclick="setPlatformFilter('All', this)">
                        <i class="bi bi-globe me-1"></i> All Platforms
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 text-dark" data-platform="Console" onclick="setPlatformFilter('Console', this)">
                        <i class="bi bi-terminal-split me-1 text-primary"></i> Console (Materio)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 text-dark" data-platform="Dashboard" onclick="setPlatformFilter('Dashboard', this)">
                        <i class="bi bi-grid-1x2 me-1 text-info"></i> Dashboard (Legacy)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 text-dark" data-platform="Android Lite" onclick="setPlatformFilter('Android Lite', this)">
                        <i class="bi bi-phone me-1 text-warning"></i> Android Lite
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 text-dark" data-platform="Android Premium" onclick="setPlatformFilter('Android Premium', this)">
                        <i class="bi bi-android2 me-1 text-success"></i> Android Native
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 text-dark" data-platform="Desktop" onclick="setPlatformFilter('Desktop', this)">
                        <i class="bi bi-display me-1 text-secondary"></i> Desktop (Electron)
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Circular Health Progress KPI -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 14px; background: #fff;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small">Overall System Health</span>
                        <h3 class="fw-bold mb-0 mt-1" id="kpi-health-percent">100%</h3>
                        <small class="text-muted" id="kpi-health-subtext">Dimension & Issue Health</small>
                    </div>
                    <div style="width: 68px; height: 68px; position: relative;">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                  fill="none" stroke="#eee" stroke-width="4" />
                            <path id="kpi-circle-svg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                  fill="none" stroke="#28a745" stroke-width="4.2" stroke-dasharray="100, 100" stroke-linecap="round" />
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center" style="line-height: 1;">
                            <span id="kpi-circle-text" class="fw-bold" style="font-size: 13px;">0%</span>
                            <div style="font-size: 8px; opacity: 0.6;">Prob</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Issues KPI -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 14px; background: #fff;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small">Total Reported Issues</span>
                        <h3 class="fw-bold mb-0 mt-1 text-dark" id="kpi-total-issues">0</h3>
                        <small class="text-danger fw-semibold" id="kpi-critical-high">0 Critical / High</small>
                    </div>
                    <div class="p-3 bg-light rounded-circle text-primary">
                        <i class="bi bi-list-task fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Open & Ongoing KPI -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 14px; background: #fff;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small">Active Pending Tasks</span>
                        <h3 class="fw-bold mb-0 mt-1 text-warning" id="kpi-active-issues">0</h3>
                        <small class="text-muted" id="kpi-active-subtext">Open & Ongoing Work</small>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 rounded-circle text-warning">
                        <i class="bi bi-hourglass-split fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed & Progress KPI -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 14px; background: #fff;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small">Resolved / Completed</span>
                        <h3 class="fw-bold mb-0 mt-1 text-success" id="kpi-completed-issues">0</h3>
                        <small class="text-muted" id="kpi-avg-progress">Avg Progress: 100%</small>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 rounded-circle text-success">
                        <i class="bi bi-check-circle-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container with Tabs -->
    <div class="card border-0 shadow-sm" style="border-radius: 14px;">
        <!-- Card Header with Navigation Tabs & Filter Toolbar -->
        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <ul class="nav nav-tabs card-header-tabs" id="mainIssueTabs">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-features-catalog">
                            <i class="bi bi-layers-half me-1 text-primary"></i> Features & Dimensions (<span id="tab-features-badge">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab-all-issues">
                            <i class="bi bi-bug me-1 text-danger"></i> Reported Issues (<span id="tab-issues-badge">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab-screen-dimensions">
                            <i class="bi bi-grid-3x3-gap me-1 text-info"></i> Full Matrix (<span id="tab-screens-badge">0</span>)
                        </button>
                    </li>
                </ul>

                <!-- Filter Controls -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" id="filter-search" class="form-control form-control-sm" placeholder="Search features, modules, scripts..." style="width: 220px;" oninput="applyFilters()">
                    <select id="filter-module" class="form-select form-select-sm" style="width: 140px;" onchange="applyFilters()">
                        <option value="All">All Modules</option>
                    </select>
                    <select id="filter-status" class="form-select form-select-sm" style="width: 130px;" onchange="applyFilters()">
                        <option value="All">All Status</option>
                        <option value="Open">Open</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Testing">Testing</option>
                        <option value="Completed">Completed</option>
                        <option value="On Hold">On Hold</option>
                    </select>
                    <select id="filter-priority" class="form-select form-select-sm" style="width: 120px;" onchange="applyFilters()">
                        <option value="All">All Priority</option>
                        <option value="Critical">Critical</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="tab-content">

                <!-- TAB 1: FEATURES & DIMENSIONS TABLE (ORDERED BY MODULE) -->
                <div class="tab-pane fade show active p-0" id="tab-features-catalog">
                    <!-- Compact Legend Bar -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between px-3 py-2 bg-light border-bottom small text-muted">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-dark"><i class="bi bi-info-circle me-1"></i> Dimension Icons:</span>
                            <span><i class="bi bi-check-circle-fill text-success"></i> OK</span>
                            <span><i class="bi bi-x-circle-fill text-danger"></i> Error (70%)</span>
                            <span><i class="bi bi-bug-fill text-danger opacity-75"></i> Bug (30%)</span>
                            <span><i class="bi bi-clock-history text-warning"></i> On Progress (50%)</span>
                            <span><i class="bi bi-dash-circle text-muted"></i> Not Tested (100%)</span>
                            <span><i class="bi bi-slash-circle text-info"></i> N/A (0%)</span>
                        </div>
                        <div class="small text-muted">
                            Ordered by <code>modulelist.slno</code> & Module
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="featuresCatalogTable">
                            <thead class="table-light text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">
                                <tr>
                                    <th style="width: 45px;" class="ps-3 text-center">#</th>
                                    <th style="width: 130px;">Module</th>
                                    <th>Feature & Screen Route</th>
                                    <th style="width: 290px;">15 Dimensions</th>
                                    <th style="width: 85px;" class="text-center">Error %</th>
                                    <th style="width: 95px;" class="text-center">Issues</th>
                                    <th style="width: 110px;" class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="featuresCatalogTableBody">
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <div class="spinner-border spinner-border-sm me-2 text-primary"></div> Loading features catalog...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- TAB 2: ALL ISSUES TABLE -->
                <div class="tab-pane fade" id="tab-all-issues">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="issuesMasterTable">
                            <thead class="table-light text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">
                                <tr>
                                    <th style="width: 60px;" class="ps-3">ID</th>
                                    <th>Platform</th>
                                    <th>Module & Screen</th>
                                    <th>Topic & Description</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th style="width: 150px;">Progress</th>
                                    <th>Assigned To</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="issuesTableBody">
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <div class="spinner-border spinner-border-sm me-2 text-primary"></div> Loading issues...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: SCREEN DIMENSIONS MATRIX -->
                <div class="tab-pane fade p-3" id="tab-screen-dimensions">
                    <div class="alert alert-info py-2 px-3 small d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="bi bi-info-circle-fill me-1"></i> 
                            <strong>15-Dimension Screen Health Matrix:</strong> Evaluates UI, Light, Dark, View, Insert, Update, Delete, Cache, Push, Pull, Dropdown, Modal, Print, PDF, Permission.
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle small mb-0">
                            <thead class="table-dark text-center" style="font-size: 11px;">
                                <tr>
                                    <th class="text-start">Screen / Route</th>
                                    <th>Health</th>
                                    <th>UI</th>
                                    <th>Light</th>
                                    <th>Dark</th>
                                    <th>View</th>
                                    <th>Insert</th>
                                    <th>Update</th>
                                    <th>Delete</th>
                                    <th>Cache</th>
                                    <th>Push</th>
                                    <th>Pull</th>
                                    <th>Dropdown</th>
                                    <th>Modal</th>
                                    <th>Print</th>
                                    <th>PDF</th>
                                    <th>Perm</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="dimensionsMatrixBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Master Issue Edit / Create Modal -->
<div class="modal fade" id="issueMasterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <h5 class="modal-title fw-bold" id="issueModalTitle">
                    <i class="bi bi-bug me-1"></i> Report / Edit Issue
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="modal-issue-id" value="0">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Platform</label>
                        <select id="modal-platform" class="form-select">
                            <option value="Console">Console</option>
                            <option value="Dashboard">Dashboard</option>
                            <option value="Android Lite">Android Lite</option>
                            <option value="Android Premium">Android Premium</option>
                            <option value="Desktop">Desktop</option>
                            <option value="General">General (Cross-platform)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Module</label>
                        <select id="modal-module" class="form-select">
                            <!-- Populated dynamically -->
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Screen / Script Name</label>
                        <input type="text" id="modal-script" class="form-control" placeholder="e.g. attendance-register.php">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Topic / Sub-System</label>
                        <input type="text" id="modal-topic" class="form-control" placeholder="e.g. Punch log sync, Export dialog">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Issue Details / Bug Description</label>
                        <textarea id="modal-desc" class="form-control" rows="3" placeholder="Provide full problem description, edge cases, error logs..."></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Priority</label>
                        <select id="modal-priority" class="form-select">
                            <option value="Critical">Critical</option>
                            <option value="High">High</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Status</label>
                        <select id="modal-status" class="form-select">
                            <option value="Open" selected>Open</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Testing">Testing</option>
                            <option value="Completed">Completed</option>
                            <option value="Closed">Closed</option>
                            <option value="On Hold">On Hold</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Progress: <span id="modal-progress-label">0%</span></label>
                        <input type="range" class="form-range" id="modal-progress" min="0" max="100" value="0" 
                               oninput="document.getElementById('modal-progress-label').innerText = this.value + '%'">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Assigned Developer / QA</label>
                        <input type="text" id="modal-assignee" class="form-control" placeholder="e.g. Developer Name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Closing Target Date</label>
                        <input type="date" id="modal-closing-date" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="saveMasterIssue()">Save Issue</button>
            </div>
        </div>
    </div>
</div>

<!-- Feature Issues List Popup Modal -->
<div class="modal fade" id="featureIssuesPopupModal" tabindex="-1" aria-hidden="true" style="z-index: 106000;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="featureIssuesModalTitle">Feature Issues</h5>
                        <small class="opacity-75" id="featureIssuesModalSubtitle"></small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" id="featureIssuesModalAddBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add Issue
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4 bg-light">
                <div id="featureIssuesModalList">
                    <!-- Populated dynamically -->
                </div>
            </div>
            <div class="modal-footer bg-white py-2 px-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Feature Add / Edit Master Modal -->
<div class="modal fade" id="featureMasterModal" tabindex="-1" aria-hidden="true" style="z-index: 105500;">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h5 class="modal-title fw-bold text-white mb-0" id="featureModalTitle">
                    <i class="bi bi-folder-plus me-1"></i> Add Feature
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="modal-feature-id" value="0">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Module <span class="text-danger">*</span></label>
                        <select id="modal-feature-module" class="form-select">
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Feature Name <span class="text-danger">*</span></label>
                        <input type="text" id="modal-feature-name" class="form-control" placeholder="e.g. Student Admission, Mark Entry Grid">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Associated Script Route</label>
                        <input type="text" id="modal-feature-route" class="form-control" placeholder="e.g. student-profile.php, api/v1/save-mark.php">
                        <small class="text-muted" style="font-size: 11px;">Links this feature to screen dimension matrix & quick Go action</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Feature Description</label>
                        <textarea id="modal-feature-desc" class="form-control" rows="3" placeholder="Explain the feature purpose, components or business rules..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="saveFeatureBtn" onclick="saveFeature()">
                    <i class="bi bi-check2-circle me-1"></i> Save Feature
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<!-- Master Issue Tracker Script -->
<script>
let allIssuesCache = [];
let allDimensionsCache = [];
let allFeaturesCatalogCache = [];
let activePlatformFilter = 'All';

const DIMENSION_CONFIG = [
    { key: 'ui', label: 'UI / UX', short: 'UI' },
    { key: 'light', label: 'Light Theme', short: 'Light' },
    { key: 'dark', label: 'Dark Theme', short: 'Dark' },
    { key: 'view', label: 'Data View', short: 'View' },
    { key: 'insert', label: 'Data Add', short: 'Add' },
    { key: 'update', label: 'Data Edit', short: 'Edit' },
    { key: 'delete', label: 'Data Delete', short: 'Del' },
    { key: 'cache', label: 'Cache Sync', short: 'Cache' },
    { key: 'push', label: 'Cloud Push', short: 'Push' },
    { key: 'pull', label: 'Local Pull', short: 'Pull' },
    { key: 'dropdown', label: 'Cascading Dropdown', short: 'Drop' },
    { key: 'modal', label: 'Dialog / Modal', short: 'Modal' },
    { key: 'print', label: 'Print Layout', short: 'Print' },
    { key: 'pdf', label: 'PDF Export', short: 'PDF' },
    { key: 'permission', label: 'RBAC Access', short: 'Perm' }
];

document.addEventListener("DOMContentLoaded", function () {
    loadAllIssues();
});

function setPlatformFilter(platform, tabBtn) {
    activePlatformFilter = platform;
    document.querySelectorAll('#platformFilterPills .nav-link').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.add('text-dark');
    });
    tabBtn.classList.add('active');
    tabBtn.classList.remove('text-dark');
    loadAllIssues();
}

function loadAllIssues() {
    fetch(`issues/get-all-issues.php?platform=${encodeURIComponent(activePlatformFilter)}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data) {
                allIssuesCache = res.data.issues || [];
                allDimensionsCache = res.data.dimension_screens || [];
                allFeaturesCatalogCache = res.data.features_catalog || [];

                updateKpiDashboard(res.data.kpis);
                populateModuleFilter(res.data.modules);
                applyFilters();
                renderDimensionsMatrix(allDimensionsCache);
            }
        })
        .catch(err => console.error('Error loading all issues:', err));
}

function updateKpiDashboard(kpis) {
    if (!kpis) return;
    const globalHealth = kpis.global_health !== undefined ? kpis.global_health : 100;
    const globalProb = Math.max(0, 100 - globalHealth);

    document.getElementById('kpi-health-percent').innerText = `${globalHealth}%`;
    document.getElementById('kpi-circle-text').innerText = `${globalProb}%`;

    const svgStroke = document.getElementById('kpi-circle-svg');
    if (svgStroke) {
        svgStroke.setAttribute('stroke-dasharray', `${globalHealth}, 100`);
        let strokeColor = '#28a745';
        if (globalHealth < 50) strokeColor = '#dc3545';
        else if (globalHealth < 80) strokeColor = '#ffc107';
        svgStroke.setAttribute('stroke', strokeColor);
    }

    document.getElementById('kpi-total-issues').innerText = kpis.total || 0;
    document.getElementById('kpi-critical-high').innerText = `${kpis.critical || 0} Critical / ${kpis.high || 0} High`;

    const activeCount = (kpis.open || 0) + (kpis.ongoing || 0);
    document.getElementById('kpi-active-issues').innerText = activeCount;

    document.getElementById('kpi-completed-issues').innerText = kpis.completed || 0;
    document.getElementById('kpi-avg-progress').innerText = `Avg Progress: ${kpis.avg_progress || 0}%`;

    document.getElementById('tab-features-badge').innerText = allFeaturesCatalogCache.length;
    document.getElementById('tab-issues-badge').innerText = kpis.total || 0;
    document.getElementById('tab-screens-badge').innerText = allDimensionsCache.length;
}

let allModulesCache = [];

function populateModuleFilter(modules) {
    const modSelect = document.getElementById('filter-module');
    const modalModSelect = document.getElementById('modal-module');
    const modalFeatureModSelect = document.getElementById('modal-feature-module');
    if (!modules || modules.length === 0) return;

    allModulesCache = modules;

    let opts = '<option value="All">All Modules</option>';
    let modalOpts = '';
    modules.forEach(m => {
        const modName = m.module_name || m;
        opts += `<option value="${modName}">${modName}</option>`;
        modalOpts += `<option value="${modName}">${modName}</option>`;
    });
    if (modSelect) modSelect.innerHTML = opts;
    if (modalModSelect) modalModSelect.innerHTML = modalOpts;
    if (modalFeatureModSelect) modalFeatureModSelect.innerHTML = modalOpts;
}

function applyFilters() {
    const search = (document.getElementById('filter-search').value || '').toLowerCase();
    const module = document.getElementById('filter-module').value;
    const status = document.getElementById('filter-status').value;
    const priority = document.getElementById('filter-priority').value;

    // Filter Issues Table
    const filteredIssues = allIssuesCache.filter(item => {
        if (module !== 'All' && item.module !== module) return false;
        if (status !== 'All' && item.status !== status) return false;
        if (priority !== 'All' && item.priority !== priority) return false;
        if (search) {
            const combined = `${item.feature} ${item.topic} ${item.issues} ${item.script} ${item.assigned_to}`.toLowerCase();
            if (!combined.includes(search)) return false;
        }
        return true;
    });
    renderIssuesTable(filteredIssues);

    // Filter Features Catalog Table
    const filteredFeatures = allFeaturesCatalogCache.filter(f => {
        if (module !== 'All' && f.module_name !== module) return false;
        if (status !== 'All') {
            const hasStatus = (f.issues || []).some(iss => iss.status === status);
            if (!hasStatus) return false;
        }
        if (priority !== 'All') {
            const hasPriority = (f.issues || []).some(iss => iss.priority === priority);
            if (!hasPriority) return false;
        }
        if (search) {
            const combined = `${f.feature_name} ${f.module_name} ${f.route || ''} ${f.feature_desc || ''}`.toLowerCase();
            if (!combined.includes(search)) return false;
        }
        return true;
    });
    renderFeaturesCatalogTable(filteredFeatures);
}

function getDimensionIcon(status, dimLabel) {
    const s = String(status || '').toLowerCase().trim();
    if (s === 'ok' || s === 'completed') {
        return `<i class="bi bi-check-circle-fill text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="${dimLabel}: OK"></i>`;
    } else if (s === 'error') {
        return `<i class="bi bi-x-circle-fill text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="${dimLabel}: Error"></i>`;
    } else if (s === 'bug') {
        return `<i class="bi bi-bug-fill text-danger" style="opacity:0.85;" data-bs-toggle="tooltip" data-bs-placement="top" title="${dimLabel}: Bug"></i>`;
    } else if (s === 'on progress' || s === 'ongoing') {
        return `<i class="bi bi-clock-history text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="${dimLabel}: In Progress"></i>`;
    } else if (s === 'not applicable' || s === 'n/a' || s === 'na') {
        return `<i class="bi bi-slash-circle text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="${dimLabel}: N/A"></i>`;
    }
    return `<i class="bi bi-dash-circle text-muted" style="opacity: 0.35;" data-bs-toggle="tooltip" data-bs-placement="top" title="${dimLabel}: Not Tested"></i>`;
}

function renderFeaturesCatalogTable(features) {
    const tbody = document.getElementById('featuresCatalogTableBody');
    if (!tbody) return;

    if (features.length === 0) {
        tbody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                <h6 class="fw-bold mb-1">No Matching Features Found</h6>
                <p class="small mb-0">Try changing your filters or search keywords above.</p>
            </td>
        </tr>`;
        return;
    }

    let html = '';
    features.forEach((f, idx) => {
        const dims = f.dimensions || {};
        const err = f.problem_percent !== undefined ? f.problem_percent : 0;
        const issCount = f.issue_count || 0;

        let errBadgeClass = 'bg-success text-white';
        if (err > 50) errBadgeClass = 'bg-danger text-white';
        else if (err > 20) errBadgeClass = 'bg-warning text-dark';
        else if (err > 0) errBadgeClass = 'bg-info text-dark';

        // 15 compact icons
        let compactIconsHtml = '<div class="d-inline-flex flex-wrap gap-1 align-items-center py-1 px-2 bg-light rounded border">';
        DIMENSION_CONFIG.forEach(d => {
            const val = dims[d.key] || 'Not Tested';
            compactIconsHtml += getDimensionIcon(val, d.label);
        });
        compactIconsHtml += '</div>';

        // Issue Count badge button
        let issueBadgeHtml = '';
        if (issCount > 0) {
            issueBadgeHtml = `
            <button type="button" class="btn btn-sm btn-danger py-0 px-2 rounded-pill fw-bold" onclick="openFeatureIssuesPopup(${idx})" title="Click to view ${issCount} reported issues">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>${issCount}
            </button>`;
        } else {
            issueBadgeHtml = `
            <span class="badge bg-light text-muted border rounded-pill py-1 px-2" style="font-size: 11px;">
                <i class="bi bi-check2 text-success me-1"></i>0
            </span>`;
        }

        // Go button
        let goBtnHtml = '';
        if (f.route && f.route.trim() !== '') {
            goBtnHtml = `
            <a href="${f.route}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-2 py-0 d-inline-flex align-items-center gap-1" title="Open ${f.route}">
                <i class="bi bi-box-arrow-up-right"></i><span>Go</span>
            </a>`;
        } else {
            goBtnHtml = `
            <button class="btn btn-sm btn-light text-muted rounded-pill px-2 py-0 d-inline-flex align-items-center gap-1" disabled title="No route link available">
                <i class="bi bi-box-arrow-up-right"></i><span>Go</span>
            </button>`;
        }

        // Main Row
        html += `
        <tr id="feat-row-${idx}">
            <td class="ps-3 text-center">
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 rounded-circle" onclick="toggleFeatureDetail(${idx})" id="expand-btn-${idx}" title="Expand platform & dimension details">
                    <i class="bi bi-chevron-down" id="feat-icon-${idx}"></i>
                </button>
            </td>
            <td>
                <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1">
                    <i class="bi bi-${f.module_icon || 'folder2'} text-primary"></i>
                    <span>${f.module_name}</span>
                </span>
            </td>
            <td>
                <div class="fw-bold text-dark">${f.feature_name}</div>
                <div class="text-muted small d-flex align-items-center gap-2">
                    <code class="text-secondary" style="font-size: 10px;">${f.route || 'No script route'}</code>
                    ${f.feature_desc ? `<span class="text-truncate" style="max-width: 250px;">• ${f.feature_desc}</span>` : ''}
                </div>
            </td>
            <td>${compactIconsHtml}</td>
            <td class="text-center">
                <span class="badge ${errBadgeClass} fw-bold" style="font-size: 11px;">${err}%</span>
            </td>
            <td class="text-center">${issueBadgeHtml}</td>
            <td class="text-end pe-3">
                <div class="d-inline-flex align-items-center gap-1">
                    ${goBtnHtml}
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" onclick="openScreenModalFromMatrix('${f.route || f.feature_name}')" title="Configure 15 Dimensions">
                        <i class="bi bi-sliders"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" onclick="openEditFeatureModal(${idx})" title="Edit Feature Details">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" onclick="deleteFeature(${f.feature_id})" title="Delete Feature">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;

        // Expandable Platform Breakdown & Detail Child Row
        const pb = f.platform_breakdown || {};
        html += `
        <tr id="feat-detail-row-${idx}" style="display: none;" class="bg-light">
            <td colspan="7" class="p-3">
                <div class="card border shadow-sm p-3 bg-white" style="border-radius: 12px;">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-display me-1 text-primary"></i> Cross-Platform & Dimension Health Breakdown: ${f.feature_name}
                            </h6>
                            <small class="text-muted">${f.module_name} • Route: <code>${f.route || 'Not assigned'}</code></small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-success rounded-pill px-3" onclick="quickCreateIssueForFeature(${idx})">
                                <i class="bi bi-plus-circle me-1"></i> Report Issue
                            </button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="openEditFeatureModal(${idx})">
                                <i class="bi bi-pencil me-1"></i> Edit Feature
                            </button>
                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="openScreenModalFromMatrix('${f.route || f.feature_name}')">
                                <i class="bi bi-sliders me-1"></i> Dimensions
                            </button>
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="deleteFeature(${f.feature_id})" title="Delete Feature">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Platform Breakdown Cards -->
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <div class="card p-2 border ${pb.Console > 0 ? 'border-danger bg-danger bg-opacity-10' : 'bg-light'} text-center h-100">
                                <small class="text-muted fw-bold">Console</small>
                                <div class="fs-6 fw-bold ${pb.Console > 0 ? 'text-danger' : 'text-success'} mt-1">
                                    <i class="bi ${pb.Console > 0 ? 'bi-exclamation-circle' : 'bi-check2'} me-1"></i>${pb.Console || 0} Issues
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card p-2 border ${pb.Dashboard > 0 ? 'border-danger bg-danger bg-opacity-10' : 'bg-light'} text-center h-100">
                                <small class="text-muted fw-bold">Dashboard</small>
                                <div class="fs-6 fw-bold ${pb.Dashboard > 0 ? 'text-danger' : 'text-success'} mt-1">
                                    <i class="bi ${pb.Dashboard > 0 ? 'bi-exclamation-circle' : 'bi-check2'} me-1"></i>${pb.Dashboard || 0} Issues
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card p-2 border ${pb['Android Lite'] > 0 ? 'border-danger bg-danger bg-opacity-10' : 'bg-light'} text-center h-100">
                                <small class="text-muted fw-bold">Android Lite</small>
                                <div class="fs-6 fw-bold ${pb['Android Lite'] > 0 ? 'text-danger' : 'text-success'} mt-1">
                                    <i class="bi ${pb['Android Lite'] > 0 ? 'bi-exclamation-circle' : 'bi-check2'} me-1"></i>${pb['Android Lite'] || 0} Issues
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card p-2 border ${pb['Android Premium'] > 0 ? 'border-danger bg-danger bg-opacity-10' : 'bg-light'} text-center h-100">
                                <small class="text-muted fw-bold">Android Native</small>
                                <div class="fs-6 fw-bold ${pb['Android Premium'] > 0 ? 'text-danger' : 'text-success'} mt-1">
                                    <i class="bi ${pb['Android Premium'] > 0 ? 'bi-exclamation-circle' : 'bi-check2'} me-1"></i>${pb['Android Premium'] || 0} Issues
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card p-2 border ${pb.Desktop > 0 ? 'border-danger bg-danger bg-opacity-10' : 'bg-light'} text-center h-100">
                                <small class="text-muted fw-bold">Desktop</small>
                                <div class="fs-6 fw-bold ${pb.Desktop > 0 ? 'text-danger' : 'text-success'} mt-1">
                                    <i class="bi ${pb.Desktop > 0 ? 'bi-exclamation-circle' : 'bi-check2'} me-1"></i>${pb.Desktop || 0} Issues
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Full 15 Dimensions Badges Grid -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted mb-1">15 Dimension Verification Details</label>
                        <div class="d-flex flex-wrap gap-2">
                            ${DIMENSION_CONFIG.map(d => {
                                const val = dims[d.key] || 'Not Tested';
                                const badgeClass = eimboxGetStatusBadgeClass ? eimboxGetStatusBadgeClass(val) : 'bg-secondary text-white';
                                return `<span class="badge ${badgeClass} p-2 rounded-3 small">
                                    <strong>${d.label}:</strong> ${val}
                                </span>`;
                            }).join('')}
                        </div>
                    </div>

                    ${f.notes ? `
                    <div class="alert alert-light border py-2 px-3 small mb-0">
                        <i class="bi bi-sticky me-1 text-primary"></i> <strong>Implementation Notes:</strong> ${f.notes}
                    </div>` : ''}
                </div>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;

    // Initialize Bootstrap tooltips
    if (typeof bootstrap !== 'undefined') {
        const tooltipList = [].slice.call(document.querySelectorAll('#featuresCatalogTable [data-bs-toggle="tooltip"]'));
        tooltipList.map(el => new bootstrap.Tooltip(el));
    }
}

function toggleFeatureDetail(idx) {
    const detailRow = document.getElementById(`feat-detail-row-${idx}`);
    const icon = document.getElementById(`feat-icon-${idx}`);
    if (!detailRow) return;

    if (detailRow.style.display === 'none') {
        detailRow.style.display = 'table-row';
        if (icon) {
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-up');
        }
    } else {
        detailRow.style.display = 'none';
        if (icon) {
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
        }
    }
}

function openFeatureIssuesPopup(idx) {
    const f = allFeaturesCatalogCache[idx];
    if (!f) return;

    document.getElementById('featureIssuesModalTitle').innerText = `${f.feature_name} - Issues`;
    document.getElementById('featureIssuesModalSubtitle').innerText = `${f.module_name} • ${f.route || 'No Route'}`;

    const addBtn = document.getElementById('featureIssuesModalAddBtn');
    if (addBtn) {
        addBtn.onclick = function () {
            const bsModal = bootstrap.Modal.getInstance(document.getElementById('featureIssuesPopupModal'));
            if (bsModal) bsModal.hide();
            quickCreateIssueForFeature(idx);
        };
    }

    const listContainer = document.getElementById('featureIssuesModalList');
    const issues = f.issues || [];

    if (issues.length === 0) {
        listContainer.innerHTML = `
        <div class="card border-0 text-center py-5 bg-white" style="border-radius: 12px;">
            <i class="bi bi-check-circle-fill text-success fs-1 mb-2"></i>
            <h6 class="fw-bold text-dark">No Active Issues</h6>
            <p class="text-muted small mb-0">All systems and dimensions are clear for this feature.</p>
        </div>`;
    } else {
        let html = '<div class="list-group gap-2">';
        issues.forEach(iss => {
            const priorityColor = iss.priority === 'Critical' ? 'danger' : (iss.priority === 'High' ? 'warning text-dark' : 'secondary');
            const statusColor = iss.status === 'Completed' ? 'success' : (iss.status === 'Ongoing' ? 'primary' : 'dark');

            html += `
            <div class="list-group-item border rounded-3 p-3 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-secondary me-1">${iss.platform || 'General'}</span>
                        <span class="badge bg-${priorityColor} me-1">${iss.priority}</span>
                        <span class="badge bg-${statusColor} me-2">${iss.status}</span>
                        <strong class="text-dark">${iss.topic || iss.feature || 'Issue #' + iss.id}</strong>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary py-0 px-2" onclick="openEditIssueFromPopup(${JSON.stringify(iss).replace(/"/g, '&quot;')})">
                            <i class="bi bi-pencil"></i>
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

    const bsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('featureIssuesPopupModal'));
    bsModal.show();
}

function openEditIssueFromPopup(iss) {
    const popModal = bootstrap.Modal.getInstance(document.getElementById('featureIssuesPopupModal'));
    if (popModal) popModal.hide();
    openEditIssueModal(iss);
}

function quickCreateIssueForFeature(idx) {
    const f = allFeaturesCatalogCache[idx];
    openCreateIssueModal();
    if (f) {
        const modSelect = document.getElementById('modal-module');
        if (modSelect) modSelect.value = f.module_name;
        const scriptInput = document.getElementById('modal-script');
        if (scriptInput) scriptInput.value = f.route || '';
        const topicInput = document.getElementById('modal-topic');
        if (topicInput) topicInput.value = f.feature_name || '';
    }
}

function renderIssuesTable(issues) {
    const tbody = document.getElementById('issuesTableBody');
    if (!tbody) return;

    if (issues.length === 0) {
        tbody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                <h6 class="fw-bold mb-1">No Matching Issues Found</h6>
                <p class="small mb-0">Try changing your filters or report a new issue above.</p>
            </td>
        </tr>`;
        return;
    }

    let html = '';
    issues.forEach(iss => {
        const priorityBadge = iss.priority === 'Critical' ? 'bg-danger text-white' : 
                             (iss.priority === 'High' ? 'bg-warning text-dark' : 'bg-secondary text-white');

        const statusBadge = iss.status === 'Completed' ? 'bg-success text-white' :
                           (iss.status === 'Ongoing' ? 'bg-primary text-white' :
                           (iss.status === 'Testing' ? 'bg-info text-dark' : 'bg-dark text-white'));

        const platformBadge = iss.platform === 'Console' ? 'bg-primary' :
                             (iss.platform === 'Dashboard' ? 'bg-info' :
                             (iss.platform === 'Android Premium' ? 'bg-success' : 'bg-secondary'));

        html += `
        <tr>
            <td class="ps-3 fw-bold text-muted">#${iss.id}</td>
            <td><span class="badge ${platformBadge}">${iss.platform}</span></td>
            <td>
                <div class="fw-bold text-dark">${iss.module}</div>
                <code class="text-secondary small" style="font-size: 11px;">${iss.script || '—'}</code>
            </td>
            <td>
                <div class="fw-semibold text-dark">${iss.topic || iss.feature}</div>
                <div class="text-muted small text-truncate" style="max-width: 320px;">${iss.issues || ''}</div>
            </td>
            <td><span class="badge ${priorityBadge}">${iss.priority}</span></td>
            <td><span class="badge ${statusBadge}">${iss.status}</span></td>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="progress flex-grow-1" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: ${iss.progress_percent || 0}%;"></div>
                    </div>
                    <span class="small fw-bold" style="font-size: 11px;">${iss.progress_percent || 0}%</span>
                </div>
            </td>
            <td>
                <span class="small text-secondary"><i class="bi bi-person me-1"></i>${iss.assigned_to || 'Unassigned'}</span>
            </td>
            <td class="text-end pe-3">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="openEditIssueModal(${JSON.stringify(iss).replace(/"/g, '&quot;')})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="deleteMasterIssue(${iss.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
}

function renderDimensionsMatrix(screens) {
    const tbody = document.getElementById('dimensionsMatrixBody');
    if (!tbody) return;

    if (screens.length === 0) {
        tbody.innerHTML = `
        <tr>
            <td colspan="18" class="text-center py-4 text-muted">
                No screen dimensions tracked yet. Open any page and use the bottom-right Track Issue button to configure!
            </td>
        </tr>`;
        return;
    }

    const dimCols = ['ui', 'light', 'dark', 'view', 'insert', 'update', 'delete', 'cache', 'push', 'pull', 'dropdown', 'modal', 'print', 'pdf', 'permission'];

    let html = '';
    screens.forEach(s => {
        const health = s.health_percent !== undefined ? s.health_percent : 100;
        const healthClass = health >= 80 ? 'text-success' : (health >= 50 ? 'text-warning' : 'text-danger');

        html += `
        <tr>
            <td class="text-start fw-bold">
                ${s.title || s.route}
                <br><code class="text-muted" style="font-size: 10px;">${s.route}</code>
            </td>
            <td class="text-center fw-bold ${healthClass}">${health}%</td>`;

        dimCols.forEach(d => {
            const val = s[d] || 'Not Tested';
            let badge = 'secondary';
            if (val.toLowerCase() === 'ok') badge = 'success';
            else if (val.toLowerCase() === 'on progress') badge = 'warning text-dark';
            else if (val.toLowerCase() === 'bug') badge = 'danger bg-opacity-75';
            else if (val.toLowerCase() === 'error') badge = 'danger';
            else if (val.toLowerCase().includes('not applicable')) badge = 'info text-dark';

            html += `<td class="text-center"><span class="badge bg-${badge}" style="font-size: 9px;">${val}</span></td>`;
        });

        html += `
            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary py-0 px-2" onclick="openScreenModalFromMatrix('${s.route}')">
                    <i class="bi bi-pencil-square"></i>
                </button>
            </td>
        </tr>`;
    });

    tbody.innerHTML = html;
}

function openCreateIssueModal() {
    document.getElementById('issueModalTitle').innerHTML = '<i class="bi bi-plus-circle me-1"></i> Report New Issue';
    document.getElementById('modal-issue-id').value = '0';
    document.getElementById('modal-script').value = '';
    document.getElementById('modal-topic').value = '';
    document.getElementById('modal-desc').value = '';
    document.getElementById('modal-status').value = 'Open';
    document.getElementById('modal-priority').value = 'Medium';
    document.getElementById('modal-progress').value = 0;
    document.getElementById('modal-progress-label').innerText = '0%';
    document.getElementById('modal-assignee').value = '';
    document.getElementById('modal-closing-date').value = '';

    const bsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('issueMasterModal'));
    bsModal.show();
}

function openEditIssueModal(iss) {
    document.getElementById('issueModalTitle').innerHTML = `<i class="bi bi-pencil-square me-1"></i> Edit Issue #${iss.id}`;
    document.getElementById('modal-issue-id').value = iss.id;
    document.getElementById('modal-platform').value = iss.platform;
    document.getElementById('modal-module').value = iss.module;
    document.getElementById('modal-script').value = iss.script || '';
    document.getElementById('modal-topic').value = iss.topic || iss.feature || '';
    document.getElementById('modal-desc').value = iss.issues || '';
    document.getElementById('modal-status').value = iss.status;
    document.getElementById('modal-priority').value = iss.priority;
    document.getElementById('modal-progress').value = iss.progress_percent || 0;
    document.getElementById('modal-progress-label').innerText = (iss.progress_percent || 0) + '%';
    document.getElementById('modal-assignee').value = iss.assigned_to || '';
    document.getElementById('modal-closing-date').value = iss.possible_closing_at ? iss.possible_closing_at.split(' ')[0] : '';

    const bsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('issueMasterModal'));
    bsModal.show();
}

function saveMasterIssue() {
    const id = parseInt(document.getElementById('modal-issue-id').value) || 0;
    const action = id > 0 ? 'update' : 'create';

    const payload = {
        action: action,
        id: id,
        platform: document.getElementById('modal-platform').value,
        module: document.getElementById('modal-module').value,
        script: document.getElementById('modal-script').value,
        topic: document.getElementById('modal-topic').value,
        feature: document.getElementById('modal-topic').value || 'Feature Issue',
        issues: document.getElementById('modal-desc').value,
        status: document.getElementById('modal-status').value,
        priority: document.getElementById('modal-priority').value,
        progress_percent: parseInt(document.getElementById('modal-progress').value) || 0,
        assigned_to: document.getElementById('modal-assignee').value,
        possible_closing_at: document.getElementById('modal-closing-date').value || null
    };

    fetch('api/v1/issues/manage-issue.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            const bsModal = bootstrap.Modal.getInstance(document.getElementById('issueMasterModal'));
            if (bsModal) bsModal.hide();
            loadAllIssues();
        } else {
            alert(res.message || 'Error saving issue');
        }
    })
    .catch(err => console.error('Save issue error:', err));
}

function deleteMasterIssue(id) {
    if (!confirm(`Are you sure you want to delete issue #${id}?`)) return;

    fetch('api/v1/issues/manage-issue.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: id })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            loadAllIssues();
        } else {
            alert(res.message || 'Error deleting issue');
        }
    })
    .catch(err => console.error('Delete issue error:', err));
}

function openScreenModalFromMatrix(route) {
    if (typeof eimboxOpenIssueModal === 'function') {
        EIMBOX_ISSUE_CONFIG.script = route;
        const currentScriptEl = document.getElementById('eimbox-current-script-name');
        if (currentScriptEl) currentScriptEl.innerText = route;
        eimboxOpenIssueModal();
    } else {
        window.open(route, '_blank');
    }
}

/* Feature Master Management */
function syncFeatureModuleDropdown(selectedVal = '') {
    const modSelect = document.getElementById('modal-feature-module');
    if (!modSelect) return;

    // If options are missing or empty, build them from allModulesCache
    if (modSelect.options.length === 0 && allModulesCache.length > 0) {
        let opts = '';
        allModulesCache.forEach(m => {
            const name = m.module_name || m;
            opts += `<option value="${name}">${name}</option>`;
        });
        modSelect.innerHTML = opts;
    }

    if (selectedVal) {
        // Ensure option exists even if not in standard list
        let exists = false;
        for (let i = 0; i < modSelect.options.length; i++) {
            if (modSelect.options[i].value.toLowerCase() === selectedVal.toLowerCase()) {
                modSelect.selectedIndex = i;
                exists = true;
                break;
            }
        }
        if (!exists) {
            const opt = document.createElement('option');
            opt.value = selectedVal;
            opt.innerText = selectedVal;
            opt.selected = true;
            modSelect.appendChild(opt);
        }
    } else if (modSelect.options.length > 0) {
        modSelect.selectedIndex = 0;
    }
}

function openCreateFeatureModal() {
    document.getElementById('featureModalTitle').innerHTML = '<i class="bi bi-folder-plus me-1"></i> Add New Feature';
    document.getElementById('modal-feature-id').value = '0';
    document.getElementById('modal-feature-name').value = '';
    document.getElementById('modal-feature-route').value = '';
    document.getElementById('modal-feature-desc').value = '';

    syncFeatureModuleDropdown();

    const bsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('featureMasterModal'));
    bsModal.show();
}

function openEditFeatureModal(idx) {
    const f = allFeaturesCatalogCache[idx];
    if (!f) return;

    document.getElementById('featureModalTitle').innerHTML = `<i class="bi bi-pencil-square me-1"></i> Edit Feature: ${f.feature_name}`;
    document.getElementById('modal-feature-id').value = f.feature_id;
    document.getElementById('modal-feature-name').value = f.feature_name || '';
    document.getElementById('modal-feature-route').value = f.route || '';
    document.getElementById('modal-feature-desc').value = f.feature_desc || '';

    syncFeatureModuleDropdown(f.module_name || 'General');

    const bsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('featureMasterModal'));
    bsModal.show();
}

function saveFeature() {
    const id = parseInt(document.getElementById('modal-feature-id').value) || 0;
    const action = id > 0 ? 'update' : 'create';
    const featureName = document.getElementById('modal-feature-name').value.trim();
    const moduleName = document.getElementById('modal-feature-module').value;
    const route = document.getElementById('modal-feature-route').value.trim();
    const desc = document.getElementById('modal-feature-desc').value.trim();

    if (!featureName) {
        alert('Please enter a feature name.');
        document.getElementById('modal-feature-name').focus();
        return;
    }

    const saveBtn = document.getElementById('saveFeatureBtn');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    }

    const payload = {
        action: action,
        id: id,
        feature_id: id,
        feature_name: featureName,
        module_name: moduleName,
        route: route,
        description: desc
    };

    fetch('api/v1/issues/manage-feature.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(res => {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Feature';
        }

        if (res.status === 'success') {
            const bsModal = bootstrap.Modal.getInstance(document.getElementById('featureMasterModal'));
            if (bsModal) bsModal.hide();
            loadAllIssues();
        } else {
            alert(res.message || 'Error saving feature');
        }
    })
    .catch(err => {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Feature';
        }
        console.error('Save feature error:', err);
        alert('An unexpected error occurred while saving the feature.');
    });
}

function deleteFeature(featureId) {
    if (!featureId) return;
    if (!confirm(`Are you sure you want to delete this feature (ID: ${featureId})? This will also unlink any issues attached to this feature.`)) return;

    fetch('api/v1/issues/manage-feature.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: featureId, feature_id: featureId })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            loadAllIssues();
        } else {
            alert(res.message || 'Error deleting feature');
        }
    })
    .catch(err => {
        console.error('Delete feature error:', err);
        alert('An unexpected error occurred while deleting the feature.');
    });
}
</script>
