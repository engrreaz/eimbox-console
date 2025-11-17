<?php require_once 'header.php'; ?>
<?php
// -------------------------------------------------
// Error Log Viewer (UI)
// Path config
$LOG_FILE = __DIR__ . "/core/php-error.log";
$ARCHIVE_DIR = __DIR__ . "/core/logs";
$API = 'core/log-api.php';

// CSRF token for forms / AJAX
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['log_csrf'])) $_SESSION['log_csrf'] = bin2hex(random_bytes(16));
$CSRF = $_SESSION['log_csrf'];

// default per-page
$per_page_default = 20;
?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="mb-3">📄 Error Log Viewer — EIMBox</h4>

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
                    <input type="text" name="file" id="filterFile" class="form-control" placeholder="Filename (e.g. student-payable.php)">
                </div>
                <div class="col-md-3">
                    <input type="text" name="text" id="filterText" class="form-control" placeholder="Search text...">
                </div>
                <div class="col-md-1">
                    <button id="btnSearch" class="btn btn-primary w-100" type="button">Search</button>
                </div>
            </form>
        </div>

        <div class="col-md-3 text-end">
            <div class="btn-group" role="group">
                <button id="btnDownload" class="btn btn-outline-secondary"><i class="bi bi-cloud-arrow-down"></i></button>
                <button id="btnClear" class="btn btn-outline-danger"><i class="bi bi-stars"></i></button>
                <button id="btnArchive" class="btn btn-outline-info"><i class="bi bi-archive-fill"></i></button>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div id="statsArea" class="mb-3"></div>

    <!-- Live Tail Toggle -->
    <div class="form-check form-switch mb-3">
      <input class="form-check-input" type="checkbox" id="liveToggle">
      <label class="form-check-label" for="liveToggle">Live Tail (auto-refresh)</label>
    </div>

    <!-- List area -->
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

    <!-- Hidden form for non-AJAX remove fallback -->
    <form id="fallbackRemoveForm" method="POST" action="<?php echo $API; ?>" style="display:none;">
        <input type="hidden" name="action" value="remove">
        <input type="hidden" name="line" id="fallbackLine">
        <input type="hidden" name="csrf" value="<?php echo $CSRF; ?>">
    </form>

</div>

<?php require_once 'footer.php'; ?>

<script>
/* global bootstrap */
const API = '<?php echo $API; ?>';
const CSRF = '<?php echo $CSRF; ?>';
let currentPage = 1;
let perPage = <?php echo $per_page_default; ?>;
let liveEnabled = false;
let liveInterval = null;

function renderBadge(type){
    let cls = 'bg-secondary text-light';
    if(type==='ERROR') cls = 'border border-danger text-danger bg-white';
    else if(type==='WARNING') cls = 'border border-warning text-warning bg-white';
    else if(type==='NOTICE') cls = 'border border-info text-info bg-white';
    return `<span class="badge me-1" style="font-weight:600;">${type}</span>`;
}

function fetchStats(){
    fetch(API, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'stats', csrf:CSRF})
    }).then(r=>r.json()).then(data=>{
        if(!data.ok) { document.getElementById('statsArea').innerHTML=''; return; }
        let html = `<div class="card mb-3"><div class="card-body">
            <div class="row">
              <div class="col-md-3"><strong>Total:</strong> ${data.total}</div>
              <div class="col-md-3"><strong>ERROR:</strong> ${data.counts.ERROR||0}</div>
              <div class="col-md-3"><strong>WARNING:</strong> ${data.counts.WARNING||0}</div>
              <div class="col-md-3"><strong>NOTICE:</strong> ${data.counts.NOTICE||0}</div>
            </div>
            <hr>
            <div><strong>Top files:</strong> ${data.top_files_html}</div>
        </div></div>`;
        document.getElementById('statsArea').innerHTML = html;
    });
}

function fetchPage(page=1){
    const params = {
        action: 'fetch',
        page: page,
        per_page: perPage,
        date: document.getElementById('filterDate').value || '',
        type: document.getElementById('filterType').value || '',
        file: document.getElementById('filterFile').value || '',
        text: document.getElementById('filterText').value || '',
        csrf: CSRF
    };

    fetch(API, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(params)
    }).then(r=>r.json()).then(data=>{
        if(!data.ok){
            document.getElementById('logList').innerHTML = `<div class="alert alert-warning">${data.msg||'Error'}</div>`;
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        // render logs
        const list = data.items.map((it, idx)=> {
            const time = it.time || 'Unknown';
            const type = it.type || 'UNKNOWN';
            const file = it.file || 'Unknown';
            const msg = it.msg || '';
            // each item has 'raw' for removal
            const cardColor = (type==='ERROR') ? 'border-danger' : (type==='WARNING' ? 'border-warning' : (type==='NOTICE' ? 'border-info' : 'border-secondary'));
            return `
            <div class="card mb-2 ${cardColor}">
              <div class="card-header d-flex justify-content-between align-items-start p-2">
                <div>
                  <strong style="font-size:13px">${time}</strong>
                  <div class="mt-1"><small>${renderBadge(type)} <span class="badge bg-light text-dark">${file}</span></small></div>
                </div>
                <div>
                  <button class="btn btn-sm btn-outline-danger me-1" onclick="removeLineAjax(${idx}, ${data.page_start_index + idx})">❌</button>
                  <button class="btn btn-sm btn-outline-secondary" onclick="copyLine(${data.page_start_index + idx})">Copy</button>
                </div>
              </div>
              <div class="card-body"><pre style="white-space:pre-wrap;font-size:13px;margin:0;">${escapeHtml(msg)}</pre></div>
            </div>`;
        }).join('');

        document.getElementById('logList').innerHTML = list || '<div class="alert alert-info">No logs matched.</div>';

        // pagination
        const total = data.total;
        const totalPages = Math.ceil(total / perPage);
        currentPage = data.page;
        renderPagination(totalPages);
    }).catch(e=>{
        document.getElementById('logList').innerHTML = `<div class="alert alert-danger">Request failed</div>`;
    });
}

