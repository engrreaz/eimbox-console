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

// 🔹 Sync Table Button
document.querySelectorAll('.sync-table').forEach(btn => {
    btn.addEventListener('click', () => {
        const table = btn.dataset.table;
        const createSQL = btn.dataset.create;
        fetch("apply-schema.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({ action: "apply", schema: schemaData })
        })
            .then(res => {
                if (!res.ok) {
                    throw new Error("HTTP " + res.status + " " + res.statusText);
                }
                return res.json();
            })
            .then(data => {
                console.log("✅ Response:", data);
            })
            .catch(err => {
                console.error("❌ Error:", err);
            });

    });
});

// 🔹 Sync Column Button
document.querySelectorAll('.sync-column').forEach(btn => {
    btn.addEventListener('click', () => {
        const table = btn.dataset.table;
        const column = btn.dataset.column;

        const payload = JSON.stringify([{ table, column }]);
        fetch('apply-schema.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'items=' + encodeURIComponent(payload)
        })
            .then(res => res.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'ok') {
                        const r = data.results[0];
                        if (r.status === 'applied') alert(`✅ Column applied: ${r.table} → ${r.column}`);
                        else if (r.status === 'skipped') alert(`⚠️ Skipped: ${r.table} → ${r.column}\nReason: ${r.error}`);
                        else alert(`❌ Error: ${r.table} → ${r.column}\n${r.error}`);
                        location.reload();
                    } else showMessage('❌ Server error: ' + data.msg, 'danger');
                } catch (e) {
                    showMessage('❌ JSON parse error: ' + e.message + '\nRaw:\n' + text, 'danger');
                }
            })
            .catch(err => showMessage('❌ AJAX error: ' + err.message, 'danger'));
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
document.getElementById('syncSelected').addEventListener('click', () => {
    const selected = [];
    document.querySelectorAll('.check-column:checked').forEach(chk => {
        selected.push({ table: chk.dataset.table, column: chk.dataset.column });
    });

    if (selected.length === 0) {
        showMessage('❌ No columns selected!', 'danger');
        return;
    }

    fetch('apply-schema.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'items=' + encodeURIComponent(JSON.stringify(selected))
    })
        .then(res => res.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.status === 'ok') {
                    let msg = '✅ Sync completed:\n';
                    data.results.forEach(r => {
                        msg += `${r.table} → ${r.column} : ${r.status}\n`;
                        if (r.error) msg += '⚠️ Error: ' + r.error + '\n';
                    });
                    showMessage(msg, 'success');
                } else showMessage('❌ Server error: ' + data.msg, 'danger');
            } catch (e) { showMessage('❌ JSON parse error: ' + e.message + '\nRaw:\n' + text, 'danger'); }
        })
        .catch(err => showMessage('❌ AJAX error: ' + err.message, 'danger'));
});