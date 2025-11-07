// 🔹 Switch Button
function reve() {
    const url = new URL(window.location);
    if (url.searchParams.get('reverse') === '1') url.searchParams.delete('reverse');
    else url.searchParams.set('reverse', '1');
    window.location = url.toString();
}

// 🔹 Notification helper
function showMessage(text, type = 'info') {
    const container = document.createElement('div');
    container.className = `alert alert-${type}`;
    container.innerText = text;
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

        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            showMessage('❌ JSON parse error:\n' + text, 'danger');
            return null;
        }
    } catch (e) {
        showMessage('❌ Network error: ' + e.message, 'danger');
        return null;
    }
}

// 🔹 Sync Table Button
document.querySelectorAll('.sync-table').forEach(btn => {
    btn.addEventListener('click', async () => {
        const table = btn.dataset.table;
        const createSQL = btn.dataset.create;

        console.log(createSQL);
        const res = await postData('apply-schema.php', {
            action: 'apply-table',
            table,
            sql: createSQL
        });

        if (res && res.status === 'ok') showMessage('✅ Table applied: ' + table, 'success');
        else if (res) showMessage('❌ Error: ' + res.msg, 'danger');
    });
});

// 🔹 Sync Column Button
document.querySelectorAll('.sync-column').forEach(btn => {
    btn.addEventListener('click', async () => {
        const table = btn.dataset.table;
        const column = btn.dataset.column;
        const res = await postData('apply-schema.php', {
            action: 'apply-column',
            table,
            column
        });

        if (res && res.status === 'ok') {
            const r = res.results[0];
            showMessage(`✅ Column ${r.table}.${r.column} ${r.status}`, 'success');
            location.reload();
        } else if (res) showMessage('❌ Error: ' + res.msg, 'danger');
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
        showMessage('❌ No columns selected!', 'danger');
        return;
    }

    const res = await postData('apply-schema.php', {
        action: 'apply-selected',
        items: selected
    });

    if (res && res.status === 'ok') {
        let msg = '✅ Sync completed:\n';
        res.results.forEach(r => {
            msg += `${r.table}.${r.column} → ${r.status}\n`;
            if (r.error) msg += '⚠️ ' + r.error + '\n';
        });
        showMessage(msg, 'success');
    } else if (res) showMessage('❌ Server error: ' + res.msg, 'danger');
});