// helpers
function escapeHtml(s){
    if(!s) return '';
    return s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');
}

function renderPagination(totalPages){
    let html = '';
    const range = 3;
    const start = Math.max(1, currentPage - range);
    const end = Math.min(totalPages, currentPage + range);

    if(currentPage > 1) html += `<li class="page-item"><a class="page-link" href="#" onclick="gotoPage(1);return false;">«</a></li>`;
    for(let i=start;i<=end;i++){
        html += `<li class="page-item ${i===currentPage?'active':''}"><a class="page-link" href="#" onclick="gotoPage(${i});return false;">${i}</a></li>`;
    }
    if(currentPage < totalPages) html += `<li class="page-item"><a class="page-link" href="#" onclick="gotoPage(${totalPages});return false;">»</a></li>`;

    document.getElementById('pagination').innerHTML = html;
}

function gotoPage(p){ fetchPage(p); }

// Remove (AJAX) uses index position in current full-file indexing to ensure uniqueness
function removeLineAjax(idxOnPage, globalIndex){
    if(!confirm('Confirm remove this log line?')) return;
    fetch(API, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'remove_by_index', index: globalIndex, csrf: CSRF})
    }).then(r=>r.json()).then(res=>{
        if(res.ok){
            fetchStats();
            fetchPage(currentPage);
        } else {
            alert(res.msg || 'Remove failed');
        }
    });
}

// Copy raw line (helper)
function copyLine(globalIndex){
    fetch(API, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'get_raw_by_index', index: globalIndex, csrf: CSRF})
    }).then(r=>r.json()).then(res=>{
        if(res.ok){
            navigator.clipboard.writeText(res.raw).then(()=>alert('Copied'));
        } else alert(res.msg || 'Failed');
    });
}

// Download
document.getElementById('btnDownload').addEventListener('click', ()=>{
    // use direct link
    window.location.href = API + '?action=download&csrf=' + CSRF;
});

// Archive now
document.getElementById('btnArchive').addEventListener('click', ()=>{
    fetch(API, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'archive', csrf: CSRF})
    }).then(r=>r.json()).then(res=>{
        const modalContent = document.getElementById('archiveResultContent');
        if(res.ok){
            modalContent.innerHTML = `<div class="modal-header"><h5 class="modal-title">Archive Done</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p>Archived to: <strong>${res.archive}</strong></p></div>
            <div class="modal-footer"><button class="btn btn-primary" data-bs-dismiss="modal">OK</button></div>`;
        } else {
            modalContent.innerHTML = `<div class="modal-body"><div class="alert alert-danger">${res.msg||'Failed'}</div></div>`;
        }
        var m = new bootstrap.Modal(document.getElementById('archiveModal'));
        m.show();
        fetchStats();
        fetchPage(currentPage);
    });
});

// Clear modal
document.getElementById('btnClear').addEventListener('click', ()=>{
    var m = new bootstrap.Modal(document.getElementById('clearModal'));
    m.show();
});

document.getElementById('clearForm').addEventListener('submit', function(e){
    e.preventDefault();
    const fd = new FormData(this);
    fetch(API, {method:'POST', body: fd}).then(r=>r.json()).then(res=>{
        if(res.ok){
            alert('Log cleared');
            var m = bootstrap.Modal.getInstance(document.getElementById('clearModal'));
            m.hide();
            fetchStats();
            fetchPage(1);
        } else {
            alert(res.msg || 'Clear failed');
        }
    });
});

// Search button
document.getElementById('btnSearch').addEventListener('click', ()=> fetchPage(1));

// Live tail toggle
document.getElementById('liveToggle').addEventListener('change', function(){
    liveEnabled = this.checked;
    if(liveEnabled){
        liveInterval = setInterval(()=> {
            // always fetch page 1 latest in live mode
            fetchPage(1);
            fetchStats();
        }, 3000);
    } else {
        clearInterval(liveInterval);
    }
});

// initial load
fetchStats();
fetchPage(1);
</script>
