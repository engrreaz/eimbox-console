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
                Unified issue management and 18-dimension screen verification across Console, Dashboard, Android & Desktop
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
                    <button class="nav-link fw-bold py-2" data-platform="Console" onclick="setPlatformFilter('Console', this)">
                        <i class="bi bi-terminal-split me-1 text-primary"></i> Console
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2" data-platform="Dashboard" onclick="setPlatformFilter('Dashboard', this)">
                        <i class="bi bi-grid-1x2 me-1 text-info"></i> Dashboard
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2" data-platform="Android Lite" onclick="setPlatformFilter('Android Lite', this)">
                        <i class="bi bi-phone me-1 text-warning"></i> Android Lite
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2" data-platform="Android Premium" onclick="setPlatformFilter('Android Premium', this)">
                        <i class="bi bi-android2 me-1 text-success"></i> Android Native
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2" data-platform="Desktop" onclick="setPlatformFilter('Desktop', this)">
                        <i class="bi bi-display me-1 text-secondary"></i> Desktop
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Circular Health Progress KPI -->
        <div class="col-xl-3 col-md-6">
            <div class="card eimbox-stat-card border-0 shadow-sm h-100 p-3" style="border-radius: 14px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small">Overall System Health</span>
                        <h3 class="fw-bold mb-0 mt-1" id="kpi-health-percent">100%</h3>
                        <small class="text-muted" id="kpi-health-subtext">Dimension & Issue Health</small>
                    </div>
                    <div style="width: 68px; height: 68px; position: relative;">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <path id="kpi-circle-track" class="kpi-circle-track-svg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                  fill="none" stroke="#edf2f7" stroke-width="4" />
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
            <div class="card eimbox-stat-card border-0 shadow-sm h-100 p-3" style="border-radius: 14px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fw-semibold small">Total Reported Issues</span>
                        <h3 class="fw-bold mb-0 mt-1" id="kpi-total-issues">0</h3>
                        <small class="text-danger fw-semibold" id="kpi-critical-high">0 Critical / High</small>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 rounded-circle text-primary">
                        <i class="bi bi-list-task fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Open & Ongoing KPI -->
        <div class="col-xl-3 col-md-6">
            <div class="card eimbox-stat-card border-0 shadow-sm h-100 p-3" style="border-radius: 14px;">
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
            <div class="card eimbox-stat-card border-0 shadow-sm h-100 p-3" style="border-radius: 14px;">
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
        <div class="card-header border-bottom p-3">
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
                <div class="d-flex flex-wrap align-items-center gap-2 mt-5">
                    <input type="text" id="filter-search" class="form-control form-control-sm" placeholder="Search..." style="width: 170px;" oninput="applyFilters()">
                    <select id="filter-platform" class="form-select form-select-sm" style="width: 130px;" onchange="onPlatformDropdownChange(this.value)">
                        <option value="All">All Platforms</option>
                        <option value="Console">Console</option>
                        <option value="Dashboard">Dashboard</option>
                        <option value="Android Lite">Android Lite</option>
                        <option value="Android Premium">Android Premium</option>
                        <option value="Desktop">Desktop</option>
                        <option value="General">General</option>
                    </select>
                    <select id="filter-module" class="form-select form-select-sm" style="width: 130px;" onchange="onModuleFilterChange()">
                        <option value="All">All Modules</option>
                    </select>
                    <select id="filter-feature" class="form-select form-select-sm" style="width: 160px;" onchange="applyFilters()">
                        <option value="All">All Features</option>
                    </select>
                    <select id="filter-status" class="form-select form-select-sm" style="width: 115px;" onchange="applyFilters()">
                        <option value="All">All Status</option>
                        <option value="Open">Open</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Testing">Testing</option>
                        <option value="Completed">Completed</option>
                        <option value="On Hold">On Hold</option>
                    </select>
                    <select id="filter-priority" class="form-select form-select-sm" style="width: 110px;" onchange="applyFilters()">
                        <option value="All">All Priority</option>
                        <option value="Critical">Critical</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" onclick="resetAllFilters()" title="Reset all filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="tab-content">

                <!-- TAB 1: FEATURES & DIMENSIONS TABLE (ORDERED BY MODULE) -->
                <div class="tab-pane fade show active p-0" id="tab-features-catalog">
                    <!-- Compact Legend Bar -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between px-3 py-2 border-bottom small" style="background: rgba(125, 125, 125, 0.05);">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold"><i class="bi bi-info-circle me-1 text-primary"></i> Dimension Icons:</span>
                            <span><i class="bi bi-check-circle-fill text-success"></i> OK</span>
                            <span><i class="bi bi-x-circle-fill text-danger"></i> Error (70%)</span>
                            <span><i class="bi bi-bug-fill text-danger opacity-75"></i> Bug (30%)</span>
                            <span><i class="bi bi-clock-history text-warning"></i> On Progress (50%)</span>
                            <span><i class="bi bi-dash-circle text-muted"></i> Not Tested (100%)</span>
                            <span><i class="bi bi-slash-circle text-info"></i> N/A (0%)</span>
                        </div>
                        <div class="small opacity-75">
                            Ordered by <code>modulelist.slno</code> & Module
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="featuresCatalogTable">
                            <thead class="table-light text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">
                                <tr>
                                    <th class="ps-3 text-center" style="width: 40px;">#</th>
                                    <th style="min-width: 200px;">Module, Feature & Screen Route</th>
                                    <th style="min-width: 220px;">18 Dimensions</th>
                                    <th class="text-end pe-3" id="featuresTableHeaderMerged" style="min-width: 250px;">5-Plat Avg Error, Issues & Actions</th>
                                </tr>
                            </thead>
                            <tbody id="featuresCatalogTableBody">
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
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
                            <strong>18-Dimension Screen Health Matrix:</strong> Evaluates UI, Light, Dark, View, Insert, Update, Delete, Cache, Push, Pull, Dropdown, Modal, Print, PDF, Permission, Documentation, FAQ, YouTube Video.
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
                                    <th>Doc</th>
                                    <th>FAQ</th>
                                    <th>Video</th>
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
            <div class="modal-footer py-2 px-4 border-top">
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
            <div class="modal-body p-4">
                <div id="featureIssuesModalList">
                    <!-- Populated dynamically -->
                </div>
            </div>
            <div class="modal-footer py-2 px-4 border-top">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
/* =========================================================
   Windows Explorer Compact Tree View Style
   ========================================================= */
.win-tree-container {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    font-size: 12px;
    line-height: 1.35;
    user-select: none;
    background: transparent !important;
    background-color: transparent !important;
    color: inherit !important;
    overflow-y: auto !important;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: #888888 rgba(0, 0, 0, 0.08);
}

/* High visibility Windows-style scrollbar */
.win-tree-container::-webkit-scrollbar {
    width: 8px !important;
    display: block !important;
}
.win-tree-container::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
}
.win-tree-container::-webkit-scrollbar-thumb {
    background: #888888;
    border-radius: 4px;
    border: 1px solid rgba(255, 255, 255, 0.4);
}
.win-tree-container::-webkit-scrollbar-thumb:hover {
    background: #555555;
}

html.dark-style .win-tree-container,
[data-bs-theme="dark"] .win-tree-container {
    scrollbar-color: #64748b rgba(255, 255, 255, 0.08);
}
html.dark-style .win-tree-container::-webkit-scrollbar-track,
[data-bs-theme="dark"] .win-tree-container::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}
html.dark-style .win-tree-container::-webkit-scrollbar-thumb,
[data-bs-theme="dark"] .win-tree-container::-webkit-scrollbar-thumb {
    background: #64748b;
    border: 1px solid rgba(0, 0, 0, 0.3);
}
html.dark-style .win-tree-container::-webkit-scrollbar-thumb:hover,
[data-bs-theme="dark"] .win-tree-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.win-tree-node {
    position: relative;
    margin-bottom: 2px;
    color: inherit !important;
}

/* Tree Row Base (Parent & Item) */
.win-tree-row {
    display: flex;
    align-items: center;
    min-height: 24px;
    padding: 2px 6px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.12s ease;
    gap: 6px;
    position: relative;
    color: inherit !important;
}

.win-tree-row:hover {
    background-color: rgba(105, 108, 255, 0.08);
}

html.dark-style .win-tree-row:hover,
[data-bs-theme="dark"] .win-tree-row:hover {
    background-color: rgba(255, 255, 255, 0.06);
}

/* Selected Node State - Clean Accent tint with inherited text */
.win-tree-row.active {
    background-color: rgba(105, 108, 255, 0.15) !important;
    border: 1px solid rgba(105, 108, 255, 0.4) !important;
    font-weight: 600;
    color: inherit !important;
}

html.dark-style .win-tree-row.active,
[data-bs-theme="dark"] .win-tree-row.active {
    background-color: rgba(105, 108, 255, 0.25) !important;
    border: 1px solid rgba(105, 108, 255, 0.5) !important;
    color: inherit !important;
}

/* Toggle Chevron */
.win-tree-toggle {
    width: 14px;
    height: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: inherit;
    opacity: 0.65;
    font-size: 9px;
    transition: transform 0.15s ease;
    flex-shrink: 0;
}

.win-tree-node.expanded > .win-tree-row > .win-tree-toggle > i {
    transform: rotate(90deg);
}

