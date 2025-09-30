<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EIMBox Full Dashboard</title>

    <!-- Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f8f9fa;
        }

        h2 {
            text-align: center;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
        }

        canvas {
            width: 100% !important;
            height: 300px !important;
        }

        button {
            margin: 5px;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: #007bff;
            color: white;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <h2>📊 EIMBox Full Dashboard</h2>

    <!-- Filters -->
    <div class="card">
        <label>Start Date: <input type="date" id="startDate"></label>
        <label>End Date: <input type="date" id="endDate"></label>
        <button onclick="applyFilter()">Apply Filter</button>
    </div>

    <!-- Export Buttons -->
    <div class="card">
        <button onclick="exportFilteredCSV('pageTable','page_report.csv')">Export Pages CSV</button>
        <button onclick="exportFilteredCSV('userTable','user_report.csv')">Export Users CSV</button>
        <button onclick="exportFilteredPDF('pageTable','page_report.pdf','Most Visited Pages')">Export Pages
            PDF</button>
        <button onclick="exportFilteredPDF('userTable','user_report.pdf','Top Active Users')">Export Users PDF</button>
    </div>

    <!-- Dashboard -->
    <div class="grid">
        <div class="card">
            <h3>Most Visited Pages</h3>
            <canvas id="pageChart"></canvas>
            <table id="pageTable" class="display">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Visits</th>
                        <th>Total Time (min)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="card">
            <h3>Top Active Users</h3>
            <canvas id="userChart"></canvas>
            <table id="userTable" class="display">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Visits</th>
                        <th>Total Time (min)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="card">
            <h3>Daily Active Users</h3>
            <canvas id="dailyChart"></canvas>
        </div>
    </div>

    <script>
        // --- Initialize DataTables ---
        let pageTableDT, userTableDT;

        function initTables() {
            pageTableDT = $('#pageTable').DataTable({ destroy: true });
            userTableDT = $('#userTable').DataTable({ destroy: true });
        }

        // --- CSV Export ---
        function exportFilteredCSV(tableId, filename) {
            const table = $(`#${tableId}`).DataTable();
            const data = table.rows({ search: 'applied' }).data().toArray();
            const headers = table.columns().header().toArray().map(h => h.innerText);
            let csv = [headers.join(',')];
            data.forEach(row => csv.push(row.join(',')));
            const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
            const link = document.createElement('a'); link.href = URL.createObjectURL(blob);
            link.download = filename; document.body.appendChild(link); link.click(); document.body.removeChild(link);
        }

        // --- PDF Export ---
        function exportFilteredPDF(tableId, filename, title) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.setFontSize(16); doc.text(title, 14, 15);
            const table = $(`#${tableId}`).DataTable();
            const data = table.rows({ search: 'applied' }).data().toArray();
            const headers = [table.columns().header().toArray().map(h => h.innerText)];
            doc.autoTable({ head: headers, body: data, startY: 25, theme: 'grid', headStyles: { fillColor: [0, 123, 255], textColor: 255 }, alternateRowStyles: { fillColor: [240, 240, 240] }, styles: { fontSize: 10 } });
            doc.save(filename);
        }

        // --- Load Dashboard Data ---
        async function loadData(startDate = '', endDate = '') {
            const res = await fetch(`dashboard_data.php?start=${startDate}&end=${endDate}`);
            const data = await res.json();

            // Charts
            new Chart(document.getElementById('pageChart'), {
                type: 'bar',
                data: {
                    labels: data.pages, datasets: [
                        { label: 'Visits', data: data.pageVisits, backgroundColor: '#007bff' },
                        { label: 'Time (min)', data: data.pageTime, backgroundColor: '#ffc107' }
                    ]
                },
                options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { x: { stacked: true }, y: { stacked: true } } }
            });

            new Chart(document.getElementById('userChart'), {
                type: 'bar',
                data: {
                    labels: data.users, datasets: [
                        { label: 'Visits', data: data.userVisits, backgroundColor: '#6c757d' },
                        { label: 'Time (min)', data: data.userTime, backgroundColor: '#28a745' }
                    ]
                },
                options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { x: { stacked: true }, y: { stacked: true } } }
            });

            new Chart(document.getElementById('dailyChart'), {
                type: 'line',
                data: { labels: data.days.reverse(), datasets: [{ label: 'Active Users', data: data.activeUsers.reverse(), borderColor: '#ff5733', fill: false }] }
            });

            // Tables
            let pageRows = ''; data.pages.forEach((p, i) => { pageRows += `<tr><td>${p}</td><td>${data.pageVisits[i]}</td><td>${data.pageTime[i]}</td></tr>`; });
            $('#pageTable tbody').html(pageRows);

            let userRows = ''; data.users.forEach((u, i) => { userRows += `<tr><td>${u}</td><td>${data.userVisits[i]}</td><td>${data.userTime[i]}</td></tr>`; });
            $('#userTable tbody').html(userRows);

            initTables(); // reinitialize DataTables
        }

        // --- Apply Filter ---
        function applyFilter() {
            const start = $('#startDate').val();
            const end = $('#endDate').val();
            loadData(start, end);
        }

        // --- Initial Load ---
        loadData();
    </script>
</body>

</html>