<?php require_once 'header.php'; ?>
<?php
$LOG_FILE = __DIR__ . "/core/php-error.log";
$ARCHIVE_DIR = __DIR__ . "/core/logs";
$API = 'core/log-api.php';

$CSRF = $_SESSION['csrf_token'];
$per_page_default = 20;
?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-3">📄 Error Log Viewer — EIMBox</h4>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-9">
            <form id="searchForm" class="row g-2">
                <div class="col-md-3">
                    <input type="date" name="date" id="filterDate" class="form-control" placeholder="Date">
                </div>
                <div class="col-md-2">
                    <select name="type" id="filterType" class="form-control">
                        <option value="">All Types</option>
                        <option>ERROR</option>
                        <option>WARNING</option>
                        <option>NOTICE</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="file" id="filterFile" class="form-control" placeholder="Filename">
                </div>
                <div class="col-md-3">
                    <input type="text" name="text" id="filterText" class="form-control" placeholder="Search text...">
                </div>
                <div class="col-md-1">
                    <button id="btnSearch" class="btn btn-primary p-3 mt-1 w-100 tour" type="button"><i
                            class="bi bi-search"></i></button>
                </div>
            </form>
        </div>

        <div class="col-md-3 text-end">
            <div class="btn-group" role="group">
                <button id="btnDownload" class="btn btn-outline-secondary"><i
                        class="bi bi-cloud-arrow-down"></i></button>
                <button id="btnClear" class="btn btn-outline-danger"><i class="bi bi-stars"></i></button>
                <button id="btnArchive" class="btn btn-outline-info tour"><i class="bi bi-archive-fill"></i></button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div id="statsArea" class="mb-3 tour"></div>

    <!-- Delete All (per file) -->

    <!-- Live Tail Toggle -->
    <div class="form-check form-switch mb-3">
        <div class="row">
            <div class="col-6">
                <input class="form-check-input" type="checkbox" id="liveToggle">
                <label class="form-check-label" for="liveToggle">Live Tail (auto-refresh)</label>
            </div>

            <div class="col-6 text-end">
                <button id="btnRemoveAll" class="btn btn-danger btn-sm mb-3 ">Delete All for Selected File</button>

            </div>
        </div>





    </div>


    <!-- Logs List -->
    <div id="logList"></div>

    <!-- Pagination -->
    <nav>
        <ul id="pagination" class="pagination"></ul>
    </nav>

    <!-- Modal: Confirm Clear -->
    <div class="modal fade" id="clearModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="clearForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Clear Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>এই ক্রিয়া পুনরুদ্ধারযোগ্য নয়। চালিয়ে যেতে পাসওয়ার্ড দিন:</p>
                    <input type="password" name="clear_password" class="form-control" placeholder="Clear password">
                    <input type="hidden" name="action" value="clear">
                    <input type="hidden" name="csrf" value="<?php echo $CSRF; ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Clear</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Archive Result -->
    <div class="modal fade" id="archiveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="archiveResultContent"></div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
    const API = '<?php echo $API; ?>';
    const CSRF = '<?php echo $CSRF; ?>';
    let currentPage = 1;
    let perPage = <?php echo $per_page_default; ?>;
    let liveEnabled = false;
    let liveInterval = null;

    // ================= Helpers =================
    function escapeHtml(s) {
        if (!s) return '';
        return s.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
    }

    function renderBadge(type) {
        let cls = 'bg-secondary text-light';
        if (type === 'ERROR') cls = 'border border-danger text-danger bg-white';
        else if (type === 'WARNING') cls = 'border border-warning text-warning bg-white';
        else if (type === 'NOTICE') cls = 'border border-info text-info bg-white';
        return `<span class="badge mx-2" style="font-weight:600;">${type}</span>`;
    }

    function fetchStats() {
        fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'stats', csrf: CSRF })
        }).then(r => r.json()).then(data => {
            if (!data.ok) { document.getElementById('statsArea').innerHTML = ''; return; }
            let html = `<div class="card mb-3"><div class="card-body">
            <div class="row">
              <div class="col-md-3"><strong>Total:</strong> ${data.total}</div>
              <div class="col-md-3"><strong>ERROR:</strong> ${data.counts.ERROR || 0}</div>
              <div class="col-md-3"><strong>WARNING:</strong> ${data.counts.WARNING || 0}</div>
              <div class="col-md-3"><strong>NOTICE:</strong> ${data.counts.NOTICE || 0}</div>
            </div>
            <hr>
            <div><strong>Top files:</strong> ${data.top_files_html}</div>
        </div></div>`;
            document.getElementById('statsArea').innerHTML = html;
        });
    }

    // ================= Fetch Logs =================
    function fetchPage(page = 1) {
        const params = {
            action: 'fetch',
            page,
            per_page: perPage,
            date: document.getElementById('filterDate').value || '',
            type: document.getElementById('filterType').value || '',
            file: document.getElementById('filterFile').value || '',
            text: document.getElementById('filterText').value || '',
            csrf: CSRF
        };

        fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(params)
        }).then(r => r.json()).then(data => {
            if (!data.ok) {
                document.getElementById('logList').innerHTML = `<div class="alert alert-warning">${data.msg || 'Error'}</div>`;
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            const list = data.items.map(it => {
                const time = it.time || 'Unknown';
                const type = it.type || 'UNKNOWN';
                const file = it.file || 'Unknown';
                const msg = it.msg || '';
                const cardColor = (type === 'ERROR') ? 'border-danger' : (type === 'WARNING' ? 'border-warning' : (type === 'NOTICE' ? 'border-info' : 'border-secondary'));
                return `
                <div class="card mb-2 ${cardColor}">
                    <div class="card-header d-flex justify-content-between align-items-start p-2">
                        <div>
                            <strong style="font-size:13px">${time}</strong>
                            <div class="mt-1"><small>${renderBadge(type)} <span class="badge bg-light text-dark">${file}</span></small></div>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-danger del-one" data-idx="${it.global_index}">❌</button>
                            <button class="btn btn-sm btn-outline-secondary copy-one" data-idx="${it.global_index}">Copy</button>
                        </div>
                    </div>
                    <div class="card-body"><pre style="white-space:pre-wrap;font-size:13px;margin:0;">${escapeHtml(msg)}</pre></div>
                </div>
            `;
            }).join('');

            document.getElementById('logList').innerHTML = list || '<div class="alert alert-info">No logs matched.</div>';
            currentPage = data.page;

            // attach dynamic buttons
            document.querySelectorAll('.del-one').forEach(btn => btn.addEventListener('click', () => removeLineAjax(btn.dataset.idx)));
            document.querySelectorAll('.copy-one').forEach(btn => btn.addEventListener('click', () => copyLine(btn.dataset.idx)));

            renderPagination(Math.ceil(data.total / perPage));
        });
    }

    // ================= Pagination =================
    function renderPagination(totalPages) {
        let html = '';
        const range = 3;
        const start = Math.max(1, currentPage - range);
        const end = Math.min(totalPages, currentPage + range);

        if (currentPage > 1) html += `<li class="page-item"><a class="page-link" href="#" onclick="gotoPage(1);return false;">«</a></li>`;
        for (let i = start; i <= end; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" onclick="gotoPage(${i});return false;">${i}</a></li>`;
        }
        if (currentPage < totalPages) html += `<li class="page-item"><a class="page-link" href="#" onclick="gotoPage(${totalPages});return false;">»</a></li>`;

        document.getElementById('pagination').innerHTML = html;
    }

    function gotoPage(p) { fetchPage(p); }

    // ================= Actions =================
    function removeLineAjax(globalIndex) {
        if (!confirm('Confirm remove this log line?')) return;
        fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'remove_by_index', index: parseInt(globalIndex), csrf: CSRF }) })
            .then(r => r.json()).then(res => {
                if (res.ok) { fetchStats(); fetchPage(currentPage); } else alert(res.msg || 'Remove failed');
            });
    }

    function copyLine(globalIndex) {
        fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'get_raw_by_index', index: parseInt(globalIndex), csrf: CSRF }) })
            .then(r => r.json()).then(res => {
                if (res.ok) { navigator.clipboard.writeText(res.raw).then(() => alert('Copied')); } else alert(res.msg || 'Failed');
            });
    }

    // ================= Event Listeners =================
    document.getElementById('btnSearch').addEventListener('click', () => fetchPage(1));

    document.getElementById('btnDownload').addEventListener('click', () => {
        window.location.href = API + '?action=download&csrf=' + CSRF;
    });

    document.getElementById('btnArchive').addEventListener('click', () => {
        fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'archive', csrf: CSRF }) })
            .then(r => r.json()).then(res => {
                const modalContent = document.getElementById('archiveResultContent');
                modalContent.innerHTML = res.ok ?
                    `<div class="modal-header"><h5 class="modal-title">Archive Done</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p>Archived to: <strong>${res.archive}</strong></p></div>
                <div class="modal-footer"><button class="btn btn-primary" data-bs-dismiss="modal">OK</button></div>` :
                    `<div class="modal-body"><div class="alert alert-danger">${res.msg || 'Failed'}</div></div>`;
                new bootstrap.Modal(document.getElementById('archiveModal')).show();
                fetchStats(); fetchPage(currentPage);
            });
    });

    document.getElementById('btnClear').addEventListener('click', () => new bootstrap.Modal(document.getElementById('clearModal')).show());

    document.getElementById('clearForm').addEventListener('submit', function (e) {
        e.preventDefault();
        fetch(API, { method: 'POST', body: new FormData(this) }).then(r => r.json()).then(res => {
            if (res.ok) { alert('Log cleared'); bootstrap.Modal.getInstance(document.getElementById('clearModal')).hide(); fetchStats(); fetchPage(1); }
            else alert(res.msg || 'Clear failed');
        });
    });

    // Live tail
    document.getElementById('liveToggle').addEventListener('change', function () {
        liveEnabled = this.checked;
        if (liveEnabled) { liveInterval = setInterval(() => { fetchPage(1); fetchStats(); }, 3000); }
        else clearInterval(liveInterval);
    });

    // Delete all for selected file
    document.getElementById('btnRemoveAll').addEventListener('click', function () {
        const file = document.getElementById('filterFile').value;
        if (!file) { alert('Select a file first'); return; }
        if (!confirm('Delete all errors for "' + file + '" ?')) return;
        fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'remove_all_by_file', file: file, csrf: CSRF }) })
            .then(r => r.json()).then(d => { if (d.ok) { alert('All errors removed'); fetchStats(); fetchPage(1); } else alert('Failed: ' + d.msg); });
    });
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('file-badge')) {
            const file = e.target.dataset.file;
            document.getElementById('filterFile').value = file;
            fetchPage(1); // page 1 থেকে লোড হবে
        }
    });
    // ================= Initial Load =================
    fetchStats();
    fetchPage(1);
</script>