/* Tree Icons */
.win-tree-icon {
    font-size: 13px;
    flex-shrink: 0;
    line-height: 1;
}

/* Labels */
.win-tree-label {
    flex-grow: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 11.5px;
    color: inherit !important;
}

.win-tree-parent > .win-tree-label {
    font-weight: 600;
    font-size: 12px;
    color: inherit !important;
}

/* Monospace Route Hint */
.win-tree-route {
    font-size: 10px;
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    opacity: 0.7;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 115px;
    background: rgba(0, 0, 0, 0.05);
    padding: 0 4px;
    border-radius: 3px;
    color: inherit !important;
}

html.dark-style .win-tree-route,
[data-bs-theme="dark"] .win-tree-route {
    background: rgba(255, 255, 255, 0.08);
    color: inherit !important;
}

/* Quick Add Button on Module Hover */
.win-tree-add-btn {
    opacity: 0;
    padding: 0 4px;
    height: 18px;
    line-height: 16px;
    font-size: 11px;
    border: 1px solid rgba(2, 132, 199, 0.3);
    background: rgba(2, 132, 199, 0.08);
    color: #0284c7;
    border-radius: 3px;
    transition: opacity 0.15s ease, background 0.15s ease;
}

.win-tree-row:hover .win-tree-add-btn {
    opacity: 1;
}

.win-tree-add-btn:hover {
    background: #0284c7;
    color: #ffffff;
}

/* Children Branches & Connecting Guide Lines (Windows Explorer Style) */
.win-tree-children {
    display: none;
    margin-left: 13px;
    padding-left: 12px;
    border-left: 1px dotted #94a3b8;
    position: relative;
}

html.dark-style .win-tree-children,
[data-bs-theme="dark"] .win-tree-children {
    border-left: 1px dotted rgba(255, 255, 255, 0.22);
}

.win-tree-node.expanded > .win-tree-children {
    display: block;
}

/* Horizontal Guide Connector Line (├──) */
.win-tree-children > .win-tree-row::before {
    content: '';
    position: absolute;
    top: 12px;
    left: -12px;
    width: 10px;
    height: 1px;
    border-top: 1px dotted #94a3b8;
}

html.dark-style .win-tree-children > .win-tree-row::before,
[data-bs-theme="dark"] .win-tree-children > .win-tree-row::before {
    border-top: 1px dotted rgba(255, 255, 255, 0.22);
}

.win-tree-empty {
    font-size: 11px;
    font-style: italic;
    color: #94a3b8;
    padding: 2px 4px;
    position: relative;
}

.win-tree-empty::before {
    content: '';
    position: absolute;
    top: 10px;
    left: -12px;
    width: 10px;
    height: 1px;
    border-top: 1px dotted #94a3b8;
}

html.dark-style .win-tree-empty::before,
[data-bs-theme="dark"] .win-tree-empty::before {
    border-top: 1px dotted rgba(255, 255, 255, 0.22);
}

/* =========================================================
   Feature Modal Form Panel - High Contrast Theme Text
   ========================================================= */
#featureMasterModal .modal-body,
#featureMasterModal #featureModalRightPanel {
    color: inherit !important;
}

#featureMasterModal .form-label {
    color: inherit !important;
    font-weight: 600 !important;
    font-size: 13px !important;
    margin-bottom: 5px;
    opacity: 1 !important;
}

#featureMasterModal .form-control,
#featureMasterModal .form-select {
    color: inherit !important;
    font-size: 13.5px;
    border-color: rgba(105, 108, 255, 0.25);
    background-color: transparent !important;
}

html.dark-style #featureMasterModal .form-control,
html.dark-style #featureMasterModal .form-select,
[data-bs-theme="dark"] #featureMasterModal .form-control,
[data-bs-theme="dark"] #featureMasterModal .form-select {
    background-color: rgba(255, 255, 255, 0.05) !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
    color: inherit !important;
}

#featureMasterModal .form-control:focus,
#featureMasterModal .form-select:focus {
    color: inherit !important;
    border-color: #696cff !important;
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15);
}

#featureMasterModal small,
#featureMasterModal .text-muted {
    color: inherit !important;
    opacity: 0.85 !important;
}

#featureMasterModal ::placeholder {
    color: inherit !important;
    opacity: 0.45 !important;
}
</style>

<!-- Feature Add / Edit Master Modal with Vertical Hierarchy Tree Panel -->
<div class="modal fade" id="featureMasterModal" tabindex="-1" aria-hidden="true" style="z-index: 105500;">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h5 class="modal-title fw-bold text-white mb-0" id="featureModalTitle">
                    <i class="bi bi-folder-plus me-1"></i> Add Feature
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left Vertical Panel: Module & Feature Hierarchy Tree -->
                    <div class="col-lg-5 col-md-5 border-end p-3 d-flex flex-column" style="height: 560px; max-height: 560px;">
                        <!-- Panel Title & Counter -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-bold small d-flex align-items-center gap-1">
                                <i class="bi bi-diagram-3-fill text-primary"></i>
                                <span>Module & Feature Tree</span>
                            </div>
                            <span class="badge bg-primary rounded-pill px-2 py-1" id="treeFeatureCountBadge">0 Features</span>
                        </div>

                        <!-- Search Box -->
                        <div class="mb-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text border-end-0" style="background: transparent;"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="treeFeatureSearchInput" class="form-control border-start-0" placeholder="Filter module or feature..." oninput="filterFeatureTree(this.value)">
                                <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('treeFeatureSearchInput').value=''; filterFeatureTree('');" title="Clear filter">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tree Actions -->
                        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                            <small class="text-muted" style="font-size: 11px;">Click feature to edit / inspect</small>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 11px;" onclick="expandAllFeatureTree(true)">
                                    <i class="bi bi-arrows-expand me-1"></i>Expand
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 11px;" onclick="expandAllFeatureTree(false)">
                                    <i class="bi bi-arrows-collapse me-1"></i>Collapse
                                </button>
                            </div>
                        </div>

                        <!-- Hierarchy Tree Container with Explicit Scrollbar & Transparent Background -->
                        <div id="featureHierarchyTree" class="win-tree-container flex-grow-1 overflow-auto border rounded p-2" style="height: 380px; max-height: 380px; overflow-y: auto !important; background: transparent !important;">
                            <!-- Populated dynamically via renderFeatureModalTree() -->
                        </div>

                        <!-- Reset / New Feature Button -->
                        <div class="mt-2 pt-2 border-top">
                            <button type="button" class="btn btn-sm btn-outline-success w-100 rounded-pill fw-semibold" onclick="resetFeatureModalToAdd()">
                                <i class="bi bi-plus-circle me-1"></i> Switch to Add New Feature
                            </button>
                        </div>
                    </div>

                    <!-- Right Form Panel: Feature Input Fields -->
                    <div class="col-lg-7 col-md-7 p-4 d-flex flex-column justify-content-between" id="featureModalRightPanel">
                        <div>
                            <!-- Header Mode Indicator -->
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-semibold px-2 py-1" id="featureModalModeIndicator">
                                        <i class="bi bi-plus-circle me-1"></i> Add New Feature Mode
                                    </span>
                                </div>
                                <small id="featureModalSelectedHint" style="font-size: 11.5px; opacity: 0.85;">Fill out the fields to add a feature</small>
                            </div>

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
                                    <small style="font-size: 11.5px; opacity: 0.85;">Links this feature to screen dimension matrix & quick Go action</small>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Feature Description</label>
                                    <textarea id="modal-feature-desc" class="form-control" rows="4" placeholder="Explain the feature purpose, components or business rules..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 px-4 d-flex justify-content-between border-top">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="resetFeatureModalToAdd()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Clear Form
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="saveFeatureBtn" onclick="saveFeature()">
                        <i class="bi bi-check2-circle me-1"></i> Save Feature
                    </button>
                </div>
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
    { key: 'permission', label: 'RBAC Access', short: 'Perm' },
    { key: 'documentation', label: 'Documentation', short: 'Doc' },
    { key: 'faq', label: 'FAQ & Help', short: 'FAQ' },
    { key: 'youtube_video', label: 'YouTube Video', short: 'Video' }
];

document.addEventListener("DOMContentLoaded", function () {
    loadAllIssues();
});

function setPlatformFilter(platform, tabBtn) {
    activePlatformFilter = platform;
    document.querySelectorAll('#platformFilterPills .nav-link').forEach(btn => {
        btn.classList.remove('active');
    });
    if (tabBtn) tabBtn.classList.add('active');

    // Sync filter-platform dropdown
    const platDropdown = document.getElementById('filter-platform');
    if (platDropdown) platDropdown.value = platform;

    const thMerged = document.getElementById('featuresTableHeaderMerged');
    if (thMerged) {
        thMerged.innerText = (platform === 'All' ? '5-Plat Avg Error, Issues & Actions' : platform + ' Error, Issues & Actions');
    }

    loadAllIssues();
}

