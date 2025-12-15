<?php require_once 'header.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">

    <?php
    /* ---------------------------
       Filter Handle
    --------------------------- */
    $where = " WHERE sccode='$sccode' ";

    $sms_types = [];
    $campaigns = [];

    if (!empty($_GET['sms_type'])) {
        $sms_types = $_GET['sms_type'];
        $in = "'" . implode("','", $sms_types) . "'";
        $where .= " AND sms_type IN ($in)";
    }

    if (!empty($_GET['campaign'])) {
        $campaigns = $_GET['campaign'];
        $in = "'" . implode("','", $campaigns) . "'";
        $where .= " AND campaign IN ($in)";
    }

    /* ---------------------------
       Dropdown Data
    --------------------------- */
    $typeQ = mysqli_query($conn, "SELECT DISTINCT sms_type FROM sms WHERE sccode='$sccode'");
    $campQ = mysqli_query($conn, "SELECT DISTINCT campaign FROM sms WHERE sccode='$sccode'");
    ?>

    <div class="card">
        <!-- Filters -->
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="date" id="from" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" id="to" class="form-control">
            </div>
            <div class="col-md-3">
                <button class="btn btn-danger" onclick="exportPDF()">Export PDF</button>
            </div>
        </div>

        <!-- DataTable -->
        <div class="table-responsive">
            <table id="smsTable" class="table table-bordered table-sm w-100">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Mobile</th>
                        <th>Type</th>
                        <th>Campaign</th>
                        <th>Status</th>
                        <th>SMS</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>



</div>

<?php require_once 'footer.php'; ?>



<div class="modal fade" id="smsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>SMS Text</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="smsBody"></div>
        </div>
    </div>
</div>

<script>
    function showSMS(t) {
        $('#smsBody').text(atob(t));
        new bootstrap.Modal('#smsModal').show();
    }
</script>



<script>
    function showSMS(txt) {
        $('#smsBody').text(atob(txt));
        new bootstrap.Modal('#smsModal').show();
    }
</script>




<script>
    let page = 1;

    function loadSMS(p = 1) {
        page = p;
        $.post("ajax/sms-log-fetch.php", {
            keyword: $('#keyword').val(),
            from: $('#from').val(),
            to: $('#to').val(),
            page: p
        }, function (res) {
            let h = "", sl = (p - 1) * 10 + 1;
            res.data.forEach(r => {
                h += `<tr>
                    <td>${sl++}</td>
                    <td>${r.date}</td>
                    <td>${r.mobile_number}</td>
                    <td>${r.sms_type}</td>
                    <td>${r.campaign}</td>
                    <td>${r.status == 1 ? 'Sent' : 'Fail'}</td>
                    <td>${r.success_message}</td>
                    <td>
                        <button class="btn btn-sm btn-info"
                        onclick="showSMS('${btoa(r.sms_text)}')">
                        View
                        </button>
                    </td>
                    </tr>`;
            });
            $("#smsData").html(h);

            let pg = "";
            for (i = 1; i <= res.pages; i++) {
                pg += `<button class="btn btn-sm ${i == p ? 'btn-primary' : 'btn-outline-primary'}"
      onclick="loadSMS(${i})">${i}</button> `;
            }
            $("#pagination").html(pg);
        }, "json");
    }

    $('#keyword,#from,#to').on('keyup change', () => loadSMS(1));
    loadSMS();
</script>

<script>
    function exportPDF() {
        window.open('export/sms-pdf.php', '_blank');
    }
</script>



<script>
    let table = $('#smsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: "ajax/sms-datatable.php",
            type: "POST",
            data: function (d) {
                d.from = $('#from').val();
                d.to = $('#to').val();
            }
        },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
            { data: "date" },
            { data: "mobile_number" },
            { data: "campaign" },
            {
                data: "status",
                render: d => d == 1
                    ? '<span class="badge bg-success">Sent</span>'
                    : '<span class="badge bg-danger">Fail</span>'
            },
            {
                data: "sms_text",
                render: d => `<button class="btn btn-sm btn-info"
       onclick="showSMS('${btoa(d)}')">View</button>`
            },
            {
                data: "id",
                render: id => `<button class="btn btn-sm btn-warning"
       onclick="resendSMS(${id})">Resend</button>`
            }
        ]
    });

    $('#from,#to').change(() => table.ajax.reload());
</script>
</body>

</html>