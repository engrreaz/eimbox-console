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
                        <button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#tab-all-issues">
                            <i class="bi bi-table me-1"></i> Issues List (<span id="tab-issues-badge">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#tab-screen-dimensions">
                            <i class="bi bi-grid-3x3-gap me-1"></i> Screen Health Matrix (<span id="tab-screens-badge">0</span>)
                        </button>
                    </li>
                </ul>

                <!-- Filter Controls -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" id="filter-search" class="form-control form-control-sm" placeholder="Search issues, topics, scripts..." style="width: 220px;" oninput="applyFilters()">
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
                
                <!-- TAB 1: ALL ISSUES TABLE -->
                <div class="tab-pane fade show active" id="tab-all-issues">
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

                <!-- TAB 2: SCREEN DIMENSIONS MATRIX -->
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

<?php require_once 'footer.php'; ?>

<!-- Master Issue Tracker Script -->
<script>
let allIssuesCache = [];
let allDimensionsCache = [];
let activePlatformFilter = 'All';

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
    fetch(`api/v1/issues/get-all-issues.php?platform=${encodeURIComponent(activePlatformFilter)}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data) {
                allIssuesCache = res.data.issues || [];
                allDimensionsCache = res.data.dimension_screens || [];

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

    document.getElementById('tab-issues-badge').innerText = kpis.total || 0;
    document.getElementById('tab-screens-badge').innerText = allDimensionsCache.length;
}

function populateModuleFilter(modules) {
    const modSelect = document.getElementById('filter-module');
    const modalModSelect = document.getElementById('modal-module');
    if (!modules || !modSelect) return;

    let opts = '<option value="All">All Modules</option>';
    let modalOpts = '';
    modules.forEach(m => {
        opts += `<option value="${m.module_name}">${m.module_name}</option>`;
        modalOpts += `<option value="${m.module_name}">${m.module_name}</option>`;
    });
    modSelect.innerHTML = opts;
    if (modalModSelect) modalModSelect.innerHTML = modalOpts;
}

function applyFilters() {
    const search = (document.getElementById('filter-search').value || '').toLowerCase();
    const module = document.getElementById('filter-module').value;
    const status = document.getElementById('filter-status').value;
    const priority = document.getElementById('filter-priority').value;

    const filtered = allIssuesCache.filter(item => {
        if (module !== 'All' && item.module !== module) return false;
        if (status !== 'All' && item.status !== status) return false;
        if (priority !== 'All' && item.priority !== priority) return false;
        if (search) {
            const combined = `${item.feature} ${item.topic} ${item.issues} ${item.script} ${item.assigned_to}`.toLowerCase();
            if (!combined.includes(search)) return false;
        }
        return true;
    });

    renderIssuesTable(filtered);
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
    EIMBOX_ISSUE_CONFIG.script = route;
    const currentScriptEl = document.getElementById('eimbox-current-script-name');
    if (currentScriptEl) currentScriptEl.innerText = route;
    eimboxOpenIssueModal();
}
</script>
