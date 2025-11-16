document.addEventListener('DOMContentLoaded', () => {

    /* ---------------------------------------------------
       🔹 Base64 Safe Encode
    --------------------------------------------------- */
    function b64(obj) {
        return btoa(unescape(encodeURIComponent(JSON.stringify(obj))));
    }

    /* ---------------------------------------------------
       🔹 Notification Helper (Toast)
    --------------------------------------------------- */
    function showToastx(type, message, title = '') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = title ? `<strong>${title}</strong><br>${message}` : message;

        toast.style.position = 'fixed';
        toast.style.top = '15px';
        toast.style.right = '15px';
        toast.style.minWidth = '250px';
        toast.style.padding = '12px 16px';
        toast.style.borderRadius = '6px';
        toast.style.color = '#fff';
        toast.style.zIndex = 99999;
        toast.style.boxShadow = '0 3px 10px rgba(0,0,0,0.2)';
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s, transform 0.3s';

        switch(type){
            case 'success': toast.style.backgroundColor = '#28a745'; break;
            case 'info': toast.style.backgroundColor = '#17a2b8'; break;
            case 'warning': toast.style.backgroundColor = '#ffc107'; toast.style.color='#212529'; break;
            case 'danger': toast.style.backgroundColor = '#dc3545'; break;
            default: toast.style.backgroundColor = '#17a2b8';
        }

        document.body.appendChild(toast);

        // fade-in
        setTimeout(() => { toast.style.opacity = '1'; toast.style.transform = 'translateY(0)'; }, 50);
        // fade-out
        setTimeout(() => { 
            toast.style.opacity = '0'; 
            toast.style.transform = 'translateY(-20px)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    /* ---------------------------------------------------
       🔹 Fetch POST Safe Wrapper
    --------------------------------------------------- */
    async function postData(url, payload) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'data=' + encodeURIComponent(b64(payload))
            });

            if (!response.ok) {
                showToast('danger', `HTTP Error (${response.status})`, 'Error');
                return null;
            }

            return await response.text();
        }
        catch (err) {
            showToast('danger', `Network Error: ${err.message}`, 'Error');
            return null;
        }
    }

    /* ---------------------------------------------------
       🔹 Safe Output to #syncResult
    --------------------------------------------------- */
    function setSyncResult(html) {
        const target = document.getElementById('syncResult');
        if (target) target.innerHTML = html;
        else showToast('warning', 'syncResult container missing!', 'Warning');

        // also show small toasts for each alert inside
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        tempDiv.querySelectorAll('.alert').forEach(alert => {
            const cls = alert.className.includes('alert-success') ? 'success' :
                        alert.className.includes('alert-danger') ? 'danger' :
                        alert.className.includes('alert-warning') ? 'warning' : 'info';
            showToast(cls, alert.innerHTML);
        });
    }

    /* ---------------------------------------------------
       🔹 Toggle Reverse Mode
    --------------------------------------------------- */
    function reve() {
        const url = new URL(window.location);
        if (url.searchParams.get('reverse') === '1')
            url.searchParams.delete('reverse');
        else 
            url.searchParams.set('reverse', '1');
        window.location = url.toString();
    }

    /* ---------------------------------------------------
       🔹 Sync a Table
    --------------------------------------------------- */
    document.querySelectorAll('.sync-table').forEach(btn => {
        btn.addEventListener('click', async () => {
            const table = btn.dataset.table;
            const createSQL = btn.dataset.create;

            const html = await postData('apply-schema.php', {
                action: 'apply-table',
                table,
                create: createSQL
            });

            if (html) setSyncResult(html);
        });
    });

    /* ---------------------------------------------------
       🔹 Sync a Single Column
    --------------------------------------------------- */
    document.querySelectorAll('.sync-column').forEach(btn => {
        btn.addEventListener('click', async () => {
            const table = btn.dataset.table;
            const columnDef = btn.dataset.column;

            const html = await postData('apply-schema.php', {
                action: 'apply-column',
                table,
                column: columnDef
            });

            if (html) setSyncResult(html);
        });
    });

    /* ---------------------------------------------------
       🔹 Toggle Checkboxes per Table
    --------------------------------------------------- */
    document.querySelectorAll('.toggle-check').forEach(btn => {
        btn.addEventListener('click', () => {
            const table = btn.dataset.table;
            document
                .querySelectorAll(`.check-column[data-table="${table}"]`)
                .forEach(c => (c.checked = !c.checked));
        });
    });

    /* ---------------------------------------------------
       🔹 Sync Selected Columns
    --------------------------------------------------- */
    const syncSelectedBtn = document.getElementById('syncSelected');
    if (syncSelectedBtn) {
        syncSelectedBtn.addEventListener('click', async () => {

            const items = [];
            document.querySelectorAll('.check-column:checked').forEach(chk => {
                items.push({
                    table: chk.dataset.table,
                    column: chk.dataset.column
                });
            });

            if (items.length === 0) {
                showToast('danger', 'No columns selected!', 'Error');
                return;
            }

            const html = await postData('apply-schema.php', {
                action: 'apply-selected',
                items
            });

            if (html) setSyncResult(html);
        });
    }

});
