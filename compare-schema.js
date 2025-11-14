// 🔹 Switch Button
function reve() {
    const url = new URL(window.location);
    if (url.searchParams.get('reverse') === '1') url.searchParams.delete('reverse');
    else url.searchParams.set('reverse', '1');
    window.location = url.toString();
}

// 🔹 Notification helper
function showMessage(htmlContent) {
    const container = document.createElement('div');
    container.className = `alert`;
    container.innerHTML = htmlContent;
    container.style.position = 'fixed';
    container.style.top = '20px';
    container.style.left = '50%';
    container.style.transform = 'translateX(-50%)';
    container.style.zIndex = '9999';
    container.style.minWidth = '300px';
    container.style.textAlign = 'center';
    container.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    document.body.appendChild(container);
    setTimeout(() => container.remove(), 5000);
}

// 🔹 Helper to POST safely
async function postData(url, dataObj) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'data=' + encodeURIComponent(JSON.stringify(dataObj))
        });
        const html = await response.text();
        return html; // সরাসরি HTML
    } catch (e) {
        showMessage('❌ Network error: ' + e.message);
        return null;
    }
}

// 🔹 Sync Table Button
document.querySelectorAll('.sync-table').forEach(btn => {
    btn.addEventListener('click', async () => {
        const table = btn.dataset.table;
        const createSQL = btn.dataset.create;
        const html = await postData('apply-schema.php', {
            action: 'apply-table',
            table,
            sql: createSQL
        });
        if (html) document.getElementById('syncResult').innerHTML = html;
    });
});

// 🔹 Sync Column Button
document.querySelectorAll('.sync-column').forEach(btn => {
    btn.addEventListener('click', async () => {
        const table = btn.dataset.table;
        const column = btn.dataset.column;
        const html = await postData('apply-schema.php', {
            action: 'apply-column',
            table,
            column
        });
        if (html) document.getElementById('syncResult').innerHTML = html;
    });
});

// 🔹 Toggle Checkboxes
document.querySelectorAll('.toggle-check').forEach(btn => {
    btn.addEventListener('click', () => {
        const table = btn.dataset.table;
        document.querySelectorAll(`.check-column[data-table='${table}']`).forEach(c => c.checked = !c.checked);
    });
});

// 🔹 Sync Selected Columns
document.getElementById('syncSelected').addEventListener('click', async () => {
    const selected = [];
    document.querySelectorAll('.check-column:checked').forEach(chk => {
        selected.push({ table: chk.dataset.table, column: chk.dataset.column });
    });

    if (selected.length === 0) {
        showMessage('<div class="alert alert-danger">❌ No columns selected!</div>');
        return;
    }

    const html = await postData('apply-schema.php', {
        action: 'apply-selected',
        items: selected
    });

    if (html) document.getElementById('syncResult').innerHTML = html;
});
