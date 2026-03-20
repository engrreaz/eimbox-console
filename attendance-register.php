<?php require_once 'header.php'; ?>

<style>
    tr th {
        padding: 2px;
    }

    .full-height {
        height: 100%;
    }

    .vr-full {
        width: 1px;
        background-color: #ccc;
        height: 100%;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

    <div id="eposlink" hidden>..............</div>

    <?php
    $chain_param = '-c 12 -t Attendence Register -u -r -b View Attendance';
    include 'components/slot-tree-ui.php';

    ?>

    <div class="card mb-3 card-border-shadow-primary">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label text-small" for="date-from-main">Date From</label>
                    <input type="date" class="form-control form-control-sm" id="date-from-main">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-small" for="date-to-main">Date To</label>
                    <input type="date" class="form-control form-control-sm" id="date-to-main">
                </div>





                <div class="col-md-4 text-center row pt-2 full-height">

                  

                    <!-- Divider -->
                    <div class="col-auto d-flex justify-content-center">
                        <div class="vr-full"></div>
                    </div>

                    <!-- Right -->
                    <div class="col text-end d-flex flex-column justify-content-start">
                        <div class="row text-start">
                            <div class="col">
                                <button class="btn btn-sm btn-outline-info border-0 p-0 shadow-none me-3"
                                    onclick="printSelectedReport()" title="Print Collection Report">
                                    <i class="bi bi-printer-fill icon-24px"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-0 shadow-none"
                                    onclick="downloadSelectedReport()" title="Download Collection Report">
                                    <i class="bi bi-file-pdf-fill icon-24px"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row mt-3 text-start">
                            <div class="col">
                                Report
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="col-auto d-flex justify-content-center">
                        <div class="vr-full"></div>
                    </div>

                    <!-- Right -->
                    <div class="col text-end d-flex flex-column justify-content-start">
                        <div class="row text-start">
                            <div class="col">
                                <button class="btn btn-sm btn-outline-secondary border-0 p-0 shadow-none me-3"
                                    onclick="printSelectedReport()" title="Print Collection Report">
                                    <i class="bi bi-chat-square-text icon-24px"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary border-0 p-0 shadow-none"
                                    onclick="downloadSelectedReport()" title="Download Collection Report">
                                    <i class="bi bi-envelope icon-24px"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row mt-3 text-start">
                            <div class="col">
                                Message
                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>


    <div class="row">

        <div class="col-md-12 ">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="fw-bold  text-center text-info">Attendance Register</h5>
                </div>
                <div class="form-group row">
                    <div class="col-12 ">
                        <div class=" table-responsive " id="data-body-main">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>





<?php require_once 'footer.php'; ?>

<script>

    function defaultLoadScript() {

        // Get all filter values
        let slot = $('#slot-main').val();
        let year = $('#session-main').val();
        let cls = $('#class-main').val();
        let sec = $('#section-main').val();
        let dateFrom = $('#date-from-main').val();
        let dateTo = $('#date-to-main').val();
        let collector = $('#collector-main').val();

        // Use object instead of query string
        let infor = {
            slot: slot,
            year: year,
            cls: cls,
            sec: sec,
            dateFrom: dateFrom,
            dateTo: dateTo
        };


        $("#data-body-main").html('<tr><td colspan="4" class="text-center text-info pt-5">Retrieve Dues List ...</td></tr>');

        $.ajax({
            type: "POST",
            url: "attendance/fetch-attnd-register.php",
            data: infor,
            cache: false,
            success: function (html) {
                $("#data-body-main").html(html);
                let a = $('#motmot').text();
                $('#totalAmont').val(a);
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                $("#data-body-main").html('<tr><td colspan="4" class="text-center text-danger pt-5">Failed to retrieve data!</td></tr>');
            }
        });
    }


    $(function () {
        function tryGetList() {
            let cls = $('#class-main').val();
            let sec = $('#section-main').val();
            if (cls && sec) {
                defaultLoadScript();
            }
        }

        $('#class-main').on('change', function () {
            setTimeout(tryGetList, 500);
        });

        $('#section-main').on('change', function () {
            tryGetList();
        });

    });

    $(document).on('click', '.btn-chain', function () {
        defaultLoadScript();
    });
</script>
<script>
    $(document).ready(function () {
        $('.data-table').DataTable({
            pageLength: 10,          // প্রতি পেজে কয়টা row
            lengthChange: true,      // dropdown show
            searching: true,         // search box
            ordering: true,          // sorting
            info: true,              // "Showing x to y"
            paging: true,            // pagination
            responsive: true,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ rows",
                info: "Showing _START_ to _END_ of _TOTAL_ rows",
                paginate: {
                    previous: "Prev",
                    next: "Next"
                }
            }
        });
    });
</script>

<script>
    function chainBtnFunc() {
        window.location.reload();
    }
</script>

<!-- ---------------------------- CHAIN -------------- -->

<script>
    function getReportUrl(type) {
    let slot = $('#slot-main').val();
    let year = $('#session-main').val();
    let cls = $('#class-main').val();
    let sec = $('#section-main').val();
    let dateFrom = $('#date-from-main').val();
    let dateTo = $('#date-to-main').val();

    // প্যারামিটার তৈরি করা
    let params = `?slot=${slot}&year=${year}&cls=${cls}&sec=${sec}&dateFrom=${dateFrom}&dateTo=${dateTo}&type=${type}`;
    return "attendance/attendance-report-print.php" + params;
}

function printSelectedReport() {
    let url = getReportUrl('print');
    window.open(url, '_blank');
}

function downloadSelectedReport() {
    let url = getReportUrl('pdf');
    
    // একটি অদৃশ্য লিঙ্ক তৈরি করে ক্লিক করানো
    let link = document.createElement('a');
    link.href = url;
    link.target = '_blank'; // যদি নতুন ট্যাবে চান
    link.download = 'Attendance_Report.pdf'; // ডাউনলোডের জন্য সাজেস্টেড নাম
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<!-- ----------------------------------- -->
</body>

</html>