function onPlatformDropdownChange(platform) {
    activePlatformFilter = platform;
    document.querySelectorAll('#platformFilterPills .nav-link').forEach(btn => {
        if (btn.getAttribute('data-platform') === platform) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    const thMerged = document.getElementById('featuresTableHeaderMerged');
    if (thMerged) {
        thMerged.innerText = (platform === 'All' ? '5-Plat Avg Error, Issues & Actions' : platform + ' Error, Issues & Actions');
    }

    applyFilters();
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
                const curMod = document.getElementById('filter-module') ? document.getElementById('filter-module').value : 'All';
                populateFeatureFilterDropdown(curMod);
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

function populateFeatureFilterDropdown(selectedModule = 'All') {
    const featSelect = document.getElementById('filter-feature');
    if (!featSelect) return;

    const curVal = featSelect.value || 'All';
    let opts = '<option value="All">All Features</option>';

    let featuresToShow = allFeaturesCatalogCache || [];
    if (selectedModule && selectedModule !== 'All') {
        featuresToShow = featuresToShow.filter(f => (f.module_name || 'General') === selectedModule);
    }

    const sorted = [...featuresToShow].sort((a, b) => (a.feature_name || '').localeCompare(b.feature_name || ''));

    sorted.forEach(f => {
        const fId = f.feature_id;
        const fName = f.feature_name || `Feature #${fId}`;
        const isSelected = String(curVal) === String(fId) ? 'selected' : '';
        opts += `<option value="${fId}" ${isSelected}>${fName}</option>`;
    });

    featSelect.innerHTML = opts;
    if (curVal !== 'All' && !sorted.some(f => String(f.feature_id) === String(curVal))) {
        featSelect.value = 'All';
    }
}

function onModuleFilterChange() {
    const selectedMod = document.getElementById('filter-module') ? document.getElementById('filter-module').value : 'All';
    populateFeatureFilterDropdown(selectedMod);
    applyFilters();
}

function resetAllFilters() {
    const searchEl = document.getElementById('filter-search');
    if (searchEl) searchEl.value = '';

    const platEl = document.getElementById('filter-platform');
    if (platEl) platEl.value = 'All';

    const modEl = document.getElementById('filter-module');
    if (modEl) modEl.value = 'All';

    populateFeatureFilterDropdown('All');

    const featEl = document.getElementById('filter-feature');
    if (featEl) featEl.value = 'All';

    const statEl = document.getElementById('filter-status');
    if (statEl) statEl.value = 'All';

    const prioEl = document.getElementById('filter-priority');
    if (prioEl) prioEl.value = 'All';

    activePlatformFilter = 'All';
    document.querySelectorAll('#platformFilterPills .nav-link').forEach(btn => {
        if (btn.getAttribute('data-platform') === 'All') btn.classList.add('active');
        else btn.classList.remove('active');
    });

    const thMerged = document.getElementById('featuresTableHeaderMerged');
    if (thMerged) thMerged.innerText = '5-Plat Avg Error, Issues & Actions';

    applyFilters();
}

function applyFilters() {
    const search = (document.getElementById('filter-search')?.value || '').toLowerCase().trim();
    const platform = document.getElementById('filter-platform')?.value || 'All';
    const module = document.getElementById('filter-module')?.value || 'All';
    const feature = document.getElementById('filter-feature')?.value || 'All';
    const status = document.getElementById('filter-status')?.value || 'All';
    const priority = document.getElementById('filter-priority')?.value || 'All';

    // 1. Filter Issues Table (Reported Issues)
    const filteredIssues = allIssuesCache.filter(item => {
        // Platform filter
        if (platform !== 'All') {
            const itemPlat = (item.platform || 'General').toLowerCase().trim();
            if (itemPlat !== platform.toLowerCase().trim()) return false;
        }

        // Module filter
        if (module !== 'All' && item.module !== module) return false;

        // Feature filter
        if (feature !== 'All') {
            const featObj = allFeaturesCatalogCache.find(x => String(x.feature_id) === String(feature) || (x.feature_name && x.feature_name.toLowerCase() === feature.toLowerCase()));
            const targetName = featObj ? featObj.feature_name.toLowerCase() : feature.toLowerCase();
            const targetId = featObj ? parseInt(featObj.feature_id) : parseInt(feature);

            const matchId = targetId > 0 && parseInt(item.feature_id) === targetId;
            const matchName = item.feature && item.feature.toLowerCase() === targetName;
            const matchScript = featObj && featObj.route && item.script && item.script.toLowerCase() === featObj.route.toLowerCase();

            if (!matchId && !matchName && !matchScript) return false;
        }

        // Status filter
        if (status !== 'All' && item.status !== status) return false;

        // Priority filter
        if (priority !== 'All' && item.priority !== priority) return false;

        // Search filter
        if (search) {
            const combined = `${item.feature || ''} ${item.topic || ''} ${item.issues || ''} ${item.script || ''} ${item.assigned_to || ''} ${item.platform || ''} ${item.module || ''}`.toLowerCase();
            if (!combined.includes(search)) return false;
        }
        return true;
    });

    renderIssuesTable(filteredIssues);
    const issuesBadge = document.getElementById('tab-issues-badge');
    if (issuesBadge) issuesBadge.innerText = filteredIssues.length;

    // 2. Filter Features Catalog Table
    const filteredFeatures = allFeaturesCatalogCache.filter(f => {
        if (module !== 'All' && f.module_name !== module) return false;
        if (feature !== 'All') {
            if (String(f.feature_id) !== String(feature) && (f.feature_name || '').toLowerCase() !== feature.toLowerCase()) {
                return false;
            }
        }
        if (status !== 'All') {
            const hasStatus = (f.issues || []).some(iss => iss.status === status);
            if (!hasStatus) return false;
        }
        if (priority !== 'All') {
            const hasPriority = (f.issues || []).some(iss => iss.priority === priority);
            if (!hasPriority) return false;
        }
        if (search) {
            const combined = `${f.feature_name || ''} ${f.module_name || ''} ${f.route || ''} ${f.feature_desc || ''}`.toLowerCase();
            if (!combined.includes(search)) return false;
        }
        return true;
    });

    renderFeaturesCatalogTable(filteredFeatures);
    const featBadge = document.getElementById('tab-features-badge');
    if (featBadge) featBadge.innerText = filteredFeatures.length;
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

function getAggregatedDimensionIcon(f, dimConfig, platformFilter) {
    const dimKey = dimConfig.key;
    const dimLabel = dimConfig.label;

    // 1. If a specific single platform is selected
    if (platformFilter && platformFilter !== 'All') {
        const pData = (f.platforms_data && f.platforms_data[platformFilter]) ? f.platforms_data[platformFilter] : null;
        const val = (pData && pData.dimensions && pData.dimensions[dimKey]) ? pData.dimensions[dimKey] : 'Not Tested';
        return getDimensionIcon(val, `${platformFilter} • ${dimLabel}`);
    }

    // 2. All 5 platforms aggregated calculation
    const platforms = ['Console', 'Dashboard', 'Android Lite', 'Android Premium', 'Desktop'];
    let okCount = 0;
    let errorCount = 0;
    let bugCount = 0;
    let progressCount = 0;
    let naCount = 0;
    let untestedCount = 0;
    const breakdown = [];

    platforms.forEach(pl => {
        const pData = (f.platforms_data && f.platforms_data[pl]) ? f.platforms_data[pl] : null;
        let s = 'Not Tested';
        if (pData && pData.dimensions && pData.dimensions[dimKey]) {
            s = pData.dimensions[dimKey];
        }
        const sLower = String(s).toLowerCase().trim();
        breakdown.push(`${pl}: ${s}`);

        if (sLower === 'ok' || sLower === 'completed') okCount++;
        else if (sLower === 'error') errorCount++;
        else if (sLower === 'bug') bugCount++;
        else if (sLower === 'on progress' || sLower === 'ongoing' || sLower === 'progress') progressCount++;
        else if (sLower === 'not applicable' || sLower === 'n/a' || sLower === 'na') naCount++;
        else untestedCount++;
    });

    const tooltipBreakdown = `${dimLabel} (5 Platforms):&#10;• ` + breakdown.join('&#10;• ');

    // Any Error / Bug -> Red Alert
    if (errorCount > 0 || bugCount > 0) {
        return `<i class="bi bi-x-circle-fill text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="${tooltipBreakdown}&#10;Result: ${errorCount + bugCount} platform(s) with problems"></i>`;
    }
    // All 5 Platforms OK (or OK + N/A) -> Green Success
    if (okCount === 5 || (okCount > 0 && okCount + naCount === 5)) {
        return `<i class="bi bi-check-circle-fill text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="${tooltipBreakdown}&#10;Result: 5/5 Platforms Verified OK"></i>`;
    }
    // Partial OK (e.g. 1 to 4 platforms OK, no errors) -> Blue Double-Check
    if (okCount > 0) {
        return `<i class="bi bi-check2-all text-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="${tooltipBreakdown}&#10;Result: ${okCount}/5 Platforms OK (${5 - okCount} remaining)"></i>`;
    }
    // Ongoing / In Progress
    if (progressCount > 0) {
        return `<i class="bi bi-clock-history text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="${tooltipBreakdown}&#10;Result: In Progress"></i>`;
    }
    // All N/A
    if (naCount === 5) {
        return `<i class="bi bi-slash-circle text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="${tooltipBreakdown}&#10;Result: N/A"></i>`;
    }
    // All 5 Not Tested
    return `<i class="bi bi-dash-circle text-muted" style="opacity: 0.35;" data-bs-toggle="tooltip" data-bs-placement="top" title="${tooltipBreakdown}&#10;Result: Untested on all 5 platforms"></i>`;
}

function getDimChipBadgeClass(val) {
    const s = String(val || '').toLowerCase().trim();
    if (s === 'ok' || s === 'completed') return 'bg-success text-white border-0';
    if (s === 'error') return 'bg-danger text-white border-0';
    if (s === 'bug') return 'bg-warning text-dark border-0';
    if (s === 'on progress' || s === 'ongoing' || s === 'progress') return 'bg-info text-dark border-0';
    if (s === 'not applicable' || s === 'n/a' || s === 'na') return 'bg-secondary bg-opacity-25 text-body border';
    return 'bg-secondary bg-opacity-10 text-body border border-secondary border-opacity-25';
}

function getDimChipIcon(val) {
    const s = String(val || '').toLowerCase().trim();
    if (s === 'ok' || s === 'completed') return 'bi-check-circle-fill';
    if (s === 'error') return 'bi-x-circle-fill';
    if (s === 'bug') return 'bi-bug-fill';
    if (s === 'on progress' || s === 'ongoing' || s === 'progress') return 'bi-hourglass-split';
    if (s === 'not applicable' || s === 'n/a' || s === 'na') return 'bi-slash-circle';
    return 'bi-dash-circle';
}

function getCircularProgressHtml(errVal, size = 36) {
    const val = Math.max(0, Math.min(100, parseFloat(errVal) || 0));
    let strokeColor = '#198754';
    if (val > 50) strokeColor = '#dc3545';
    else if (val > 20) strokeColor = '#ffc107';
    else if (val > 0) strokeColor = '#0dcaf0';

    return `
    <div class="position-relative d-inline-flex align-items-center justify-content-center" style="width: ${size}px; height: ${size}px; min-width: ${size}px; min-height: ${size}px;">
        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
            <path stroke="rgba(125, 125, 125, 0.18)" stroke-width="3.5" fill="none"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
            <path stroke="${strokeColor}" stroke-width="3.8" stroke-linecap="round" fill="none"
                  stroke-dasharray="${val}, 100"
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
        </svg>
        <div class="position-absolute top-50 start-50 translate-middle text-center" style="line-height: 1;">
            <span class="fw-bold" style="font-size: ${size <= 38 ? '9px' : '10.5px'}; color: ${strokeColor};">${Math.round(val)}%</span>
        </div>
    </div>`;
}

function renderFeaturesCatalogTable(features) {
    const tbody = document.getElementById('featuresCatalogTableBody');
    if (!tbody) return;

    if (features.length === 0) {
        tbody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center py-5 text-muted">
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
        const avgErr = f.avg_5_platform_error !== undefined ? f.avg_5_platform_error : (f.problem_percent || 0);
        const err = (activePlatformFilter === 'All' || !activePlatformFilter) ? avgErr : (f.platforms_data && f.platforms_data[activePlatformFilter] ? f.platforms_data[activePlatformFilter].error_percent : f.problem_percent);
        const issCount = f.issue_count || 0;

        // 15 compact icons based on 5 platforms or active platform filter
        let compactIconsHtml = `<div id="feat-dims-box-${idx}" class="d-inline-flex flex-wrap gap-1 align-items-center py-1 px-2 rounded border" style="background: rgba(125, 125, 125, 0.06);">`;
        DIMENSION_CONFIG.forEach(d => {
            compactIconsHtml += getAggregatedDimensionIcon(f, d, activePlatformFilter);
        });
        compactIconsHtml += '</div>';

        // Issue Count badge button
        let issueBadgeHtml = '';
        if (issCount > 0) {
            issueBadgeHtml = `
            <button type="button" class="btn btn-sm btn-danger py-0 px-2 rounded-pill fw-bold d-inline-flex align-items-center gap-1" onclick="openFeatureIssuesPopup(${f.feature_id}, ${idx})" title="Click to view ${issCount} reported issues">
                <i class="bi bi-exclamation-triangle-fill"></i><span>${issCount}</span>
            </button>`;
        } else {
            issueBadgeHtml = `
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill d-inline-flex align-items-center gap-1" onclick="openFeatureIssuesPopup(${f.feature_id}, ${idx})" style="font-size: 11px; opacity: 0.85;" title="0 Issues Reported - Click to view or report new issue">
                <i class="bi bi-check2 text-success"></i><span>0</span>
            </button>`;
        }

        // Go button
        let goBtnHtml = '';
        if (f.route && f.route.trim() !== '') {
            goBtnHtml = `
            <a href="${f.route}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1" title="Open ${f.route}">
                <i class="bi bi-box-arrow-up-right"></i><span>Go</span>
            </a>`;
        } else {
            goBtnHtml = `
            <button class="btn btn-sm btn-light text-muted rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1" disabled title="No route link available">
                <i class="bi bi-box-arrow-up-right"></i><span>Go</span>
            </button>`;
        }

        // Main Row with 4 columns: [1: Expand, 2: Module/Feature, 3: 15 Dimensions, 4: Merged Metrics & Actions]
        html += `
        <tr id="feat-row-${idx}">
            <td class="ps-3 text-center">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center p-0" style="width: 26px; height: 26px; min-width: 26px; min-height: 26px;" onclick="toggleFeatureDetail(${idx})" id="expand-btn-${idx}" title="Expand 5-platform breakdown & dimensions">
                    <i class="bi bi-chevron-down" id="feat-icon-${idx}" style="font-size: 11px;"></i>
                </button>
            </td>
            <td>
                <span class="badge border d-inline-flex align-items-center gap-1" style="color: inherit; background: rgba(125, 125, 125, 0.08);">
                    <i class="bi bi-${f.module_icon || 'folder2'} text-primary"></i>
                    <span>${f.module_name}</span>
                </span>
          
                <div class="fw-bold">${f.feature_name}</div>
                <div class="text-muted small d-flex align-items-center gap-2">
                    <code class="text-secondary" style="font-size: 10px;">${f.route || 'No script route'}</code>
                    ${f.feature_desc ? `<span class="text-truncate" style="max-width: 250px;">• ${f.feature_desc}</span>` : ''}
                </div>
            </td>
            <td>${compactIconsHtml}</td>
            <td class="text-end pe-3">
                <div class="d-inline-flex align-items-center justify-content-end gap-2">
                    <!-- Circular Progress Error Indicator -->
                    <div id="feat-circular-err-${idx}" class="d-inline-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="${(activePlatformFilter === 'All' || !activePlatformFilter) ? '5-Platform Average Error: ' + err + '%' : activePlatformFilter + ' Error: ' + err + '%'}">
                        ${getCircularProgressHtml(err, 36)}
                    </div>

                    <!-- Issues Count Badge -->
                    <div class="d-inline-flex align-items-center">
                        ${issueBadgeHtml}
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-inline-flex align-items-center gap-1">
                        ${goBtnHtml}
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-pill" onclick="openScreenModalFromMatrix('${f.route || f.feature_name}')" title="Configure 15 Dimensions Modal">
                            <i class="bi bi-sliders"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 rounded-pill" onclick="openEditFeatureModal(${f.feature_id}, ${idx})" title="Edit Feature Details">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 rounded-pill" onclick="deleteFeature(${f.feature_id})" title="Delete Feature">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </td>
        </tr>`;

        // Expandable Platform Breakdown & Detail Child Row - 5 PLATFORMS WITH DISTINCT 15 DIMENSIONS & INLINE STATUS SWITCHER
        html += `
        <tr id="feat-detail-row-${idx}" style="display: none;">
            <td colspan="4" class="p-3" style="background: rgba(125, 125, 125, 0.03);">
                <div class="card border shadow-sm p-3" style="border-radius: 12px;">
                    <!-- Compact Toolbar with Action Buttons -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold small d-inline-flex align-items-center gap-1">
                                <i class="bi bi-cpu text-primary"></i> 5 Platforms Independent 18-Dimension Matrix:
                            </span>
                            <span id="feat-avg-pill-${idx}" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 10.5px;">
                                5-Plat Avg Error: ${avgErr}%
                            </span>
                            <span class="badge border text-secondary" style="font-size: 10px; background: rgba(125,125,125,0.06);">
                                Click any dimension chip to change status directly
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button class="btn btn-sm btn-success rounded-pill px-2 py-1" style="font-size: 11.5px;" onclick="quickCreateIssueForFeature(${f.feature_id}, ${idx})">
                                <i class="bi bi-plus-circle me-1"></i> Report Issue
                            </button>
                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" style="font-size: 11.5px;" onclick="openScreenModalFromMatrix('${f.route || f.feature_name}')">
                                <i class="bi bi-sliders me-1"></i> Modal Editor
                            </button>
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" style="font-size: 11.5px;" onclick="openEditFeatureModal(${f.feature_id}, ${idx})">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" style="font-size: 11.5px;" onclick="deleteFeature(${f.feature_id})" title="Delete Feature">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>

                    <!-- 5 Platforms Cards Grid with distinct 15 Dimensions & Inline Status Dropdowns -->
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-2 mb-2">
                        ${['Console', 'Dashboard', 'Android Lite', 'Android Premium', 'Desktop'].map(pl => {
                            const plSafe = pl.replace(/\s+/g, '_');
                            const pData = (f.platforms_data && f.platforms_data[pl]) ? f.platforms_data[pl] : { error_percent: 100, issue_count: 0, status: 'Untested', dimensions: {} };
                            const plDims = pData.dimensions || {};
                            const plIcons = {
                                'Console': 'bi-terminal-split text-primary',
                                'Dashboard': 'bi-grid-1x2 text-info',
                                'Android Lite': 'bi-phone text-warning',
                                'Android Premium': 'bi-android2 text-success',
                                'Desktop': 'bi-display text-secondary'
                            };
                            const pErr = pData.error_percent !== undefined ? pData.error_percent : 100;
                            let pBadge = 'bg-success text-white';
                            if (pErr > 50) pBadge = 'bg-danger text-white';
                            else if (pErr > 20) pBadge = 'bg-warning text-dark';
                            else if (pErr > 0) pBadge = 'bg-info text-dark';
                            else if (pData.status === 'Untested') pBadge = 'border text-secondary';

                            return `
                            <div class="col">
                                <div class="card p-2 border ${pErr > 50 ? 'border-danger bg-danger bg-opacity-10' : (pErr > 0 ? 'border-warning bg-warning bg-opacity-10' : '')} h-100 d-flex flex-column" style="border-radius: 10px;">
                                    <!-- Platform Header -->
                                    <div class="d-flex align-items-center justify-content-between mb-1 pb-1 border-bottom">
                                        <div class="d-flex align-items-center gap-1 text-truncate">
                                            <i class="bi ${plIcons[pl] || 'bi-laptop'}"></i>
                                            <span class="fw-bold" style="font-size: 11.5px;">${pl}</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-success py-0 px-1 rounded-pill" style="font-size: 9px;" onclick="setAllPlatformDimensionsOK(${f.feature_id}, '${f.route || ''}', '${pl}', ${idx})" title="Mark all 18 dimensions OK for ${pl}">
                                            <i class="bi bi-check-all"></i> All OK
                                        </button>
                                    </div>

                                    <!-- Platform Error & Status -->
                                    <div class="d-flex justify-content-between align-items-center mb-2 px-0.5">
                                        <div class="d-flex align-items-center gap-1">
                                            <span id="plat-err-badge-${idx}-${plSafe}" class="badge ${pBadge}" style="font-size: 10px;">${pErr}% Err</span>
                                            <span id="plat-status-badge-${idx}-${plSafe}" class="badge border" style="font-size: 9px; opacity: 0.9;">${pData.status || 'Untested'}</span>
                                        </div>
                                        <div>
                                            ${pData.issue_count > 0 ? `
                                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1.5 rounded-pill fw-bold d-inline-flex align-items-center gap-1" style="font-size: 9.5px;" onclick="openFeatureIssuesPopup(${f.feature_id}, ${idx}, '${pl}')" title="View ${pData.issue_count} Reported Issues on ${pl}">
                                                    <i class="bi bi-exclamation-triangle-fill"></i><span>${pData.issue_count}</span>
                                                </button>
                                            ` : `
                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 rounded-pill text-muted d-inline-flex align-items-center gap-1" style="font-size: 9.5px; opacity: 0.85;" onclick="openFeatureIssuesPopup(${f.feature_id}, ${idx}, '${pl}')" title="0 Issues on ${pl} - Click to view or report new issue">
                                                    <i class="bi bi-check2 text-success"></i><span>0</span>
                                                </button>
                                            `}
                                        </div>
                                    </div>

                                    <!-- 15 Dimensions Inside Platform Card -->
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-1 text-muted" style="font-size: 8.5px;">
                                            <span class="text-uppercase fw-semibold"><i class="bi bi-grid-3x3 me-1"></i>18 Dimensions:</span>
                                            <span style="opacity: 0.8;">(Click to Edit)</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1" id="plat-dims-box-${idx}-${plSafe}">
                                            ${DIMENSION_CONFIG.map(d => {
                                                const val = plDims[d.key] || 'Not Tested';
                                                const chipClass = getDimChipBadgeClass(val);
                                                const chipIcon = getDimChipIcon(val);
                                                return `
                                                <div class="dropdown d-inline-block">
                                                    <button type="button" class="btn btn-sm ${chipClass} py-0 px-1 dropdown-toggle dropdown-toggle-split-none" data-bs-toggle="dropdown" aria-expanded="false" id="dim-chip-${idx}-${plSafe}-${d.key}" style="font-size: 9px; line-height: 1.5; border-radius: 4px;" title="${d.label}: ${val} (Click to change status)">
                                                        <i class="bi ${chipIcon} me-0.5" style="font-size: 7.5px;"></i>
                                                        <span>${d.short}</span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm py-1" style="min-width: 135px; font-size: 11px; z-index: 1060;">
                                                        <li><h6 class="dropdown-header py-1 text-uppercase text-truncate" style="font-size: 9px;">${pl} • ${d.label}</h6></li>
                                                        <li><button type="button" class="dropdown-item py-1 text-success d-flex align-items-center gap-2" onclick="setDimensionStatusInline(${f.feature_id}, '${f.route || ''}', '${pl}', '${d.key}', 'OK', ${idx})"><i class="bi bi-check-circle-fill"></i> OK</button></li>
                                                        <li><button type="button" class="dropdown-item py-1 text-info d-flex align-items-center gap-2" onclick="setDimensionStatusInline(${f.feature_id}, '${f.route || ''}', '${pl}', '${d.key}', 'On Progress', ${idx})"><i class="bi bi-hourglass-split"></i> On Progress</button></li>
                                                        <li><button type="button" class="dropdown-item py-1 text-warning d-flex align-items-center gap-2" onclick="setDimensionStatusInline(${f.feature_id}, '${f.route || ''}', '${pl}', '${d.key}', 'bug', ${idx})"><i class="bi bi-bug-fill"></i> Bug</button></li>
                                                        <li><button type="button" class="dropdown-item py-1 text-danger d-flex align-items-center gap-2" onclick="setDimensionStatusInline(${f.feature_id}, '${f.route || ''}', '${pl}', '${d.key}', 'error', ${idx})"><i class="bi bi-x-circle-fill"></i> Error</button></li>
                                                        <li><button type="button" class="dropdown-item py-1 text-secondary d-flex align-items-center gap-2" onclick="setDimensionStatusInline(${f.feature_id}, '${f.route || ''}', '${pl}', '${d.key}', 'Not Tested', ${idx})"><i class="bi bi-dash-circle"></i> Not Tested</button></li>
                                                        <li><button type="button" class="dropdown-item py-1 text-muted d-flex align-items-center gap-2" onclick="setDimensionStatusInline(${f.feature_id}, '${f.route || ''}', '${pl}', '${d.key}', 'Not applicable', ${idx})"><i class="bi bi-slash-circle"></i> N/A</button></li>
                                                    </ul>
                                                </div>`;
                                            }).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        }).join('')}
                    </div>

                    ${f.notes ? `
                    <div class="alert border py-2 px-3 small mt-2 mb-0" style="background: rgba(125, 125, 125, 0.08);">
                        <i class="bi bi-sticky me-1 text-primary"></i> <strong>Notes:</strong> ${f.notes}
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

// Inline Toast Notification Utility
function showToast(msg, isError = false) {
    let toastContainer = document.getElementById('eimboxToastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'eimboxToastContainer';
        toastContainer.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 1090; display: flex; flex-direction: column; gap: 8px; pointer-events: none;';
        document.body.appendChild(toastContainer);
    }
    const toast = document.createElement('div');
    toast.className = `alert ${isError ? 'alert-danger' : 'alert-success'} py-2 px-3 shadow-lg border mb-0 d-flex align-items-center gap-2 fade show`;
    toast.style.cssText = 'pointer-events: auto; min-width: 250px; font-size: 12px; border-radius: 8px; transition: opacity 0.3s ease;';
    toast.innerHTML = `<i class="bi ${isError ? 'bi-exclamation-triangle-fill text-danger' : 'bi-check-circle-fill text-success'} fs-6"></i> <span>${msg}</span>`;
    toastContainer.appendChild(toast);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 2800);
}

// Inline Single Dimension Updater
function setDimensionStatusInline(featureId, route, platform, dimension, newStatus, featIdx) {
    const f = allFeaturesCatalogCache[featIdx];
    const plSafe = platform.replace(/\s+/g, '_');
    const chipBtn = document.getElementById(`dim-chip-${featIdx}-${plSafe}-${dimension}`);
    
    // Quick optimistic visual spinner
    if (chipBtn) {
        chipBtn.innerHTML = `<span class="spinner-border spinner-border-sm" style="width: 8px; height: 8px;" role="status"></span>`;
    }

    fetch('issues/save-dimension.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            feature_id: featureId,
            route: route,
            title: f ? (f.feature_name || f.screen_title || '') : '',
            platform: platform,
            dimension: dimension,
            status: newStatus
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success' && res.data) {
            const data = res.data;
            // Update cache model
            if (f && f.platforms_data && f.platforms_data[platform]) {
                if (!f.platforms_data[platform].dimensions) f.platforms_data[platform].dimensions = {};
                f.platforms_data[platform].dimensions[dimension] = newStatus;
                f.platforms_data[platform].error_percent = data.error_percent;
                f.platforms_data[platform].status = data.status;
                f.platforms_data[platform].tested_count = data.tested_count;
            }

            // Recalculate 5-platform average for feature
            if (f && f.platforms_data) {
                let sum5 = 0;
                ['Console', 'Dashboard', 'Android Lite', 'Android Premium', 'Desktop'].forEach(p => {
                    if (f.platforms_data[p]) sum5 += (f.platforms_data[p].error_percent || 0);
                });
                f.avg_5_platform_error = Math.round((sum5 / 5) * 10) / 10;
                if (!activePlatformFilter || activePlatformFilter === 'All') {
                    f.problem_percent = f.avg_5_platform_error;
                } else if (f.platforms_data[activePlatformFilter]) {
                    f.problem_percent = f.platforms_data[activePlatformFilter].error_percent;
                }
            }

            // Update Chip Button UI
            if (chipBtn) {
                const chipClass = getDimChipBadgeClass(newStatus);
                const chipIcon = getDimChipIcon(newStatus);
                const dObj = DIMENSION_CONFIG.find(x => x.key === dimension);
                const shortLbl = dObj ? dObj.short : dimension;
                const fullLbl = dObj ? dObj.label : dimension;
                chipBtn.className = `btn btn-sm ${chipClass} py-0 px-1 dropdown-toggle dropdown-toggle-split-none`;
                chipBtn.title = `${fullLbl}: ${newStatus} (Click to change status)`;
                chipBtn.innerHTML = `<i class="bi ${chipIcon} me-0.5" style="font-size: 7.5px;"></i> <span>${shortLbl}</span>`;
            }

            // Update Platform Error & Status Badges
            const pErrBadge = document.getElementById(`plat-err-badge-${featIdx}-${plSafe}`);
            if (pErrBadge) {
                const pErr = data.error_percent;
                let pBadge = 'bg-success text-white';
                if (pErr > 50) pBadge = 'bg-danger text-white';
                else if (pErr > 20) pBadge = 'bg-warning text-dark';
                else if (pErr > 0) pBadge = 'bg-info text-dark';
                else if (data.status === 'Untested') pBadge = 'border text-secondary';
                pErrBadge.className = `badge ${pBadge}`;
                pErrBadge.innerText = `${pErr}% Err`;
            }

            const pStatusBadge = document.getElementById(`plat-status-badge-${featIdx}-${plSafe}`);
            if (pStatusBadge) {
                pStatusBadge.innerText = data.status || 'Untested';
            }

            // Update 5-Plat Avg Pill inside expanded row
            const avgPill = document.getElementById(`feat-avg-pill-${featIdx}`);
            if (avgPill && f) {
                avgPill.innerText = `5-Plat Avg Error: ${f.avg_5_platform_error}%`;
            }

            // Update Main Row Error via Circular Progress Indicator
            const circularContainer = document.getElementById(`feat-circular-err-${featIdx}`);
            if (circularContainer && f) {
                let errVal = (activePlatformFilter === 'All' || !activePlatformFilter) ? f.avg_5_platform_error : (f.platforms_data[activePlatformFilter]?.error_percent ?? f.problem_percent);
                circularContainer.innerHTML = getCircularProgressHtml(errVal, 36);
                circularContainer.setAttribute('title', (activePlatformFilter === 'All' || !activePlatformFilter) ? `5-Platform Average Error: ${errVal}%` : `${activePlatformFilter} Error: ${errVal}%`);
            }

            // Update Main Row 15 Dimension Icons in Real-Time
            const dimsBox = document.getElementById(`feat-dims-box-${featIdx}`);
            if (dimsBox && f) {
                let updatedIconsHtml = '';
                DIMENSION_CONFIG.forEach(d => {
                    updatedIconsHtml += getAggregatedDimensionIcon(f, d, activePlatformFilter);
                });
                dimsBox.innerHTML = updatedIconsHtml;
                dimsBox.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
            }

            showToast(`${platform}: ${dimension.toUpperCase()} updated to ${newStatus}`);
        } else {
            showToast(res.message || 'Failed to update dimension', true);
            renderFeaturesCatalogTable(allFeaturesCatalogCache);
        }
    })
    .catch(err => {
        showToast('Network error while updating dimension', true);
        renderFeaturesCatalogTable(allFeaturesCatalogCache);
    });
}

// Inline Mark All 15 Dimensions OK for a Platform
function setAllPlatformDimensionsOK(featureId, route, platform, featIdx) {
    const f = allFeaturesCatalogCache[featIdx];
    const allDims = {};
    DIMENSION_CONFIG.forEach(d => { allDims[d.key] = 'OK'; });

    fetch('issues/save-dimension.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            feature_id: featureId,
            route: route,
            title: f ? (f.feature_name || f.screen_title || '') : '',
            platform: platform,
            dimensions: allDims
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            showToast(`${platform}: All 18 dimensions marked OK!`);
            loadAllIssues();
        } else {
            showToast(res.message || 'Error updating dimensions', true);
        }
    })
    .catch(err => {
        showToast('Network error while updating dimensions', true);
    });
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

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function openFeatureIssuesPopup(featureId, idx = -1, platformFocus = null) {
    let f = null;
    if (featureId) {
        f = allFeaturesCatalogCache.find(x => parseInt(x.feature_id) === parseInt(featureId));
    }
    if (!f && idx >= 0 && allFeaturesCatalogCache[idx]) {
        f = allFeaturesCatalogCache[idx];
    }
    if (!f) return;

    window._activeFeatureIssuesPopupId = f.feature_id;
    window._activeFeatureIssuesPopupIdx = idx;
    window._activeFeatureIssuesPopupPlatform = platformFocus || 'All';

    renderFeatureIssuesModal(f, idx, window._activeFeatureIssuesPopupPlatform);

    const bsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('featureIssuesPopupModal'));
    bsModal.show();
}

function renderFeatureIssuesModal(f, idx, activePlat) {
    const titleElem = document.getElementById('featureIssuesModalTitle');
    if (titleElem) {
        titleElem.innerHTML = `<i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> ${escapeHtml(f.feature_name)} - Issues`;
    }
    const subElem = document.getElementById('featureIssuesModalSubtitle');
    if (subElem) {
        subElem.innerText = `${f.module_name} • ${f.route || 'No Route'}${activePlat && activePlat !== 'All' ? ' • Platform: ' + activePlat : ''}`;
    }

    const addBtn = document.getElementById('featureIssuesModalAddBtn');
    if (addBtn) {
        addBtn.onclick = function () {
            const bsModal = bootstrap.Modal.getInstance(document.getElementById('featureIssuesPopupModal'));
            if (bsModal) bsModal.hide();
            quickCreateIssueForFeature(f.feature_id, idx, activePlat !== 'All' ? activePlat : 'Console');
        };
    }

    const listContainer = document.getElementById('featureIssuesModalList');
    const allIssues = f.issues || [];

    // Platform pill counts
    const platforms = ['All', 'Console', 'Dashboard', 'Android Lite', 'Android Premium', 'Desktop'];
    const platCounts = { 'All': allIssues.length };
    platforms.forEach(p => {
        if (p !== 'All') {
            platCounts[p] = allIssues.filter(iss => (iss.platform || '').toLowerCase() === p.toLowerCase()).length;
        }
    });

    let html = `
    <!-- Platform Filter Pills Toolbar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
        <div class="d-flex flex-wrap gap-1 align-items-center">
            <span class="small text-muted fw-semibold me-1" style="font-size: 11px;"><i class="bi bi-funnel me-1"></i>Platform:</span>
            ${platforms.map(pl => {
                const count = platCounts[pl] || 0;
                const isSelected = (activePlat === pl);
                let btnClass = isSelected ? 'btn-primary text-white' : (count > 0 ? 'btn-outline-danger' : 'btn-outline-secondary');
                let badgeClass = isSelected ? 'bg-light text-primary' : (count > 0 ? 'bg-danger text-white' : 'bg-secondary text-white');
                return `
                <button type="button" class="btn btn-sm ${btnClass} py-0 px-2 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 11px;" onclick="switchFeatureIssuesModalPlatform('${pl}')">
                    <span>${pl}</span>
                    <span class="badge ${badgeClass} rounded-pill" style="font-size: 9px; padding: 2px 5px;">${count}</span>
                </button>`;
            }).join('')}
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-success py-0 px-2.5 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 11px;" onclick="openCreateFromPopup(${f.feature_id}, ${idx}, '${activePlat !== 'All' ? activePlat : 'Console'}')">
                <i class="bi bi-plus-circle"></i> <span>Report on ${activePlat !== 'All' ? activePlat : 'Feature'}</span>
            </button>
        </div>
    </div>`;

    const filteredIssues = (activePlat === 'All') 
        ? allIssues 
        : allIssues.filter(iss => (iss.platform || '').toLowerCase() === activePlat.toLowerCase());

    if (filteredIssues.length === 0) {
        html += `
        <div class="card border-0 text-center py-4 px-3" style="border-radius: 12px; background: rgba(125,125,125,0.04);">
            <i class="bi bi-check-circle-fill text-success fs-2 mb-2"></i>
            <h6 class="fw-bold mb-1">No Active Issues ${activePlat !== 'All' ? 'for ' + activePlat : ''}</h6>
            <p class="text-muted small mb-3">All dimensions and tests are reported normal with 0 open bugs.</p>
            <div>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1" onclick="openCreateFromPopup(${f.feature_id}, ${idx}, '${activePlat !== 'All' ? activePlat : 'Console'}')">
                    <i class="bi bi-plus-circle"></i> Report New Issue ${activePlat !== 'All' ? 'for ' + activePlat : ''}
                </button>
            </div>
        </div>`;
    } else {
        html += '<div class="list-group gap-2">';
        filteredIssues.forEach(iss => {
            const priorityColor = iss.priority === 'Critical' ? 'danger' : (iss.priority === 'High' ? 'warning text-dark' : 'secondary');
            const statusColor = iss.status === 'Completed' ? 'success' : (iss.status === 'Ongoing' ? 'primary' : 'secondary');

            html += `
            <div class="list-group-item border rounded-3 p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-secondary me-1">${escapeHtml(iss.platform || 'General')}</span>
                        <span class="badge bg-${priorityColor} me-1">${escapeHtml(iss.priority || 'Medium')}</span>
                        <span class="badge bg-${statusColor} me-2">${escapeHtml(iss.status || 'Open')}</span>
                        <strong>${escapeHtml(iss.topic || iss.feature || 'Issue #' + iss.id)}</strong>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary py-0 px-2" onclick="openEditIssueFromPopup(${JSON.stringify(iss).replace(/"/g, '&quot;')})" title="Edit Issue">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger py-0 px-2" onclick="deleteIssueFromPopup(${iss.id}, ${f.feature_id || 0}, ${idx})" title="Delete Issue">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <p class="mb-2 text-secondary small">${escapeHtml(iss.issues || 'No description provided')}</p>
                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                    <div class="d-flex align-items-center gap-2 w-50">
                        <span class="small text-muted" style="font-size: 11px;">Progress:</span>
                        <div class="progress flex-grow-1" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: ${iss.progress_percent || 0}%;"></div>
                        </div>
                        <span class="small fw-bold" style="font-size: 11px;">${iss.progress_percent || 0}%</span>
                    </div>
                    <div class="small text-muted" style="font-size: 11px;">
                        <i class="bi bi-person me-1"></i>${escapeHtml(iss.assigned_to || 'Unassigned')}
                    </div>
                </div>
            </div>`;
        });
        html += '</div>';
    }

    listContainer.innerHTML = html;
}

function switchFeatureIssuesModalPlatform(plat) {
    window._activeFeatureIssuesPopupPlatform = plat;
    let f = null;
    if (window._activeFeatureIssuesPopupId) {
        f = allFeaturesCatalogCache.find(x => parseInt(x.feature_id) === parseInt(window._activeFeatureIssuesPopupId));
    }
    if (!f && window._activeFeatureIssuesPopupIdx >= 0 && allFeaturesCatalogCache[window._activeFeatureIssuesPopupIdx]) {
        f = allFeaturesCatalogCache[window._activeFeatureIssuesPopupIdx];
    }
    if (f) {
        renderFeatureIssuesModal(f, window._activeFeatureIssuesPopupIdx, plat);
    }
}

function openCreateFromPopup(featureId, idx, platform) {
    const popModal = bootstrap.Modal.getInstance(document.getElementById('featureIssuesPopupModal'));
    if (popModal) popModal.hide();
    quickCreateIssueForFeature(featureId, idx, platform);
}

function deleteIssueFromPopup(issueId, featureId, featIdx) {
    if (!confirm(`Are you sure you want to delete issue #${issueId}?`)) return;

    fetch('issues/manage-issue.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: issueId })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            showToast(`Issue #${issueId} deleted successfully`);

            // 1. Remove from allIssuesCache
            allIssuesCache = allIssuesCache.filter(iss => parseInt(iss.id) !== parseInt(issueId));

            // 2. Remove from the feature's issues array in allFeaturesCatalogCache
            let f = null;
            if (featureId) {
                f = allFeaturesCatalogCache.find(x => parseInt(x.feature_id) === parseInt(featureId));
            }
            if (!f && featIdx >= 0 && allFeaturesCatalogCache[featIdx]) {
                f = allFeaturesCatalogCache[featIdx];
            }
            if (f && Array.isArray(f.issues)) {
                f.issues = f.issues.filter(iss => parseInt(iss.id) !== parseInt(issueId));
                f.issue_count = f.issues.length;

                // Re-render the popup content for this feature preserving active platform filter
                renderFeatureIssuesModal(f, featIdx, window._activeFeatureIssuesPopupPlatform || 'All');
            }

            // 3. Reload all issues from server to update KPIs, badges, tables and filters
            loadAllIssues();
        } else {
            showToast(res.message || 'Error deleting issue', true);
        }
    })
    .catch(err => {
        console.error('Delete issue error:', err);
        showToast('Network error while deleting issue', true);
    });
}

function openEditIssueFromPopup(iss) {
    const popModal = bootstrap.Modal.getInstance(document.getElementById('featureIssuesPopupModal'));
    if (popModal) popModal.hide();
    openEditIssueModal(iss);
}

function quickCreateIssueForFeature(featureId, idx = -1, platform = '') {
    let f = null;
    if (featureId) {
        f = allFeaturesCatalogCache.find(x => parseInt(x.feature_id) === parseInt(featureId));
    }
    if (!f && idx >= 0 && allFeaturesCatalogCache[idx]) {
        f = allFeaturesCatalogCache[idx];
    }
    openCreateIssueModal();
    if (f) {
        const modSelect = document.getElementById('modal-module');
        if (modSelect) modSelect.value = f.module_name;
        const scriptInput = document.getElementById('modal-script');
        if (scriptInput) scriptInput.value = f.route || '';
        const topicInput = document.getElementById('modal-topic');
        if (topicInput) topicInput.value = f.feature_name || '';
    }
    if (platform) {
        const platSelect = document.getElementById('modal-platform');
        if (platSelect) platSelect.value = platform;
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
                           (iss.status === 'Testing' ? 'bg-info text-dark' : 'bg-secondary text-white'));

        const platformBadge = iss.platform === 'Console' ? 'bg-primary' :
                             (iss.platform === 'Dashboard' ? 'bg-info' :
                             (iss.platform === 'Android Premium' ? 'bg-success' : 'bg-secondary'));

        html += `
        <tr>
            <td class="ps-3 fw-bold text-muted">#${iss.id}</td>
            <td><span class="badge ${platformBadge}">${iss.platform}</span></td>
            <td>
                <div class="fw-bold">${iss.module}</div>
                <code class="text-secondary small" style="font-size: 11px;">${iss.script || '—'}</code>
            </td>
            <td>
                <div class="fw-semibold">${iss.topic || iss.feature}</div>
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
            <td colspan="21" class="text-center py-4 text-muted">
                No screen dimensions tracked yet. Open any page and use the bottom-right Track Issue button to configure!
            </td>
        </tr>`;
        return;
    }

    const dimCols = ['ui', 'light', 'dark', 'view', 'insert', 'update', 'delete', 'cache', 'push', 'pull', 'dropdown', 'modal', 'print', 'pdf', 'permission', 'documentation', 'faq', 'youtube_video'];

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

    fetch('issues/manage-issue.php', {
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

    fetch('issues/manage-issue.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: id })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            showToast(`Issue #${id} deleted successfully`);
            loadAllIssues();
        } else {
            showToast(res.message || 'Error deleting issue', true);
        }
    })
    .catch(err => {
        console.error('Delete issue error:', err);
        showToast('Network error while deleting issue', true);
    });
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

let currentTreeActiveFeatureId = 0;

function resetFeatureModalToAdd(presetModule = '') {
    currentTreeActiveFeatureId = 0;
    const titleEl = document.getElementById('featureModalTitle');
    if (titleEl) titleEl.innerHTML = '<i class="bi bi-folder-plus me-1"></i> Add New Feature';

    const idEl = document.getElementById('modal-feature-id');
    if (idEl) idEl.value = '0';

    const nameEl = document.getElementById('modal-feature-name');
    if (nameEl) nameEl.value = '';

    const routeEl = document.getElementById('modal-feature-route');
    if (routeEl) routeEl.value = '';

    const descEl = document.getElementById('modal-feature-desc');
    if (descEl) descEl.value = '';

    const modeBadge = document.getElementById('featureModalModeIndicator');
    if (modeBadge) {
        modeBadge.className = 'badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-semibold px-2 py-1';
        modeBadge.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add New Feature Mode';
    }

    const hintEl = document.getElementById('featureModalSelectedHint');
    if (hintEl) hintEl.innerText = 'Fill out the fields to add a feature';

    syncFeatureModuleDropdown(presetModule);

    // Remove active highlight in tree
    document.querySelectorAll('.tree-feature-item').forEach(el => el.classList.remove('active', 'bg-primary', 'bg-opacity-10'));
    
    if (nameEl) nameEl.focus();
}

function selectFeatureFromTree(featureId, idx = -1) {
    let f = null;
    if (featureId) {
        f = allFeaturesCatalogCache.find(item => parseInt(item.feature_id) === parseInt(featureId));
    }
    if (!f && idx >= 0 && allFeaturesCatalogCache[idx]) {
        f = allFeaturesCatalogCache[idx];
    }

    if (!f) return;

    currentTreeActiveFeatureId = f.feature_id;

    const titleEl = document.getElementById('featureModalTitle');
    if (titleEl) titleEl.innerHTML = `<i class="bi bi-pencil-square me-1"></i> Edit Feature: ${f.feature_name}`;

    const idEl = document.getElementById('modal-feature-id');
    if (idEl) idEl.value = f.feature_id;

    const nameEl = document.getElementById('modal-feature-name');
    if (nameEl) nameEl.value = f.feature_name || '';

    const routeEl = document.getElementById('modal-feature-route');
    if (routeEl) routeEl.value = f.route || '';

    const descEl = document.getElementById('modal-feature-desc');
    if (descEl) descEl.value = f.feature_desc || '';

    syncFeatureModuleDropdown(f.module_name || 'General');

    const modeBadge = document.getElementById('featureModalModeIndicator');
    if (modeBadge) {
        modeBadge.className = 'badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-50 fw-semibold px-2 py-1';
        modeBadge.innerHTML = `<i class="bi bi-pencil-square me-1"></i> Editing Feature #${f.feature_id}`;
    }

    const hintEl = document.getElementById('featureModalSelectedHint');
    if (hintEl) hintEl.innerText = `Selected from tree: ${f.module_name} » ${f.feature_name}`;

    // Highlight active in tree
    document.querySelectorAll('.win-tree-item').forEach(el => {
        if (parseInt(el.getAttribute('data-feature-id')) === parseInt(f.feature_id)) {
            el.classList.add('active');
            el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            // Auto expand parent module node if collapsed
            const parentNode = el.closest('.win-tree-node');
            if (parentNode) {
                parentNode.classList.add('expanded');
                const folderIcon = parentNode.querySelector('.win-tree-folder-icon');
                if (folderIcon) {
                    folderIcon.classList.remove('bi-folder-fill');
                    folderIcon.classList.add('bi-folder2-open');
                }
            }
        } else {
            el.classList.remove('active');
        }
    });
}

function renderFeatureModalTree(selectedId = 0, query = '') {
    const container = document.getElementById('featureHierarchyTree');
    const countBadge = document.getElementById('treeFeatureCountBadge');
    if (!container) return;

    currentTreeActiveFeatureId = selectedId;
    const filter = (query || '').toLowerCase().trim();

    // Map features grouped by module
    const moduleMap = {};

    // 1. Ensure all standard modules appear in the tree
    allModulesCache.forEach(m => {
        const mName = m.module_name || m;
        if (!moduleMap[mName]) {
            moduleMap[mName] = {
                name: mName,
                slno: parseInt(m.slno) || 999,
                features: []
            };
        }
    });

    // 2. Put all features in their respective modules
    allFeaturesCatalogCache.forEach(f => {
        const mName = f.module_name || 'General';
        if (!moduleMap[mName]) {
            moduleMap[mName] = {
                name: mName,
                slno: 999,
                features: []
            };
        }
        moduleMap[mName].features.push(f);
    });

    // Sort modules by slno
    const sortedModules = Object.values(moduleMap).sort((a, b) => {
        if (a.slno !== b.slno) return a.slno - b.slno;
        return a.name.localeCompare(b.name);
    });

    let totalFeatures = allFeaturesCatalogCache.length;
    if (countBadge) countBadge.innerText = `${totalFeatures} Features`;

    let html = '';
    let matchCount = 0;

    sortedModules.forEach((mod, mIdx) => {
        let matchedFeatures = mod.features;
        const moduleMatches = mod.name.toLowerCase().includes(filter);

        if (filter) {
            if (!moduleMatches) {
                matchedFeatures = mod.features.filter(f => 
                    (f.feature_name || '').toLowerCase().includes(filter) ||
                    (f.route || '').toLowerCase().includes(filter) ||
                    (f.feature_desc || '').toLowerCase().includes(filter)
                );
            }
        }

        if (filter && !moduleMatches && matchedFeatures.length === 0) {
            return;
        }

        matchCount += matchedFeatures.length;
        const count = mod.features.length;
        const isAutoExpanded = filter ? true : (mIdx < 4 || matchedFeatures.some(f => parseInt(f.feature_id) === parseInt(selectedId)));
        const collapseId = `win-tree-mod-${mIdx}`;

        html += `
        <div class="win-tree-node ${isAutoExpanded ? 'expanded' : ''}" id="${collapseId}">
            <div class="win-tree-row win-tree-parent" onclick="toggleWinTreeNode(this)">
                <span class="win-tree-toggle">
                    <i class="bi bi-chevron-right"></i>
                </span>
                <i class="win-tree-icon win-tree-folder-icon bi ${isAutoExpanded ? 'bi-folder2-open' : 'bi-folder-fill'} text-warning"></i>
                <span class="win-tree-label text-truncate" title="${mod.name}">${mod.name}</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary win-tree-count" title="${count} features in module">${count}</span>
                <button type="button" class="win-tree-add-btn" title="Add feature in ${mod.name}" 
                        onclick="event.stopPropagation(); resetFeatureModalToAdd('${mod.name}');">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="win-tree-children">`;

        if (mod.features.length === 0) {
            html += `
            <div class="win-tree-empty d-flex align-items-center gap-1">
                <i class="bi bi-dash text-muted"></i> 
                <span>No features</span>
                <button type="button" class="btn btn-link btn-xs p-0 ms-1 text-decoration-none" style="font-size: 10.5px;" onclick="resetFeatureModalToAdd('${mod.name}')">+ Add</button>
            </div>`;
        } else {
            matchedFeatures.forEach(f => {
                const isActive = parseInt(f.feature_id) === parseInt(selectedId);
                const issCount = f.issue_count || (f.issues ? f.issues.length : 0);
                const hasRoute = f.route && f.route.trim() !== '';

                html += `
                <div class="win-tree-row win-tree-item ${isActive ? 'active' : ''}"
                     data-feature-id="${f.feature_id}"
                     onclick="selectFeatureFromTree(${f.feature_id})">
                    <i class="win-tree-icon bi bi-file-earmark-code text-primary"></i>
                    <span class="win-tree-label text-truncate" title="${f.feature_name}">${f.feature_name}</span>
                    ${hasRoute 
                        ? `<span class="win-tree-route" title="${f.route}">${f.route}</span>` 
                        : ''}
                    <div class="ms-auto d-flex align-items-center flex-shrink-0">
                        ${issCount > 0 
                            ? `<span class="badge bg-danger rounded-pill px-1" style="font-size: 9px;" title="${issCount} issues">${issCount}</span>` 
                            : `<i class="bi bi-check2 text-success" style="font-size: 11px;" title="No issues"></i>`}
                    </div>
                </div>`;
            });
        }

        html += `
            </div>
        </div>`;
    });

    if (sortedModules.length === 0 || (filter && matchCount === 0)) {
        html = `
        <div class="text-center py-4 text-muted">
            <i class="bi bi-search fs-4 d-block mb-1 text-secondary"></i>
            <span class="small">No matching features found</span>
        </div>`;
    }

    container.innerHTML = html;
}

function toggleWinTreeNode(rowEl) {
    const nodeEl = rowEl.closest('.win-tree-node');
    if (!nodeEl) return;
    nodeEl.classList.toggle('expanded');

    const folderIcon = nodeEl.querySelector('.win-tree-folder-icon');
    if (folderIcon) {
        if (nodeEl.classList.contains('expanded')) {
            folderIcon.classList.remove('bi-folder-fill');
            folderIcon.classList.add('bi-folder2-open');
        } else {
            folderIcon.classList.remove('bi-folder2-open');
            folderIcon.classList.add('bi-folder-fill');
        }
    }
}

function toggleModuleCollapse(collapseId, headerEl) {
    toggleWinTreeNode(headerEl);
}

function expandAllFeatureTree(expand = true) {
    document.querySelectorAll('.win-tree-node').forEach(node => {
        const folderIcon = node.querySelector('.win-tree-folder-icon');
        if (expand) {
            node.classList.add('expanded');
            if (folderIcon) {
                folderIcon.classList.remove('bi-folder-fill');
                folderIcon.classList.add('bi-folder2-open');
            }
        } else {
            node.classList.remove('expanded');
            if (folderIcon) {
                folderIcon.classList.remove('bi-folder2-open');
                folderIcon.classList.add('bi-folder-fill');
            }
        }
    });
}

function filterFeatureTree(val) {
    renderFeatureModalTree(currentTreeActiveFeatureId, val);
}

function openCreateFeatureModal() {
    resetFeatureModalToAdd();
    renderFeatureModalTree(0);

    const searchInput = document.getElementById('treeFeatureSearchInput');
    if (searchInput) searchInput.value = '';

    const bsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('featureMasterModal'));
    bsModal.show();
}

function openEditFeatureModal(featureId, idx = -1) {
    let f = null;
    if (featureId) {
        f = allFeaturesCatalogCache.find(item => parseInt(item.feature_id) === parseInt(featureId));
    }
    if (!f && idx >= 0 && allFeaturesCatalogCache[idx]) {
        f = allFeaturesCatalogCache[idx];
    }
    if (!f) return;

    selectFeatureFromTree(f.feature_id, idx);
    renderFeatureModalTree(f.feature_id);

    const searchInput = document.getElementById('treeFeatureSearchInput');
    if (searchInput) searchInput.value = '';

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

    fetch('issues/manage-feature.php', {
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
            showToast(res.message || 'Feature saved successfully!');
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

    fetch('issues/manage-feature.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete', id: featureId, feature_id: featureId })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            showToast(res.message || 'Feature deleted successfully!');
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
