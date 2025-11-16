document.addEventListener('DOMContentLoaded', () => {

    /* ---------------------------------------------------
       🔹 Base64 Safe Encode
    --------------------------------------------------- */
    function b64(obj) {
        return btoa(unescape(encodeURIComponent(JSON.stringify(obj))));
    }

    /* ---------------------------------------------------
       🔹 Notification Helper
    --------------------------------------------------- */
    function showMessage(content, type = 'info') {
        const div = document.createElement('div');
        div.className = `alert alert-${type}`;
        div.innerHTML = content;
        div.style.position = 'fixed';
        div.style.top = '15px';
        div.style.left = '50%';
        div.style.transform = 'translateX(-50%)';
        div.style.zIndex = '99999';
        div.style.minWidth = '300px';
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 4500);
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
                showMessage(`❌ HTTP Error (${response.status})`, 'danger');
                return null;
            }

            return await response.text();
        }
        catch (err) {
            showMessage(`❌ Network Error: ${err.message}`, 'danger');
            return null;
        }
    }

    /* ---------------------------------------------------
       🔹 Safe Output to #syncResult
    --------------------------------------------------- */
    function setSyncResult(html) {
        const target = document.getElementById('syncResult');
        if (target) target.innerHTML = html;
        else showMessage('⚠️ syncResult container missing!', 'warning');
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
                create: createSQL // Already Base64 encoded
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
            const columnDef = btn.dataset.column;  // raw SQL definition

            const html = await postData('apply-schema.php', {
                action: 'apply-column',
                table,
                column: columnDef  // Safe after Base64
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
                showMessage('❌ No columns selected!', 'danger');
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
